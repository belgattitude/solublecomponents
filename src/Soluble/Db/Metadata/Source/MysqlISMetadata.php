<?php
namespace Soluble\Db\Metadata\Source;



use Soluble\Db\Metadata\Source\AbstractSource;
use Soluble\Db\Metadata\Cache\CacheAwareInterface;
use Soluble\Db\Metadata\Exception;

use Zend\Db\Adapter\Adapter;
use Zend\Cache\Storage\StorageInterface;


class MysqlISMetadata extends AbstractSource implements CacheAwareInterface
{
	
	/**
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;

	/**
	 *
	 * @var array
	 */
	protected $tables_information = array();
	
	
	/**
	 *
	 * @var boolean
	 */
	protected $cacheEnabled = false;

	/**
	 *
	 * @var Zend\Cache\Storage\StorageInterface
	 */
	protected $cacheStorage;

	
	/**
	 * @param array
	 */
	protected $localCache;
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @param string $schema default schema, taken from adapter if not given
	 * @throws \Exception
	 */
	function __construct(Adapter $adapter, $schema=null)
	{
		$this->adapter = $adapter;
		if ($schema === null) {
			$this->schema = $adapter->getCurrentSchema();
		}
		
	}
	
	
	/**
	 * Get primary key on table 
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\NoPrimaryKeyException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * @return string
	 */
	function getPrimaryKey($table, $schema=null)
	{
		return $this->getFromLocalCache('primary_key', $table, $schema);
	}
	
	/**
	 * Get primary keys (multiple) on table 
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\NoPrimaryKeyException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 *
	 * @return array
	 */
	function getPrimaryKeys($table, $schema=null)
	{
		return $this->getFromLocalCache('primary_keys', $table, $schema);
		
	}

	/**
	 * Get unique keys on table 
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @param boolean $include_primary include primary keys in the list
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\NoPrimaryKeyException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * @return array
	 */
	function getUniqueKeys($table, $schema=null, $include_primary=false)
	{
		return $this->getFromLocalCache('unique_keys', $table, $schema);
	}
	
	

	/**
	 * Return indexes information on a table
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * 
	 * @return array
	 */	
	function getIndexesInformation($table, $schema=null) {
		
		return $this->getFromLocalCache('indexes_information', $table, $schema);
	}


	/**
	 * Return columns information on a table
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * 
	 * @return array
	 */
	function getColumnsInformation($table, $schema=null)
	{
		return $this->getFromLocalCache('columns_information', $table, $schema);
	}

	
	/**
	 * Return relations on a table
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * 
	 * @return array
	 */	
	function getRelations($table, $schema=null)
	{
		return $this->getFromLocalCache('relations', $table, $schema);
	}
	
	
	
	
	
	/**
	 * Return tables information on a schema
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * 
	 * @return array
	 */
	function getTablesInformation($schema=null)
	{
		if ($this->localCache[$schema]['schema']['tables'] === null) {
			$this->localCache[$schema]['schema']['tables'] = $this->loadTablesInformation($s);
		}
		return $this->localCache[$schema]['schema']['tables'];
		
	}
	
	
	
	
	/**
	 * 
	 * @param string $key
	 * @param string $table
	 * @param string $schema
	 * @throws Exception\ExceptionInterface
	 * @return mixed
	 */
	protected function getFromLocalCache($key, $table, $schema=null) {

		if ($schema === null) {
			$schema = $this->schema; 
		} elseif (!is_string($schema) || trim($schema) == '') {
			throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
		}
		
		if ($this->localCache[$schema]['tables'][$table][$key] === null) {
			$this->loadLocalCache($table, $schema);
		}
		
		return $this->localCache[$schema]['tables'][$table][$key];
		
	}
	
	/**
	 * 
	 * @param string $table
	 * @param string $schema
	 * @throws Exception\ExceptionInterface
	 */
	protected function loadLocalCache($table, $schema=null) {
		
		if (!is_string($table) || trim($table) == '') {
			throw new Exception\InvalidArgumentException("Table name must be a valid string or an empty string detected");
		} 
		if ($schema === null) {
			$schema = $this->schema; 
		} elseif (!is_string($schema) || trim($schema) == '') {
			throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
		}
		
		
		$s = $schema;
		$t = $table;

		/**
		 * @todo make not lazy works as well
		 */
		$lazy = true;
		
		if ($this->cacheEnabled) {
			$cache_key = md5(__CLASS__ . __METHOD__ . $schema);
			if ($this->cacheStorage->hasItem($cache_key)) {
				$this->localCache = unserialize($this->cacheStorage->getItem($cache_key));
			}
		}
		
		if ($lazy) {
				$this->localCache[$s]['tables'][$t]['columns_information'] = $this->loadColumnsInformation($t, $s);
				$this->localCache[$s]['tables'][$t]['indexes_information'] = $this->loadIndexesInformation($t, $s);
				$this->localCache[$s]['tables'][$t]['unique_keys']		   = $this->loadUniqueKeys($t, $s);
				$this->localCache[$s]['tables'][$t]['primary_keys']		   = $this->loadPrimaryKeys($t, $s);
				$this->localCache[$s]['tables'][$t]['primary_key']		   = $this->loadPrimaryKey($t, $s);
				$this->localCache[$s]['tables'][$t]['relations']		   = $this->loadRelations($t, $s);
				
		} else {
			foreach($this->getTablesInformation() as $t => $info) {
				$this->localCache[$s]['tables'][$t]['columns_information'] = $this->loadColumnsInformation($t, $s);
				$this->localCache[$s]['tables'][$t]['indexes_information'] = $this->loadIndexesInformation($t, $s);
				$this->localCache[$s]['tables'][$t]['unique_keys']		 = $this->loadUniqueKeys($t, $s);
				$this->localCache[$s]['tables'][$t]['primary_keys'] = $this->loadPrimaryKeys($t, $s);
				$this->localCache[$s]['tables'][$t]['primary_key'] = $this->loadPrimaryKey($t, $s);
				$this->localCache[$s]['tables'][$t]['relations'] = $this->loadRelations($t, $s);
			}
		}
		
		if ($this->cacheEnabled) {
			$this->cacheStorage->setItem($cache_key, serialize($this->localCache));
		}
		
	}
	
