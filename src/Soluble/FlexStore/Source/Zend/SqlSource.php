<?php

/**
 *
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Source\Zend;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Options;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use ArrayObject;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\Type\MetadataMapper;
use Soluble\Flexstore\Metadata\Reader\AbstractMetadataReader;
use Soluble\FlexStore\Metadata\Reader as MetadataReader;

class SqlSource extends AbstractSource implements QueryableSourceInterface
{

    /**
     * @var Sql
     */
    protected $sql;
    
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
     * @param Adapter $params
     */
    public function __construct(Adapter $adapter, Select $select=null)
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($this->adapter);
        if ($select !== null) {
            $this->setSelect($select);
        }
    }
    
    /**
     * @param Select
     * @return SqlSource 
     */
    public function setSelect(Select $select)
    {
        $this->select = $select;
        return $this;
    }
    
    /**
     * 
     * @return Select
     */
    public function getSelect()
    {
        return $this->select();
    }
    
    /**
     * 
     * @return Select
     */
    public function select()
    {
        if ($this->select === null) {
            $this->select = $this->sql->select();
        }
        return $this->select;
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
            if ($options->hasOffset()) {
                $select->offset($options->getOffset());
            }
            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));
        }
        return $select;
    }

    /**
     *
     * @param Options $options
     * @throws Exception\EmptyQueryException
     * @throws Exception\ErrorException
     * @return ResultSet
     */
    public function getData(Options $options = null)
    {

        if ($options === null) {
            $options = $this->getOptions();
        }
        
        $select = $this->assignOptions(clone $this->getSelect(), $options);


        $sql = new Sql($this->adapter);
        $sql_string = (string) $sql->getSqlStringForSqlObject($select);
        //echo $this->select->getSqlString($this->adapter->getPlatform());
        //echo "----" . var_dump($sql_string) . "----\n";
        // In  ZF 2.3.0 an empty query will return SELECT .*
        if (in_array($sql_string, array('', 'SELECT .*'))) {
            throw new Exception\EmptyQueryException(__METHOD__ . ': Cannot return data of an empty query');
        }
        $this->query_string = $sql_string;


        // In case of unbuffered results (default on mysqli) !!!
        // Seems to not be needed anymore in ZF 2.3+
        // Uncomment if necessary, see also below is_mysqli
        /*
          $is_mysqli = false;
          $driver = $this->adapter->getDriver();
          if (false && $driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {
          $stmt_prototype_backup = $driver->getStatementPrototype();
          if (self::$cache_stmt_prototype === null) {
          // With buffer results
          self::$cache_stmt_prototype = new \Zend\Db\Adapter\Driver\Mysqli\Statement($buffer=true);
          }
          $driver->registerStatementPrototype(self::$cache_stmt_prototype);
          $is_mysqli = true;
          }
         */


        /**
         * Check whether there's a column model
         */
        /*
          $limit_columns = false;
          $renderers     = false;
          if ($this->columnModel !== null) {
          // TODO: optimize when the column model haven't been modified.
          $limit_columns = $this->columnModel->getColumns();
          $renderers     = $this->columnModel->getRowRenderers();
          }
         */
        //$cm = $this->getColumnModel();
        //$cm->setExcluded(array('user_id'));
        //$this->columns = $cm->getColumns();
        //var_dump($this->columns);
        //die();
        try {



            $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
            //$stmt = $sql->prepareStatementForSqlObject( $select );
            //$results = $stmt->execute();
            //var_dump(get_class($results));

            $r = new ResultSet($results);
            $r->setSource($this);

            if ($this->columnModel !== null) {
                $hydrated_columns = $this->columnModel->getColumns();

                $r->setHydratedColumns(array_keys((array) $hydrated_columns));

                $row_renderers = $this->columnModel->getRowRenderers();
                if ($row_renderers->count() > 0) {
                    $r->setRowRenderers($row_renderers);
                }
            }


            if ($options->hasLimit()) {
                //$row = $this->adapter->query('select FOUND_ROWS() as total_count')->execute()->current();
                $row = $this->adapter->createStatement('select FOUND_ROWS() as total_count')->execute()->current();
                $r->setTotalRows($row['total_count']);
            } else {
                $r->setTotalRows($r->count());
            }



            // restore result prototype
            // $this->adapter->getDriver()->registerResultPrototype($result_prototype_backup);
            // restore statement prototype
            // seems not needed in zf 2.3
            /*
              if ($is_mysqli) {
              $this->adapter->getDriver()->registerStatementPrototype($stmt_prototype_backup);
              }
             */
        } catch (\Exception $e) {
            // restore result prototype
            //$this->adapter->getDriver()->registerResultPrototype($result_prototype_backup);
            // seems not needed in zf 2.3
            /*
              if ($is_mysqli) {
              $this->adapter->getDriver()->registerStatementPrototype($stmt_prototype_backup);
              }
             */
            throw new Exception\ErrorException(__METHOD__ . ': Cannot retrieve data (' . $e->getMessage() . ')');
        }
        return $r;
    }

    /**
     * 
     */
    public function loadDefaultColumnModel()
    {

        $sql = new Sql($this->adapter);
        $select = clone $this->select;
        $select->limit(0);
        $sql_string = $sql->getSqlStringForSqlObject($select);
        $metadata_columns = $this->getMetadataReader()->getColumnsMetadata($sql_string);
        $this->columnModel = MetadataMapper::getColumnModelFromMetadata($metadata_columns);
    }

    /**
     * 
     * @throws Exception\UnsupportedFeatureException
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
     * @throws Exception\UnsupportedFeatureException
     * @return AbstractMetadataReader
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
                throw new Exception\UnsupportedFeatureException(__METHOD__ . " Does not support default metadata reader for driver '$class'");
        }
    }
    
    /**
     * Return the query string that was executed
     * @throws Exception\InvalidUsageException
     * @return string
     */
    public function getQueryString()
    {
        if ($this->query_string == '') {
            throw new Exception\InvalidUsageException(__METHOD__ . ": Invalid usage, getQueryString must be called after data has been loaded (performance reason).");
        }
        return str_replace("\n", ' ', $this->query_string);
    }

    /**
     * Return the query string
     * See getQueryString()
     * 
     * @throws Exception\InvalidUsageException
     * @return string
     */
    public function __toString()
    {
        return $this->getQueryString();
    }

}
