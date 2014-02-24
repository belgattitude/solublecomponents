<?php

namespace Soluble\Db\Sql;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\Select as ZendDbSqlSelect;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\ResultSet\ResultSet;

class Select extends ZendDbSqlSelect implements AdapterAwareInterface
{

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;



    /**
     * Create an where clause with 'OR'
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @throws Exception\InvalidArgumentException
     * @return Select
     */
    public function orWhere($predicate)
    {
        return $this->where($predicate, Predicate\PredicateSet::OP_OR);
    }



    /**
     * Set database adapter
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return \Soluble\Db\Sql\Select
     */
    public function setDbAdapter(Adapter $adapter)
    {

        $this->adapter = $adapter;
        return $this;
    }


    /**
     * Execute the query and return a Zend\Db\Resultset\ResultSet object
     *
     * @return ResultSet
     */
    public function execute()
    {
        $sql = new Sql($this->adapter);
        $sql_string = $sql->getSqlStringForSqlObject($this);
        return $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
    }


}