	/**
	 * Get primary key on table 
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\NoPrimaryKeyException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * @return string
	 */
	protected function loadPrimaryKey($table, $schema=null)
	{
		$pks = $this->getPrimaryKeys($table, $schema);
		if (count($pks) > 1) {
			throw new Exception\ErrorException("getPrimaryKey doesn't support multiple columns pk, see table '$table'");
		}
		return $pks[0];
	}	
	
	/**
	 * Load unique keys on table 
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @param boolean $include_primary include primary keys in the list
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\NoPrimaryKeyException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * @return array
	 */
	protected function loadUniqueKeys($table, $schema=null, $include_primary=false)
	{
		$unique_keys = array();
		$indexes = $this->getIndexesInformation($table, $schema);
		foreach($indexes as $index_name => $info) {
			if ($info['unique']) {
				if ($include_primary || $index_name != 'PRIMARY') {
					$unique_keys[$index_name] = $info['columns'];
				}
			}
		}
		return $unique_keys;
	}
	
	/**
	 * Get primary keys (multiple) on table 
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\NoPrimaryKeyException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 *
	 * @return array
	 */
	protected function loadPrimaryKeys($table, $schema=null)
	{
		
		$columns = $this->getColumnsInformation($table, $schema);
		$primary_keys = array();
		foreach($columns as $key => $column) {
			if ($column['COLUMN_KEY'] == 'PRI') {
				$primary_keys[] = $key;
			}
		}
		if (count($primary_keys) == 0) {
			throw new Exception\NoPrimaryKeyException("Cannot find a primary key on table '$table'");
		}
		return $primary_keys;
	}
	
	
	
	/**
	 * Load relations on a table
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * 
	 * @return array
	 */	
	protected function loadRelations($table, $schema=null)
	{
		
		if (!is_string($table) || trim($table) == '') {
			throw new Exception\InvalidArgumentException("Table name must be a valid string or an empty string detected");
		} 
		if ($schema === null) {
			$schema = $this->schema; 
		} elseif (!is_string($schema) || trim($schema) == '') {
			throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
		}
		
		
		$tables = $this->getTables($schema);
		if (!in_array($table, $tables)) {
			throw new Exception\TableNotExistException("Table '$table' does not exists in database '$schema'");						
		}
		$query = "
				SELECT * 
					FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
					where TABLE_SCHEMA = '$schema'
					and TABLE_NAME = '$table'
					and REFERENCED_TABLE_NAME is not null";

		try {
			$result = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
		} catch (\Exception $e) {
			throw new Exception\ErrorException($e->getMessage());
		}
		
		$relations = array();

		foreach($result->toArray() as $record) {
			$constraint_name	= $record['CONSTRAINT_NAME'];
			//$table_name			= $record['TABLE_NAME'];
			//$table_schema		= $record['TABLE_SCHEMA'];
			$column_name		= $record['COLUMN_NAME'];

			$ref_table_schema	= $record['REFERENCED_TABLE_SCHEMA'];
			$ref_table_name		= $record['REFERENCED_TABLE_NAME'];
			$ref_column_name	= $record['REFERENCED_COLUMN_NAME'];
			$relations[$column_name] = array(
				'table_schema' => $ref_table_schema,
				'table_name' => $ref_table_name,
				'column_name' => $ref_column_name,
				'constraint_name' => $constraint_name,
				
			);
		};
		
		return $relations;
		
	}
	
	

