<?php

namespace Soluble\FlexStore\Writer\Excel;

use Soluble\FlexStore\Writer\AbstractSendableWriter;
use Soluble\FlexStore\Writer\Exception;
;
use Soluble\Spreadsheet\Library\LibXL;
use Soluble\FlexStore\Writer\Http\SimpleHeaders;
use Soluble\FlexStore\Column\Type;
use Soluble\FlexStore\Options;
use ExcelBook;
use ExcelFormat;

class LibXLWriter extends AbstractSendableWriter
{

    /**
     *
     * @var SimpleHeaders
     */
    protected $headers;
    protected $column_width_multiplier = 1.7;

    /**
     *
     * @var array
     */
    protected static $default_license;

    /**
     *
     * @var ExcelBook
     */
    protected $excelBook;

    /**
     *
     * @var string
     */
    protected $file_format = LibXL::FILE_FORMAT_XLSX;
    
    /**
     * Set file format (xls, xlsx), default is xlsx
     *
     * @param string $file_format
     * @return LibXLWriter
     * @throws Exception\InvalidArgumentException
     */
    public function setFormat($file_format)
    {
        if (!LibXL::isSupportedFormat($file_format)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Unsupported format given '$file_format'");
        }
        $this->file_format = $file_format;
        return $this;
    }
    
    /**
     *
     * @param string $locale
     * @param string $file_format
     * @return ExcelBook
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function getExcelBook($locale = 'UTF-8', $file_format = null)
    {
        if (!extension_loaded('excel')) {
            throw new Exception\RuntimeException(__METHOD__ . ' LibXLWriter requires excel (php_exccel) extension to be loaded');
        }
        
        if ($this->excelBook === null) {
            $libXL = new LibXL();
            if (is_array(self::$default_license)) {
                $libXL->setLicense(self::$default_license);
            }
            if ($file_format === null) {
                $file_format = $this->file_format;
            } elseif (!LibXL::isSupportedFormat($file_format)) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Unsupported format given '$file_format'");
            }
            
            $this->excelBook = $libXL->getExcelBook($file_format, $locale);
        }
        return $this->excelBook;
    }

    /**
     * @param Options $options
     * @return string
     */
    public function getData(Options $options = null)
    {
        
        $book = $this->getExcelBook();
        $this->generateExcel($book, $options);
        //$book->setLocale($locale);
        $filename = tempnam('/tmp', 'libxl');

        $book->save($filename);

        $data = file_get_contents($filename);
        unlink($filename);
        return $data;
    }

    /**
     *
     * @param ExcelBook $book
     * @param Options $options
     * @return ExcelBook
     */
    protected function generateExcel(ExcelBook $book, Options $options = null)
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
        $cm = $this->store->getColumnModel();
        $columns = $cm->getColumns();
        

        $formats = array();
        $types = array();
        $column_max_widths = array();
        foreach ($columns as $name => $column) {
            $header = $name;
            if (!array_key_exists($name, $column_max_widths)) {
                $column_max_widths[$name] = 0;
            }
            $column_max_widths[$name] = max(strlen($header) * $this->column_width_multiplier, $column_max_widths[$name]);

            switch ($column->getType()) {
                case Type::TYPE_DATE:
                    $mask = 'd/mm/yyyy';
                    $cfid = $book->addCustomFormat($mask);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);
                    $formats[$name] = $format;
                    $types[$name] = 'date';
                    break;
                case Type::TYPE_DATETIME:
                    $mask = 'd/mm/yyyy h:mm';
                    $cfid = $book->addCustomFormat($mask);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);
                    $formats[$name] = $format;
                    $types[$name] = 'datetime';
                    break;
                case Type::TYPE_INTEGER:

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
                case Type::TYPE_DECIMAL:
                    //$precision = $definition->getNumericPrecision();
                    $hide_thousands_separator = true;
                    if ($hide_thousands_separator) {
                        $formatString = '0.';
                    } else {
                        $formatString = '#,##0.';
                    }
                    /*
                    if ($precision > 0) {
                        $zeros = str_repeat("0", $precision);
                        $formatString = $formatString . '.' . $zeros;
                    }*/
                    
                    $cfid = $book->addCustomFormat($formatString);
                    $format = $book->addFormat();
                    $format->numberFormat($cfid);
                    $formats[$name] = $format;
                    $types[$name] = 'number';
                    break;
            }

            $sheet->write($row = 0, $col_idx, $header, $headerFormat);
            $col_idx++;
        }

        $sheet->setRowHeight(0, 30);

        // fixed header
        $sheet->splitSheet(1, 0);


        // Fill document content
        $data = $this->store->getData($options);

        foreach ($data as $idx => $row) {
            $col_idx = 0;
            $row_idx = $idx + 1;
            foreach ($columns as $name => $column) {
                $value = $row[$name];
                if (array_key_exists($name, $formats)) {
                    $format = $formats[$name];
                    switch ($types[$name]) {
                        case 'number':
                            $sheet->write($row_idx, $col_idx, (string) $value, $format, ExcelFormat::AS_NUMERIC_STRING);
                            break;
                        case 'date':
                        case 'datetime':
                            if ($value != '') {
                                $time = strtotime($value);
                            } else {
                                $time = null;
                            }
                            $sheet->write($row_idx, $col_idx, $time, $format, ExcelFormat::AS_DATE);
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

        foreach (array_values($column_max_widths) as $idx => $width) {
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
     * @param string $license_name
     * @param string $license_key
     */
    public static function setDefaultLicense($license_name, $license_key)
    {

        self::$default_license = array('name' => $license_name, 'key' => $license_key);
    }

    /**
     * Return default headers for sending store data via http
     * @return SimpleHeaders
     */
    public function getHttpHeaders()
    {
        if ($this->headers === null) {
            $this->headers = new SimpleHeaders();
            $this->headers->setContentType('application/excel', 'utf-8');
            $this->headers->setContentDispositionType(SimpleHeaders::DIPOSITION_ATTACHEMENT);
        }
        return $this->headers;
    }
}
