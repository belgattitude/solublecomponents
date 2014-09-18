<?php
namespace Soluble\FlexStore\Writer\Excel;
use Soluble\FlexStore\Writer\AbstractWriter;

use Soluble\Spreadsheet\Library\LibXL;

use Soluble\FlexStore\Writer\SendHeaders;
use Soluble\Db\Metadata\Column;
use ExcelBook;
use ExcelFormat;



class LibXLWriter extends AbstractWriter
{
    protected $column_width_multiplier = 1.7;


    /**
     *
     * @var string
     */
    protected static $default_license;
    
    /**
     *
     * @var ExcelBook
     */
    protected $excelBook;
    
    
    /**
     * @var array
     */
    private static $typesMap = array(

        Column\Type::TYPE_INTEGER	=> 'Definition\IntegerColumn',
        Column\Type::TYPE_DECIMAL	=> 'Definition\DecimalColumn',
        Column\Type::TYPE_STRING	=> 'Definition\StringColumn',
        Column\Type::TYPE_BOOLEAN	=> 'Definition\BooleanColumn',
        Column\Type::TYPE_DATETIME	=> 'Definition\DatetimeColumn',
        Column\Type::TYPE_BLOB		=> 'Definition\BlobColumn',
        Column\Type::TYPE_DATE		=> 'Definition\DateColumn',
        Column\Type::TYPE_TIME		=> 'Definition\TimeColumn',
        Column\Type::TYPE_FLOAT     => 'Definition\FloatColumn',

    );    

    /**
     * 
     * @param string $locale
     * @param string $file_format
     * @return ExcelBook
     * @throws Exception\RuntimeException
     */
    public function getExcelBook($locale='UTF-8', $file_format=LibXL::FILE_FORMAT_XLSX)
    {
        if (!extension_loaded('excel')) {
            throw new Exception\RuntimeException(__METHOD__ . ' LibXLWriter requires excel (php_exccel) extension to be loaded');
        }
        
        if ($this->excelBook === null) {
            $libXL = new LibXL();
            if (is_array(self::$default_license)) {
                $libXL->setLicense(self::$default_license);
            }
            $this->excelBook = $libXL->getExcelBook($locale, $file_format);
        }
        return $this->excelBook;
    }
    

    /**
     *
     * @return string
     */
    public function getData()
    {
        $book = $this->getExcelBook();
        $this->generateExcel($book);
        //$book->setLocale($locale);
        $filename = tempnam('/tmp', 'libxl');

        $book->save($filename);

        $data =  file_get_contents($filename);
        unlink($filename);
        return $data;
    }


    protected function generateExcel(ExcelBook $book)
    {
        $sheet = $book->addSheet("Sheet");




        // Font selection
        $headerFont = $book->addFont();
        $headerFont->name("Tahoma");
        $headerFont->size(12);
        $headerFont->color(ExcelFormat::COLOR_WHITE);

        $headerFormat = $book->addFormat();

        $headerFormat->setFont($headerFont);

        $headerFormat->borderStyle(ExcelFormat::BORDERSTYLE_THIN);
        $headerFormat->verticalAlign(ExcelFormat::ALIGNV_CENTER);
        $headerFormat->borderColor(ExcelFormat::COLOR_GRAY50);
        //$headerFormat->patternBackgroundColor(ExcelFormat:COLOR_LIGHTBLUE);
        $headerFormat->patternForegroundColor(ExcelFormat::COLOR_LIGHTBLUE);
        $headerFormat->fillPattern(ExcelFormat::FILLPATTERN_SOLID);


        // print header
        $col_idx = 0;
        $cm = $this->source->getColumnModel();
        $columns = $cm->getColumns();

        $formats = array();
        $types   = array();
        $column_max_widths = array();
        foreach($columns as $name => $definition) {
            $header = $name;
            $column_max_widths[$name] = max(strlen($header) * $this->column_width_multiplier, $column_max_widths[$name]);
            
            $datatype = $definition->getDataType();
            switch ($datatype) {
                case Column\Type::TYPE_DATE :
					$mask = 'd/mm/yyyy';
                    $cfid = $book->addCustomFormat($mask);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);				
                    $formats[$name] = $format;    
                    $types[$name] = 'date';
                    break;
                case Column\Type::TYPE_DATETIME:
					$mask = 'd/mm/yyyy h:mm';
                    $cfid = $book->addCustomFormat($mask);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);				
                    $formats[$name] = $format;    
                    $types[$name] = 'datetime';
                    break;
                case Column\Type::TYPE_INTEGER:
                    
                    $hide_thousands_separator = true;
                    if ($hide_thousands_separator) {
                        $formatString = '0';
                    } else {
                        $formatString = '#,##0';
                    }
                    $cfid = $book->addCustomFormat($formatString);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);				
                    $formats[$name] = $format;                    
                    $types[$name] = 'number';
                    break;
                case Column\Type::TYPE_DECIMAL:
                    $precision = $definition->getNumericPrecision();
                    $hide_thousands_separator = true;
                    if ($hide_thousands_separator) {
                        $formatString = '0';
                    } else {
                        $formatString = '#,##0';
                    }
                    if ($precision > 0) {
                        $zeros = str_repeat("0", $precision);
                        $formatString = $formatString . '.' . $zeros;
                    }
                    $cfid = $book->addCustomFormat($formatString);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);				
                    $formats[$name] = $format;                    
                    $types[$name] = 'number';
                    break;
                    
            }
            
            $sheet->write($row=0, $col_idx, $header, $headerFormat);
            $col_idx++;
        }

        $sheet->setRowHeight(0, 30);

        // Fill document content
        $data = $this->source->getData();

        foreach($data as $idx => $row) {
            $col_idx = 0;
            $row_idx = $idx + 1;
            foreach ($columns as $name => $definition) {
                $value = $row[$name];
                if (array_key_exists($name, $formats)) {
                    $format = $formats[$name];
                    switch ($types[$name]) {
                        case 'number' :
                            $sheet->write($row_idx, $col_idx,  (string) $value, $format, ExcelFormat::AS_NUMERIC_STRING);
                            break;
                        case 'date' :
                        case 'datetime' :    
                            if ($value != '') {
                                $time = strtotime($value);
                            } else {
                                $time = null;
                            }
                            $sheet->write($row_idx, $col_idx,  $time, $format, ExcelFormat::AS_DATE);
                            break;
                        default:
                            $sheet->write($row_idx, $col_idx, $value);
                    }
                } else {
                    $sheet->write($row_idx, $col_idx, $value);
                }
                
                $column_max_widths[$name] = max(strlen($value) * $this->column_width_multiplier, $column_max_widths[$name]);
                $col_idx++;
            }
        }

        foreach(array_values($column_max_widths) as $idx => $width) {
            $sheet->setColWidth($idx, ceil($idx), $width);
        }

        $sheet->setPrintGridlines(true);
        //$sheet->setPrintRepeatRows(1, 2);
        //$sheet->setPrintHeaders(true);
        //$sheet->setVerPageBreak($col_idx, true);

        return $book;

    }



    /**
     *
     * @param \Soluble\FlexStore\Writer\SendHeaders $headers
     */
    public function send(SendHeaders $headers=null)
    {
        if ($headers === null) $headers = new SendHeaders();
        ob_end_clean();
        $headers->setContentType('application/excel; charset=utf-8');
        $headers->printHeaders();
        $json = $this->getData();
        echo $json;
    }

    /**
     *
     * @param string $license_name
     * @param string $license_key
     */
    public static function setDefaultLicense($license_name, $license_key)
    {

        self::$default_license = array('name' => $license_name, 'key' => $license_key);
    }

}
