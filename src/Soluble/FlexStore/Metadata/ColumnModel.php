<?php

namespace Soluble\FlexStore\Metadata;
use Soluble\FlexStore\Metadata\Column\Definition\AbstractColumnDefinition;

use ArrayObject;

class ColumnModel
{

    /**
     *
     * @var ArrayObject
     */
    protected $columns;


    public function __construct(ArrayObject $columns)
    {
        $this->columns = $columns;
    }
    

    /**
     *
     * @param string $column
     * @return AbstractColumnDefinition
     */
    public function getColumnDefinition($column)
    {
        return $this->columns[$column];
    }

    /**
     * Return column names
     *
     * @return ArrayObject
     */
    public function getColumns()
    {
        return $this->columns;
    }




}
