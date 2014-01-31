<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Exception;
use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;


use ArrayObject;

class TableSearch {


	/**
	 * @param TableManager
	 */
	protected $tableManager;
	
	/**
	 *
	 * @var \Soluble\Db\Sql\Select;
	 */
	protected $select;

	/**
	 * 
	 * @param string $table table name
	 * @param \Soluble\Normalist\Synthetic\TableManager $tableManager
	 */
	function __construct(Select $select, TableManager $tableManager) {
		$this->select = $select;
		$this->tableManager = $tableManager;
	}

	/**
	 * 
	 * @param int $limit
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function limit($limit)
	{
		$this->select->limit($limit);
		return $this;
	}

	/**
	 * 
	 * @param int $offset
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function offset($offset)
	{
		$this->select->offset($offset);
		return $this;
	}
	
	
	/**
	 * 
	 * @param array $columns
	 * @param boolean $prefixColumnsWithTable
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function columns($columns, $prefixColumnsWithTable=false)
	{
		$this->select->columns($columns, $prefixColumnsWithTable=false);
		return $this;
	}
	
	/**
	 * 
	 * @param array $group
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function group($group) 
	{
		$this->select->group($group);
		return $this;
	}

	/**
	 * 
	 * @param type $order
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function order($order)
	{
		$this->select->order($order);
		return $this;
	}

	/**
	 * 
	 * @param type $predicate
	 * @param type $combination
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function where($predicate, $combination=null)
	{
		$this->select->where($predicate, $combination);
		return $this;
	}
	
	/**
	 * 
	 * @param type $table
	 * @param type $on
	 * @param type $columns
	 * @param type $type
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 */
	function join($table, $on, $columns=null, $type=null)
	{
		$this->select->join($table, $on, $columns, $type);
		return $this;
	}
	
	
	/**
	 * 
	 * @return \Soluble\Db\Sql\Select
	 */
	function getSelect()
	{
		return $this->select;
	}
	
	function getSql()
	{
		return $this->select->getSqlString();
	}
	
	
	/**
	 * 
	 * @return string Json encoded 
	 */
	function toJson()
	{
		return json_encode($this->select->execute()->toArray());
	}
	
	/**
	 * 
	 * @return array
	 */
	function toArray()
	{
		return $this->select->execute()->toArray();
	}

	/**
	 * Return an array indexed by $indexKey
	 * useful for comboboxes...
	 * 
	 * @param string $columnKey
	 * @param string $indexKey
	 * @return array
	 */
	function toArrayColumn($columnKey, $indexKey)
	{
		$select = clone $this->select;
		$select->reset($select::COLUMNS)->columns(array($columnKey, $indexKey));
		return array_column($select->execute()->toArray(), $columnKey, $indexKey);
	}
	
	
}