<?php
namespace Soluble\FlexStore\Writer\Excel;
use Soluble\FlexStore\Writer\AbstractWriter;

use Soluble\FlexStore\Writer\SendHeaders;
use ExcelBook;
use ExcelFormat;

class LibXLWriter extends AbstractWriter
{
    protected $column_width_multiplier = 1.7;

    /**
     *
     * @var string
     */
    protected static $license_name;

    /**
     *
     * @var string
     */
    protected static $license_key;


    /**
     *
     * @return string
     */
    public function getData()
    {
        if (!extension_loaded('excel')) {
            throw new Exception\RuntimeException(__METHOD__ . ' LibXLWriter requires excel (php_exccel) extension to be loaded');
        }
        $license_name = self::$license_name;
        $license_key = self::$license_key;
        $book = new ExcelBook($license_name, $license_key, $excel2007=true);
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

        $column_max_widths = array();
        foreach($columns as $name => $definition) {

            $header = $name;

            $column_max_widths[$key] = max(strlen($header) * $this->column_width_multiplier, $column_max_widths[$key]);
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

                $sheet->write($row_idx, $col_idx, $value);

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
    public static function setLicense($license_name, $license_key)
    {

        self::$license_name = $license_name;
        self::$license_key = $license_key;
    }

}
