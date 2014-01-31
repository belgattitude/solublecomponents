<?php
namespace Soluble\Normalist\Synthetic;


use Soluble\Normalist\SyntheticRecord;
use Soluble\Normalist\Exception;
use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;

//use Zend\Cache\StorageFactory;
use Zend\Db\Adapter\AdapterAwareInterface;

//use Soluble\Db\Metadata\Cache\CacheAwareInterface;


use ArrayObject;

class Table implements AdapterAwareInterface {

	/**
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * @param TableManager
	 */
	protected $tableManager;
	
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
	 * @var Zend\Db\Sql\Sql
	 */
	protected $sql;

	/**
	 * 
	 * @param string $table table name
	 * @param \Soluble\Normalist\Synthetic\TableManager $tableManager
	 */
	function __construct($table, TableManager $tableManager) {
		$this->table = $table;
		$this->tableManager = $tableManager;
		$this->sql = new Sql($tableManager->getDbAdapter());
	}
	
	/**
	 * Get a Zend\Db\Select object
	 *  
	 * @param string $table_alias
	 * @return \Soluble\Db\Sql\Select
	 */
	function select($table_alias=null) 
	{
		$prefixed_table = $this->prefixTable($this->table);
		$select = new Select();
		$select->setDbAdapter($this->tableManager->getDbAdapter());
		if ($table_alias === null) {
			$table_spec = $prefixed_table;
		} else {
			$table_spec = array($table_alias => $prefixed_table);
		}
		$select->from($table_spec);
		return $select;
	}
	

	/**
	 * 
	 * @return \Soluble\Normalist\Synthetic\TableSearch
	 * @throws \Exception
	 */
	public function search($table_alias=null)
	{
		return new TableSearch($this->select($table_alias), $this->tableManager);
	}
	
	
	/**
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->search()->toArray();
	}
	
	/**
	 * Find a record
	 * 
	 * @param int $id
	 * @throws Exception\InvalidArgumentException	 
	 * @return SyntheticRecord|false 
	 */
	function find($id) {
		$prefixed_table = $this->prefixTable($this->table);
		if (!is_scalar($id)) {
			$type = gettype($id);
			throw new Exception\InvalidArgumentException("Unable to find a record, argument must be a scalar type (numeric, string,...), type '$type' given");
		}
		$primary = $this->tableManager->getMetadata()->getPrimaryKey($prefixed_table);
		$record =  $this->findOneBy(array($primary => $id));
		return $record;	
	}

	/**
	 * Find a record
	 * 
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @throws Exception\InvalidArgumentException	  
	 * @throws Exception\UnexpectedValueException
	 * @return SyntheticRecord|false 
	 */
	function findOneBy($predicate, $combination=Predicate\PredicateSet::OP_AND) {
		$table = $this->table;
		$select = $this->select($table);
		$select->where($predicate, $combination);
		$results = $select->execute()->toArray();
		if (count($results) == 0) return false;
		if (count($results) > 1) throw new Exception\UnexpectedValueException("Find one by return more than one record");
		return $this->makeRecord($table, $results[0]);
	}
	
	
	/**
	 * Test if a record exists
	 * 
	 * @param int $id
	 * @return boolean
	 */
	function exists($id) {
		$record = $this->find($id);
		return ($record !== false);
	}

	
	/**
	 * Delete a record
	 * 
	 * @param string $table
	 * @param int $id
	 * @return boolean if deletion worked
	 */
	function delete($id) {
		if (!$this->exists($id)) return false;
		$prefixed_table = $this->prefixTable($this->able);
		$primary = $this->tableManager->getMetadata()->getPrimaryKey($prefixed_table);		
		$platform = $this->tableManager->getDbAdapter()->platform;		
		
		$delete = $this->sql->delete($prefixed_table)
				  ->where($platform->quoteIdentifier($primary) . " = " . $platform->quoteValue($id));
		$statement = $this->sql->prepareStatementForSqlObject($delete);
		$result    = $statement->execute();
		//var_dump($result->getAffectedRows()); die('cool');
		return true;
	}

	/**
	 * Insert data into table
	 * @param string $table
	 * @param array|ArrayObject $data
	 * @throws Exception\InvalidQueryException when a column does not exist in database
	 * @throws Exception\RuntimeException when a constraint violation or a not null value is thrown
	 * @throws Exception\ErrorException other exception	 
	 * @return \Soluble\Normalist\SyntheticRecord
	 */
	
