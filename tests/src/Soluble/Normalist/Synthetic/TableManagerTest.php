<?php

namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Driver;
use Soluble\Schema\Source;
use Zend\Db\Adapter\Adapter;

class TableManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableManager
     */
    protected $tableManager;


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
        $this->tableManager = \SolubleTestFactories::getTableManager();
        $this->adapter = $this->tableManager->getDbAdapter();
        
        //$this->tableManager = new TableManager($this->adapter);
        $this->table = $this->tableManager->table('product_category');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->adapter);
        unset($this->tableManager);
        unset($this->table);
    }

    public function testGetDefaultMetadata()
    {
        $tm = \SolubleTestFactories::getTableManager();
        $metadata = $tm->metadata();
        $this->assertInstanceOf('\Soluble\Schema\Source\AbstractSchemaSource', $metadata);
    }
    
    
    
    
    public function testTable()
    {
        $medias = $this->tableManager->table('media');
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Table', $medias);
    }

    public function testTableThrowsInvalidArgumentException()
    {
        $this->setExpectedException("\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException");
        $medias = $this->tableManager->table(array('cool'));
    }
    
    
    public function testTableThrowsTableNotFoundException()
    {
        $this->setExpectedException("\Soluble\Normalist\Synthetic\Exception\TableNotFoundException");
        $medias = $this->tableManager->table('table_that_does_not_exists');
    }

    public function testSelect()
    {
        $select = $this->tableManager->select('media');
        $this->assertInstanceOf('\Soluble\Db\Sql\Select', $select);
    }

    
    /*
    public function testUpdateThrowsColumnNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');
        $tm = $this->tableManager;
        $tm->update('media', array('cool' => 'test'), 'media_id = 1');
        
    }
    
    public function testInsertThrowsColumnNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');
        $tm = $this->tableManager;
        $tm->insert('media', array('cool' => 'test'));
        
    }
    */

    public function testTransaction()
    {
        $tm = $this->tableManager;

        $transaction = $tm->transaction();
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Transaction', $transaction);
        
        
        $legacy_mapping = "phpunit_tablemanager_transaction";
        $data = $this->createMediaRecordData($legacy_mapping);

        // Cleanup
        $medias = $tm->table('media');
        $media = $medias->findOneBy(array('legacy_mapping' => $legacy_mapping));
        if ($media) {
            $medias->delete($media['media_id']);
        }
        
        $tm->transaction()->start();
        
        $m = $medias->insert($data);
        $media_id_rollback = $m['media_id'];
        $this->assertTrue(is_numeric($media_id_rollback));
        $tm->transaction()->rollback();
        
        $this->assertFalse($medias->find($media_id_rollback));
        
        $tm->transaction()->start();
        $m = $medias->insert($data);
        $media_id_commit = $m['media_id'];
        $this->assertTrue(is_numeric($media_id_commit));
        $tm->transaction()->commit();
        
        $this->assertGreaterThanOrEqual($media_id_rollback, $media_id_commit);
        $this->assertTrue($medias->exists($media_id_commit));
        $medias->delete($media_id_commit);
    }

    public function testBeginTransactionThrowsTransactionException()
    {
        $driver = $this->adapter->getDriver();
        if ($driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {
            // TEST with PDO_MYSQL instead
            $this->assertTrue(true);
        } else {
            $catched = false;
            $tm = $this->tableManager;
            $tm->transaction()->start();
            try {
                $tm->transaction()->start();
            } catch (Exception\TransactionException $e) {
                $catched = true;
                $tm->transaction()->rollback();
            }
            $this->assertTrue($catched);
        }
    }
    

    public function testCommitThrowsTransactionException()
    {
        $driver = $this->adapter->getDriver();
        
        if ($driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {
            // testing PDO_MYSQL instead, Mysqli won't throw any exception
            // on invalid commit, rollback, start...
            $driver = 'PDO_Mysql';
            $adapter = \SolubleTestFactories::getDbAdapter(null, $driver);
            $tm = \SolubleTestFactories::getTableManager($adapter);
            
            // test than Mysqli does not throw exception
            $catched = false;
            try {
                $this->tableManager->transaction()->commit();
            } catch (Exception\TransactionException $e) {
                $catched = true;
            }
            $this->assertFalse($catched, "Mysqli should not throw a transaction exception");
        } else {
            $tm = $this->tableManager;
        }
        
        $catched = false;
        
        
        try {
            $tm->transaction()->commit();
        } catch (Exception\TransactionException $e) {
            $catched = true;
        }
        $this->assertTrue($catched, "Commit without starting a transaction should fail");

        $catched = false;
        try {
            $tm->transaction()->rollback();
        } catch (Exception\TransactionException $e) {
            $catched = true;
        }
        $this->assertTrue($catched, "Rollback without starting a transaction should fail");

        $catched = false;
        try {
            $tm->transaction()->start();
            $tm->transaction()->start();
        } catch (Exception\TransactionException $e) {
            $tm->transaction()->rollback();
            $catched = true;
        }
        $this->assertTrue($catched, "Double begin transaction should fail");
    }
    
    public function testRollbackThrowsTransactionException()
    {
        $driver = $this->adapter->getDriver();
        if ($driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {
            $this->assertTrue(true);
        } else {
            $catched = false;
            $tm = $this->tableManager;

            try {
                $tm->transaction()->rollback();
            } catch (Exception\TransactionException $e) {
                $catched = true;
            }
            $this->assertTrue($catched);
        }
    }

    
    public function testGetDbAdapter()
    {
        $adapter = $this->tableManager->getDbAdapter();
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $adapter);
    }

    public function testSetTablePrefix()
    {
        $tm = \SolubleTestFactories::getTableManager();
        $ret = $tm->setTablePrefix('prefix_');
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\TableManager', $ret);
    }

    public function testGetTablePrefix()
    {
        $tm = \SolubleTestFactories::getTableManager();
        $prefix = $tm->setTablePrefix('prefix_')->getTablePrefix();
        $this->assertEquals('prefix_', $prefix);
    }


    public function testGetPrefixedTable()
    {
        $tm = \SolubleTestFactories::getTableManager();
        $prefixed = $tm->setTablePrefix('prefix_')->getPrefixedTable('cool');
        
        $this->assertEquals('prefix_cool', $prefixed);
    }





    public function testGetMetadata()
    {
        $metadata = $this->tableManager->metadata();
        $this->assertInstanceOf('\Soluble\Schema\Source\AbstractSchemaSource', $metadata);
    }

    public function testGetMetadataThrowsUnsupportedFeatureException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\UnsupportedFeatureException');
        // Fake adapter
        
        $adapter = new Adapter(array(
            'driver' => 'Pdo_Sqlite',
            'database' => 'path/to/sqlite.db'
        ));
        
        
        $tm = \SolubleTestFactories::getTableManager($adapter);
        $metadata = $tm->metadata();
    }


    public function testSetMetadata()
    {
        $conn = $this->adapter->getDriver()->getConnection()->getResource();
        $metadata = new Source\MysqlInformationSchema($conn);
        //$metadata = new Source\MysqlISMetadata($this->adapter);
        $tableManager = \SolubleTestFactories::getTableManager();
        $ret = $tableManager->setMetadata($metadata);
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\TableManager', $ret);
    }
    
    /**
     * Return a media record suitable for database insertion
     * @return array
     */
    protected function createMediaRecordData($legacy_mapping = null)
    {
        $tm = $this->tableManager;
        $container = $tm->table('media_container')->findOneBy(array('reference' => 'PRODUCT_MEDIAS'));
        $container_id = $container['container_id'];
        $data  = array(
            'filename'  => 'phpunit_tablemanager.pdf',
            'filemtime' => 111000,
            'filesize'  => 5000,
            'container_id' => $container_id,
            'legacy_mapping' => $legacy_mapping
        );
        return $data;
    }
}
