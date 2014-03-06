<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Metadata;

use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

use Zend\Db\Sql\Predicate;



//use Soluble\Db\Metadata\Cache\CacheAwareInterface;


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
     * @param Adapter $adapter
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
            $this->metadata = $this->getDefaultMetadata($this->adapter);
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
     * @param Adapter $adapter
     * @throws Exception\UnsupportedFeatureException
     */
    protected function getDefaultMetadata(Adapter $adapter)
    {
        $adapterName = $adapter->getPlatform()->getName();
        switch (strtolower($adapterName)) {
            case 'mysql':
                //$metadata = new Source\Mysql\InformationSchema($adapter);
                $metadata = new Metadata\DynamicSchema($adapter);
                break;
            default :
                throw new Exception\UnsupportedFeatureException(__METHOD__ . ":  Adapter '$adapterName' is not yet supported.");
        }
        return $metadata;

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
