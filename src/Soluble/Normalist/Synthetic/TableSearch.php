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
use Zend\Db\Sql\Expression;


use ArrayObject;

class TableSearch
{
    /**
     * @var Table
     */
    protected $table;

    /**
     *
     * @var Select
     */
    protected $select;

    /**
     * @var boolean
     */
    protected $has_modified_columns = false;

    /**
     *
     * @var array|string
     */
    protected $tableIdentifier;

    /**
     *
     * @param Select $select table name
     * @param Table $table
     */
    public function __construct(Select $select, Table $table)
    {
        $this->select = $select;
        $this->table = $table;
        $this->tableIdentifier = $this->select->getRawState(Select::TABLE);

    }

    /**
     * Limit the number of results
     *
     * @param int $limit
     * @return TableSearch
     */
    public function limit($limit)
    {
        $this->select->limit($limit);
        return $this;
    }

    /**
     * Set offset to use when a limit has been set.
     *
     * @param int $offset
     * @return TableSearch
     */
    public function offset($offset)
    {
        $this->select->offset($offset);
        return $this;
    }


    /**
     * Set the table columns to retrieve
     *
     * @param array $columns array list of columns, key are used as aliases
     * @param boolean $prefixColumnsWithTable
     * @return TableSearch
     */
    public function columns(array $columns, $prefixColumnsWithTable=true)
    {
        $this->has_modified_columns = true;
        $this->select->columns($columns, $prefixColumnsWithTable);
        return $this;
    }

    /**
     * Add table prefixed columns, will automatically
     * quote table parts identifiers found in the column name.
     * It provides an alternative for defining columns from multiple/joined
     * table in one go.
     *
     * @param array $columns
     */
    public function prefixedColumns(array $columns)
    {
        $this->has_modified_columns = true;
        $this->select->prefixedColumns($columns);
        return $this;
    }

    /**
     * Set a group option
     *
     * @param array|string $group a column or an array of columns to group by
     * @return TableSearch
     */
    public function group($group)
    {
        $this->select->group($group);
        return $this;
    }

    /**
     * Set a habing option
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return Select

     * @return TableSearch
     */
    public function having($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        $this->select->having($predicate, $combination);
        return $this;
    }


    /**
     * Set an order by clause
     *
     * @param string|array $order a columns or an array definition of columns
     * @return TableSearch
     */
    public function order($order)
    {
        $this->select->order($order);
        return $this;
    }

    /**
     * Add a where condition
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Zend\Db\Sql\Predicate\PredicateSet
     * @throws Zend\Db\Sql\Exception\InvalidArgumentException
     * @return TableSearch
     */
    public function where($predicate, $combination=null)
    {
        $this->select->where($predicate, $combination);
        return $this;
    }

    /**
     * Add an orWhere condition to the search
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @throws Zend\Db\Sql\Exception\InvalidArgumentException
     * @return TableSearch
     */
    public function orWhere($predicate)
    {
        $this->select->orWhere($predicate);
        return $this;
    }


    /**
     * Add an inner table join to the search
     *
     * @param  string|array $table
     * @param  string $on
     * @param  string|array $columns by default won't retrieve any column from the joined table
     * @return TableSearch
     */
    public function join($table, $on, $columns = array())
    {

        $prefixed_table = $this->prefixTableJoinCondition($table);


        //$this->columns($this->getPrefixedColumns());

        $this->select->join($prefixed_table, $on, $columns, Select::JOIN_INNER);
        return $this;
    }

    /**
     * Add an left outer table join to the search
     *
     * @param  string|array $table
     * @param  string $on
     * @param  string|array $columns by default won't retrieve any column from the joined table
     * @return TableSearch
     */
    public function joinLeft($table, $on, $columns = array())
    {

        $prefixed_table = $this->prefixTableJoinCondition($table);
        $this->select->join($prefixed_table, $on, $columns, Select::JOIN_LEFT);
        return $this;
    }



    /**
     * Add an right outer table join to the search
     *
     * @param  string|array $table
     * @param  string $on
     * @param  string|array $columns by default won't retrieve any column from the joined table
     * @return TableSearch
     */
    public function joinRight($table, $on, $columns = array())
    {
        $prefixed_table = $this->prefixTableJoinCondition($table);
        $this->select->join($prefixed_table, $on, $columns, Select::JOIN_RIGHT);
        return $this;
    }



    /**
     * Return the underlying select
     *
     * @return Select
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
     *
     * @return string Json encoded
     */
    public function toJson()
    {
        return json_encode($this->select->execute()->toArray());
    }

    /**
     * Return an array version of the results
     *
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
       $rs = new ResultSet($this->select, $this->table, !$this->has_modified_columns);
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

    /**
     *
     * @param array|string $tableSpec


    protected function getPrefixedColumns($tableSpec=null)
    {

        if ($tableSpec === null) $tableSpec = $this->tableIdentifier;

        if (is_array($tableSpec)) {
            $alias = key($this->tableIdentifier);
            $table = $this->tableIdentifier[$alias];
        } else {
            $alias = $this->tableIdentifier;
            $table = $alias;
        }

        $columns = array();
        $pf = $this->table->getTableManager()->getDbAdapter()->getPlatform();
        $cols = array_keys($this->table->getColumnsInformation());
        foreach($cols as $column) {
            //$columns["$alias.$column"] = $column;
            //$columns[$column] = new \Zend\Db\Sql\TableIdentifier($pf->quoteIdentifier($alias) . $pf->getIdentifierSeparator() . $pf->quoteIdentifier($column));
            //$columns[$column] =
            $columns[$column] = new Predicate\Expression($pf->quoteIdentifier($alias) . $pf->getIdentifierSeparator() . $pf->quoteIdentifier($column));
        }
        return $columns;


    }
    */
    /**
     * Prefix table join condition
     *
     * @param string|array $table
     * @return array|string
     */
    protected function prefixTableJoinCondition($table)
    {
        $tm = $this->table->getTableManager();
        if (is_array($table)) {
            $alias = key($table);
            $prefixed_table = $tm->getPrefixedTable($table[$alias]);
            $table = array($alias => $prefixed_table);
        } elseif (is_string($table)) {
            $prefixed_table = $tm->getPrefixedTable($table);
            $table = $prefixed_table;
        }
        return $table;

    }


}
