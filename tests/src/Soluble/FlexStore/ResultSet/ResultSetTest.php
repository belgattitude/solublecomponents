<?php

namespace Soluble\FlexStore\ResultSet;
use Soluble\FlexStore\FlexStore;
use Zend\Paginator\Paginator;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-14 at 18:08:25.
 */
class ResultSetTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ResultSet
     */
    protected $resultset;

    /**
     *
     * @var FlexStore
     */
    protected $store;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand');
        $parameters = array(
            'adapter' => $this->adapter,
            'select' => $select
        );
        $this->store = new FlexStore('zend\select', $parameters);


        $this->resultset = $this->store->getSource()->getData();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\AbstractResultSet::toArray
     */
    public function testGetArray()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand');
        $parameters = array(
            'adapter' => $this->adapter,
            'select' => $select
        );
        $store = new FlexStore('zend\select', $parameters);

        $resultset = $this->store->getSource()->getData();
        $arr = $resultset->toArray();
        $this->assertInternalType('array', $arr);
    }


    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::setColumns
     */
    public function testSetColumns()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand');
        $parameters = array(
            'adapter' => $this->adapter,
            'select' => $select
        );
        $store = new FlexStore('zend\select', $parameters);

        $columns = array('legacy_mapping', 'brand_id');
        $resultset = $this->store->getSource()->getData();
        $resultset->setColumns($columns);
        $arr = $resultset->toArray();
        $this->assertInternalType('array', $arr);

        $first = $arr[0];
        foreach($columns as $column) {
            $this->assertArrayHasKey($column, $first);
        }
        // test number of returned columns
        $test = array_keys($first);
        $this->assertEquals(count($columns), count($test));

        // test order / sort

        $this->assertEquals(array_shift($columns), array_shift($test));
        $this->assertEquals(array_shift($columns), array_shift($test));
    }


    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::getPaginator
     */
    public function testGetPaginatorThrowsInvalidUsageException()
    {
        $this->setExpectedException('Soluble\FlexStore\Exception\InvalidUsageException');
        $paginator = $this->resultset->getPaginator();
        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::getPaginator
     */
    public function testGetPaginator()
    {
        /*
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand');
        $parameters = array(
            'adapter' => $this->adapter,
            'select' => $select
        );

        $store = new FlexStore('zend\select', $parameters);
        $source = $store->getSource();
        $source->getOptions()->setLimit(10, 0);
        $resultset = $source->getData();
        $paginator = $resultset->getPaginator();
        $this->assertInstanceOf('Zend\Paginator\Paginator', $paginator);
         *
         */
    }


    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::setSource
     * @todo   Implement testSetSource().
     */
    public function testSetSource()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::getSource
     * @todo   Implement testGetSource().
     */
    public function testGetSource()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::setTotalRows
     * @todo   Implement testSetTotalRows().
     */
    public function testSetTotalRows()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::getTotalRows
     * @todo   Implement testGetTotalRows().
     */
    public function testGetTotalRows()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::setArrayObjectPrototype
     * @todo   Implement testSetArrayObjectPrototype().
     */
    public function testSetArrayObjectPrototype()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::getArrayObjectPrototype
     * @todo   Implement testGetArrayObjectPrototype().
     */
    public function testGetArrayObjectPrototype()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::getReturnType
     * @todo   Implement testGetReturnType().
     */
    public function testGetReturnType()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\FlexStore\ResultSet\ResultSet::current
     * @todo   Implement testCurrent().
     */
    public function testCurrent()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
