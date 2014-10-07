<?php

namespace Soluble\FlexStore\Source;

use Soluble\FlexStore\Options;

interface SourceInterface
{
    /**
     *
     * @param Options $options
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(Options $options = null);


    /**
     * @return \Soluble\FlexStore\Column\ColumnModel
     */
    public function getColumnModel();
    
    /**
     *
     * @return \Soluble\Flexstore\Metadata\Reader\AbstractMetadataReader
     */
    public function getMetadataReader();
}
