<?php
namespace Soluble\Normalist\Driver\Metadata;

use Soluble\Db\Metadata\Source;
use Soluble\Db\Metadata\Exception;

class NormalistModels extends Source\AbstractSource
{

    /**
     * Current class version
     */
    const VERSION = '1.0';

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
     * @param boolean $include_primary include primary keys in the list
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     * @return array
     */
    public function getUniqueKeys($table, $include_primary = false)
    {
        $this->checkTableArgument($table);
        return $this->model_definition['tables'][$table]['unique_keys'];
    }


    /**
     * Return indexes information on a table
     *
     * @param string $table table name
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @return array
     */
    public function getIndexesInformation($table)
    {
        $this->checkTableArgument($table);
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
     * @return string|int primary key
     */
    public function getPrimaryKey($table)
    {
        $this->checkTableArgument($table);
        $pks = $this->getPrimaryKeys($table);
        if (count($pks) > 1) {
            $keys = join(',', $pks);
            throw new Exception\MultiplePrimaryKeyException(__METHOD__ . ". Multiple primary keys found on table '$table':  $keys");
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
     * @return array primary keys
     */
    public function getPrimaryKeys($table)
    {
        $this->checkTableArgument($table);
        $pks = $this->model_definition['tables'][$table]['primary_keys'];
        if (count($pks) == 0) {
            throw new Exception\NoPrimaryKeyException(__METHOD__ . ". No primary keys found on table '$table'.");
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
     * @return array associative array [column_name => infos]
     */
    public function getColumnsInformation($table)
    {
        $this->checkTableArgument($table);
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
     *
     * @return array
     */
    public function getRelations($table)
    {
        $this->checkTableArgument($table);
        return $this->model_definition['tables'][$table]['foreign_keys'];

    }

    /**
     * Return table informations
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @return array associative array indexed by table_name
     */
    public function getTablesInformation()
    {
        return $this->model_definition['tables'];
    }
}
