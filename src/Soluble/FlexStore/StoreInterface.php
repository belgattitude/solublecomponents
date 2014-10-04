<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore;

interface StoreInterface
{

    /**
     * Return underlying data source
     * @return Source\SourceInterface
     */
    public function getSource();

    /**
     * Return the underlying store data as a resultset
     * 
     * @param Options $options
     * @return ResultSet
     */
    public function getData(Options $options = null);

    /**
     * Return column model associated with datasource
     * @return ColumnModel
     */
    public function getColumnModel();
}
