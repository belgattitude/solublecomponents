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
    public function __construct($column_name)
    {
        $this->column_name = $column_name;
    }

    /**
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->column_name;
    }
}
