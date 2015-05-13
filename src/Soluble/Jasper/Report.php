<?php
namespace Soluble\Jasper;

// Report

$jasperReport = new Report();

$ds = Datasource::getSource("mysql://");


$jasperReport->setDatasource($ds);
$jasperReport->setReportFile();


$jasperReport->setDBConnection($dsn)

class Report
{

    /**
     * @var Java("java.util.HashMap")
     */
    protected $params;

    /**
     * @var Java("java.sql.Connection")
     */
    protected $conn;

    /**
     * Path to directory where temporary report files will be stored.
     * @var string
     */
    protected $temp_dir;

    /**
     *
     * @var Zend_Locale
     */
    protected $locale;

    /**
     * JRXML file
     * @var string
     */
    protected $file;

    
    /**
     * @see http://jasperreports.sourceforge.net/api/net/sf/jasperreports/engine/JRDataSource.html
     * @var Java("net.sf.jasperreports.JRDataSource")
     */
    protected $jrDataSource;
    
    /**
     * Constructor
     *
     * <code>
     *
     * $dsn = Vision_Db_Util::getJdbcDsnFromDoctrine(Doctrine_Manager::connection());
     *
     * try {
     *  $report = new Vision_Report();
     *  $report->setDbConnection($dsn)
     *        ->setLocale(new Zend_Locale('en_GB')
     *        ->setJRXMLFile('/tmp/test.jrxml');
     * } catch (Exception $e) {
     *   // Do your stuff here
     * }
     *
     * $file = $report->getPDFReport();
     * $pdf_content = file_get_contents($file);
     *
     * </code>
     */
    public function __construct($bridge_url = null)
    {
        $this->temp_dir = realpath(APPLICATION_PATH . "/../data/reports");

        if (!$this->temp_dir) {
            throw new Exception('Config error, temp dir must exists :' . APPLICATION_PATH . "/../data/reports");
        }

        if (is_null($bridge_url)) {
            $bridge_url = "http://localhost:8082/JavaBridge/java/Java.inc";
        }
        $this->getJavaBridge($bridge_url);

        $this->params = new Java("java.util.HashMap");

        if (Zend_Registry::isRegistered('Zend_Locale')) {
            $this->locale = Zend_Registry::get('Zend_Locale');
        } else {
            $this->locale = new Zend_Locale('en_GB');
        }
        $this->ds_type = self::DS_NON;
    }

    /**
     * Set the jrxml file
     *
     * @throws Vision_Report_Exception if file is not readable or not exists
     *
     * @param string $file
     * @return Vision_Report
     */
    function setJRXmlFile($file)
    {
        if (!file_exists($file)) {
            throw new Vision_Report_Exception("File $file does not exists");
        } if (!is_file($file)) {
            throw new Vision_Report_Exception("File $file is not a file");
        }

        $this->file = realpath($file);
        return $this;
    }

    /**
     * Sets report locale, if not defined default to
     * Zend_Registry::get('Zend_Locale');
     *
     * @throws Zend_Exception if locale is not supported
     *
     * @param string|Zend_Locale $locale
     * @return Vision_Report
     */
    function setLocale($locale)
    {
        if (is_string($locale)) {
            $locale = new Zend_Locale($locale);
        }
        $this->locale = $locale;
        return $this;
    }

    /**
     * Add custom report params
     *
     * @throws Vision_Report_Exception
     *
     * @param array $in_params Params to pass to the report generator.
     * @params boolean $convert Whether or not to convert the value of the parameter to java.
     * @return Vision_Report
     */
    function addParams($in_params, $convert = false)
    {
        if (!is_array($in_params)) {
            throw new Exception("Parameters must be an array");
        }
            
        try {
            foreach ($in_params as $key => $val) {
                $this->addParam($key, $val);
            }
        } catch (JavaException $je) {
            throw new Vision_Report_Exception($je->getCause());
        }
        return $this;
    }

    /**
     * Add/set a param to a jasper report
     *
     * @throws Vision_Report_Exception
     *
     * @param string $key
     * @param mixed $value
     * @return Vision_Report
     */
    function addParam($key, $value)
    {
        if (!is_string($key) || trim($key) == '') {
            throw new Exception("Cannot add param to report : key param must be a valid string ($key)");
        }
        $this->params->put($key, $value);
        return $this;
    }

    function getParams()
    {
        $params = array();
        foreach ($this->params->keySet() as $k) {
            $k = (string) $k;
            $v = $this->params->get($k);
            $params[$k] = (string) $v;
        }
        return $params;
    }
    
