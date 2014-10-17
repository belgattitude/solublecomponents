<?php

namespace Soluble\FlexStore\Formatter;

class RowColumn
{

    /**
     *
     * @var string
     */
    protected $column_name;

    /**
     *
     * @param string $column_name
     */
    function __construct($column_name)
    {
        $this->column_name = $column_name;
    }

    /**
     *
     * @return string
     */
    function getColumnName()
    {
        return $this->column_name;
    }
}
