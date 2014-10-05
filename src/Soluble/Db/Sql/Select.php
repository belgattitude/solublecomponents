<?php

namespace Soluble\Db\Sql;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\Select as ZendDbSqlSelect;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\ResultSet\ResultSet;

class Select extends ZendDbSqlSelect implements AdapterAwareInterface
{

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Constructor
     *
     * @param Adapter $adapter
     * @param  null|string|array|TableIdentifier $table
     */
    public function __construct(Adapter $adapter = null, $table = null)
    {

        if ($adapter) {
            $this->setDbAdapter($adapter);
        }
        parent::__construct($table);
    }


    /**
     * Create an where clause with 'OR'
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @throws Zend\Db\Sql\Exception\InvalidArgumentException
     * @return Select
     */
    public function orWhere($predicate)
    {
        return $this->where($predicate, Predicate\PredicateSet::OP_OR);
    }



    /**
     * Add table prefixed columns, will automatically
     * quote table parts identifiers found in the column name.
     * It provides an alternative for defining columns from multiple/joined
     * table in one go.
     *
     * <code>
     * $select->from(array('p' =>'product')
     *        ->prefixedColumns(array('product_title' => 'p.title'));
     * </code>
     * Possible valid states:
     *   array(value, ...)
     *     value can be strings or Expression objects
     *
     *   array(string => value, ...)
     *     key string will be use as alias,
     *     value can be string or Expression objects
     *
     * @throws Exception\InvalidArgumentException when usage not correct
     * @param  array $columns
     * @return Select
     */
    public function prefixedColumns(array $columns)
    {
        $pf = $this->adapter->getPlatform();
        $identifierSeparator = $pf->getIdentifierSeparator();
        $names = array();
        $cols = array();
        foreach ($columns as $alias => $column) {
            if (is_string($column)) {
                if (strpos($column, self::SQL_STAR) !== false) {
                    $msg = __METHOD__ . " Invalid argument, prefixedColumn() does not accept sql * column specification";
                    throw new Exception\InvalidArgumentException($msg);
                }
                $parts = explode($identifierSeparator, $column);
                if (count($parts) > 1) {
                    $quotedParts = array();
                    foreach ($parts as $part) {
                        $quotedParts[] = $pf->quoteIdentifier($part);
                    }
                    // to remove PHPAnalyzer warnings
                    //var_dump($quotedParts[count($quotedParts)-1]);
                    //die();
                    $last_part = $parts[count($parts)-1];

                    if (!is_string($alias)) {
                        $alias = $last_part;
                    }

                    if (in_array($alias, $names)) {
                        $msg = __METHOD__ . ": Invalid argument, multiple columns have the same alias ($alias)";
                        throw new Exception\InvalidArgumentException($msg);
                    }
                    $names[] = $alias;

                    $cols[$alias] = new Expression(join($identifierSeparator, $quotedParts));

                } else {
                    if (in_array($alias, $names)) {
                        $msg = __METHOD__ . ": Invalid argument, multiple columns have the same alias ($alias)";
                        throw new Exception\InvalidArgumentException($msg);
                    }

                    $cols[$alias] = $column;
                    $names[] = $alias;

                }
            } else {
                if (in_array($alias, $names)) {
                     $msg = __METHOD__ . ": Invalid argument, multiple columns have the same alias ($alias)";
                     throw new Exception\InvalidArgumentException($msg);
                }

                $cols[$alias] = $column;
                $names[] = $alias;

            }
        }
        $this->columns($cols);
        return $this;
    }



    /**
     * Set database adapter
     *
     * @param Adapter $adapter
     * @return Select
     */
    public function setDbAdapter(Adapter $adapter)
    {

        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Return an sql string accordingly to the internat database adapter
     *
     * @throws Exception\InvalidUsageException
     * @return string
     */
    public function getSql()
    {
        if ($this->adapter === null) {
            $msg = __METHOD__ . ": Error, prior to use execute method you must provide a valid database adapter. See Select::setDbAdapter() method.";
            throw new Exception\InvalidUsageException($msg);
        }
        $sql = new Sql($this->adapter);
        return $sql->getSqlStringForSqlObject($this);
    }

    /**
     * Execute the query and return a Zend\Db\Resultset\ResultSet object
     *
     * @throws Exception\InvalidUsageException
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function execute()
    {
        if ($this->adapter === null) {
            $msg = __METHOD__ . ": Error, prior to use execute method you must provide a valid database adapter. See Select::setDbAdapter() method.";
            throw new Exception\InvalidUsageException($msg);
        }
        $sql = new Sql($this->adapter);
        $sql_string = $sql->getSqlStringForSqlObject($this);
        //return $this->adapter->createStatement($sql_string)->execute();
        return $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
    }

    
    /**
     * Return an sql string accordingly to the internat database adapter
     *
     * @throws Exception\InvalidUsageException
     * @return string
     */
    public function __toString()
    {
        return $this->getSql();
    }
}