	function insert($table, $data) 
	{
		$prefixed_table = $this->prefixTable($table);
		$primary = $this->getMetadata()->getPrimaryKey($prefixed_table);		
		if ($data instanceOf ArrayObject) {
			$d = (array) $data;
		} else {
			$d = $data;
		}

		
		$insert = $this->sql->insert($prefixed_table);
		$insert->values($d);
		
		try {
			$statement = $this->sql->prepareStatementForSqlObject($insert);			
			$result    = $statement->execute();
		} catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
			$messages = array();
			$ex = $e;
			do {
				$messages[] = $ex->getMessage();
			} while ($ex = $ex->getPrevious());	
			$message = join(', ', array_unique($messages));
			
			// In ZF2, PDO_Mysql and MySQLi return different exception, 
			// attempt to normalize
			
			$lmsg = strtolower($message);
			
			if (strpos($lmsg, 'constraint violation') !== false ||
				strpos($lmsg, 'sqlstate[23000]') !== false) {
				$rex = new Exception\RuntimeException($message, $e->getCode(), $e);
				throw $rex;
				
			} else {
				$sql_string = $insert->getSqlString($this->sql->getAdapter()->getPlatform());
				$iqex = new Exception\InvalidQueryException($message, $e->getCode(), $e);
				$iqex->setSqlString($sql_string);
				throw $iqex;
			}
		} catch (\Zend\Db\Adapter\Exception\RuntimeException $e) {
			$messages = array();
			$ex = $e;
			do {
				$messages[] = $ex->getMessage();
			} while ($ex = $ex->getPrevious());	
			$message = join(', ', array_unique($messages));
			$rex = new Exception\RuntimeException($message, $e->getCode(), $e);
			throw $rex;
		}
		
