<?php
namespace Soluble\FlexStore\Writer;
use Soluble\FlexStore\Writer\AbstractWriter;
use Soluble\FlexStore\Writer\Exception;

class CSV extends AbstractWriter
{
    const SEPARATOR_TAB = "\t";
    const SEPARATOR_COMMA = ',';
    const SEPARATOR_SEMICOLON = ';';
    const SEPARATOR_NEWLINE_UNIX = "\n";
    const SEPARATOR_NEWLINE_WIN = "\r\n";

    /**
     * @var array
     */
    protected $options = array(
        'field_separator' => ";",
        'line_separator' => "\n",
        'enclosure' => '',
        'charset' => 'UTF-8',
        'escape' => '\\'
    );


    /**
     * @throws Exception\CharsetConversionException
     * @return string csv encoded data
     */
    public function getData()
    {

// TODO - TEST database connection charset !!!
//
        
        ini_set("default_charset", 'UTF-8');
        if (version_compare(PHP_VERSION, '5.6.0', '<')) {        
            iconv_set_encoding('internal_encoding', 'UTF-8');
        }         
/*
        $backup_encoding = iconv_get_encoding("internal_encoding");
        iconv_set_encoding("internal_encoding", "UTF-8");
        iconv_set_encoding("input_encoding", "UTF-8");
        iconv_set_encoding("output_encoding", "UTF-8");
        mb_internal_encoding("UTF-8");
*/


        $csv = '';
        $data = $this->source->getData()->toArray();
//echo "éééééààà";
//	var_dump($data); die();
        if (count($data) == 0) {
            return $data;
        }

        //$internal_encoding = strtoupper(iconv_get_encoding('internal_encoding'));
        $internal_encoding = strtoupper(ini_get('default_charset'));


        $charset = strtoupper($this->options['charset']);


        $header_line = join($this->options['field_separator'], array_keys($data[0]));
        $csv .= $header_line . $this->options['line_separator'];


        foreach ($data as $row) {

            switch ($this->options['field_separator']) {
                case self::SEPARATOR_TAB:
                    array_walk($row, array($this, 'escapeTabDelimiter'));
                    break;
                default:
                    array_walk($row, array($this, 'escapeFieldDelimiter'));

            }

            array_walk($row, array($this, 'escapeLineDelimiter'));

            if ($this->options['enclosure'] != '') {
                array_walk($row, array($this, 'addEnclosure'));
            }

            $line = join($this->options['field_separator'], $row);


            if ($charset != $internal_encoding) {

                if (!function_exists('iconv')) {
                    throw new Exception\RuntimeException('CSV writer requires iconv extension');
                }

                $l = (string) $line;
                if ($l != '') {
                    $l = iconv($internal_encoding, $this->options['charset'] . "//TRANSLIT//IGNORE", $l);

                    if ($l === false) {
                        throw new Exception\CharsetConversionException("Cannot convert the charset to '" . $this->options['charset'] . "' from charset '$internal_encoding', value: '$line'.");
                    } else {
                        $line = $l;
                    }
                }
            }

            $csv .= $line . $this->options['line_separator'];
        }
        return $csv;
    }


    /**
     *
     * @param SendHeaders $headers
     * @return void
     */
    public function send(SendHeaders $headers=null)
    {
        if ($headers === null) $headers = new SendHeaders();
        ob_end_clean();
        //Content-Type: text/csv; name="filename.csv"
        //Content-Disposition: attachment; filename="filename.csv"

        $headers->setContentType('text/csv; charset=' . $this->options['charset']);
        $headers->printHeaders();
        $json = $this->getData();
        echo $json;
    }

    /**
     *
     * @param string $item
     * @return void
     */
    protected function escapeLineDelimiter(&$item)
    {
        $item = str_replace(self::SEPARATOR_NEWLINE_WIN, " ", $item);
        $item = str_replace(self::SEPARATOR_NEWLINE_UNIX, " ", $item);
    }

    /**
     *
     * @param string $item
     * @return void
     */
    protected function escapeTabDelimiter(&$item)
    {
        $item = str_replace("\t", " ", $item);
    }

    /**
     *
     * @param string $item
     * @param string $key
     * @return void
     */
    protected function escapeFieldDelimiter(&$item)
    {
        $item = str_replace($this->options['field_separator'], $this->options['escape'] . $this->options['field_separator'], $item);
    }


    /**
     *
     * @param string $item
     * @param string $key
     * @return void
     */
    protected function addEnclosure(&$item, $key)
    {
        $enc = $this->options['enclosure'];
        $escape = $this->options['escape'];
        if ($escape == '') {
            $item = $enc . str_replace($enc, '', $item) . $enc;
        } else {
            $item = $enc . str_replace($enc, $escape . $enc, $item) . $enc;
        }
    }
}
