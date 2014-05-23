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

use Soluble\FlexStore\Column\ColumnModel;
use Soluble\Flexstore\Metadata\Reader\AbstractMetadataReader;
use Soluble\FlexStore\Metadata\Reader as MetadataReader;

class SelectSource extends AbstractSource
{

    /**
     *
     * @var Select
     */
    protected $select;

    /**
     *
     * @var Adapter
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
     * @var ColumnModel
     */
    protected $columnModel;

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
            throw new Exception\InvalidArgumentException(__METHOD__ . ": Params must be either an ArrayObject or an array");
        }

        if ($params->offsetExists('select')) {
            if ($params['select'] instanceof Select) {
                $this->select = $params['select'];
            } else {
                throw new Exception\InvalidArgumentException(__METHOD__ . ": Param 'source' must be an instance of Zend\Db\Sql\Select");
            }
        } else {
            throw new Exception\MissingArgumentException(__METHOD__ . ": Missing param 'select'");
        }

        if ($params->offsetExists('adapter')) {
            if ($params['adapter'] instanceof \Zend\Db\Adapter\Adapter) {
                $this->adapter = $params['adapter'];
            } else {
                throw new Exception\InvalidArgumentException(__METHOD__ . ": Param 'adapter' must be an instance of Zend\Db\Adapter\Adapter");
            }
        } else {
            throw new Exception\MissingArgumentException(__METHOD__ . ": Missing param 'adapter'");
        }
        $this->params = $params;
    }

    /**
     *
     * @param Select $select
     * @param Options $options
     * @return Select
     */
    protected function assignOptions(Select $select, Options $options)
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
     * @param Options $options
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

        // In  ZF 2.3.0 an empty query will return SELECT .*
        if (in_array($sql_string, array('', 'SELECT .*'))) {
            throw new Exception\EmptyQueryException('Query was empty');
        }
        $this->query_string = $sql_string;


        // In case of unbuffered results (default on mysqli) !!!
        $driver = $this->adapter->getDriver();
        if ($driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {
            $stmt_prototype_backup = $driver->getStatementPrototype();
            if (self::$cache_stmt_prototype === null) {
                // With buffer results
                self::$cache_stmt_prototype = new \Zend\Db\Adapter\Driver\Mysqli\Statement($buffer=true);
            }
            $driver->registerStatementPrototype(self::$cache_stmt_prototype);
            //$is_mysqli = true;
        } else {
            //$is_mysqli = false;
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
            //var_dump(get_class($results));

            $r = new ResultSet($results);
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


    /**
     * @return ColumnModel
     */
    public function getColumnModel()
    {
        if ($this->columnModel === null) {
            $sql = new Sql($this->adapter);
            $select = clone $this->select;
            $select->limit(0);
            $sql_string = $sql->getSqlStringForSqlObject($select);
            $this->columnModel = $this->getMetadataReader()->getColumnModel($sql_string);
        }
        return $this->columnModel;
    }



    /**
     *
     * @return AbstractMetadataReader
     */
    public function getMetadataReader()
    {
        if ($this->metadataReader === null) {
            $this->metadataReader = $this->getDefaultMetadataReader();
        }
        return $this->metadataReader;
    }

    /**
     * 
     * @return \Soluble\FlexStore\Metadata\Reader\AbstractMetadataReader
     */
    protected function getDefaultMetadataReader()
    {
        $conn = $this->adapter->getDriver()->getConnection()->getResource();
        $class = strtolower(get_class($conn));
        switch ($class) {
            case 'pdo':
                return new MetadataReader\PDOMysqlMetadataReader($conn);
            case 'mysqli':
                return new MetadataReader\MysqliMetadataReader($conn);
            default:
                throw new \Exception(__METHOD__ . " Cannot handle default metadata reader for driver '$class'");
        }

    }

    /**
     * Return the query string that was executed
     * @return string
     */
    public function getQueryString()
    {
        if ($this->query_string == '') {
            throw new Exception\InvalidUsageException(__METHOD__ . ": Invalid usage, getQueryString must be called after data has been loaded (performance reason).");
        }
        return $this->query_string;
    }


}
