<?php

namespace Soluble\Spreadsheet\Library;

use ExcelBook;
use ExcelFormat;

class LibXL
{

    const FILE_FORMAT_XLS = 'xls';
    const FILE_FORMAT_XLSX = 'xlsx';

    /**
     *
     * @var array|null
     */
    static protected $default_license;

    /**
     *
     * @var array|null
     */
    protected $license;
    
    /**
     *
     * @var array
     */
    protected static $supportedFormats = array(
        self::FILE_FORMAT_XLS,
        self::FILE_FORMAT_XLSX
    );

    /**
     *
     * @throws Exception\InvalidArgumentException
     * @param array $license associative array with 'name' and 'key'
     */
    public function __construct(array $license = null)
    {

        if ($license !== null) {
            $this->setLicense($license);
        }
    }

    /**
     * Return an empty ExcelBook instance
     *
     * @throws Exception\RuntimeException if no excel extension is found
     * @throws Exception\InvalidArgumentException if unsupported format
     *
     * @param string $file_format by default xlsx, see constants FILE_FORMAT_*
     * @param string $locale by default utf-8
     * @return ExcelBook
     */
    public function getExcelBook($file_format = self::FILE_FORMAT_XLSX, $locale = 'UTF-8')
    {
        //@codeCoverageIgnoreStart
        if (!extension_loaded('excel')) {
            throw new Exception\RuntimeException(__METHOD__ . ' LibXL requires excel extension (https://github.com/iliaal/php_excel) and http://libxl.com/.');
        }
        //@codeCoverageIgnoreEnd
        if (!$this->isSupportedFormat($file_format)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Unsupported file format '$file_format'.");
        }

        $license = $this->getLicense();
        $license_name = $license['name'];
        $license_key = $license['key'];
        $excel2007 = true;
        switch ($file_format) {
            case self::FILE_FORMAT_XLS:
                $excel2007 = false;
                break;
        }

        $book = new ExcelBook($license_name, $license_key, $excel2007);
        if ($locale !== null) {
            $book->setLocale($locale);
        }
        return $book;
    }

    /**
     * Return libxl license
     *
     * @return array|null
     */
    public function getLicense()
    {
        if ($this->license === null) {
            return self::getDefaultLicense();
        }
        return $this->license;
    }

    /**
     * Check whether the format is supported
     *
     * @throws Exception\InvalidArgumentException
     * @param string $format
     * @return boolean
     */
    public static function isSupportedFormat($format)
    {
        if (!is_string($format)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " file_format must be a string");
        }
        return in_array((string) $format, self::$supportedFormats);
    }

    /**
     * Set license
     *
     * @throws Exception\InvalidArgumentException
     * @param array $license associative array with 'name' and 'key'
     */
    public function setLicense(array $license)
    {
        if (!array_key_exists('name', $license) || !array_key_exists('key', $license)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " In order to set a libxl license you must provide an associative array with 'name' and 'key' set.");
        }
        $this->license = $license;
    }

    /**
     * Return default license information
     *
     * @return array|null
     */
    public static function getDefaultLicense()
    {
        return self::$default_license;
    }

    /**
     * Set default license information
     *
     * @throws Exception\InvalidArgumentException
     * @param array $license associative array with 'name' and 'key'
     */
    public static function setDefaultLicense(array $license)
    {
        if (!array_key_exists('name', $license) || !array_key_exists('key', $license)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " In order to set a default libxl license you must provide an associative array with 'name' and 'key' set.");
        }

        self::$default_license = $license;
    }

    /**
     * Unset default license, useful for unit tests only
     *
     */
    public static function unsetDefaultLicense()
    {
        self::$default_license = null;
    }
}
