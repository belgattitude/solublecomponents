<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Synthetic\Exception;
use Soluble\Db\Metadata\Source;


class TableTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TableManager
     */
    protected $tableManager;



    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $adapter = \SolubleTestFactories::getDbAdapter();
        $cache   = \SolubleTestFactories::getCacheStorage();
        $metadata = new Source\MysqlISMetadata($adapter);
        $metadata->setCache($cache);

        $this->tableManager = new TableManager($adapter);
        $this->tableManager->setMetadata($metadata);

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::getColumnsInformation
     */
    public function testGetColumnsInformation()
    {
        $table = $this->tableManager->getTable('product_category');
        $columns = $table->getColumnsInformation();
        $expected = array(
                'category_id','parent_id','reference','slug','title',
                'description','sort_index','icon_class','lft','rgt',
                'root','lvl','created_at','updated_at','created_by',
                'updated_by','legacy_mapping','legacy_synchro_at'            
        );
        $keys = array_keys($columns);
        $this->assertEquals($expected, $keys);
    }
    

    


    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTableAlias
     */
    public function testSetTableAlias()
    {
        // Test 1: with table alias set
        $table = $this->tableManager->getTable('product_category');
        $tableAlias = $table->setTableAlias('pc')->getTableAlias();
        $this->assertEquals('pc', $tableAlias);
        
        // Test 2: with table alias not set 
        $table = $this->tableManager->getTable('product_category');
        $tableAlias = $table->getTableAlias();
        $this->assertEquals('product_category', $tableAlias);
        
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::getTableAlias
     */
    public function testSetTableAliasThrowsException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->getTable('product_category');
        $table->setTableAlias('88');
    }
    
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTableAlias
     */
    public function testSetTableAliasThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->getTable('product_category');
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Table', $table);
        $tableAlias = $table->setTableAlias('77')->getTableAlias();
        $this->assertEquals('pc', $tableAlias);
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTableAlias
     */
    public function testSetTableAliasThrowsInvalidArgumentExceptionWithEmpty()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->getTable('product_category');
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Table', $table);
        $tableAlias = $table->setTableAlias('')->getTableAlias();
        $this->assertEquals('pc', $tableAlias);
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::select
     */
    public function testSelect()
    {
        $select = $this->tableManager->getTable('product_category')->select();
        $this->assertInstanceOf('\Soluble\Db\Sql\Select', $select);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\Table::find
     */
    public function testFind()
    {
        $table = $this->tableManager->getTable('product_category');        
        $record = $table->find(1);
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
        $this->assertEquals(1, $record->category_id);
        $this->assertEquals(1, $record['category_id']);

        $record = $table->find(984546465);
        $this->assertFalse($record);
    }

    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::find
     */
    public function testFindThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->getTable('product_category');        
        $record = $table->find(array('cool'));
    }    

    /**
     * @covers Soluble\Normalist\Synthetic\Table::find
     */
    public function testFindOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $table = $this->tableManager->getTable('product_category');        
        $record = $table->findOrFail('cool');
    }        
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::findOneBy
     */
    public function testFindOneBy()
    {
        $table = $this->tableManager->getTable('product_category');                
        $record = $table->findOneBy(array('category_id' => 12));
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
        $this->assertEquals(12, $record->category_id);
        $this->assertEquals(12, $record['category_id']);

        $record = $table->find(984546465);
        $this->assertFalse($record);        
    }

    
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::findOneBy
     */
    public function testFindOneByThrowsUnexpectedException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\UnexpectedValueException');
        $table = $this->tableManager->getTable('product_category');                
        $record = $table->findOneBy(array('sort_index' => 50));
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::count
     */
    public function testCount()
    {
        $tm = $this->tableManager;
        $table = $tm->getTable('product_category');                
        $count = $table->count();
        $this->assertInternalType('integer', $count);
        $this->assertEquals(1541, $count);

        $table = $tm->getTable('product_media');                
        $count = $table->count();
        $this->assertEquals(0, $count);
        
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::exists
     */
    public function testExists()
    {
        $table = $this->tableManager->getTable('product_category');                
        $exists = $table->exists(1);
        $this->assertTrue($exists);
        $this->assertInternalType('boolean', $exists);

        $exists = $table->exists(5464546545454);
        $this->assertFalse($exists);
        $this->assertInternalType('boolean', $exists);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\Table::search
     */
    public function testSearch()
    {
        $table = $this->tableManager->getTable('product_category');                
        $search = $table->search();
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\TableSearch', $search);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\Table::insert
     */
    public function testInsertThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->getTable('media');                
        $table->insert('cool');
        
    }
 
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::insert
     */
    public function testInsertThrowsUnexistentColumnException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\UnexistentColumnException');
        $table = $this->tableManager->getTable('media');                
        
        $data = array(
                'media_id' => 10,
                'column_not_exists' => 1
               );
        $table->insert($data);
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::insert
     */
    public function testInsertThrowsForeignKeyException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ForeignKeyException');
        $table = $this->tableManager->getTable('media');                
        $data = array(
        
                'filename'  => 'phpunit_test.pdf',
                'filemtime' => 111000,
                'filesize'  => 5000,
                'container_id' => 212313132132132121
        );            
        $table->insert($data);
    }    

    /**
      * @covers Soluble\Normalist\Synthetic\Table::insert
     
    public function testInsertThrowsRuntimeException()
    {
        $tm = $this->tableManager;
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\RuntimeException');
        $table = $tm->getTable('media');                
        $container_id = $tm->getTable('media_container')
                           ->findOneBy(array('reference' => 'PRODUCT_MEDIAS'))->get('container_id');
        
        $data = array(
                'filename'  => 'test_invalid_filemtime_and_size.pdf',
                'filemtime' => 'BBBBBB',
                'filesize'  => 'CCCCCCCCC',
                'container_id' => $container_id
        );            
        $table->insert($data);
    }    
    */
        
    

    

    /**
     * @covers Soluble\Normalist\SyntheticTable::insert
     */
    /*
    public function testInsertNotNullRuntimeExceptionMessage()
    {
        $table = $this->tableManager->getTable('media');                        
        $data = array('title' => "A title that shouln't be saved in phpunit database", 'reference' => null);
        try {
            $table->insert('product_type', $data);
        } catch (\Soluble\Normalist\Exception\RuntimeException $e) {
            $msg = strtolower($e->getMessage());
            $this->assertContains("reference", $msg);
            $this->assertContains("cannot be null", $msg);
        }
    }
    */

    /**
     * @covers Soluble\Normalist\Synthetic\Table::delete
     */
    public function testDelete()
    {
        $medias = $this->tableManager->getTable('media');
        $nb = $medias->delete(4546465456464);
        $this->assertEquals(0, $nb);

        $media = $medias->findOneBy(array('legacy_mapping' => 'tobedeleted_phpunit_testdelete'));
        if ($media) {
            $medias->deleteOrFail($media->media_id);
        }
        
        $data   = $this->createMediaRecordData('tobedeleted_phpunit_testdelete');
        $media  = $medias->insert($data);
        $nb = $medias->delete($media->media_id);
        $this->assertEquals(1, $nb);
        
    }    

    /**
     * @covers Soluble\Normalist\Synthetic\Table::deleteOrFail
     */
    public function testDeleteThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $medias = $this->tableManager->getTable('media');
        $nb = $medias->delete(array('cool'));
    }        
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::deleteOrFail
     */
    public function testDeleteOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $medias = $this->tableManager->getTable('media');
        $medias->deleteOrFail(987894546561);
    }    
    

    /**
     * @covers Soluble\Normalist\Synthetic\Table::insert
     */
    public function testInsertThrowsDuplicateEntryException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\DuplicateEntryException');
        $medias = $this->tableManager->getTable('media');                
        $media = $medias->findOneBy(array('legacy_mapping' => 'duplicate_key_phpunit'));
        if ($media) {
            $medias->delete($media->media_id);
        }
        
        $data = array(
        
                'filename'  => 'phpunit_test.pdf',
                'filemtime' => 111000,
                'filesize'  => 5000,
                'container_id' => 1,
                'legacy_mapping' => 'duplicate_key_phpunit'
        );            
        $medias->insert($data);
        
        // Will throw the Exception
        $medias->insert($data);
    }    
    
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::insert
     */
    public function testInsert()
    {
        $legacy_mapping = 'phpunit_testInsert';
        $medias = $this->tableManager->getTable('media');
        $data   = $this->createMediaRecordData($legacy_mapping);
        $media  = $medias->findOneBy(array('legacy_mapping' => $data['legacy_mapping']));
        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data['filename'] = 'my_test_filename';
        
        $media = $medias->insert($data);
        $this->assertEquals($data['filename'], $media['filename']);

        // Test with arrayObject
        $data = new \ArrayObject($this->createMediaRecordData($legacy_mapping));
        $media = $medias->findOneBy(array('legacy_mapping' => $data['legacy_mapping']));

        if ($media) {
            $medias->delete($media['media_id']);
        }

    }



    /**
     * @covers Soluble\Normalist\Synthetic\Table::all
     * @covers Soluble\Normalist\Synthetic\Table::count
     */
    public function testAll()
    {
        $tm = $this->tableManager->getTable('media');
        $all = $tm->all();
        $count = $tm->count();
        $this->assertInternalType('array', $all);
        $this->assertEquals($count, count($all));
    }






    /**
     * @covers Soluble\Normalist\Synthetic\Table::insertOnDuplicateKey
     * @todo   Implement testInsertOnDuplicateKey().
     */
    public function testInsertOnDuplicateKey()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }



    /**
     * @covers Soluble\Normalist\Synthetic\Table::getRelations
     * @todo   Implement testGetRelations().
     */
    public function testGetRelations()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }


    /**
     * @covers Soluble\Normalist\Synthetic\Table::getRecordCleanedData
     * @todo   Implement testGetRecordCleanedData().
     */
    public function testGetRecordCleanedData()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\Normalist\Synthetic\Table::getPrimaryKeys
     * @todo   Implement testGetPrimaryKeys().
     */
    public function testGetPrimaryKeys()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\Normalist\Synthetic\Table::getPrimaryKey
     * @todo   Implement testGetPrimaryKey().
     */
    public function testGetPrimaryKey()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }


    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTablePrefix
     * @todo   Implement testSetTablePrefix().
     */
    public function testSetTablePrefix()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
    

    /**
     * Return a media record suitable for database insertion
     * @return array
     */
    protected function createMediaRecordData($legacy_mapping=null)
    {
        $tm = $this->tableManager;
        $container_id = $tm->getTable('media_container')->findOneBy(array('reference' => 'PRODUCT_MEDIAS'))->get('container_id');
        $data  = array(
            'filename'  => 'phpunit_test.pdf',
            'filemtime' => 111000,
            'filesize'  => 5000,
            'container_id' => $container_id,
            'legacy_mapping' => $legacy_mapping
        );
        return $data;
    }    
    

}
