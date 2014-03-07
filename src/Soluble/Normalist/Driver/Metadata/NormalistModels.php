<?php
namespace Soluble\Normalist\Driver\Metadata;

use Soluble\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Config;
use Zend\Config\Writer;
use Soluble\Db\Metadata\Exception;


class NormalistModels extends Source\AbstractSource
{
    
    /**
     * @var array
     */
    protected $model_definition;
    
    /**
     * @param array $model_definition
     */
    public function __construct(array $model_definition)
    {
        $this->model_definition = $model_definition;
    }    
    
    
    /**
     * Get unique keys on table
     *
     * @param string $table table name
     * @param string $schema schema name
     * @param boolean $include_primary include primary keys in the list
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     * @return array
     */
    public function getUniqueKeys($table, $schema=null, $include_primary=false) 
    {
        
        return $this->model_definition['tables'][$table]['unique_keys'];
    }


    /**
     * Return indexes information on a table
     *
     * @param string $table table name
     * @param string $schema schema name
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @return array
     */
    public function getIndexesInformation($table, $schema=null)
    {
        
        return $this->model_definition['tables'][$table]['indexes'];
    }

    /**
     * Return unique table primary key
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException when no pk 
     * @throws Exception\MultiplePrimaryKeyException when multiple pk found
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     * @return string|int primary key
     */
    public function getPrimaryKey($table, $schema=null)
    {
        if ($schema === null) $schema = $this->schema;
        $pks = $this->getPrimaryKeys($table, $schema);
        if (count($pks) > 1) {
            $keys = join(',', $pks);
            throw new Exception\MultiplePrimaryKeyException(__METHOD__ . ". Multiple primary keys found on table '$schema'.'$table':  $keys");
        }
        return $pks[0];
    }


    /**
     * Return composite primary keys
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     * @return array primary key
     */
    public function getPrimaryKeys($table, $schema=null)
    {
        if ($schema === null) $schema = $this->schema;
        $pks = $this->model_definition['tables'][$table]['primary_keys'];
        if (count($pks) == 0) {
            throw new Exception\NoPrimaryKeyException(__METHOD__ . ". No primary keys found on table '$schema'.'$table'.");
        }
        return $pks;
    }


    /**
     * Return column information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     * @return array associative array [column_name => infos]
     */
    public function getColumnsInformation($table, $schema=null)
    {
        
        return $this->model_definition['tables'][$table]['columns'];
        
    }


    /**
     * Return relations information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     *
     * @return array
     */
    public function getRelations($table, $schema=null)
    {
        
        return $this->model_definition['tables'][$table]['foreign_keys'];
        
    }

    /**
     * Return table informations
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @param string $schema
     * @return array associative array indexed by table_name
     */
    public function getTablesInformation($schema=null)
    {
        return $this->model_definition['tables'];
    }
    
    
}