<?php

namespace Soluble\FlexStore\Writer\Excel;

use Soluble\FlexStore\Source\Zend\SqlSource;
use Soluble\Db\Sql\Select;
use Soluble\FlexStore\Store;
use Zend\Db\Sql\Expression;
use PHPExcel_IOFactory;
use Soluble\Spreadsheet\Library\LibXL;
use Soluble\FlexStore\Formatter;

class LibXLWriterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LibXLWriter
     */
    protected $xlsWriter;

    /**
     * @var SqlSource
     */
    protected $source;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        if (!extension_loaded('excel')) {
            $this->markTestSkipped(
                    "Excel extension not available."
            );
        } else {

            $this->adapter = \SolubleTestFactories::getDbAdapter();

        }
    }

    /**
     * 
     * @return SelectSource
     */
    protected function getTestSource()
    {

        $select = new Select($this->adapter);
        $select->from(array('p' => 'product'), array())
                ->join(array('ppl' => 'product_pricelist'), 'ppl.product_id = p.product_id', array(), Select::JOIN_LEFT)
                ->join(array('p18' => 'product_translation'), new Expression("p.product_id = p18.product_id and p18.lang = 'fr'"), array(), Select::JOIN_LEFT)
                ->limit(100);

        $select->columns(array(
            'test_chars' => new Expression('"french accents éàùêûçâµè and chinese 请收藏我们的网址"'),
            'product_id' => new Expression('p.product_id'),
            'brand_id' => new Expression('p.brand_id'),
            'reference' => new Expression('p.reference'),
            'description' => new Expression('p.description'),
            'volume' => new Expression('p.volume'),
            'weight' => new Expression('p.weight'),
            'barcode_ean13' => new Expression('1234567890123'),
            'created_at' => new Expression('NOW()'),
            'price' => new Expression('ppl.price'),
            'discount_1' => new Expression('ppl.discount_1'),
            'promo_start_at' => new Expression('ppl.promo_start_at'),
            'promo_end_at' => new Expression('cast(NOW() as date)'),
            'title_fr' => new Expression('p18.title'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'"),
            'test_float' => new Expression("1.212")
            
        ));

        $source = new SqlSource($this->adapter, $select);
        return $source;
    }

    protected function tearDown()
    {
        ini_set('error_reporting', E_ALL | E_STRICT);
    }
    
   /**
     * 
     */
    public function testColumnModel()
    { 
        
        $output_file = \SolubleTestFactories::getCachePath() . DIRECTORY_SEPARATOR . 'tmp_phpunit_lbxl_test4.xlsx';        
        
        $store = new Store($this->getTestSource());
        $cm = $store->getColumnModel();
        
        $locale = 'en_US';
        $formatter = Formatter::createFormatter('currency', array(
            'currency_code' => new \Soluble\FlexStore\Formatter\RowColumn('currency_reference'),
            'locale' => $locale
        ));
        
        $cm->search()->regexp('/price/')->setFormatter($formatter);
        
        $formatted_data = $store->getData()->toArray();
        $this->assertEquals('CN¥15.30', $formatted_data[0]['list_price']);

        $xlsWriter = new LibXLWriter();
        $xlsWriter->setStore($store);
        
        $xlsWriter->save($output_file);

        $this->assertFileExists($output_file);
        $filesize = filesize($output_file);
        $this->assertGreaterThan(0, $filesize);

        // test Output

        $arr = $this->excelToArray($output_file);
        
        //$this->assertEquals(113, $arr[5]['B']);
        $this->assertTrue(is_float($arr[2]['O']));
        $this->assertEquals(number_format(15.3,1), number_format($arr[2]['O'], 1));
        $this->assertEquals(number_format(18.2,1), number_format($arr[2]['P'],1));
        $this->assertEquals('CNY', $arr[2]['Q']);
        $this->assertEquals("", $arr[4]['O']);
        $this->assertEquals("", $arr[4]['P']);
        
    }

     
    

    public function testGetDataXlsx()
    {
        //$data = $this->xlsWriter->getData();
        //$this->assertInternalType('string', $data);
        $output_file = \SolubleTestFactories::getCachePath() . DIRECTORY_SEPARATOR . 'tmp_phpunit_lbxl_test1.xlsx';

        $source = $this->getTestSource();
        
        $cm = $source->getColumnModel();

        $xlsWriter = new LibXLWriter();
        $xlsWriter->setStore(new Store($source));
        
        $xlsWriter->save($output_file);

        $this->assertFileExists($output_file);
        $filesize = filesize($output_file);
        $this->assertGreaterThan(0, $filesize);

        // test Output

        $arr = $this->excelToArray($output_file);
        //$this->assertEquals(113, $arr[5]['B']);
        $this->assertEquals('french accents éàùêûçâµè and chinese 请收藏我们的网址', $arr[2]['A']);
        $this->assertEquals('.030 Corde séparée pour guitare électrique.', $arr[4]['N']);
    }
    
   public function testGetDataXls()
    {
        //$data = $this->xlsWriter->getData();
        //$this->assertInternalType('string', $data);
        
        $output_file = \SolubleTestFactories::getCachePath() . DIRECTORY_SEPARATOR . 'tmp_phpunit_lbxl_test1.xls';

        $source = $this->getTestSource();
        
        $cm = $source->getColumnModel();

        $xlsWriter = new LibXLWriter();
        $xlsWriter->setFormat(LibXL::FILE_FORMAT_XLS);
        $xlsWriter->setStore(new Store($source));
        
        $xlsWriter->save($output_file);

        $this->assertFileExists($output_file);
        $filesize = filesize($output_file);
        $this->assertGreaterThan(0, $filesize);

        // test Output

        $arr = $this->excelToArray($output_file, "Excel5");
        //$this->assertEquals(113, $arr[5]['B']);
        $this->assertEquals('french accents éàùêûçâµè and chinese 请收藏我们的网址', $arr[2]['A']);
    }    

    public function testGetDataWithColumnExclusion()
    {
        $output_file = \SolubleTestFactories::getCachePath() . DIRECTORY_SEPARATOR . 'tmp_phpunit_lbxl_test2.xlsx';

        $source = $this->getTestSource();
        
        $cm = $source->getColumnModel();
        $cm->exclude(array('reference', 'description', 'volume', 'weight', 'barcode_ean13', 'created_at', 'price', 'discount_1', 'promo_start_at', 'promo_end_at'));
        
        $xlsWriter = new LibXLWriter();
        $xlsWriter->setStore(new Store($source));
        
        $xlsWriter->save($output_file);
        $this->assertFileExists($output_file);
        $filesize = filesize($output_file);
        $this->assertGreaterThan(0, $filesize);

        // test Output

        $arr = $this->excelToArray($output_file);
        $this->assertEquals(10, $arr[2]['B']);
        $this->assertEquals(173, $arr[2]['C']);
        
        $this->assertEquals('french accents éàùêûçâµè ', $arr[2]['D']);
    }

    
    
    protected function excelToArray($file, $reader="Excel2007")
    {
        // Due to notice by php_excel class
        if (strtoupper($reader) == "EXCEL5") {
            ini_set("error_reporting", E_ALL ^ E_NOTICE);
        }
        $excelReader = PHPExcel_IOFactory::createReader($reader);
        $excelFile = $excelReader->load($file);
        $excelFile->setActiveSheetIndex(0);
        $sheet = $excelFile->getActiveSheet();
        
        $arr = $sheet->toArray($nullValue = null, $calculateFormulas = false, $formatData = false, $returnCellRef = true);
        if (strtoupper($reader) == "EXCEL5") {
            ini_set('error_reporting', E_ALL | E_STRICT);
        }
        return $arr;
    }


}
