<?php

namespace Soluble\Normalist\Driver\Metadata;

use Zend\Db\Adapter\Adapter;
use Soluble\Normalist\Driver\ZeroConfDriver;

class NormalistModelsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var NormalistModels
     */
    protected $metadata;

    /**
     * @var ZeroConfDriver
     */
    protected $driver;

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $this->driver = new ZeroConfDriver($this->adapter);
        $this->driver->clearMetadataCache();
        $this->driver->getMetadata();
        $model_definition = $this->driver->getModelsDefinition();
        $this->metadata = new NormalistModels($model_definition);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    

    
    
    public function testGetRelations()
    {
        $relations = $this->metadata->getRelations('product');
        $this->assertInternalType('array', $relations);
        $this->assertArrayHasKey('brand_id', $relations);
        $this->assertArrayHasKey('referenced_column', $relations['unit_id']);
        $this->assertArrayHasKey('referenced_table', $relations['unit_id']);
        $this->assertArrayHasKey('constraint_name', $relations['unit_id']);
    }

    
    
    

    
    public function testGetTablesInformation()
    {
        $ti = $this->metadata->getTablesInformation();
        $table = 'media';
        $this->assertInternalType('array', $ti);
        $this->assertArrayHasKey($table, $ti);
        $this->assertArrayHasKey('columns', $ti[$table]);
        $this->assertArrayHasKey('indexes', $ti[$table]);
        $this->assertArrayHasKey('primary_keys', $ti[$table]);
        $this->assertArrayHasKey('unique_keys', $ti[$table]);
        
        
    }
/*
    public function testGetIndexesInformationKeys()
    {
        $indexes = $this->metadata->getIndexesInformation('product');

        // Actually nothing
        
    }
    
  */
    public function testGetUniqueKeys()
    {
        $unique = $this->metadata->getUniqueKeys('test_table_with_unique_key');
        
        $this->assertInternalType('array', $unique);
        $this->assertEquals(1, count($unique));
        $this->assertArrayHasKey('unique_id_1', $unique);
        $this->assertInternalType('array', $unique['unique_id_1']);
        $this->assertEquals(2, count($unique['unique_id_1']));
        $this->assertEquals(array('unique_id_1', 'unique_id_2'), $unique['unique_id_1']);
        
        
        $unique = $this->metadata->getUniqueKeys('product');
        $this->assertInternalType('array', $unique);
        $this->assertEquals(3, count($unique));
        $this->assertArrayHasKey('unique_legacy_mapping_idx', $unique);
        $this->assertArrayHasKey('unique_reference_idx', $unique);
        $this->assertArrayHasKey('unique_slug_idx', $unique);
        
        
    }
    
    
    public function testGetPrimaryKey()
    {
        $primary = $this->metadata->getPrimaryKey('user');
        $this->assertInternalType('string', $primary);
        $this->assertEquals('user_id', $primary);
    }
    
    public function testGetPrimaryKeyThrowsMultiplePrimaryKeyException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\MultiplePrimaryKeyException');
        $primary = $this->metadata->getPrimaryKey('test_table_with_multipk');
    }
      
        

    public function testgetPrimaryKeyThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $primary = $this->metadata->getPrimaryKey(array('cool'));
        
    }
    

    public function testgetPrimaryKeysThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $primary = $this->metadata->getPrimaryKeys(array('cool'));
        
    }
    
    
    public function testGetPrimaryKeyThrowsNoPrimaryKeyException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\NoPrimaryKeyException');
        $primary = $this->metadata->getPrimaryKey('test_table_without_pk');
    }
    
    public function testGetPrimaryKeys()
    {
        $keys = $this->metadata->getPrimaryKeys('user');
        $this->assertInternalType('array', $keys);
        $this->assertEquals('user_id', $keys[0]);

    }

    public function testGetColumnsInformation()
    {
        $columns = $this->metadata->getColumnsInformation('user');
        $this->assertInternalType('array', $columns);
        $this->assertEquals('varchar', $columns['password']['type']);

    }

    
    
    public function testGetPrimaryKeysThrowsNoPrimaryKeyException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\NoPrimaryKeyException');
        $primary = $this->metadata->getPrimaryKeys('test_table_without_pk');
    }
}
