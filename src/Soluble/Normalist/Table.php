<?php
namespace Soluble\Normalist;


use Soluble\Normalist\Record;
use Soluble\Normalist\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
//use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;
use Zend\Cache\StorageFactory;
use Zend\Db\Adapter\AdapterAwareInterface;

use Soluble\Db\Metadata\Cache\CacheAwareInterface;
use Soluble\Db\Metadata\Source;



use ArrayObject;

class Table implements AdapterAwareInterface {


	/**
	 *
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 */
	protected $adapter;

	
	/**
	 *
	 * @var Source\AbstractSource
	 */
	protected $metadata;
	
	
	/**
	 *
	 * @var string
	 */
	protected $tablePrefix;
	
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @param string $table table name
	 */
	function __construct(Adapter $adapter) {
		$this->setDbAdapter($adapter);
	}

	/**
	 * Return prefixed table anme
	 * @param string $table
	 * @return string
	 * @throws Exception\InvalidArgumentException
	 */
	function getTableName($table) {
		if (!is_string($table)) throw new Exception\InvalidArgumentException("Table name must be a string");
		if (trim($table) == '') throw new Exception\InvalidArgumentException("Table name is empty");
		return $this->tablePrefix . $table;
	}

	
	/**
	 * 
	 * @param string $table
	 * @param string $table_alias
	 * @return \Zend\Db\Sql\Select
	 */
	function select($table, $table_alias=null) {

		$prefixed_table = $this->getTableName($table);
		$select = new Select();
		if ($table_alias === null) {
			$table_spec = $prefixed_table;
		} else {
			$table_spec = array($table_alias => $prefixed_table);
		}
		$select->from($table_spec);
		return $select;
	}

	
	/**
	 * Find a record
	 * @param string $table
	 * @param int $id
	 * @return array|false 
	 * @throws Exception\RecordNotFoundException
	 * @throws Exception\InvalidArgumentException	 
	 */
	function find($table, $id) {
		$prefixed_table = $this->getTableName($table);
		$primary = $this->metadata->getPrimaryKey($prefixed_table);
		$record =  $this->findOneBy($table, array($primary => $id));
		if (!$record) throw new Exception\RecordNotFoundException("Cannot find a record with id '$primary=$id' on table '$prefixed_table'");
		return $record;	
	}
	
	/**
	 * Fetch all records in a table
	 * @param string $table
	 * @param array|string|null $columns
	 * @return array
	 */
	function fetchAll($table, $columns=null) {
		$table = $this->getTableName($table);
		$sql = new Sql($this->adapter);
		$select = new Select();
		$select->from($table);
		if ($columns !== null) {
			$columns = (array) $columns;
			$select->columns($columns);
		}
		$sql_string = $sql->getSqlStringForSqlObject($select);
		$results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray();		
		return $results;
	}
	
	/**
	 * Find a record
	 * 
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @throws Exception\InvalidArgumentException	  
	 * @return array|false 
	 */
	function findOneBy($table, $predicate, $combination=Predicate\PredicateSet::OP_AND) {
		
		$prefixed_table = $this->getTableName($table);
		$sql = new Sql($this->adapter);
		$select = new Select();
		$select->from($prefixed_table);
		$select->where($predicate, $combination);
		$sql_string = $sql->getSqlStringForSqlObject($select);
		$results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray();		
		if (count($results) == 0) return false;
		if (count($results) > 1) throw new Exception\UnexpectedValueException("Find one by return more than one record");
		return $this->makeRecord($table, $results[0]);
	}
	
	/**
	 * 
	 * @param string $table
	 * @param array $data
	 * @return \Soluble\Normalist\Record
	 */
	protected function makeRecord($table, $data) {
		$record = new Record($this, $table, $data);
		return $record;
	} 
	
	
	/**
	 * Test if a record exists
	 * @param string $table
	 * @param int $id
	 */
	function exists($table, $id) {
		$record = $this->find($table, $id);
		return ($record !== false);
	}

	
	/**
	 * 
	 * @param string $table
	 * @param int $id
	 * @return boolean if deletion worked
	 */
	function delete($table, $id) {
		if (!$this->exists($table, $id)) {
			return false;
		}
		
		$prefixed_table = $this->getTableName($table);
		$primary = $this->metadata->getPrimaryKey($prefixed_table);		
		
		$platform = $this->adapter->platform;		
		$sql = new Sql($this->adapter);
		$delete = $sql->delete($prefixed_table)
				  ->where($platform->quoteIdentifier($primary) . " = " . $platform->quoteValue($id));
		$statement = $sql->prepareStatementForSqlObject($delete);
		$result    = $statement->execute();
		//var_dump($result->getAffectedRows()); die('cool');
		return true;
		
	}

	/**
	 * Insert data into table
	 * @param string $table
	 * @param array|ArrayObject $data
	 * @return \Soluble\Normalist\Record
	 */
	function insert($table, $data) {
		$prefixed_table = $this->getTableName($table);
		$primary = $this->metadata->getPrimaryKey($prefixed_table);		

		
		if ($data instanceOf ArrayObject) {
			$d = (array) $data;
		} else {
			$d = $data;
		}
		
		$sql = new Sql($this->adapter);
		$insert = $sql->insert($prefixed_table);
		$insert->values($data);

		$statement = $sql->prepareStatementForSqlObject($insert);
		$result    = $statement->execute();
		
		if (array_key_exists($primary, $data)) {
			// not using autogenerated value
			$id = $data['primary'];
		} else {
			$id = $this->adapter->getDriver()->getLastGeneratedValue();	
		}
		$record = $this->find($table, $id);
		if (!$record) {
			throw new \Exception("Insert may have failed, cannot retrieve inserted record with id='$id' on table '$table'");
		}
		return $record;
	}
	
