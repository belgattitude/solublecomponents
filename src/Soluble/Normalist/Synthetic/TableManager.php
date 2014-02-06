<?php
namespace Soluble\Normalist\Synthetic;


use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

use Zend\Db\Sql\Predicate;


use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Sql\PreparableSqlInterface;

//use Soluble\Db\Metadata\Cache\CacheAwareInterface;


use ArrayObject;

class TableManager
{
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
     * Global table prefix
     * @var string
     */
    protected $table_prefix;

    /**
     *
     * @var Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * @var ArrayObject
     
    // removed use of static 
    protected static $cachedTables;
    */
    
    /**
     *
     * @var \ArrayObject
     */
    protected $localTableCache;
    
    /**
     * @var boolean
     */
    protected $useLocalCache = true;
    
    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param string $table table name
     */
    public function __construct(Adapter $adapter)
    {
        $this->localTableCache = new \ArrayObject();
        $this->setDbAdapter($adapter);
        $this->sql = new Sql($adapter);
    }



    /**
     * Return a synthetic table
     *
     * @param string $table_name table name
     *
     * @throws Exception\InvalidArgumentException if table name is not valid
     *
     * @return Table
     */
    public function table($table_name)
    {
        if (!is_string($table_name)) {
            throw new Exception\InvalidArgumentException("Table name must be a string");
        }
        $tables = $this->getMetadata()->getTables();
        if (!in_array($table_name, $tables)) {
            throw new Exception\TableNotFoundException("Table $table_name is not found in database, if table exists please make sure cache is updated.");
        }
        
        if ($this->useLocalCache) {

            if (!$this->localTableCache->offsetExists($table_name)) {
                $table = new Table($table_name, $this);
                $this->localTableCache->offsetSet($table_name, $table);
            }
            return $this->localTableCache->offsetGet($table_name);    
                    
        } else {
            $table = new Table($table_name, $this);
            return $table;
        }

    }
    /**
     * Return a synthetic table
     *
     * @param string $table_name table name
     *
     * @throws Exception\InvalidArgumentException if table name is not valid
     *
     * @return Table
    // REMOVED with static cache 
    
    public function table($table_name)
    {
        if (!is_string($table_name)) {
            throw new Exception\InvalidArgumentException("Table name must be a string");
        }
        if (!$this->localTableCache instanceof \ArrayObject) {
            $this->localTableCache = new \ArrayObject();
        };

        if (!$this->localTableCache->offsetExists($table_name)) {
            $tables = $this->getMetadata()->getTables();
            if (!in_array($table_name, $tables)) {
                throw new Exception\TableNotFoundException("Table $table_name is not found in database, if table exists please make sure cache is updated.");
            }
            $table = new Table($table_name, $this);
            $this->localTableCache->offsetSet($table_name, $table);
        }
        return $this->localTableCache->offsetGet($table_name);
    }
    */
    
    /**
     * Return a generic select
     *
     * @return \Soluble\Db\Sql\Select
     */
    public function select()
    {
        /**
// search for at most 2 artists who's name starts with Brit, ascending
$rowset = $artistTable->select(function (Select $select) {
     $select->where->like('name', 'Brit%');
     $select->order('name ASC')->limit(2);
});
         */
        $select = new Select();
        $select->setDbAdapter($this->adapter);
        return $select;
    }


    /**
     * Update data into table
     *
     * @param string $table_name name of the table (un-prefixed)
     * @param array|ArrayObject $data
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ColumnNotFoundException when $data contains columns that does not exists in table
     * @throws Exception\ForeignKeyException when insertion failed because of an invalid foreign key
     * @throws Exception\DuplicateEntryException when insertion failed because of an invalid foreign key
     * @throws Exception\NotNullException when insertion failed because a column cannot be null
     * @throws Exception\RuntimeException when insertion failed for another reason
     *
     * @return int number of affected rows
     */

