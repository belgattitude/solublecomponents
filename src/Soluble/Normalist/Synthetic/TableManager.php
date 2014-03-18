<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Driver;
use Soluble\Normalist\Metadata;

use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;


use ArrayObject;

class TableManager
{
    /**
     *
     * @param Adapter $adapter
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
     *
     * @var Transaction
     */
    protected $transaction;


    /**
     *
     * @var \ArrayObject
     */
    protected $localTableCache;


    /**
     *
     * @var Driver\DriverInterface
     */
    protected $driver;
    
    /**
     *
     * @param Driver\DriverInterface $adapter
     * @param string $table table name
     */
    public function __construct(Driver\DriverInterface $driver)
    {
        $this->localTableCache = new \ArrayObject();
        $this->driver = $driver;
        $this->setDbAdapter($driver->getDbAdapter());
        
        $this->sql = new Sql($this->adapter);
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
            throw new Exception\InvalidArgumentException(__METHOD__ . ": Table name must be a string");
        }

        if (!$this->localTableCache->offsetExists($table_name)) {
            $tables = $this->metadata()->getTables();
            if (!in_array($table_name, $tables)) {
                throw new Exception\TableNotFoundException(__METHOD__ . ": Table $table_name is not found in database, if table exists please make sure cache is updated.");
            }
            $table = new Table($table_name, $this);
            $this->localTableCache->offsetSet($table_name, $table);
        }
        return $this->localTableCache->offsetGet($table_name);

    }

    /**
     * Return a generic select
     *
     * @return Select
     */
    public function select()
    {
        $select = new Select();
        $select->setDbAdapter($this->adapter);
        return $select;
    }



    /**
     * Return a transaction object
     *
     * @return Transaction
     */
    public function transaction()
    {
        if ($this->transaction === null) {
            $this->transaction = new Transaction($this->adapter);
        }
        return $this->transaction;
    }




    /**
     * Return underlyng Zend\Db\Adapter\Adapter
     *
     * @return Adapter $adapter
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
     * Return a metadata reader
     *
     * @return Source\AbstractSource
     */
    public function metadata()
    {
        if ($this->metadata === null) {
            $this->loadDefaultMetadata();
        }
        return $this->metadata;
    }

    /**
     * Set the database adapter
     *
     * @param Adapter $adapter
     * @return TableManager
     */
    protected function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Return default metadata reader associated to an adapter
     *
     * @throws Exception\UnsupportedFeatureException
     */
    protected function loadDefaultMetadata()
    {
        
        $adapterName = $this->adapter->getPlatform()->getName();
        switch (strtolower($adapterName)) {
            case 'mysql':
                // supported
                break;
            default :
                throw new Exception\UnsupportedFeatureException(__METHOD__ . ":  Adapter '$adapterName' is not yet supported.");
        }        
        if ($this->driver === null) {
            $options = array();
            $driver = new Driver\ZeroConfDriver($this->getDbAdapter(), $options);
            $driver->setDbAdapter($this->adapter);
            $this->driver = $driver;
        }
            
        $this->metadata = $this->driver->getMetadata();
    }

    /**
     *
     * @param Source\AbstractSource $metadata
     * @return TableManager
     */
    public function setMetadata(Source\AbstractSource $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

}
