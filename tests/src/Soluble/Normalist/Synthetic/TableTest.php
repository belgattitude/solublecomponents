<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Db\Metadata\Source;
use Soluble\Db\Metadata\Exception;


class TableTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var TableManager
	 */
	protected $tableManager;
	
	
	/**
	 *
	 * @var Table 
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
		
		$this->tableManager = new TableManager($adapter);
		$this->tableManager->setMetadata($metadata);
		
		$this->table = $this->tableManager->getTable('product_category');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::select
	 * @todo   Implement testSelect().
	 */
	public function testSelect() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::find
	 */
	public function testFind() 
	{
		$record = $this->table->find(1);
		$this->assertInstanceOf('\Soluble\Normalist\Synthetic\Record', $record);
		$this->assertEquals(1, $record->category_id);
		$this->assertEquals(1, $record['category_id']);
		
		$record = $this->table->find(984546465);
		$this->assertFalse($record);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::findOneBy
	 * @todo   Implement testFindOneBy().
	 */
	public function testFindOneBy() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::exists
	 */
	public function testExists() {
		$exists = $this->table->exists(1);
		$this->assertTrue($exists);
		$this->assertInternalType('boolean', $exists);

		$exists = $this->table->exists(5464546545454);
		$this->assertFalse($exists);
		$this->assertInternalType('boolean', $exists);
	}
	
	/**
	 * @covers Soluble\Normalist\Synthetic\Table::search
	 */
	public function testSearch() {
		
		$results = $this->table->search()->toArray();
		$this->assertInternalType('array', $results);

		$results = $this->table->search()->limit(10)->toArray();
		$this->assertInternalType('array', $results);
		$this->assertEquals(10, count($results));
		
		$results = $this->table->search()->columns(array('reference'))->limit(1)->toArray();		
		$keys = array_keys($results[0]);
		$this->assertEquals(1, count($keys));
		$this->assertEquals('reference', $keys[0]);
		
		$results = $this->table->search()
						->where(array('reference' => 'AC'))
						->order(array('reference DESC', 'category_id ASC'))
						->toArrayColumn('category_id', 'reference');
		$this->assertArrayHasKey('AC', $results);
		$this->assertEquals(12, $results['AC']);

		$sql = $this->table->search()->getSql();
		$this->assertInternalType('string', $sql);
	
	}

	
	
	
	/**
	 * @covers Soluble\Normalist\Synthetic\Table::all
	 * @todo   Implement testAll().
	 */
	public function testAll() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getArrayColumn
	 * @todo   Implement testGetArrayColumn().
	 */
	public function testGetArrayColumn() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getArray
	 * @todo   Implement testGetArray().
	 */
	public function testGetArray() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::delete
	 * @todo   Implement testDelete().
	 */
	public function testDelete() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::insert
	 * @todo   Implement testInsert().
	 */
	public function testInsert() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::insertOnDuplicateKey
	 * @todo   Implement testInsertOnDuplicateKey().
	 */
	public function testInsertOnDuplicateKey() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::update
	 * @todo   Implement testUpdate().
	 */
	public function testUpdate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getRelations
	 * @todo   Implement testGetRelations().
	 */
	public function testGetRelations() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getColumnsInformation
	 * @todo   Implement testGetColumnsInformation().
	 */
	public function testGetColumnsInformation() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getRecordCleanedData
	 * @todo   Implement testGetRecordCleanedData().
	 */
	public function testGetRecordCleanedData() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getPrimaryKeys
	 * @todo   Implement testGetPrimaryKeys().
	 */
	public function testGetPrimaryKeys() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getPrimaryKey
	 * @todo   Implement testGetPrimaryKey().
	 */
	public function testGetPrimaryKey() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::getMetadata
	 * @todo   Implement testGetMetadata().
	 */
	public function testGetMetadata() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::setDbAdapter
	 * @todo   Implement testSetDbAdapter().
	 */
	public function testSetDbAdapter() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::setMetadata
	 * @todo   Implement testSetMetadata().
	 */
	public function testSetMetadata() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers Soluble\Normalist\Synthetic\Table::setTablePrefix
	 * @todo   Implement testSetTablePrefix().
	 */
	public function testSetTablePrefix() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}
