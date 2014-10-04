<?php

namespace Soluble\FlexStore\Column\ColumnModel;

use Soluble\FlexStore\Column\ColumnModel\Search\Result;
use Soluble\FlexStore\Column\Exception;
use ArrayObject;

class Search
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
     *
     * @return Result
     */
    function all()
    {
        return new Result(array_keys((array) $this->columns), $this->columns);
    }

    /**
     *
     * @throws Exception\InvalidArgumentException
     * @return Result
     */
    function notIn(array $columns)
    {
        $results = array();
        foreach ($this->columns as $name => $column) {
            if (!array_key_exists($name, $columns)) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     *
     * @throws Exception\InvalidArgumentException
     * @return Result
     */
    function in(array $columns)
    {
        $results = array();
        foreach ($columns as $column) {
            $column = trim($column);
            if ($this->columns->offsetExists($column)) {
                $results[] = $column;
            } else {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in column model.");
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     *
     * @return Result
     */
    function regexp($regexp)
    {
        $results = array();
        foreach ($this->columns as $name => $column) {
            $column = trim($column);
            if (preg_match($regexp, $name)) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     *
     * @param string $type
     * @return Result
     */
    function findByType($type)
    {
        $results = array();
        foreach ($this->columns as $name => $column) {
            if ($column->getType() == $type) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }
}
