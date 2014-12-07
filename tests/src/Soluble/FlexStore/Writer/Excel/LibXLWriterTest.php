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
            'currency_reference' => new Expression("if (p.product_id = 10, 'CNY', 'EUR')"),
            'test_float' => new Expression("1.212"),
            'test_date' => new Expression("cast('2012-12-31 15:10:59' as date)"),
            'test_datetime' => new Expression("cast('2012-12-31 15:10:59' as datetime)"),
            'test_time' => new Expression("cast('2012-12-31 15:10:59' as time)"),
            'unit' => new Expression("'Kg'"),
            'length' => new Expression("1.36666666")
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
    public function testColumnModelWithColumnExclusion()
    {
        
        $output_file = \SolubleTestFactories::getCachePath() . DIRECTORY_SEPARATOR . 'tmp_phpunit_lbxl_test5.xlsx';
        
        $store = new Store($this->getTestSource());
        $cm = $store->getColumnModel();
        $cm->search()->in(array('test_chars', 'brand_id', 'reference', 'description'))->setExcluded();
        $cm->sort(array('product_id', 'price', 'list_price', 'public_price', 'currency_reference'));
        $locale = 'en_US';
        $formatterDb = Formatter::create('currency', array(
            'currency_code' => new \Soluble\FlexStore\Formatter\RowColumn('currency_reference'),
            'locale' => $locale
        ));
        $this->assertInstanceOf('Soluble\FlexStore\Formatter\RowColumn', $formatterDb->getCurrencyCode());
        
        $formatterEur = Formatter::create('currency', array(
            'currency_code' => 'EUR',
            'locale' => $locale
        ));
        $this->assertNotInstanceOf('Soluble\FlexStore\Formatter\RowColumn', $formatterEur->getCurrencyCode());
        
        $unitFormatter = Formatter::create('unit', array(
            'unit' => '%',
            'decimals' => 2,
            'locale' => $locale
        ));

        $intFormatter = Formatter::create('number', array(
            'decimals' => 0,
            'locale' => $locale
        ));

        $volumeFormatter = Formatter::create('number', array(
            'decimals' => 3,
            'locale' => $locale
        ));
        
        
        $cm->search()->regexp('/price/')->setFormatter($formatterDb);
        $cm->get('currency_reference')->setExcluded();
        $cm->get('public_price')->setFormatter($formatterEur);
        $cm->get('volume')->setFormatter($volumeFormatter);
        $cm->get('length')->setFormatter($intFormatter);
        
        $cm->search()->regexp('/discount\_[1-4]$/')->setFormatter($unitFormatter);
        
        
        $formatted_data = $store->getData()->toArray();
        $this->assertEquals('CN¥15.30', $formatted_data[0]['list_price']);
        $this->assertEquals('€18.20', $formatted_data[0]['public_price']);
        $this->assertEquals('2012-12-31', $formatted_data[0]['test_date']);
        $this->assertEquals('2012-12-31 15:10:59', $formatted_data[0]['test_datetime']);
        $this->assertEquals('15:10:59', $formatted_data[0]['test_time']);

        $xlsWriter = new LibXLWriter();
        $xlsWriter->setStore($store);
        
        $xlsWriter->save($output_file);

        $this->assertFileExists($output_file);
        $filesize = filesize($output_file);
        $this->assertGreaterThan(0, $filesize);

        // test Output
        $arr = $this->excelToArray($output_file);
        
        $this->assertEquals('price', $arr[1]['B']);
        $this->assertTrue(is_float($arr[2]['B']));
        $this->assertEquals(number_format(15.3, 1), number_format($arr[2]['C'], 1));
        $this->assertEquals(number_format(18.2, 1), number_format($arr[2]['D'], 1));
        $this->assertEquals("", $arr[4]['C']);
        $this->assertEquals("", $arr[4]['B']);
        
        $excel = $this->getExcelReader($output_file);
        $sheet = $excel->getActiveSheet();
        //$c2 = $sheet->getCellByColumnAndRow('B', 4);
        $c2 = $sheet->getCell('C2');

        $this->assertEquals('n', $c2->getDataType());
        $this->assertEquals('15.30 CN¥', $c2->getFormattedValue());
        
        $d2 = $sheet->getCell('D2');
        $this->assertEquals('n', $d2->getDataType());
        $this->assertEquals('18.20 €', $d2->getFormattedValue());

        $n2 = $sheet->getCell('N2');
        
        $this->assertEquals('31/12/2012', $n2->getFormattedValue());
        $this->assertEquals('n', $n2->getDataType());
        
        $o2 = $sheet->getCell('O2');
        
        // Why PHPExcel does not return seconds as well ?
        $this->assertEquals('31/12/2012 15:10', $o2->getFormattedValue());
        $this->assertEquals('n', $o2->getDataType());
        $p2 = $sheet->getCell('P2');
        
        $this->assertEquals('15:10:59', $p2->getFormattedValue());
        $this->assertEquals('s', $p2->getDataType());
        
        // Length must have no decimals
        $r2 = $sheet->getCell('R2');
        $this->assertEquals('1', $r2->getFormattedValue());
        $this->assertEquals(number_format(1.366666, 2), number_format($r2->getValue(), 2));
        $this->assertEquals('n', $r2->getDataType());
        
        // Volume mus have 3 decimals
        $e2 = $sheet->getCell("E2");

        $this->assertTrue("0.300" === $e2->getFormattedValue());
        $this->assertEquals(number_format(0.3, 2), number_format($e2->getValue(), 2));
        $this->assertEquals('n', $r2->getDataType());
        
        // Discount_1 2 decimals and % symbol
        
        $i2 = $sheet->getCell("I2");
        $this->assertEquals(number_format(22.00, 2), number_format($i2->getValue(), 2));
        $this->assertTrue("22.00 %" === $i2->getFormattedValue());
        $this->assertEquals('n', $i2->getDataType());
        
    }
    
    
    /**
     *
     */
    public function testColumnModel()
    {
        
        $output_file = \SolubleTestFactories::getCachePath() . DIRECTORY_SEPARATOR . 'tmp_phpunit_lbxl_test4.xlsx';
        
        $store = new Store($this->getTestSource());
        $cm = $store->getColumnModel();
        $cm->search()->in(array('test_chars', 'brand_id', 'reference', 'description'))->setExcluded();
        $cm->sort(array('product_id', 'price', 'list_price', 'public_price', 'currency_reference'));
        $locale = 'en_US';
        $formatterDb = Formatter::create('currency', array(
            'currency_code' => new \Soluble\FlexStore\Formatter\RowColumn('currency_reference'),
            'locale' => $locale
        ));
        $this->assertInstanceOf('Soluble\FlexStore\Formatter\RowColumn', $formatterDb->getCurrencyCode());
        
        $formatterEur = Formatter::create('currency', array(
            'currency_code' => 'EUR',
            'locale' => $locale
        ));
        $this->assertNotInstanceOf('Soluble\FlexStore\Formatter\RowColumn', $formatterEur->getCurrencyCode());
        
        $cm->search()->regexp('/price/')->setFormatter($formatterDb);
        $cm->get('public_price')->setFormatter($formatterEur);
        
        
        $formatted_data = $store->getData()->toArray();
        $this->assertEquals('CN¥15.30', $formatted_data[0]['list_price']);
        $this->assertEquals('€18.20', $formatted_data[0]['public_price']);
        $this->assertEquals('2012-12-31', $formatted_data[0]['test_date']);
        $this->assertEquals('2012-12-31 15:10:59', $formatted_data[0]['test_datetime']);
        $this->assertEquals('15:10:59', $formatted_data[0]['test_time']);

        $xlsWriter = new LibXLWriter();
        $xlsWriter->setStore($store);
        
        $xlsWriter->save($output_file);

        $this->assertFileExists($output_file);
        $filesize = filesize($output_file);
        $this->assertGreaterThan(0, $filesize);

        // test Output

        $arr = $this->excelToArray($output_file);
        
        
        
        $this->assertEquals('price', $arr[1]['B']);
        $this->assertTrue(is_float($arr[2]['B']));
        $this->assertEquals(number_format(15.3, 1), number_format($arr[2]['C'], 1));
        $this->assertEquals(number_format(18.2, 1), number_format($arr[2]['D'], 1));
        $this->assertEquals('CNY', $arr[2]['E']);
        $this->assertEquals('EUR', $arr[3]['E']);
        $this->assertEquals("", $arr[4]['C']);
        $this->assertEquals("", $arr[4]['B']);
        //$this->assertEquals('2012-12-31', $arr[2]['O']);
        //$this->assertEquals('2012-12-31 15:10:00', $arr[2]['P']);
        //$this->assertEquals('15:10:00', $arr[2]['Q']);
        
        
        $excel = $this->getExcelReader($output_file);
        $sheet = $excel->getActiveSheet();
        //$c2 = $sheet->getCellByColumnAndRow('B', 4);
        $c2 = $sheet->getCell('C2');

        $this->assertEquals('n', $c2->getDataType());
        $this->assertEquals('15.30 CN¥', $c2->getFormattedValue());
        
        $d2 = $sheet->getCell('D2');
        $this->assertEquals('n', $d2->getDataType());
        $this->assertEquals('18.20 €', $d2->getFormattedValue());

        $o2 = $sheet->getCell('O2');
        $this->assertEquals('n', $o2->getDataType());
        $this->assertEquals('31/12/2012', $o2->getFormattedValue());
        
        $p2 = $sheet->getCell('P2');
        
        // Why PHPExcel does not return seconds as well ?
        $this->assertEquals('31/12/2012 15:10', $p2->getFormattedValue());
        $this->assertEquals('n', $p2->getDataType());
        $q2 = $sheet->getCell('Q2');
        
        $this->assertEquals('15:10:59', $q2->getFormattedValue());
        $this->assertEquals('s', $q2->getDataType());
        
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

    
    /**
     *
     * @param string $file
     * @param string $reader
     * @return \PHPExcel
     */
    protected function getExcelReader($file, $reader = "Excel2007")
    {
        $excelReader = PHPExcel_IOFactory::createReader($reader);
        $excelReader = $excelReader->load($file);
        $excelReader->setActiveSheetIndex(0);
        return $excelReader;
    }
    
    
    protected function excelToArray($file, $reader = "Excel2007")
    {
        // Due to notice by php_excel class
        if (strtoupper($reader) == "EXCEL5") {
            ini_set("error_reporting", E_ALL ^ E_NOTICE);
        }
        $excelReader = $this->getExcelReader($file, $reader);
        $sheet = $excelReader->getActiveSheet();
        
        $arr = $sheet->toArray($nullValue = null, $calculateFormulas = false, $formatData = false, $returnCellRef = true);
        if (strtoupper($reader) == "EXCEL5") {
            ini_set('error_reporting', E_ALL | E_STRICT);
        }
        return $arr;
    }
    
    public function testSetFormatThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\FlexStore\Writer\Exception\InvalidArgumentException');
        $xlsWriter = new LibXLWriter();
        $xlsWriter->setFormat('cool');
    }

    
    public function testExcelBookThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\FlexStore\Writer\Exception\InvalidArgumentException');
        $xlsWriter = new LibXLWriter();
        $xlsWriter->getExcelBook('coo:');
    }
    
    public function testGetHTTPHeaders()
    {
        $xlsWriter = new LibXLWriter();
        $headers = $xlsWriter->getHttpHeaders();
        $this->assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        $this->assertEquals('application/excel', $headers->getContentType());
    }
}
