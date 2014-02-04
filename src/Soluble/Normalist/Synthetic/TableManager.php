<?php
namespace Soluble\Normalist\Synthetic;


use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

use Zend\Db\Adapter\AdapterAwareInterface;

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
     */
    
    static protected $cachedTables;

    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param string $table table name
     */
    public function __construct(Adapter $adapter)
    {
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
        if (!self::$cachedTables instanceof \ArrayObject) {
            self::$cachedTables = new \ArrayObject();
        };        
        
        if (!self::$cachedTables->offsetExists($table_name)) {
            $tables = $this->getMetadata()->getTables();
            if (!in_array($table_name, $tables)) {
                throw new Exception\TableNotFoundException("Table $table_name is not found in database, if table exists please make sure cache is updated.");
            }
            $table = new Table($table_name, $this);
            self::$cachedTables->offsetSet($table_name, $table);
        }
        return self::$cachedTables->offsetGet($table_name);
    }
    
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

        $update = $this->sql->update($prefixed_table);
        $update->set($d);
        $update->where($predicate);
        
        $statement = $this->sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();
        $affectedRows =  $result->getAffectedRows();
        return $affectedRows;

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


}
