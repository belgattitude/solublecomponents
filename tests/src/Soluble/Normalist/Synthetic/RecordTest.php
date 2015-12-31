<?php

namespace Soluble\Normalist\Synthetic;

use Soluble\Db\Metadata\Source;
use Soluble\Db\Metadata\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;
use \Zend\Db\Sql\Predicate;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-02-06 at 12:21:13.
 */
class RecordTest extends \PHPUnit_Framework_TestCase
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
     *
     * @var Table
     */
    protected $table;
    

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //$this->adapter = \SolubleTestFactories::getDbAdapter();
        
        //$cache   = \SolubleTestFactories::getCacheStorage();
        //$metadata = new Source\MysqlInformationSchema($this->adapter);
        //$metadata->setCache($cache);
        
        //$this->tableManager = new TableManager($this->adapter);
        //
        //$this->tableManager->setMetadata($metadata);
        $this->tableManager = \SolubleTestFactories::getTableManager();
        $this->adapter = $this->tableManager->getDbAdapter();
        
        
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

    /*
    public function testSetDataThrowsFieldNotFoundException()
    {
        $medias  = $this->tableManager->table('media');
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\FieldNotFoundException');
        $invalid_data = array(
            'coolnotexists' => 'hello'
        );
        $new_record = $medias->newRecord($invalid_data);
    }
     
     */
    
    
    

    public function testToArray()
    {
        $data = $this->table->find(1)->toArray();
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('category_id', $data);
    }
    
    public function testToJson()
    {
        $data = $this->table->find(1)->toArray();
        
        $json = $this->table->find(1)->toJson();
        $this->assertEquals($data, json_decode($json, $assoc = true));
    }
    
    
    public function testGetTable()
    {
        $table  = $this->tableManager->table('media');
        $data   = $this->createMediaRecordData('phpunit_testGetTable');
        $record = $table->insertOnDuplicateKey($data, array('legacy_mapping'));
        
        $returned_table = $record->getTable();
        $this->assertInstanceOf('Soluble\Normalist\Synthetic\Table', $returned_table);
        
        $this->assertEquals($table, $returned_table);
        $this->assertNotEquals($this->table, $returned_table);
    }
     
     
   
    public function test__Get()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__Get');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertEquals($new_record['legacy_mapping'], 'phpunit_test__Get');
        $this->assertEquals($new_record['legacy_mapping'], $new_record->legacy_mapping);
        
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\FieldNotFoundException');
        $a = $new_record->fieldthatnotexists;
    }
    

    public function test__GetThrowsLogicException()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__Get');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertEquals($new_record['legacy_mapping'], 'phpunit_test__Get');
        $new_record->delete();
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $a = $new_record->legacy_mapping;
    }
    

    public function test__SetThrowsLogicException()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__Get');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertEquals($new_record['legacy_mapping'], 'phpunit_test__Get');
        $new_record->delete();
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $new_record->legacy_mapping = 'cool';
    }
    
    public function test__Set()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__Get');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        
        $new_record->legacy_mapping =  'bibi';
        $this->assertEquals('bibi', $new_record['legacy_mapping']);
        $this->assertEquals('bibi', $new_record->offsetGet('legacy_mapping'));
        $this->assertEquals('bibi', $new_record->legacy_mapping);
    }
    
    
    public function testArrayAccess()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_testArrayAccess');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertEquals($new_record['legacy_mapping'], 'phpunit_testArrayAccess');
        
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\FieldNotFoundException');
        $a = $new_record['fieldthatnotexists'];
    }
    
    public function testSetDataThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');

        $stdClass = new \stdClass();
        $record = new Record($stdClass, $this->table);
    }


    public function testSave()
    {
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData('phpunit_testSave');
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        
        $saved_id = $media['media_id'];
        $media['filename'] = 'mynewfilename_testSave';
        $media->save();
        $this->assertEquals('mynewfilename_testSave', $media['filename']);
        $this->assertEquals($saved_id, $media['media_id']);
        
        $media2 = $medias->find($saved_id);
        $this->assertEquals('mynewfilename_testSave', $media2['filename']);
        $this->assertEquals($saved_id, $media2['media_id']);
        
        // test save with a new record
        
        
        $data = $this->createMediaRecordData('phpunit_testSave');
        $record = $medias->record($data);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record['legacy_mapping'] = date('Y-m-d H:i:s');
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record = $record->save();
        $this->assertEquals(Record::STATE_CLEAN, $record->getState());
        $record['filename'] = 'cool';
        $this->assertEquals(Record::STATE_DIRTY, $record->getState());
        $new_record = $record->save();
        $this->assertEquals($new_record, $record);
    }

    public function testSaveWithNewRecordThrowsDuplicateEntryException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\DuplicateEntryException');
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData('phpunit_testSave');
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));

        $record = $medias->record($data);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record->save();
    }
    
    public function testSaveThrowsDuplicateEntryException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\DuplicateEntryException');
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData('phpunit_testSave');
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));

        $data2 = $this->createMediaRecordData('phpunit_testSaveDuplicate');
        $medias->insertOnDuplicateKey($data2, array('legacy_mapping'));
        
        
        $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media['legacy_mapping'] = 'phpunit_testSaveDuplicate';
        $media->save();
    }

    
    

    public function testSaveCheckStates()
    {
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData('phpunit_testSaveCheckState');
        $tobedeleted = $medias->findOneBy(array('legacy_mapping' => 'phpunit_testSaveCheckState'));
        if ($tobedeleted) {
            $tobedeleted->delete();
        }
        
        $record = $medias->record($data);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record->save();
        $this->assertEquals(Record::STATE_CLEAN, $record->getState());
        $record->delete();
        $this->assertEquals(Record::STATE_DELETED, $record->getState());
        
        $record = $medias->record($data);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record['media_id'] = null;
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record->save();
        $this->assertGreaterThan(0, $record['media_id']);
        $this->assertEquals(Record::STATE_CLEAN, $record->getState());
        $record->delete();
        $this->assertEquals(Record::STATE_DELETED, $record->getState());
        
        $this->setExpectedException("Soluble\Normalist\Synthetic\Exception\LogicException");
        $record->save();
    }
    
    
    public function testSaveThrowsUnexpectedValueException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\UnexpectedValueException');
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData('phpunit_testSaveCheckState');
        $tobedeleted = $medias->findOneBy(array('legacy_mapping' => 'phpunit_testSaveCheckState'));
        if ($tobedeleted) {
            $tobedeleted->delete();
        }
        
        $record = $medias->record($data);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record->save();
        $this->assertEquals(Record::STATE_CLEAN, $record->getState());
        $record['media_id'] = null;
        $this->assertEquals(Record::STATE_DIRTY, $record->getState());
        $record->save();
    }
    
    
    public function testDelete()
    {
        // Test with new record
        $medias = $this->tableManager->table('media');

        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $data = $this->createMediaRecordData('phpunit_testDelete');
        $record = $medias->record($data);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
        $record->delete();
        $this->assertEquals(Record::STATE_DELETED, $record->getState());
    }

    public function testDeleteByRecord()
    {
        $medias   = $this->tableManager->table('media');
        $data     = $this->createMediaRecordData('phpunit_testDelete');
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media_id = $media['media_id'];
        $this->assertEquals(Record::STATE_CLEAN, $media->getState());
        $this->assertTrue($medias->exists($media_id));
        $media->delete();
        
        $this->assertFalse($medias->exists($media_id));
        
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $media->delete();
    }
    
    public function testDeleteByNewRecordThrowsLogicException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $medias   = $this->tableManager->table('media');
        $data     = $this->createMediaRecordData('phpunit_testLogicExceptionNewRecord');
        $record   = $medias->record($data);
        $record->delete();
    }
    
    public function testDeleteByRecordThrowsLogicException()
    {
        $medias   = $this->tableManager->table('media');
        $data     = $this->createMediaRecordData('phpunit_testLogicExceptionAfterDelete');
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();//  $media->delete();
        
        
        try {
            $a = $media->offsetGet('legacy_mapping');
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $a = $media['legacy_mapping'];
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");

        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $a = $media['legacy_mapping'];
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media['legacy_mapping'] = 'cool';
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media['legacy_mapping'] = 'cool';
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");

        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->offsetSet('legacy_mapping', 'cool');
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        
        
        // TEST START
        /*
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->getParent('cool');
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        */
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->delete();
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");

        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->save();
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");

        
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->offsetExists('legacy_mapping');
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->toArray();
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
        
        
        // TEST START
        $catched = false;
        $media    = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $media->delete();
        try {
            $media->setData(array());
        } catch (\Soluble\Normalist\Synthetic\Exception\LogicException $e) {
            $catched=true;
        }
        $this->assertTrue($catched, "LogicExceptionAfterDelete works as expected");
    }
    


    public function testOffsetExists()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_testOffsetExists');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        
        
        $exists = $new_record->offsetExists('fieldthatnotexists');
        $this->assertFalse($exists);
        
        $exists = $new_record->offsetExists('media_id');
        $this->assertTrue($exists);
    }

    public function testOffsetGet()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__Get');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertEquals($new_record->offsetGet('legacy_mapping'), 'phpunit_test__Get');
        $this->assertEquals($new_record->offsetGet('legacy_mapping'), $new_record['legacy_mapping']);
        
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\FieldNotFoundException');
        $new_record->offsetGet('fieldthatnotexists');
    }
    

    
    /*
    public function testIsDirty()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_testIsDirty');
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertFalse($media->isDirty());
        $media['legacy_mapping'] = 'cool';
        $this->assertTrue($media->isDirty());

        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertFalse($media->isDirty());
        $media->offsetSet('legacy_mapping', 'cool');
        $this->assertTrue($media->isDirty());
        
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $this->assertFalse($media->isDirty());
        $media['legacy_mapping'] = 'cool';
        $this->assertTrue($media->isDirty());
    }*/

    public function testOffsetSet()
    {
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__OffsetGet');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $new_record['legacy_mapping'] = 'cool';
        $this->assertEquals('cool', $new_record['legacy_mapping']);
        $this->assertEquals('cool', $new_record->offsetGet('legacy_mapping'));
        $this->assertEquals('cool', $new_record->legacy_mapping);
        
        $new_record->offsetSet('legacy_mapping', 'bibi');
        $this->assertEquals('bibi', $new_record['legacy_mapping']);
        $this->assertEquals('bibi', $new_record->offsetGet('legacy_mapping'));
        $this->assertEquals('bibi', $new_record->legacy_mapping);
    }

    public function testOffsetUnsetTriggerFieldNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\FieldNotFoundException');
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__OffsetUnGet');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $new_record->offsetUnset('legacy_mapping');
        $test = $new_record['legacy_mapping'];
    }
    
    
    public function testOffsetUnsetThrowsLogicException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $medias = $this->table->getTableManager()->table('media');
        $data = $this->createMediaRecordData('phpunit_test__OffsetUnGet');
        $new_record = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $new_record->delete();
        $new_record->offsetUnset('legacy_mapping');
    }

    /*
    public function testGetParent()
    {
        
        $data = $this->createMediaRecordData('phpunit_testGetParent');
        $medias = $this->table->getTableManager()->table('media');
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));

        $parent = $media->getParent('media_container');
        $this->assertEquals($media['container_id'], $parent['container_id']);
        
    }

    public function testGetParentThrowsRelationNotFoundException()
    {
        
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\RelationNotFoundException');
        $data = $this->createMediaRecordData('phpunit_testGetParent');
        $medias = $this->table->getTableManager()->table('media');
        $media = $medias->insertOnDuplicateKey($data, array('legacy_mapping'));
        $parent = $media->getParent('product_category');
        
    }
    */
    
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
