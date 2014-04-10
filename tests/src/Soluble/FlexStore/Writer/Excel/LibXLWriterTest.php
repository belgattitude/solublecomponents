<?php

namespace Soluble\FlexStore\Writer\Excel;

use Soluble\FlexStore\Source\Zend\SelectSource;

class LibXLWriterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LibXLWriter
     */
    protected $xlsWriter;

    /**
     * @var SelectSource
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
                $select = new \Zend\Db\Sql\Select();
                $select->from('product_category_translation')->where("lang = 'fr'")->limit(200);
                $params = array(
                    'adapter' => $this->adapter,
                    'select' => $select
                );

                $this->source = new SelectSource($params);

                $this->xlsWriter = new LibXLWriter();
                $this->xlsWriter->setSource($this->source);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Soluble\FlexStore\Writer\CSV::getData
     */
    public function testGetData()
    {
        //$data = $this->xlsWriter->getData();
        //$this->assertInternalType('string', $data);
        $this->xlsWriter->save('/tmp/a.xlsx');
        
    }

    /**
     * @covers Soluble\FlexStore\Writer\CSV::send
     * @todo   Implement testSend().
     */
    public function testSend()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