	/**
	 * 
	 * @param string $table
	 * @param array|ArrayObject $data
	 * @param array|null $duplicate_exclude
	 * @return \Soluble\Normalist\Record
	 * @throws \Exception
	 */
	
	
	function insertOnDuplicateKey($table, $data, $duplicate_exclude=array()) {
		
		$platform = $this->adapter->platform;
		$prefixed_table = $this->getTableName($table);
		$primary = $this->metadata->getPrimaryKey($prefixed_table);		
		
		if ($data instanceOf ArrayObject) {
			$d = (array) $data;
		} else {
			$d = $data;
		}
		
		$sql = new Sql($this->adapter);
		$insert = $sql->insert($prefixed_table);
		$insert->values($data);

		$sql_string = $sql->getSqlStringForSqlObject($insert);
		$extras = array(); 
		$excluded_columns = array_merge($duplicate_exclude, array($primary));
		foreach($data as $column => $value) {
			if (!in_array($column, $excluded_columns)) {
				$extras[] = $platform->quoteIdentifier($column) . ' = ' . $platform->quoteValue($value); 
			}
		}
		$sql_string .= ' on duplicate key update ' . join (',', $extras);
		
		$result = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
		
		if (array_key_exists($primary, $data)) {
			// not using autogenerated value
			$pk_value = $data[$primary];
			
		} else {
			
			$id = $this->adapter->getDriver()->getLastGeneratedValue();	
			
			// This test is not made with id !== null, understand why before changing
			if ($id > 0) {
				$pk_value = $id;
			} else {
				// if the id was not generated, we have to guess on which key
				// the duplicate has been fired
				$unique_keys = $this->metadata->getUniqueKeys($prefixed_table);
				$data_columns = array_keys($data);
				$found = false;
				foreach($unique_keys as $index_name => $unique_columns) {
					echo "On duplicate key\n\n $index_name \n";
					$intersect = array_intersect($data_columns, $unique_columns);
					if (count($intersect) == count($unique_columns)) {
						// Try to see if we can find a record with the key
						$conditions = array();
						foreach($intersect as $key) {
							$conditions[$key] = $data[$key]; 
						}
						
						$record = $this->findOneBy($table, $conditions);
						if ($record) {
							$found = true;
							$pk_value = $record[$primary];
							break;
						} 
					}
				}
				
				if (!$found) {
					throw new \Exception("After probing all unique keys in table '$table', cannot dertermine which one was fired when using on duplicate key.");
				}
			}
		}
		
		$record = $this->find($table, $pk_value);
		if (!$record) {
			throw new \Exception("insertOnDuplicateKey cannot retrieve record with $primary=$pk_value");
		} elseif ($record[$primary] != $pk_value) {
			throw new \Exception("System error, returned primary key value is different, check \"$sql_string\"");
		} 
		return $record;
	}
	

	/**
	 * Update data into table
	 * @param string $table
	 * @param array|ArrayObject $data
	 * @return array|false
	 */
	function update($table, $data, $where) {
		
		$platform = $this->adapter->platform;
		$prefixed_table = $this->getTableName($table);
		$primary = $this->metadata->getPrimaryKey($prefixed_table);		
		
		
		if ($data instanceOf ArrayObject) {
			$d = (array) $data;
		} else {
			$d = $data;
		}
		
		$sql = new Sql($this->adapter);
		$update = $sql->update($prefixed_table);
		$update->set($data);
		$update->where($platform->quoteIdentifier($primary) . " = " . $platform->quoteValue($where));
		
		$statement = $sql->prepareStatementForSqlObject($update);
		$result    = $statement->execute();

		return $this->find($table, $where);
		
		
	}
	
	


	/**
	 * Return table relations
	 * @param string $table 
	 * @return array
	 */
	function getRelations($table) {
		$prefixed_table = $this->getTableName($table);
		$rel = $this->metadata->getRelations($prefixed_table);
		return $rel;
	}
	

	/**
	 * Return table columns
	 * @param string $table 
	 * @return array
	 */
	function getColumnsInformation($table) {
		$prefixed_table = $this->getTableName($table);
		return $this->metadata->getColumnsInformation($prefixed_table);
	}

	/**
	 * Return table primary keys
	 * @param string $table
	 * @return array
	 */
	function getPrimaryKeys($table) {
		$prefixed_table = $this->getTableName($table);
		return $this->metadata->getPrimaryKeys($prefixed_table);
	}

	/**
	 * Return primary key, if multiple primary keys found will
	 * throw an exception
	 * @throws Exception
	 * @return int|string
	 */
	function getPrimaryKey($table) {
		$prefixed_table = $this->getTableName($table);
		return $this->metadata->getPrimaryKey($prefixed_table);
	}

	/**
	 * 
	 * @param Adapter $adapter
	 * @throws \Exception
	 */
	protected function loadMetadata(Adapter $adapter) {
		if ($this->metadata === null) {
			$adapterName = $adapter->getPlatform()->getName(); 
			switch (strtolower($adapterName)) {
				case 'mysql':
					$this->metadata = new Source\MysqlISMetadata($adapter);
					break;
				default :
					throw new \Exception("Cannot load metadata source from adapter '$adapterName', it's not supported.");		
			}
		}
	}
	
	/**
	 * 
	 * @return \Soluble\Db\Metadata\Source\AbstractSource
	 */
	public function getMetadata() {
		return $this->metadata;
	}
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return \Soluble\Normalist\Table
	 */
	public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->loadMetadata($adapter);
		return $this;
	}
	
	/**
	 * 
	 * @param string $tablePrefix
	 * @return \Soluble\Normalist\Table
	 */
	public function setTablePrefix($tablePrefix) {
		$this->tablePrefix = $tablePrefix;
		return $this;
	}
	

}