    public function update($table_name, $data, $predicate, $combination=Predicate\PredicateSet::OP_AND)
    {
        $prefixed_table = $this->getPrefixedTable($table_name);

        if ($data instanceOf ArrayObject) {
            $d = (array) $data;
        } elseif (is_array($data)) {
            $d = $data;
        } else {
            throw new Exception\InvalidArgumentException("TableManager::update(table_name, data) requires data to be array or an ArrayObject");
        }

        $column_information = $this->getMetadata()->getColumnsInformation($prefixed_table);
        $diff = array_diff_key($d, $column_information);
        if (count($diff) > 0) {
            $msg = join(',', array_keys($diff));
            throw new Exception\ColumnNotFoundException("TableManager::update(data), cannot insert columns '$msg' does not exists in table {$table_name}.");
        }
        
        
        $update = $this->sql->update($prefixed_table);
        $update->set($d);
        $update->where($predicate);

        $result = $this->executeStatement($update);
            
        $affectedRows =  $result->getAffectedRows();
        return $affectedRows;

    }
    
    /**
     * Insert data into table
     *
     * @param string $table_name name of the table (un-prefixed)
     * @param array|ArrayObject $data
     *
     * @throws Exception\InvalidArgumentException when data is not an array or an ArrayObject
     * @throws Exception\ColumnNotFoundException when $data contains columns that does not exists in table
     * @throws Exception\ForeignKeyException when insertion failed because of an invalid foreign key
     * @throws Exception\DuplicateEntryException when insertion failed because of an invalid foreign key
     * @throws Exception\NotNullException when insertion failed because a column cannot be null
     * @throws Exception\RuntimeException when insertion failed for another reason
     *
     * @return \Soluble\Normalist\Synthetic\Record
     */
    public function insert($table_name, $data)
    {
        $prefixed_table = $this->getPrefixedTable($table_name);
        
        if ($data instanceof \ArrayObject) {
            $d = (array) $data;
        } elseif (is_array($data)) {
            $d = $data;
        } else {
            $type = gettype($data);
            throw new Exception\InvalidArgumentException("TableManager::insert(data) expects data to be array or ArrayObject. Type receive '$type'");
        }
        
        $column_information = $this->getMetadata()->getColumnsInformation($prefixed_table);
        $diff = array_diff_key($d, $column_information);
        if (count($diff) > 0) {
            $msg = join(',', array_keys($diff));
            throw new Exception\ColumnNotFoundException("TableManager::insert(data), cannot insert columns '$msg' does not exists in table {$table_name}.");
        }
        

        $insert = $this->sql->insert($prefixed_table);
        $insert->values($d);

        $this->executeStatement($insert);
        
        $pks = $this->getPrimaryKeys($table_name);
        $nb_pks = count($pks);
        if ($nb_pks > 1) {
            // In multiple keys there should not be autoincrement value
            $id = array();
            foreach ($pks as $pk) {
                $id[$pk] = $d[$pk];
            }
        } elseif (array_key_exists($pks[0], $d)) {
            // not using autogenerated value
            //$id = $d[$this->getPrimaryKey()];
            $id = $d[$pks[0]];
        } else {
            $id = $this->getDbAdapter()->getDriver()->getLastGeneratedValue();
        }
        $record = $this->table($table_name)->findOrFail($id);
        return $record;
    }
    


    /**
     * Start a new transaction
     *
     * @throws Exception\TransactionException
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    public function beginTransaction()
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();
        } catch (\Exception $e) {
            throw new Exception\TransactionException("TableManager::beginTransation(), cannot start transaction '{$e->getMessage()}'.");
        }
        return $this;
    }

    /**
     * Commit changes
     *
     * @throws Exception\TransactionException
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    public function commit()
    {
        try {
            $this->adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            throw new Exception\TransactionException("TableManager::commit(), cannot commit transaction '{$e->getMessage()}'.");
        }

        return $this;
    }

    /**
     * Rollback transaction
     *
     * @throws Exception\TransactionException
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    public function rollback()
    {
        try {
            $this->adapter->getDriver()->getConnection()->rollback();
        } catch (\Exception $e) {
            throw new Exception\TransactionException("TableManager::rollback(), cannot rollback transaction '{$e->getMessage()}'.");
        }

        return $this;
    }



    /**
     * Return underlyng Zend\Db\Adapter
     *
     * @return \Zend\Db\Adapter\Adapter $adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }




    /**
     * Set global table prefix
     *
     * @param string $table_prefix
     * @return TableManager
     */
    public function setTablePrefix($table_prefix)
    {
       $this->table_prefix = $table_prefix;
       return $this;
    }

