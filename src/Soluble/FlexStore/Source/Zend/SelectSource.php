<?php

/**
 *
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Source\Zend;

use Soluble\FlexStore\Source\AbstractSource;

use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Options;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use ArrayObject;

class SelectSource extends AbstractSource
{
//class ZendDbSqlSelect {

    /**
     *
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * Initial params received in the constructor
     * @var ArrayObject
     */
    protected $params;

    /**
     *
     * @var string
     */
    protected $query_string;

    /**
     *
     * @var \Zend\Db\Adapter\Driver\Mysqli\Statement
     */
    protected static $cache_stmt_prototype;

    /**
     *
     * @var Zend\Db\Adapter\Driver\ResultInterface
     */
    protected static $cache_result_prototype;
    
    
    /**
     *
     * @param array|ArrayObject $params
     * @throws Exception\InvalidArgumentException
     * @throws Exception\MissingArgumentException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = new ArrayObject($params);
        } elseif (!$params instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException("Params must be either an ArrayObject or an array");
        }

        if ($params->offsetExists('select')) {
            if ($params['select'] instanceof \Zend\Db\Sql\Select) {
                $this->select = $params['select'];
            } else {
                throw new Exception\InvalidArgumentException("Param 'source' must be an instance of Zend\Db\Sql\Select");
            }
        } else {
            throw new Exception\MissingArgumentException("Missing param 'select'");
        }

        if ($params->offsetExists('adapter')) {
            if ($params['adapter'] instanceof \Zend\Db\Adapter\Adapter) {
                $this->adapter = $params['adapter'];
            } else {
                throw new Exception\InvalidArgumentException("Param 'adapter' must be an instance of Zend\Db\Adapter\Adapter");
            }
        } else {
            throw new Exception\MissingArgumentException("Missing param 'adapter'");
        }
        $this->params = $params;
    }

    /**
     *
     * @param \Zend\Db\Sql\Select $select
     * @param \Soluble\FlexStore\Options $options
     * @return \Zend\Db\Sql\Select
     */
    protected function assignOptions(\Zend\Db\Sql\Select $select, \Soluble\FlexStore\Options $options)
    {
        if ($options->hasLimit()) {
            $select->limit($options->getLimit());
            if (is_numeric($options->getOffset())) {
                $select->offset($options->getOffset());
            }

            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));
        }
        return $select;
    }


    /**
     *
     * @param \Soluble\FlexStore\Options $options
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            $options = $this->getOptions();
        }
        $select = $this->assignOptions(clone $this->select, $options);


        $sql = new Sql($this->adapter);
        $sql_string = $sql->getSqlStringForSqlObject($select);
        if ($sql_string == '') {
            throw new Exception\EmptyQueryException('Query was empty');
        }
        $this->query_string = $sql_string;

        // In case of unbuffered results (default on mysqli) !!!
        $is_mysqli = ($this->adapter->getDriver() instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli);
        if ($is_mysqli) {
            $stmt_prototype_backup = $this->adapter->getDriver()->getStatementPrototype();
            if (self::$cache_stmt_prototype === null) {
                // With buffer results
                self::$cache_stmt_prototype = new \Zend\Db\Adapter\Driver\Mysqli\Statement($buffer=true);
            }
            if (self::$cache_stmt_prototype === null) {
                // With buffer results
                self::$cache_stmt_prototype = new \Zend\Db\Adapter\Driver\Mysqli\Statement($buffer=true);
            }
            
            $this->adapter->getDriver()->registerStatementPrototype(self::$cache_stmt_prototype);            
            
        }
/*
 * @todo optimize
        // Setting result prototype
        if (self::$cache_result_prototype === null) {
             self::$cache_result_prototype = new \Soluble\FlexStore\ResultSet\ResultSet();
        }
        try {
        $result_prototype_backup = $this->adapter->getDriver()->getResultPrototype();
        $this->adapter->getDriver()->registerResultPrototype(self::$cache_result_prototype);        
        } catch(\Exception $e) {
            var_dump($e->getMessage());
            die();
        }
 * 
 */
        try {
            
            $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);

            //$stmt = $sql->prepareStatementForSqlObject( $select );
            //$results = $stmt->execute();
            

            $r = new ResultSet();
            $r->initialize($results);
            $r->setSource($this);
            
            if ($options->hasLimit()) {
                $row = $this->adapter->query('select FOUND_ROWS() as total_count')->execute()->current();
                $r->setTotalRows($row['total_count']);
            } else {
                
                $r->setTotalRows($r->count());
            }

            
            if ($this->columns !== null) {
                $r->setColumns($this->columns);
            }
            
            // restore result prototype
     //       $this->adapter->getDriver()->registerResultPrototype($result_prototype_backup);        
            
            // restore statement prototype
            if ($is_mysqli) {
                $this->adapter->getDriver()->registerStatementPrototype($stmt_prototype_backup);            
            }

        } catch (\Exception $e) {
            // restore result prototype
       //     $this->adapter->getDriver()->registerResultPrototype($result_prototype_backup);        
            
            if ($is_mysqli) {
                $this->adapter->getDriver()->registerStatementPrototype($stmt_prototype_backup);            
            }
            
            throw $e;

            
        }
        return $r;
    }

    public function getMetadata()
    {

    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        if ($this->query_string == '') {
            throw new Exception\InvalidUsageException("Invalid usage, getQueryString must be called after data has been loaded (performance reason).");
        }
        return $this->query_string;
    }



}
