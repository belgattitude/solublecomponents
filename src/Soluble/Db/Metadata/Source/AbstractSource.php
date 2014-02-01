<?php
namespace Soluble\Db\Metadata\Source;


abstract class AbstractSource
{
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

}
