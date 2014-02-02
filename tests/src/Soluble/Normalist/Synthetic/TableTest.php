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
    
    
    public function testGetColumnsInformation()
    {
        $table = $this->tableManager->table('product_category');
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
    

    
    public function testSelect()
    {
        $select = $this->tableManager->table('product_category')->select();
        $this->assertInstanceOf('\Soluble\Db\Sql\Select', $select);
    }

    public function testFind()
    {
        $table = $this->tableManager->table('product_category');        
        $record = $table->find(1);
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
        $this->assertEquals(1, $record->category_id);
        $this->assertEquals(1, $record['category_id']);

        $record = $table->find(984546465);
        $this->assertFalse($record);
    }
    
    public function testFindOrFail()
    {
        $table = $this->tableManager->table('product_category');        
        $record = $table->findOrFail(1);
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
        $this->assertEquals(1, $record->category_id);
        $this->assertEquals(1, $record['category_id']);

    }
    

    
    public function testFindThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');        
        $record = $table->find(array('cool'));
    }    

    public function testFindOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $table = $this->tableManager->table('product_category');        
        $record = $table->findOrFail('cool');
    }        
    
    public function testFindOneBy()
    {
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneBy(array('category_id' => 12));
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
        $this->assertEquals(12, $record->category_id);
        $this->assertEquals(12, $record['category_id']);

        $record = $table->find(984546465);
        $this->assertFalse($record);        
    }


    public function testFindOneByThrowsUnexistentColumnException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\UnexistentColumnException');
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneBy(array('column_not_exists' => 50));
    }    

    public function testFindOneByThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneBy('count(*) <> media_id');
    }    
    
    
    public function testFindOneByThrowsUnexpectedException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\UnexpectedValueException');
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneBy(array('sort_index' => 50));
    }
    
    public function testFindOneByOrFail()
    {
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneByOrFail(array('category_id' => 12));
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
        $this->assertEquals(12, $record->category_id);
        $this->assertEquals(12, $record['category_id']);

    }

    public function testFindOneByOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $table = $this->tableManager->table('product_category');        
        $record = $table->findOneByOrFail(array('category_id' => 'cool'));
    }        

    public function testFindOneByOrFailThrowsUnexistentColumnException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\UnexistentColumnException');
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneByOrFail(array('column_not_exists' => 50));
    }    

    public function testFindOneByOrFailThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneByOrFail('count(*) <> media_id');
    }    
    
    public function testFindOneByOrFailThrowsUnexpectedException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\UnexpectedValueException');
        $table = $this->tableManager->table('product_category');                
        $record = $table->findOneByOrFail(array('sort_index' => 50));
    }
    
    
    public function testCount()
    {
        $tm = $this->tableManager;
        $table = $tm->table('product_category');                
        $count = $table->count();
        $this->assertInternalType('integer', $count);
        $this->assertEquals(1541, $count);

        $table = $tm->table('product_media');                
        $count = $table->count();
        $this->assertEquals(0, $count);
    }
    
    public function testExists()
    {
        $table = $this->tableManager->table('product_category');                
        $exists = $table->exists(1);
        $this->assertTrue($exists);
        $this->assertInternalType('boolean', $exists);

        $exists = $table->exists(5464546545454);
        $this->assertFalse($exists);
        $this->assertInternalType('boolean', $exists);
    }

    public function testExistsThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        
        $table = $this->tableManager->table('product_category');                
        $exists = $table->exists(array(10,10));
    }
    
    
    
    public function testSearch()
    {
        $table = $this->tableManager->table('product_category');                
        $search = $table->search();
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\TableSearch', $search);
    }

    public function testInsertThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('media');                
        $table->insert('cool');
        
    }

    public function testInsertThrowsUnexistentColumnException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\UnexistentColumnException');
        $table = $this->tableManager->table('media');                
        
        $data = array(
                'media_id' => 10,
                'column_not_exists' => 1
               );
        $table->insert($data);
    }
    
    public function testInsertThrowsForeignKeyException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ForeignKeyException');
        $table = $this->tableManager->table('media');                
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
        $table = $tm->table('media');                
        $container_id = $tm->table('media_container')
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
        $table = $this->tableManager->table('media');                        
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

    public function testDelete()
    {
        $medias = $this->tableManager->table('media');
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

    public function testDeleteThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $medias = $this->tableManager->table('media');
        $nb = $medias->delete(array('cool'));
    }        
    
    public function testDeleteOrFail()
    {
        $medias = $this->tableManager->table('media');

        $media = $medias->findOneBy(array('legacy_mapping' => 'tobedeleted_phpunit_testdelete'));
        if ($media) {
            $medias->delete($media->media_id);
        }
        
        $data   = $this->createMediaRecordData('tobedeleted_phpunit_testdelete');
        $media  = $medias->insert($data);
        $ret = $medias->deleteOrFail($media->media_id);
        $this->assertInstanceOf('Soluble\Normalist\Synthetic\Table', $ret);
        
    }    

    
    public function testDeleteOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $medias = $this->tableManager->table('media');
        $medias->deleteOrFail(987894546561);
    }    
    

    public function testInsertThrowsDuplicateEntryException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\DuplicateEntryException');
        $medias = $this->tableManager->table('media');                
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
    
    
    public function testInsert()
    {
        $legacy_mapping = 'phpunit_testInsert';
        $medias = $this->tableManager->table('media');
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
        
        $data['media_id'] = 999999999;
        $media = $medias->find(999999999);
        if ($media) {
            $medias->delete(999999999);
        }
        $media = $medias->insert($data);
        $this->assertEquals(999999999, $data['media_id']);
        $medias->delete(999999999);

    }



    public function testAll()
    {
        $tm = $this->tableManager->table('media');
        $all = $tm->all();
        $count = $tm->count();
        $this->assertInternalType('array', $all);
        $this->assertEquals($count, count($all));
    }






    /**
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
        $container_id = $tm->table('media_container')->findOneBy(array('reference' => 'PRODUCT_MEDIAS'))->get('container_id');
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