    /**
     * Return global table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->table_prefix;
    }




    /**
     * Return prefixed table name
     *
     * @param string $table
     * @return string
     */
    public function getPrefixedTable($table)
    {
       return $this->table_prefix . $table;
    }



    /**
     * Return table primary keys
     * @param string $table
     * @return array
     */
    public function getPrimaryKeys($table)
    {
        return $this->getMetadata()->getPrimaryKeys($this->prefixed_table . $table);
    }

    /**
     * Return primary key, if multiple primary keys found will
     * throw an exception
     * @throws Exception
     * @return string
     */
    public function getPrimaryKey($table)
    {
        return $this->getMetadata()->getPrimaryKey($this->prefixed_table . $table);
    }

    /**
     *
     * @return \Soluble\Db\Metadata\Source\AbstractSource
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = $this->getDefaultMetadata($this->adapter);
        }
        return $this->metadata;
    }

    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    protected function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     *
     * @param Adapter $adapter
     * @throws \Exception
     */
    protected function getDefaultMetadata(Adapter $adapter)
    {
        $adapterName = $adapter->getPlatform()->getName();
        switch (strtolower($adapterName)) {
            case 'mysql':
                $metadata = new Source\MysqlISMetadata($adapter);
                break;
            default :
                throw new Exception\UnsupportedFeatureException("TableManager::getDefaultMetadata() Adapter '$adapterName' is not yet supported.");
        }
        return $metadata;

    }

    /**
     *
     * @param \Soluble\Db\Metadata\Source\AbstractSource $metadata
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    public function setMetadata(Source\AbstractSource $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Execute a statement
     * 
     * @todo move to driver if not will only support MySQL
     *
     * @throws Exception\ForeignKeyException when insertion failed because of an invalid foreign key
     * @throws Exception\DuplicateEntryException when insertion failed because of an invalid foreign key
     * @throws Exception\NotNullException when insertion failed because a column cannot be null
     * @throws Exception\RuntimeException when insertion failed for another reason
     *
     * @param PreparableSqlInterface $sqlObject
     * @param StatementInterface|null $statement
     * @return Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function executeStatement(PreparableSqlInterface $sqlObject, StatementInterface $statement = null)
    {
        
        $statement = $this->sql->prepareStatementForSqlObject($sqlObject);
        try {
            $result    = $statement->execute();
        } catch (\Exception $e) {

            // In ZF2, PDO_Mysql and MySQLi return different exception,
            // attempt to normalize by catching one exception instead
            // of RuntimeException and InvalidQueryException

            $messages = array();
            $ex = $e;
            do {
                $messages[] = $ex->getMessage();
            } while ($ex = $ex->getPrevious());
            $message = join(', ', array_unique($messages));

            $lmsg = '[' . get_class($e) . '] ' . strtolower($message) . '(code:' . $e->getCode() . ')';

            if (strpos($lmsg, 'cannot be null') !== false) {
                // Integrity constraint violation: 1048 Column 'non_null_column' cannot be null
                $rex = new Exception\NotNullException($message, $e->getCode(), $e);
                throw $rex;
            } elseif (strpos($lmsg, 'duplicate entry') !== false) {
                $rex = new Exception\DuplicateEntryException($message, $e->getCode(), $e);
                throw $rex;
            } elseif (strpos($lmsg, 'constraint violation') !== false ||
                    strpos($lmsg, 'foreign key') !== false) {
                $rex = new Exception\ForeignKeyException($message, $e->getCode(), $e);
                throw $rex;
            } else {
                $sql_string = $sqlObject->getSqlString($this->sql->getAdapter()->getPlatform());
                $iqex = new Exception\RuntimeException($message . "[$sql_string]", $e->getCode(), $e);
                throw $iqex;
            }
        }
        return $result;
    }

}
