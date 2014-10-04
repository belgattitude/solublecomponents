<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Source\Zend\SqlSource;
use Soluble\FlexStore\Store;
use DateTime;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-14 at 13:07:21.
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Json
     */
    protected $jsonWriter;

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
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->where("reference = 'STAG'");

        $this->source = new SqlSource($this->adapter, $select);


        $this->jsonWriter = new Json();
        $this->jsonWriter->setStore(new Store($this->source));


    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        //header_remove();
        parent::tearDown();
    }

    /**
     * @covers Soluble\FlexStore\Writer\Json::getData
     */
    public function testGetData()
    {
        $data = $this->jsonWriter->getData();
        $this->assertJson($data);
        $d = json_decode($data, $assoc=true);
        $this->assertArrayHasKey('total', $d);
        $this->assertArrayHasKey('start', $d);
        $this->assertArrayHasKey('limit', $d);
        $this->assertArrayHasKey('data', $d);
        $this->assertTrue($d['success']);
        $this->assertArrayHasKey('timestamp', $d);
        $timestamp = DateTime::createFromFormat(DateTime::W3C, $d['timestamp']);
        
        $this->assertEquals($timestamp->format(DateTime::W3C), $d['timestamp']);
        $this->assertEquals($d['total'], count($d['data']));
        $this->assertArrayNotHasKey('query', $d);

    }

    /**
     * @covers Soluble\FlexStore\Writer\Json::getData
     * @covers Soluble\FlexStore\Writer\Json::setDebug
     */
    public function testGetDataWithDebug()
    {
        $this->jsonWriter->setDebug($debug=true);
        $data = $this->jsonWriter->getData();
        $this->assertJson($data);
        $d = json_decode($data, $assoc=true);

        $this->assertArrayHasKey('query', $d);

    }

    public function testGetHTTPHeaders()
    {
        $headers = $this->jsonWriter->getHttpHeaders();
        $this->assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        $this->assertEquals('application/json', $headers->getContentType());
        $this->assertEquals('UTF-8', strtoupper($headers->getCharset()));
        
        
    }    
}
