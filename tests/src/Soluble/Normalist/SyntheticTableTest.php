<?php

namespace Soluble\Normalist;
use Soluble\Db\Metadata\Source;
use Soluble\Db\Metadata\Exception;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-03 at 17:28:44.
 */
class SyntheticTableTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var SyntheticTable
	 */
	protected $table;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$adapter = \SolubleTestFactories::getDbAdapter();
		$cache   = \SolubleTestFactories::getCacheStorage();
		$metadata = new Source\MysqlISMetadata($adapter);
		$metadata->setCache($cache);
		
		$this->table = new SyntheticTable($adapter);
		$this->table->setMetadata($metadata);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::select
	 */
	public function testSelect() {
		
		$select = $this->table->select('media')->columns(array('media_id'));
		
		$results = $select->execute()->toArray();
		$this->assertInternalType('array', $results);
	}
	
	

	/**
	 * @covers Soluble\Normalist\SyntheticTable::find
	 */
	public function testFind() {
		$user_id = 1;
		$user = $this->table->find('user', $user_id);
		$this->assertEquals($user_id, $user['user_id']);
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::find
	 */
	public function testFindThrowsInvalidArgumentException() {
		$this->setExpectedException('Soluble\Normalist\Exception\InvalidArgumentException');
		
		$this->table->find('user', array('cool', 'test'));
		
		
		//$this->setExpectedException('Soluble\Db\Metadata\Exception\InvalidArgumentException');
		$class = new \stdClass();
		$class->id=1;
		$this->table->find('user', $class);
		  
		 
	}
	
	/**
	 * @covers Soluble\Normalist\SyntheticTable::insert
	 */
	public function testInsertThrowsInvalidQueryException() {
		$this->setExpectedException('Soluble\Normalist\Exception\InvalidQueryException');
		$data = array('column_not_exists' => 1);
		$this->table->insert('media', $data);
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::insert
	 */
	public function testInsertInvalidQueryExceptionMessage() {
		
		$data = array('column_not_exists' => 1);
		try {
			$this->table->insert('media', $data);
		} catch (\Soluble\Normalist\Exception\InvalidQueryException $e) {
			$msg = strtolower($e->getMessage());
			$this->assertContains("column_not_exists", $msg);
			$this->assertContains("unknown column", $msg);
		}
	}
	

	/**
	 * @covers Soluble\Normalist\SyntheticTable::insert
	 */
	public function testInsertNotNullRuntimeExceptionMessage() {
		
		$data = array('title' => "A title that shouln't be saved in phpunit database", 'reference' => null);
		try {
			$this->table->insert('product_type', $data);
		} catch (\Soluble\Normalist\Exception\RuntimeException $e) {
			$msg = strtolower($e->getMessage());
			$this->assertContains("reference", $msg);
			$this->assertContains("cannot be null", $msg);
		}
	}
	
	
	
	/**
	 * @covers Soluble\Normalist\SyntheticTable::insert
	 */
	public function testInsertThrowsRuntimeException() {
		$this->setExpectedException('Soluble\Normalist\Exception\RuntimeException');
		$data  = array(
			'filename'  => 'phpunit_test.pdf',
			'filemtime' => 111000,
			'filesize'  => 5000
		);
		/// wil fail because not container_id specified
		$this->table->insert('media', $data);
	}
	

	
		
	
	/**
	 * 
	 */
	public function testTableThrowsTableNotExistsException() {
		//$this->setExpectedException('InvalidArgumentException', 'Invalid magic');
		$this->setExpectedException('Soluble\Db\Metadata\Exception\TableNotExistException');
		$this->table->find("table_that_not_exists", 1);
		
	}
	
	public function testGetRecordCleanedData() {
		$data = array(
			'cool' => 'test',
			'user_id' => 1,
			'username' => 'test');
		$d = $this->table->getRecordCleanedData('user', $data, $throwException=false);
		$this->assertInstanceOf('ArrayObject', $d);
		$this->assertFalse($d->offsetExists('cool'));
		$this->assertTrue($d->offsetExists('user_id'));
		$this->assertEquals($d['username'], 'test');
	}

	public function testGetRecordCleanedDataThrowsInvalidColumnException() {
		$this->setExpectedException('Soluble\Normalist\Exception\InvalidColumnException');			
		$data = array(
			'cool' => 'test',
			'user_id' => 1,
			'username' => 'test');
		$d = $this->table->getRecordCleanedData('user', $data, $throwException=true);
	}	
	
	/**
	 * @covers Soluble\Normalist\SyntheticTable::all
	 */
	public function testAll() {
		
		$users = $this->table->all('user');
		$this->assertInternalType('array', $users->toArray());
		
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::findOneBy
	 */
	public function testFindOneBy() {
		$user_id = 1;
		$user = $this->table->findOneBy('user', array('user_id' => $user_id));
		$this->assertEquals($user_id, $user['user_id']);

	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::exists
	 */
	public function testExists() {
		$user_id = 1;
		$this->assertTrue($this->table->exists('user', $user_id));
		$this->assertFalse($this->table->exists('user', 78965465));
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::delete
	 * @covers Soluble\Normalist\SyntheticTable::insert
	 */
	public function testDelete() {
		$data = $this->createMediaRecordData('phpunit_testDelete');
		$media = $this->table->findOneBy('media', array('legacy_mapping' => 'phpunit_testDelete'));
		if ($media) {
			// cleanup if any
			
			$this->table->delete('media', $media['media_id']);
		}
		
		
		$media = $this->table->insert('media', $data);
		$media_id = $media['media_id'];
		$return = $this->table->delete('media', $media_id);
		$this->assertTrue($return);	

		$media = $this->table->find('media', $media_id);
		$this->assertFalse($media);
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::insert
	 */
	public function testInsert() {

		$data = $this->createMediaRecordData('phpunit_testInsert');
		$media = $this->table->findOneBy('media', array('legacy_mapping' => $data['legacy_mapping']));
		
		if ($media) {
			$this->table->delete('media', $media['media_id']);
		}
		
		$data['filename'] = 'my_test_filename';
		$return = $this->table->insert('media', $data);
		$this->assertEquals($data['filename'], $return['filename']);
		
		
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::insertOnDuplicateKey
	 */
	public function testInsertOnDuplicateKey() {
		$data = $this->createMediaRecordData('phpunit_testInsertOnDuplicateKeyUpdate');		
		
		$media = $this->table->findOneBy('media', array('legacy_mapping' =>$data['legacy_mapping']));
		
		if ($media) {
			$this->table->delete('media', $media['media_id']);
		}
		
		$record = $this->table->insertOnDuplicateKey('media', $data, array('legacy_mapping'));
		$this->assertTrue($this->table->exists('media', $record['media_id']));
		
		$record['filesize'] = 1000;
		$record->save();
		$this->assertEquals(1000, $record['filesize']);
		
		$record['filesize'] = 5000;
		$record = $this->table->insertOnDuplicateKey('media', $data, array('legacy_mapping'));
		$this->assertEquals(5000, $record['filesize']);

		$record = $this->table->insertOnDuplicateKey('media', $data);
		$this->assertEquals(5000, $record['filesize']);

		$record->delete();

	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::update
	 */
	public function testUpdate() {
		$data = $this->createMediaRecordData('phpunit_testUpdate');		
		$this->table->insertOnDuplicateKey('media', $data);
		$media = $this->table->findOneBy('media', array('legacy_mapping' => $data['legacy_mapping']));
		
		$affectedRows = $this->table->update('media', array('filename' => 'phpunit'), array('media_id' => $media->media_id));
		
		$new_media = $this->table->find('media', $media->media_id);
		
		$this->assertEquals($new_media->filename, 'phpunit');
				
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::getRelations
	 */
	public function testGetRelations() {
		$relations = $this->table->getRelations('media');
		$this->assertInternalType('array', $relations);
		$relation1 = $relations['container_id'];
		$this->assertInternalType('array', $relation1);
		$this->assertEquals($relation1['column_name'], 'container_id');
		$this->assertEquals($relation1['table_name'], 'media_container');
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::getColumnsInformation
	 * @todo   Implement testGetColumnsInformation().
	 */
	public function testGetColumnsInformation() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::getPrimaryKeys
	 */
	public function testGetPrimaryKeys() {
		$primary = $this->table->getPrimaryKeys('media');
		$this->assertInternalType('array', $primary);
		$this->assertEquals($primary[0], 'media_id');		
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::getPrimaryKey
	 */
	public function testGetPrimaryKey() {
		$primary = $this->table->getPrimaryKey('media');
		$this->assertEquals($primary, 'media_id');
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::getMetadata
	 * @todo   Implement testGetMetadata().
	 */
	public function testGetMetadata() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::setDbAdapter
	 * @todo   Implement testSetDbAdapter().
	 */
	public function testSetDbAdapter() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\SyntheticTable::setTablePrefix
	 * @todo   Implement testSetTablePrefix().
	 */
	public function testSetTablePrefix() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
	
	/**
	 * Return a media record suitable for database insertion
	 * @return array
	 */
	protected function createMediaRecordData($legacy_mapping=null) {
		$this->table->insertOnDuplicateKey('media_container', array('reference' => 'PRODUCT_MEDIAS'));
		$container_id = $this->table->findOneBy('media_container', array('reference' => 'PRODUCT_MEDIAS'))->get('container_id');
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
