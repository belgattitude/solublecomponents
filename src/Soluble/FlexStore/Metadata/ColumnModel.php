<?php

namespace Soluble\FlexStore\Metadata;

use ArrayObject;

class ColumnModel
{
    
    /**
     *
     * @var ArrayObject
     */
    protected $columns;
    
    function __construct(ArrayObject $columns)
    {
        $this->columns = $columns;
    }
    
    
    /**
     * @return ArrayObject
     */
    function getColumns()
    {
        return $this->columns;
    }
    
}