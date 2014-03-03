<?php

namespace Soluble\Db\Compat;

use Zend\Db\Adapter\Adapter;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-03-03 at 11:51:22.
 */
class AdoDbTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var array
     */
    protected $db_options;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->db_options = \SolubleTestFactories::getDatabaseConfig();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testGetAdapterWithMySQLi()
    {
        $ado = NewADOConnection("mysqli");
        $o = $this->db_options;
        $ado->connect($o['hostname'], $o['username'], $o['password'], $o['database']);
        
        $adapter = Adodb::getAdapter($ado);
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $adapter);
        // is 37 will render an iso latin 1 char 'é'
        $sql = "select category_id, title from product_category_translation where id=37";
        $zrows = $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray();
        $arows = $ado->Execute($sql)->getArray();
        $this->assertEquals($zrows[0]['category_id'], $arows[0]['category_id']);
        $this->assertEquals($zrows[0]['title'], $arows[0]['title']);
    }
    
    public function testGetAdapterWithPDOMySQL()
    {
        $ado = NewADOConnection("pdo");
        $o = $this->db_options;

        $ado->connect("mysql:host=" . $o['hostname'],$o['username'], $o['password'], $o['database']);
        
        $adapter = Adodb::getAdapter($ado);
        
        $this->assertInstanceOf('Zend\Db\Adapter\Adapter', $adapter);
        // is 37 will render an iso latin 1 char 'é'
        $sql = "select category_id, title from product_category_translation where id=37";
        $zrows = $adapter->query($sql, Adapter::QUERY_MODE_EXECUTE)->toArray();
        $arows = $ado->Execute($sql)->getArray();
        $this->assertEquals($zrows[0]['category_id'], $arows[0]['category_id']);
        $this->assertEquals($zrows[0]['title'], $arows[0]['title']);
    }
    
    public function testGetAdapterWithThrowsUnsupportedDriverException()
    {
        $this->setExpectedException("Soluble\Db\Compat\Exception\AdoNotConnectedException");
        $ado = NewADOConnection("postgres");
        $o = $this->db_options;
        
        $adapter = Adodb::getAdapter($ado);
        
    }
    
    

}