    /**
     * Set the datasource name of the db connection
     * The datadource name must conform to JDBC specs
     *
     * <code>
     * $dsn = Vision_Db_Util::getJdbcDsnFromDoctrine(Doctrine_Manager::connection());
     * $report = new Vision_Report();
     * $report->setDbConnection($dsn);
     * </code>
     *
     * @throws Vision_Report_Exception
     *
     * @see Vision_Db_Util::getJdbcDsnFromDoctrine
     *
     * @param string $dsn
     * @return Vision_Report
     */
    function setDBConnection($dsn, $driverClass = 'com.mysql.jdbc.Driver')
    {

        try {
            $class = new JavaClass("java.lang.Class");
            $class->forName($driverClass);
            $driverManager = new JavaClass("java.sql.DriverManager");
            $this->conn = $driverManager->getConnection($dsn);
            $this->ds_type = self::DS_DB;
        } catch (JavaException $je) {
            throw new Vision_Report_Exception($je->getCause());
        }
        return $this;
    }

    /**
     * @see http://jasperreports.sourceforge.net/api/index.html?net/sf/jasperreports/engine/JasperFillManager.html
     *
     * @param unknown $xmlFileName local or url
     *
     * @param unknown $xpathExpression
     * @throws Vision_Report_Exception
     *
     * @return Vision_Report
     */
    function setXMLDataSource($xmlFileName, $xpathExpression)
    {

        try {
            $jrxmlds = new Java("net.sf.jasperreports.engine.data.JRXmlDataSource", $xmlFileName, $xpathExpression);
            
            $this->jrDataSource = $jrxmlds;

            $this->ds_type = self::DS_XML;

        } catch (JavaException $je) {
            throw new Vision_Report_Exception($je->getCause());
        }

        return $this;
    }
    
    function setJsonDataSource($fileName, $selectExpression)
    {
    
        try {
            $this->jrDataSource = new Java("net.sf.jasperreports.engine.data.JsonDataSource", $fileName, $selectExpression);
    
            $this->ds_type = self::DS_XML;
    
        } catch (JavaException $je) {
            throw new Vision_Report_Exception($je->getCause());
        }
    
        return $this;
    }
    
    
    /**
     * Sets the CSV object that will be used as datasource.
     *
     * @throws Exception
     * @param string $path
     * @params array $params array(field_delimiter, record_delimiter, first_row_as_header]
     * @return Vision_Report
     */
    function setCSV($path, $params = null)
    {

        if (array_key_exists('first_row_as_header', $params)) {
            $first_row_as_header = $params['first_row_as_header'];
        } else {
            $first_row_as_header = self::CSV_DEFAULT_FIRST_ROW_AS_HEADER;
        }

        if (array_key_exists('field_delimiter', $params)) {
            $field_delimiter = $params['field_delimiter'];
        } else {
            $field_delimiter = self::CSV_DEFAULT_FIELD_DELIMITER;
        }

        if (array_key_exists('record_delimiter', $params)) {
            $record_delimiter = $params['record_delimiter'];
        } else {
            $record_delimiter = self::CSV_DEFAULT_RECORD_DELIMITER;
        }

        $path = realpath($path);
        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception("CSV file $path not readable or not existing.");
        }

        $file = new Java("java.io.File", $path);
        $csv_data_source = new Java("net.sf.jasperreports.engine.data.JRCsvDataSource", $file);
        $csv_data_source->setFieldDelimiter($field_delimiter);
        $csv_data_source->setRecordDelimiter($record_delimiter);

        if (!is_null($params)) {
            //Setting params
            if ($params["first_row_as_header"] === true) {
                $csv_data_source->setUseFirstRowAsHeader(true);
            }
        }

