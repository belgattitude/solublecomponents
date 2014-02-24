<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Synthetic\ResultSet\ResultSet;

use Soluble\Normalist\Synthetic\Exception;
use Soluble\Db\Sql\Select;
use Soluble\Db\Metadata\Source;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;


use ArrayObject;

class TableSearch
{
    /**
     * @param Table
     */
    protected $table;

    /**
     *
     * @var \Soluble\Db\Sql\Select;
     */
    protected $select;

    /**
     *
     * @param Select $select table name
     * @param Table $table
     */
    public function __construct(Select $select, Table $table)
    {
        $this->select = $select;
        $this->table = $table;
    }

    /**
     *
     * @param int $limit
     * @return \Soluble\Normalist\Synthetic\TableSearch
     */
    public function limit($limit)
    {
        $this->select->limit($limit);
        return $this;
    }

    /**
     *
     * @param int $offset
     * @return \Soluble\Normalist\Synthetic\TableSearch
     */
    public function offset($offset)
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
    public function columns($columns, $prefixColumnsWithTable=false)
    {
        $this->select->columns($columns, $prefixColumnsWithTable=false);
        return $this;
    }

    /**
     *
     * @param array $group
     * @return \Soluble\Normalist\Synthetic\TableSearch
     */
    public function group($group)
    {
        $this->select->group($group);
        return $this;
    }

    /**
     *
     * @param string|array $order
     * @return \Soluble\Normalist\Synthetic\TableSearch
     */
    public function order($order)
    {
        $this->select->order($order);
        return $this;
    }

    /**
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Zend\Db\Sql\Predicate\PredicateSet
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     * @return \Soluble\Normalist\Synthetic\TableSearch
     */
    public function where($predicate, $combination=null)
    {
        $this->select->where($predicate, $combination);
        return $this;
    }

    /**
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @throws \Zend\Db\Sql\Exception\InvalidArgumentException
     * @return \Soluble\Normalist\Synthetic\TableSearch
     */
    public function orWhere($predicate)
    {
        $this->select->orWhere($predicate);
        return $this;
    }


    /**
     * @param  string|array $table
     * @param  string $on
     * @param  string|array $columns
     * @param  string $type one of the JOIN_* constants
     * @return TableSearch
     */
    public function join($table, $on, $columns = Select::SQL_STAR, $type = Select::JOIN_INNER)
    {
        $this->select->join($table, $on, $columns, $type);
        return $this;
    }


    /**
     *
     * @return \Soluble\Db\Sql\Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Return SQL string
     *
     * @return string
     */
    public function getSql()
    {
        $adapterPlatform = $this->table->getTableManager()->getDbAdapter()->getPlatform();
        return $this->select->getSqlString($adapterPlatform);
    }


    /**
     * Return a json version of the results
     * @return string Json encoded 
     */
    public function toJson()
    {
        return json_encode($this->select->execute()->toArray());
    }

    /**
     * Return an array version of the results
     * @return array
     */
    public function toArray()
    {
        return $this->select->execute()->toArray();
    }



    /**
     * Return record as an array
     *
     * @return ResultSet
     */
    public function execute()
    {

       $rs = new ResultSet($this->select, $this->table);
       return $rs;

    }



    /**
     * Return an array indexed by $indexKey
     * useful for comboboxes...
     *
     * @param string $columnKey
     * @param string $indexKey
     * @return array
     */
    public function toArrayColumn($columnKey, $indexKey)
    {
        $select = clone $this->select;
        $select->reset($select::COLUMNS)->columns(array($columnKey, $indexKey));
        return array_column($select->execute()->toArray(), $columnKey, $indexKey);
    }


}
