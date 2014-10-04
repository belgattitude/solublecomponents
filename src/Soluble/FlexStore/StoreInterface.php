<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore;

use Soluble\FlexStore\Options;

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
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(Options $options = null);

    /**
     * Return column model associated with datasource
     * @return \Soluble\FlexStore\Column\ColumnModel
     */
    public function getColumnModel();
}
