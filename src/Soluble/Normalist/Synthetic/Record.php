<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Exception;
use Soluble\Normalist\SyntheticTable;
//use Zend\Db\Adapter\Adapter;
use ArrayAccess;

class Record implements ArrayAccess {
	
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
	 * @var \Soluble\Normalist\Synthetic\TableManager
	 */
	protected $tableManager;
	
	
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
	 * @param \Soluble\Normalist\Synthetic\TableManager $tableManager
	 * @param string $tableName
	 * @param array $data
	 */
	function __construct(TableManager $tableManager, $tableName, $data) {
		$this->data  = new \ArrayObject($data);
		$this->clean = true;
		$this->tableName = $tableName;
		$this->primaryKey = $tableManager->getPrimaryKey($tableName);
		$this->tableManager = $tableManager;
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
			$pkvalue = $this[$primary];
			$predicate = array($primary => $pkvalue);
			$data = $this->toArray();
			unset($data[$primary]);
			
			
			$affectedRows = $this->tableManager->update($this->tableName, $data, $predicate);
			if ($affectedRows > 1) {
				// Should never happen
				throw new Exception\ErrorException("Saving record returned more than one affected row");
			}
			$record = $this->tableManager->find($this->tableName, $pkvalue);
			
		} else {
			// insert
			$record = $this->tableManager->insert($this->tableName, $this->toArray());
			
		}
		
		$this->data = new \ArrayObject($record->toArray());
		$this->clean = true;
		return $this;		
	}
	
	/**
	 * @throws \Exception
	 * @return \Soluble\Normalist\Synthetic\Record
	 */
	function delete() {
		$primary = $this->primaryKey;
		$id = $this[$primary];
		if (!$this->clean) {
			throw new \Exception("Cannot delete record '$id', it is not in clean state (not in saved in database state)");
		}
		return $this->tableManager->delete($this->tableName, $id);
	}
	

	/**
	 * Get field value 
	 * @param string $field
	 * @return mixed
	 * @throws Exception\FieldNotFoundException
	 */
	function get($field) {
		return $this->offsetGet($field);
	}
	
	
	
	/**
	 * 
	 * @param string $field
	 * @return boolean
	 */
	function offsetExists($field) {
		return $this->data->offsetExists($field);
	}
	
	/**
	 * Get field value 
	 * @param string $field
	 * @return mixed
	 * @throws Exception\FieldNotFoundException
	 */
	public function offsetGet($field) {
		if (!$this->data->offsetExists($field)) {
			throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
		}
		
		return $this->data->offsetGet($field);
	}
	
	/**
	 * 
	 * @param string $field
	 * @param mixed $value
	 * @return \Soluble\Normalist\Record
	 */
	function offsetSet($field, $value) {
		$this->clean = false;
		$this->data->offsetSet($field, $value);
		return $this;
	}
	
	/**
	 * 
	 * @param string $field
	 * @return \Soluble\Normalist\Record
	 */
	function offsetUnset($field) {
		$this->data->offsetUnset($field);
		return $this;
	}
	
	/**
	 * 
	 * @param string $parent_table
	 */
	function getParent($parent_table) {

		$relations = $this->tableManager->getRelations($this->tableName);
		//$rels = array();
		foreach($relations as $column => $parent) {
			if ($parent['table_name'] == $parent_table) {
				// @todo, check the case when 
				// table has many relations to the same parent
				// we'll have to throw an exception 
				$record = $this->tableManager->findOneBy($parent_table, array(
					$parent['column_name'] => $this->get($column)
				));
				return $record;
			}
		}
		return false;
	}
	

	/**
	 * 
	 * @param string $field
	 * @param mixed $value
	 */
	public function __set($field, $value)
    {
		$this->data->offsetSet($field, $value);
    }

	/**
	 * 
	 * @param string $field
	 * @return mixed
	 * @throws Exception\FieldNotFoundException
	 */
    public function __get($field)
    {
		return $this->data->offsetGet($field);
    }	
	
	
	/*
	function __call($method, $arguments) {
		if (preg_match('/related/', $method)) {
			
			
		}
	}
	*/
	
}