		if (array_key_exists($primary, $d)) {
			// not using autogenerated value
			$id = $d['primary'];
		} else {
			$id = $this->adapter->getDriver()->getLastGeneratedValue();	
		}
		$record = $this->find($table, $id);
		if (!$record) {
			throw new Exception\ErrorException("Insert may have failed, cannot retrieve inserted record with id='$id' on table '$table'");
		}
		return $record;
	}

	/**
	 * 
	 * @param string $table
	 * @param array|ArrayObject $data
	 * @param array|null $duplicate_exclude
	 * @return SyntheticRecord
	 * @throws \Exception
	 */
	function insertOnDuplicateKey($table, $data, $duplicate_exclude=array()) {
		
		$platform = $this->adapter->platform;
		$prefixed_table = $this->prefixTable($table);
		$primary = $this->getMetadata()->getPrimaryKey($prefixed_table);		
		
		if ($data instanceOf ArrayObject) {
			$d = (array) $data;
		} else {
			$d = $data;
		}
		
		
		$insert = $this->sql->insert($prefixed_table);
		$insert->values($d);

		$sql_string = $this->sql->getSqlStringForSqlObject($insert);
		$extras = array(); 
		$excluded_columns = array_merge($duplicate_exclude, array($primary));
		foreach($d as $column => $value) {
			if (!in_array($column, $excluded_columns)) {
				if ($value === null) {
					$v = 'NULL';
				} else {
					$v = $platform->quoteValue($value);
				}
				$extras[] = $platform->quoteIdentifier($column) . ' = ' . $v; 
			}
		}
		$sql_string .= ' on duplicate key update ' . join (',', $extras);
		
		try {
			//$result = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
			
			$stmt = $this->adapter->query($sql_string, Adapter::QUERY_MODE_PREPARE);
			$result = $stmt->execute();
			unset($stmt);
			
		} catch (\Exception $e) {
			$message ="Cannot execute sql [ $sql_string ]";
			throw new Exception\ErrorException($message, $code=1, $e);
		}
		
		if (array_key_exists($primary, $d)) {
			// not using autogenerated value
			$pk_value = $d[$primary];
			
		} else {
			
			$id = $this->adapter->getDriver()->getLastGeneratedValue();	
			
			// This test is not made with id !== null, understand why before changing
			if ($id > 0) {
				$pk_value = $id;
			} else {
				// if the id was not generated, we have to guess on which key
				// the duplicate has been fired
				$unique_keys = $this->getMetadata()->getUniqueKeys($prefixed_table);
				$data_columns = array_keys($d);
				$found = false;
				foreach($unique_keys as $index_name => $unique_columns) {
					//echo "On duplicate key\n\n $index_name \n";
					$intersect = array_intersect($data_columns, $unique_columns);
					if (count($intersect) == count($unique_columns)) {
						// Try to see if we can find a record with the key
						$conditions = array();
						foreach($intersect as $key) {
							$conditions[$key] = $d[$key]; 
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
	 * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
	 * @return int number of affected rows
	 */
	function update($table, $data, $predicate) {
		
		//$platform = $this->adapter->platform;
		$prefixed_table = $this->prefixTable($table);
		//$primary = $this->getMetadata()->getPrimaryKey($prefixed_table);		
		
		if ($data instanceOf ArrayObject) {
			$d = (array) $data;
		} else {
			$d = $data;
		}
		
		
		$update = $this->sql->update($prefixed_table);
		$update->set($d);
		//$update->where($platform->quoteIdentifier($primary) . " = " . $platform->quoteValue($where));
		$update->where($predicate);
		
		//$sql_string = $sql->getSqlStringForSqlObject($update);
		//var_dump($sql_string);
		//die();
		$statement = $this->sql->prepareStatementForSqlObject($update);
		$result    = $statement->execute();
		$affectedRows =  $result->getAffectedRows();
		return $affectedRows;
		
	}
	

	/**
	 * 
	 * @param string $table
	 * @param array $data
	 * @return \Soluble\Normalist\SyntheticRecord
	 */
	protected function makeRecord($table, $data) {
		$record = new Record($this->tableManager, $table, $data);
		return $record;
	} 
	


	/**
	 * Return table relations
	 * @param string $table 
	 * @return array
	 */
	function getRelations($table) {
		$prefixed_table = $this->prefixTable($table);
		$rel = $this->getMetadata()->getRelations($prefixed_table);
		return $rel;
	}
	

	/**
	 * Return table columns
	 * @param string $table 
	 * @return array
	 */
	function getColumnsInformation($table) {
		$prefixed_table = $this->prefixTable($table);
		return $this->getMetadata()->getColumnsInformation($prefixed_table);
	}
	

	/**
	 * Return cleaned data suitable for inserting/updating a table
	 * If $throwException is true, if any non existing column is found
	 * an error will be thrown
	 * 
	 * @param string $table table name
	 * @param array|ArrayObject $data associative array containing data to insert
	 * @param boolean $throwException if true will throw an exception if a column does not exists
	 * @return \ArrayObject
	 * @throws Exception\InvalidColumnException
	 */
	function getRecordCleanedData($table, $data, $throwException=false)
	{
		$d = new \ArrayObject();
		$ci = $this->getColumnsInformation($table);
		$columns = array_keys($ci);
		foreach($data as $column => $value) {
			if (in_array($column, $columns)) {
				$d->offsetSet($column, $value);
			} elseif ($throwException) { 
				throw new Exception\InvalidColumnException("Column '$column' does not exists in table '$table'");
			}
		}
		return $d;
	}

	/**
	 * Return table primary keys
	 * @param string $table
	 * @return array
	 */
	function getPrimaryKeys($table) {
		$prefixed_table = $this->prefixTable($table);
		return $this->getMetadata()->getPrimaryKeys($prefixed_table);
	}

	/**
	 * Return primary key, if multiple primary keys found will
	 * throw an exception
	 * @throws Exception
	 * @return int|string
	 */
	function getPrimaryKey($table) {
		$prefixed_table = $this->prefixTable($table);
		return $this->getMetadata()->getPrimaryKey($prefixed_table);
	}
	
	/**
	 * 
	 * @return \Soluble\Db\Metadata\Source\AbstractSource
	 */
	public function getMetadata() {
		if ($this->metadata === null) {
			$this->metadata = $this->getDefaultMetadata($this->adapter);
		}
		return $this->metadata;
	}
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return \Soluble\Normalist\SyntheticTable
	 */
	public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}
	
	/**
	 * 
	 * @param Adapter $adapter
	 * @throws \Exception
	 */
	protected function getDefaultMetadata(Adapter $adapter) {
		
		$adapterName = $adapter->getPlatform()->getName(); 
		switch (strtolower($adapterName)) {
			case 'mysql':
				$metadata = new Source\MysqlISMetadata($adapter);
				break;
			default :
				throw new \Exception("Cannot load metadata source from adapter '$adapterName', it's not supported.");		
		}
		return $metadata;
		
	}
	
	/**
	 * 
	 * @param \Soluble\Db\Metadata\Source\AbstractSource $metadata
	 * @return \Soluble\Normalist\SyntheticTable
	 */
	public function setMetadata(Source\AbstractSource $metadata) {
		$this->metadata = $metadata;
		return $this;
	}
	
	
	
	/**
	 * 
	 * @param string $tablePrefix
	 * @return \Soluble\Normalist\Synthetic
	 */
	public function setTablePrefix($tablePrefix) {
		$this->tablePrefix = $tablePrefix;
		return $this;
	}

	
	/**
	 * Return prefixed table anme
	 * 
	 * @param string $table
	 * @return string
	 * @throws Exception\InvalidArgumentException
	 */
	protected function prefixTable($table) {
		if (!is_string($table)) throw new Exception\InvalidArgumentException("Table name must be a string");
		if (trim($table) == '') throw new Exception\InvalidArgumentException("Table name is empty");
		return $this->tablePrefix . $table;
	}
	
}