	/**
	 * Load column information
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * 
	 * @return array
	 */
	protected function loadColumnsInformation($table, $schema=null)
	{
		
		if (!is_string($table) || trim($table) == '') {
			throw new Exception\InvalidArgumentException("Table name must be a valid string or an empty string detected");
		} 
		if ($schema === null) {
			$schema = $this->schema; 
		} elseif (!is_string($schema) || trim($schema) == '') {
			throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
		}
		
		$tables = $this->getTables($schema);
		
		if (!in_array($table, $tables)) {
			throw new Exception\TableNotExistException("Table '$table' does not exists in database '$schema'");			
		}
		$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$schema' and TABLE_NAME = '$table'";
		
		try {
			$result = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
		} catch (\Exception $e) {
			throw new Exception\ErrorException($e->getMessage());
		}

		if ($result->count() == 0) {
			throw new \Exception("Table '$table' on schema '$schema' seems to not have columns");
		}

		$columns = array();
		
		foreach($result->toArray() as $record) {
			$name = $record['COLUMN_NAME'];
			$columns[$name] = $record;
		};
		
		return $columns;
		
	}
	
	/**
	 * Load table information on a schema
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * 
	 * @return array
	 */
	protected function loadTablesInformation($schema=null)
	{

		if ($schema === null) {
			$schema = $this->schema; 
		} elseif (!is_string($schema) || trim($schema) == '') {
			throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
		}
		

		if (!array_key_exists($schema, $this->tables_information)) {
			
			if (trim($schema) == '') throw new \Exception("Database param must be a valid string, empty string detected");
			$query = "SELECT * FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA` = '$schema'";
			try {
				$result = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
			} catch (\Exception $e) {
				throw new Exception\ErrorException($e->getMessage());
			}

			$tables = array();
			foreach($result->toArray() as $record) {
				$name = $record['TABLE_NAME'];
				$tables[$name] = $record;
			}
		}

		return $tables;
	}
	

	/**
	 * Load indexes information on a table
	 * 
	 * @param string $table table name
	 * @param string $schema schema name
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\ErrorException
	 * @throws Exception\ExceptionInterface
	 * @throws Exception\TableNotExistException
	 * 
	 * @return array
	 */	
	protected function loadIndexesInformation($table, $schema) {
		
		if (!is_string($table) || trim($table) == '') {
			throw new Exception\InvalidArgumentException("Table name must be a valid string or an empty string detected");
		} 
		if ($schema === null) {
			$schema = $this->schema; 
		} elseif (!is_string($schema) || trim($schema) == '') {
			throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
		}
		
		$tables = $this->getTables($schema);
		if (!in_array($table, $tables)) {
			throw new Exception\TableNotExistException("Table '$table' does not exists in database '$schema'");
		}
		
		$query = "
					SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, 
							GROUP_CONCAT( column_name ORDER BY seq_in_index ) AS `COLUMNS`
					FROM INFORMATION_SCHEMA.STATISTICS
					WHERE TABLE_SCHEMA = '$schema'
					AND TABLE_NAME = '$table'
					GROUP BY 1, 2, 3		 
				";
		

		try {
			$result = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
		} catch (\Exception $e) {
			throw new Exception\ErrorException($e->getMessage());
		}

		$indexes = array();
		foreach($result->toArray() as $record) {
			$name = $record['INDEX_NAME'];
			$indexes[$name] = array(
				'columns' => explode(',', $record['COLUMNS']),
				'unique'  => $record['NON_UNIQUE'] == 0 ? true : false,
			);
		};

		
		return $indexes;
	}
	
	
	/**
	 * 
	 * @param \Zend\Cache\Storage\StorageInterface $storage
	 * @return \Soluble\Db\Metadata\Source\MysqlISMetadata
	 */
	public function setCache(StorageInterface $storage) {
		$this->cacheStorage = $storage;
		$this->cacheEnabled = true;
		return $this;
	}	
	
	/**
	 * Unset cache (primarly for unit testing
	 * @return \Soluble\Db\Metadata\Source\MysqlISMetadata
	 */
	public function unsetCache() {
		$this->cacheEnabled = false;
		$this->cacheStorage = null;
		return $this;
	}
	
	
	/**
	function getFieldPosition($table, $field)
	{
		$query = "SHOW COLUMNS FROM $table";
		$result = $this->mysqli->query($query);
		if (!$result) {
			if (!$result) throw new \Exception("Cannot show columns in table ($table): " . $this->mysqli->error);
		}
		
		$fields = array();
		$rec = $result->fetch_assoc();
		while($rec!==null) {
				$nb_results++;
				$fields[] = $rec['Field'];
				$rec = $result->fetch_assoc();
		};
		$position = array_search($field, $fields);
		if ($position === false) {
			throw new \Exception("Cannot find field ($field) in table ($table)");
		}
		return $position; 
		
	}
	
	
	function getPrimaryKeys($table, $schema=null)
	{
		if (trim($table) == '') {
			throw new \Exception("Table param must be a valid string, empty string detected");
		}
		
		if ($schema === null) $schema = $this->schema;
		
        if (self::$_cache !== null && ($result = self::$_cache->load($cache_key))) {
            return unserialize($result);
        }		
		
		$primary_keys = array();
		$columns = $this->getColumnsInformation($table, $schema);
		
		foreach($columns as $key => $column) {
			if ($column['COLUMN_KEY'] == 'PRI') {
				$primary_keys[] = $key;
			}
		}
		if (isset(self::$_cache)) {
			self::$_cache->save(serialize($primary_keys), $cache_key);
        }		
		
		return $primary_keys;
		
	}
*/	
	
}