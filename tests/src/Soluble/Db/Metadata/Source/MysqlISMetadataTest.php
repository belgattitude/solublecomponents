<?php
/*
namespace Soluble\Db\Metadata\Source;

class MysqlISMetadataTest extends \PHPUnit_Framework_TestCase
{

    protected $metadata;

    protected function setUp()
    {
        $adapter = \SolubleTestFactories::getDbAdapter();

        //var_dump($adapter->getDriver()->getConnection());
        //var_dump($adapter->getCurrentSchema());

        $cache   = \SolubleTestFactories::getCacheStorage();

        $this->metadata = new MysqlISMetadata($adapter);
        $this->metadata->unsetCache();
        $tables = $this->metadata->getTables();

        //var_dump($tables);

    }
    
    

    public function testConstructThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $adapter = \SolubleTestFactories::getDbAdapter();
        $this->metadata = new MysqlISMetadata($adapter, array('schema_not_valid'));
    }        

    public function testConstructThrowsInvalidArgumentException2()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $adapter = \SolubleTestFactories::getDbAdapter();
        $this->metadata = new MysqlISMetadata($adapter, $schema="   ");
    }            
    
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

    public function testGetPrimaryKeyThrowsTableNotFoundException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\TableNotFoundException');
        $primary = $this->metadata->getPrimaryKey('table_not_found');
    }    

    public function testgetPrimaryKeyThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $primary = $this->metadata->getPrimaryKey(array('cool'));
        
    }

     public function testgetPrimaryKeyThrowsInvalidArgumentException2()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $primary = $this->metadata->getPrimaryKey('product', $schema=array('cool'));
        
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
    }

    
    public function testGetPrimaryKeysThrowsNoPrimaryKeyException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\NoPrimaryKeyException');
        $primary = $this->metadata->getPrimaryKeys('test_table_without_pk');
    }    
    
    public function testGetIndexesInformation()
    {
        $indexes = $this->metadata->getIndexesInformation('product');
        $this->assertInternalType('array', $indexes);
        $this->assertArrayHasKey('unique_slug_idx', $indexes);

    }

    public function testGetColumnsInformation()
    {
        $infos = $this->metadata->getColumnsInformation('product');
        $this->assertInternalType('array', $infos);
        $this->assertArrayHasKey('product_id', $infos);

    }
    
    public function testGetColumns()
    {
        $infos = $this->metadata->getColumns('product');
        $this->assertInternalType('array', $infos);
        $this->assertArrayNotHasKey('product_id', $infos);
        $this->assertTrue(in_array('product_id', $infos));

    }
    
    public function testGetRelationsThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $relations = $this->metadata->getRelations(array('cool'));
        
    }

    public function testGetRelationsThrowsInvalidArgumentException2()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $relations = $this->metadata->getRelations('product', array('cool'));
        
    }
    
    
    public function testGetRelations()
    {
        $relations = $this->metadata->getRelations('product');
        $this->assertInternalType('array', $relations);
        $this->assertArrayHasKey('brand_id', $relations);
        $this->assertArrayHasKey('column_name', $relations['unit_id']);
        $this->assertArrayHasKey('table_schema', $relations['unit_id']);
        $this->assertArrayHasKey('table_name', $relations['unit_id']);
        $this->assertArrayHasKey('constraint_name', $relations['unit_id']);
    }

    public function testGetRelationsThrowsTableNotFoundExceptionWrongTableName()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\TableNotFoundException');
        $relations = $this->metadata->getRelations('table_not_exists');

    }
    
    
    public function testGetRelationsThrowsTableNotFoundExceptionWithSchema()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\TableNotFoundException');
        $relations = $this->metadata->getRelations('product', 'invalid_schema_not_exists');

    }
    
    public function testGetRelationsThrowsInvalidArgumenExceptionWithSchema()
    {
        $this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
        $relations = $this->metadata->getRelations('product', array('cool'));

    }
    

    public function testGetTablesInformation()
    {
        $infos = $this->metadata->getTablesInformation();
        $this->assertInternalType('array', $infos);
        $this->assertArrayHasKey('wp_users', $infos);
    }
    
    
    public function testGetTableInformation()
    {
        $infos = $this->metadata->getTableInformation('user');
        $this->assertInternalType('array', $infos);
        $this->assertArrayHasKey('TABLE_NAME', $infos);
    }

  
}
*/