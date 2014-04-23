<?php

namespace Soluble\FlexStore\Writer\Excel;

use Soluble\FlexStore\Source\Zend\SelectSource;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

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
                $select = new Select();
                $select->from(array('p' => 'product'), array())
                        ->join(array('ppl' => 'product_pricelist'), 'ppl.product_id = p.product_id', array(), Select::JOIN_LEFT)
                        ->limit(100);
                
                $select->columns(array(
                   'product_id' => new Expression('p.product_id'),
                   'brand_id'   => new Expression('p.brand_id'),
                   'reference'  => new Expression('p.reference'),
                   'description'    => new Expression('p.description'),
                   'volume'         => new Expression('p.volume'),
                   'weight'         => new Expression('p.weight'),
                   'barcode_ean13'  => new Expression('1234567890123'),
                   'created_at'     => new Expression('NOW()'),
                   'price'          => new Expression('ppl.price'),
                   'discount_1'     => new Expression('ppl.discount_1'),
                   'promo_start_at' => new Expression('ppl.promo_start_at'),
                   'promo_end_at'   => new Expression('cast(NOW() as date)')

                ));
                
                
                $params = array(
                    'adapter' => $this->adapter,
                    'select' => $select
                );
                

                $source = new SelectSource($params);

                $this->xlsWriter = new LibXLWriter();
                $this->xlsWriter->setSource($source);
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
