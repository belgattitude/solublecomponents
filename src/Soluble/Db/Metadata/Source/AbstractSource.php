<?php
namespace Soluble\Db\Metadata\Source;

use Soluble\Db\Metadata\Exception;

abstract class AbstractSource
{
    /**
     * Default schema name
     * @var string
     */
    protected $schema;


    /**
     * Return unique indexes
     * @param string $table
     * @param string $schema
     * @return array
     */
    abstract public function getUniqueKeys($table, $schema=null, $include_primary=false);


    /**
     * Return unique table primary key
     *
     * @throws Exception in case of a multiple primary key
     * @param string $table
     * @param string $schema
     * @return null|string|int primary key
     */
    abstract public function getPrimaryKey($table, $schema=null);



    /**
     * Return column information
     *
     * @throws Exception
     * @param string $table
     * @param string $schema
     * @return array associative array [column_name => infos]
     */
    abstract public function getColumnsInformation($table, $schema=null);


    /**
     * Return relations information
     * @param string $table
     * @param string $schema
     * @return
     */
    abstract public function getRelations($table, $schema=null);

    /**
     * Return table informations
     *
     * @param string $schema
     * @return array associative array indexed by table_name
     */
    abstract public function getTablesInformation($schema=null);


    /**
     * Return column information
     *
     * @throws Exception
     * @param string $table
     * @param string $schema
     * @return array
     */

    public function getColumns($table, $schema=null)
    {
        return array_keys($this->getColumnsInformation($table, $schema));
    }


    /**
     * Return information about a specific table
     * @param string $table
     * @param string $schema
     */
    public function getTableInformation($table, $schema=null)
    {

        $infos = $this->getTablesInformation($schema);
        return $infos[$table];
    }

    /**
     *
     * @param string $schema
     * @return array
     */
    public function getTables($schema=null)
    {

        return array_keys($this->getTablesInformation($schema));
    }


    /**
     * Check whether a table exists in the specified or current scheme
     *
     * @param string $table
     * @param string $schema
     * @return bool
     */
    public function hasTable($table, $schema=null)
    {
        $tables = $this->getTables($schema);
        return in_array($table, $tables);
    }

    /**
     * Check whether a table parameter is valid and exists
     *
     * @throws Exception\TableNotFoundException
     * @param string $table
     * @param string $schema
     * @return AbstractSource
     */
    protected function validateTable($table, $schema=null)
    {
        if (!$this->hasTable($table, $schema)) {
            throw new Exception\TableNotFoundException("Table '$table' does not exists in database '$schema'");
        }
        return $this;
    }

    /**
     * Check whether a schema parameter is valid
     *
     * @throws Exception\InvalidArgumentException

     * @param string $schema
     * @return AbstractSource
     */
    protected function validateSchema($schema)
    {
        if (!is_string($schema) || trim($schema) == '') {
            throw new Exception\InvalidArgumentException("Schema name must be a valid string or an empty string detected");
        }
        return $this;
    }



    /**
     * Set default schema
     *
     * @throws Exception\InvalidArgumentException
     * @param string $schema
     * @return AbstractSource
     */
    public function setDefaultSchema($schema)
    {
        $this->validateSchema($schema);
        $this->schema = $schema;
        return $this;
    }


}
