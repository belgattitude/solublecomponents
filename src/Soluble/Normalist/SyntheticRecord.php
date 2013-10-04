<?php
namespace Soluble\Normalist;
use Soluble\Normalist\SyntheticTable;
use Zend\Db\Adapter\Adapter;
use ArrayAccess;

class SyntheticRecord implements ArrayAccess {
	
	/**
	 *
	 * @var \ArrayObject
	 */
	protected $data;
	
	/**
	 *
	 * @var string 
	 */
	protected $tableName;
	
	/**
	 *
	 * @var \Soluble\Normalist\SyntheticTable
	 */
	protected $syntheticTable;
	
	
	/**
	 *
	 * @var string
	 */
	protected $primaryKey;
	
	/**
	 *
	 * @var boolean 
	 */
	protected $clean;
	
	/**
	 * 
	 * @param \Soluble\Normalist\SyntheticTable $syntheticTable
	 * @param string $tableName
	 * @param array $data
	 */
	function __construct(SyntheticTable $syntheticTable, $tableName, $data) {
		$this->data  = new \ArrayObject($data);
		$this->clean = true;
		$this->tableName = $tableName;
		$this->primaryKey = $syntheticTable->getPrimaryKey($tableName);
		$this->syntheticTable = $syntheticTable;
	}

	/**
	 * 
	 * @return array
	 */
	function toArray() {
		return (array) $this->data;
	}
	
	
	/**
	 * @throws \Exception
	 * @return \Soluble\Normalist\SyntheticRecord
	 */
	function save() {
		$primary = $this->primaryKey;
		if ($this[$primary] != '') {
			// update
			$record = $this->syntheticTable->update($this->tableName, $this->toArray(), $this[$primary]);
		} else {
			// insert
			$record = $this->syntheticTable->insert($this->tableName, $this->toArray());
		}
		
		$this->data = new \ArrayObject($record->toArray());
		$this->clean = true;
		return $this;		
	}
	
	/**
	 * @throws \Exception
	 * @return \Soluble\Normalist\Record
	 */
	function delete() {
		$primary = $this->primaryKey;
		$id = $this[$primary];
		if (!$this->clean) {
			throw new \Exception("Cannot delete record '$id', it is not in clean state (not in saved in database state)");
		}
		return $this->syntheticTable->delete($this->tableName, $id);
	}
	
	/**
	 * 
	 * @param string $column
	 * @return mixed
	 */
	function get($column) {
		if (!$this->data->offsetExists($column)) {
			throw new \Exception("Cannot get column value, column '$column' does not exists in record.");
		}
		return $this->offsetGet($column);
	}
	
	
	
	/**
	 * 
	 * @param string $offset
	 * @return boolean
	 */
	function offsetExists($offset) {
		return $this->data->offsetExists($offset);
	}
	
	/**
	 * 
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->data->offsetGet($offset);
	}
	
	/**
	 * 
	 * @param string $offset
	 * @param mixed $value
	 * @return \Soluble\Normalist\Record
	 */
	function offsetSet($offset, $value) {
		$this->clean = false;
		$this->data->offsetSet($offset, $value);
		return $this;
	}
	
	/**
	 * 
	 * @param string $offset
	 * @return \Soluble\Normalist\Record
	 */
	function offsetUnset($offset) {
		$this->data->offsetUnset($offset);
		return $this;
	}
	
	/**
	 * 
	 * @param string $parent_table
	 */
	function getParent($parent_table) {
		
		$relations = $this->syntheticTable->getRelations($this->tableName);

		$rels = array();
		foreach($relations as $column => $parent) {
			if ($parent['table_name'] == $parent_table) {
				// @todo, check the case when 
				// table has many relations to the same parent
				// we'll have to throw an exception 
				$record = $this->syntheticTable->findOneBy($parent_table, array(
					$parent['column_name'] => $this->get($column)
				));
				return $record;
			}
		}
		return false;
	}
	

	public function __set($name, $value)
    {
		$this->data->offsetSet($name, $value);
    }

    public function __get($name)
    {
		if (!$this->data->offsetExists($name)) {
			throw new \Exception("Cannot get record column '$name' on table '{$this->tableName}'");
		}
		return 	$this->data->offsetGet($name);

    }	
	
	
	/*
	function __call($method, $arguments) {
		if (preg_match('/related/', $method)) {
			
			
		}
	}
	*/
	
}