        $this->csv_object = $csv_data_source;
        $this->ds_type = self::DS_CSV;
        return $this;
    }

    /**
     * @throws Exception
     * @return JavaClass("net.sf.jasperreports.engine.JasperReport")
     */
    function compileReport()
    {

        if ($this->file == null) {
            throw new Exception("JRXML file has not been set");
        }
        try {
            $compileManager = new JavaClass("net.sf.jasperreports.engine.JasperCompileManager");
            $compiledReport = $compileManager->compileReport($this->file);

            //$a = $compileManager->compileReportToFile($this->file, "/tmp/jasper.jasper");
        } catch (JavaException $je) {
            throw new Exception($je->getCause());
        }
        return $compiledReport;
    }

    /**
     * @throws Exception
     * @param JavaClass("net.sf.jasperreports.engine.JasperReport") $report
     * @return JavaClass("net.sf.jasperreports.engine.JasperPrint")
     */
    function fillReport($report)
    {
        if ($this->ds_type == self::DS_NON) {
            throw new Exception("No datasource provided yet.");
        }

        $locale = $this->convertValue($this->locale, "java.util.Locale");
        $this->params->put('REPORT_LOCALE', $locale);

        // class Loader
        $jpath = new Java("java.io.File", dirname($this->file));
        $url = $jpath->toUrl(); // Java.net.URL
        $urls = array($url);
        
        
        $classLoader = new Java('java.net.URLClassLoader', $urls);
        $this->params->put('REPORT_CLASS_LOADER', $classLoader);

        // Setting the class loader for the resource bundle
        // Assuming they are in the same directory as
        // the report file.
        $report_resource_bundle = $report->getResourceBundle();
        if ($report_resource_bundle != '') {
            $ResourceBundle = new JavaClass("java.util.ResourceBundle");
            $rb = $ResourceBundle->getBundle($report_resource_bundle, $locale, $classLoader);
            $this->params->put('REPORT_RESOURCE_BUNDLE', $rb);
        }
        
        try {
            $fillManager = new JavaClass("net.sf.jasperreports.engine.JasperFillManager");

            if ($this->ds_type == self::DS_DB) {
                $filledReport = $fillManager->fillReport($report, $this->params, $this->conn);
            } elseif ($this->ds_type == self::DS_CSV) {
                $filledReport = $fillManager->fillReport($report, $this->params, $this->csv_object);
            } elseif ($this->jrDataSource) {
                $filledReport = $fillManager->fillReport($report, $this->params, $this->jrDataSource);
            } else {
                throw new Exception("Internal error: Datasource type ({$this->ds_type}) not recognized.");
            }
            
        } catch (JavaException $je) {
            throw new Exception($je->getCause());
        }


        return $filledReport;
    }

    /**
     * @throws Exception
     * @param JavaClass("net.sf.jasperreports.engine.JasperPrint") $filledReport
     * @param string $filename
     * @param string $outputPath
     * @return string
     */
    function exportToPdf($filledReport, $outputPath = null)
    {

        try {
            if (!$outputPath) {
                $outputPath = tempnam($this->temp_dir, null);
            }
            // tempnam function creates automatically a file so we must erase it as a previous step to report generation
            $this->deleteFile($outputPath);


            $exportManager = new JavaClass("net.sf.jasperreports.engine.JasperExportManager");
            $exportManager->exportReportToPdfFile($filledReport, $outputPath);
            $file = new Java("java.io.File", $outputPath);
            if (!$file->setWritable(true, false)) {
                throw new Exception("Java cannot set write permission on file: $outputPath.");
            }
        } catch (JavaException $je) {
            throw new Exception($je->getCause());
        }

        return $outputPath;
    }

    function newExportToPdf($filledReport, $outputPath = null, $params = null)
    {

        try {
            if (!$outputPath) {
                $outputPath = tempnam($this->temp_dir, null);
            } // tempnam function creates automatically a file so we must erase it as a previous step to report generation
            //$this->deleteFile($outputPath);
            elseif (!file_exists($outputPath)) {
                @touch($outputPath);
            }
            $outputPath = realpath($outputPath);

            $file = new Java("java.io.File", $outputPath);
            if (!$file->setWritable(true, false)) {
                throw new Exception("Java cannot set write permission on file: $outputPath.");
            }

            $pdfExporter = new Java("net.sf.jasperreports.engine.export.JRPdfExporter");
            $pdfExporterParameter = new JavaClass("net.sf.jasperreports.engine.export.JRPdfExporterParameter");
            $pdfExporter->setParameter($pdfExporterParameter->JASPER_PRINT, $filledReport);
            $pdfExporter->setParameter($pdfExporterParameter->OUTPUT_FILE, $file);

            if (!is_null($params)) {
                if ($params["password"]) {
                    $pdfExporter->setParameter($pdfExporterParameter->IS_ENCRYPTED, true);
                    $pdfExporter->setParameter($pdfExporterParameter->IS_128_BIT_KEY, true);
                    $pdfExporter->setParameter($pdfExporterParameter->USER_PASSWORD, $params["password"]);
                    $pdfExporter->setParameter($pdfExporterParameter->OWNER_PASSWORD, $params["password"]);
                }
            }

            $pdfExporter->exportReport();
        } catch (JavaException $je) {
            throw new Exception($je->getCause());
        }

        return $outputPath;
    }

    /**
     * Generate (compile/fill) the report
     * and return the filename in which pdf content is available
     *
     * <code>
     * $reportGenerator = new Vision_Report();
     * $params = array('ISSUE_ID' => 2, 'SUBREPORT_DIR' => APPLICATION_PATH . "/modules/qc/views/reports/");
     * $reportGenerator->setParams($params);
     * $file = $reportGenerator->getPDFReport();
     * $pdf_content = file_get_contents($file);
     * </code>
     *
     * @throws Exception
     * @return filename absolute path to the created file.
     */
    function getPDFReport()
    {
        $compiledReport = $this->compileReport();
        $filledReport = $this->fillReport($compiledReport);
        $output = $this->exportToPdf($filledReport);
        return $output;
    }

    /**
     * Send pdf report (flush output with content-type)
     *
     * @throws Exception
     *
     * <code>
     *
     *
     * $reportGenerator = new Vision_Report();
     * $params = array('ISSUE_ID' => 2, 'SUBREPORT_DIR' => APPLICATION_PATH . "/modules/qc/views/reports/");
     * $reportGenerator->setParams($params);
     * $file = $reportGenerator->sendPDFReport('export.pdf');
     *
     * </code>
     *
     * @param string $basename filename that will be generated when user download the report
     * @param boolean $printHeaders print http headers
     * @param boolean $force_download whether to force download
     *
     * @return Vision_Report
     */
    function sendPDFReport($basename, $print_headers = true, $force_download = true)
    {
        $output = $this->getPDFReport();
        $content_size = filesize($output);

        if ($print_headers) {
            Vision_Report_Http::printHTTPHeaders('PDF', $force_download, $basename, $content_size);
        }

        readfile($output);
        $this->deleteFile($output);
        return $this;
    }

    /**
     * Convert a php value to a java one.
     *
     * All types for parameters of jasperreports 3.7.2 are supported except those that are objects (List, Collection, etc.).
     *
     * @param mixed $value
     * @param string $javaClass
     * @return Java
     */
    static function convertValue($value, $javaClass)
    {
        try {
            $numeric_types = array(
                "java.lang.Byte",
                "java.lang.Double",
                "java.lang.Float",
                "java.lang.Integer",
                "java.lang.Long",
                "java.lang.Short",
                "java.math.BigDecimal",
                "java.lang.Number"
            );

            if (in_array($javaClass, $numeric_types, true)) {
                if (!is_numeric($value)) {
                    throw new Exception("Numeric field received not numeric value when converting to Java type (note that decimals are indicated with colons).");
                }
                $result = new Java($javaClass, $value);
            } elseif ($javaClass === 'java.sql.Timestamp' || $javaClass === 'java.sql.Time') {
                $javaObject = new Java($javaClass);
                $result = $javaObject->valueOf($value);
            } elseif ($javaClass === 'java.util.Locale') {
                if (!is_string($value)) {
                    $value = $value->toString();
                }
                $info = explode("_", $value);
                if (count($info) === 1) {
                    $result = new Java($javaClass, $info[0]);
                } elseif (count($info) === 2) {
                    $result = new Java($javaClass, $info[0], $info[1]);
                } else {
                    throw new Exception("Locale $value not supported.");
                }
            } elseif ($javaClass == "java.lang.Boolean") {
                if (!in_array($value, array("1", "0", "", true, false), true)) {
                    throw new Exception("Not valid boolean value when converting to Java type.");
                }
                $result = new Java($javaClass, $value === "1" || $value === true);
            } else {
                $result = new Java($javaClass, $value);
            }

            return $result;
        } catch (JavaException $je) {
            throw new Exception($je->getCause());
        }
    }

    static function getAvailableTemplates()
    {
        $temps = array(
            array('DimitrisModel', "EMD official template"),
            array('QC_Report', "EMD standard template")
        );
        return $temps;
    }

    function deleteFile($path)
    {
        $path = realpath($path);
        if (file_exists($path)) {
            if (!is_writable($path)) {
                throw new Exception("Cannot remove temp report: $path not writable.");
                //Shoud we tell to user the real path?
            }
            @unlink($path);
        }
    }

    /**
     * Include Javabridge
     *
     * @deprecated
     * @see Vision_Java_Bridge
     * @throws Vision_Java_Bridge_NAException
     *
     * @param string $bridge_address hostname:port or ip:port address of bridge
     * @return void
     */
    function getJavaBridge($bridge_address)
    {
        Vision_Java_Bridge::includeBridge($bridge_address);
    }
}
