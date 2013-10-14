<?php

namespace Soluble\FlexStore\Source\Zend;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-14 at 12:05:43.
 */
class SelectSourceTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var SelectSource
	 */
	protected $source;

	
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->adapter = \SolubleTestFactories::getDbAdapter();
		$select = new \Zend\Db\Sql\Select();
		$select->from('user');
		$params = array(
				'adapter' => $this->adapter,
				'select'  => $select
			);
		
		$this->source = new SelectSource($params);
		
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @covers Soluble\FlexStore\Source\Zend\SelectSource::getData
	 */
	public function testGetData() {
		$data = $this->source->getData();
		$this->isInstanceOf('Soluble\FlexStore\ResultSet\ResultSet');
		$d = $data->toArray();
		$this->assertInternalType('array', $d);
		$this->assertArrayHasKey('user_id', $d[0]);
		$this->assertArrayHasKey('email', $d[0]);
	}

	/**
	 * @covers Soluble\FlexStore\Source\Zend\SelectSource::getQueryString
	 */
	public function testGetQueryString() {
		$data = $this->source->getData();
		$sql_string = $this->source->getQueryString();
		$this->assertInternalType('string', $sql_string);
		$this->assertRegExp('/^select/', strtolower(trim($sql_string)));		
	}
	
	
	/**
	 * @covers Soluble\FlexStore\Source\Zend\SelectSource::getQueryString
	 */
	public function testGetQueryStringThrowsInvalidUsageException() {
		$this->setExpectedException('Soluble\FlexStore\Exception\InvalidUsageException');
		$sql_string = $this->source->getQueryString();
		$this->assertInternalType('string', $sql_string);
		$this->assertRegExp('/^select/', strtolower(trim($sql_string)));		
	}
	

}
