<?php
namespace Soluble\Normalist\Synthetic;


use Soluble\Normalist\SyntheticRecord;
use Soluble\Normalist\Exception;
use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

use Zend\Db\Adapter\AdapterAwareInterface;

//use Soluble\Db\Metadata\Cache\CacheAwareInterface;


use ArrayObject;

class TableManager implements AdapterAwareInterface
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
     * @return Table
     */
    public function table($table)
    {
        return new Table($table, $this);
    }

    /**
     * Return a synthetic table
     *
     * @return Table
     */
    public function getTable($table)
    {
         return new Table($table, $this);
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
     * Return a generic select
     *
     * @return \Soluble\Db\Sql\Select
     */
    public function getSelect()
    {
        $select = new Select();
        $select->setDbAdapter($this->adapter);
        return $select;
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
     * Update data into table
     *
     * @param string $table
     * @param array|ArrayObject $data
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @return int number of affected rows
     */
    public function update($table, $data, $predicate)
    {
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
     *
     * @param string $table
     * @param array $data
     * @return \Soluble\Normalist\SyntheticRecord
     */
    protected function makeRecord($table, $data)
    {
        $record = new SyntheticRecord($this, $table, $data);
        return $record;
    }



    /**
     * Return table relations
     * @param string $table
     * @return array
     */
    public function getRelations($table)
    {
        $prefixed_table = $this->prefixTable($table);
        $rel = $this->getMetadata()->getRelations($prefixed_table);
        return $rel;
    }


    /**
     * Return table columns
     * @param string $table
     * @return array
     */
    public function getColumnsInformation($table)
    {
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
    public function getRecordCleanedData($table, $data, $throwException=false)
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
     * @return \Soluble\Normalist\SyntheticTable
     */
    public function setDbAdapter(Adapter $adapter)
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
                throw new \Exception("Cannot load metadata source from adapter '$adapterName', it's not supported.");
        }
        return $metadata;

    }

    /**
     *
     * @param \Soluble\Db\Metadata\Source\AbstractSource $metadata
     * @return \Soluble\Normalist\SyntheticTable
     */
    public function setMetadata(Source\AbstractSource $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }




}
