<?php

namespace Soluble\FlexStore\Column\ColumnModel\Search;

use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnSettableInterface;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\Exception;
use Soluble\FlexStore\Formatter\FormatterInterface;

use ArrayObject;

class Result implements ColumnSettableInterface
{

    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    /**
     *
     * @var array
     */
    protected $results;

    function __construct(array $results, ArrayObject $columns)
    {
        $this->columns = $columns;
        $this->results = $results;
    }
    
    /**
     *
     * @param FormatterInterface $formatter
     * @return Result
     */
    function setFormatter(FormatterInterface $formatter)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setFormatter($formatter);
        }
        return $this;
    }


    /**
     *
     * @throws Exception\InvalidArgumentException
     * @param string|AbstractType $type
     * @return Result
     */
    function setType($type)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setType($type);
        }
        return $this;
    }
    

    /**
     *
     * @param boolean $virtual
     * @return Result
     */
    function setVirtual($virtual = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setVirtual($virtual);
        }
        return $this;
    }
    
    /**
     *
     * @param boolean $excluded
     * @return Result
     */
    function setExcluded($excluded = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setExcluded($excluded);
        }
        return $this;
    }

    /**
     *
     * @param boolean $editable
     * @return Result
     */
    function setEditable($editable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setEditable($editable);
        }
        return $this;
    }

    /**
     *
     * @param boolean $hidden
     * @return Result
     */
    function setHidden($hidden = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setHidden($hidden);
        }
        return $this;
    }

    /**
     *
     * @param boolean $sortable
     * @return Result
     */
    function setSortable($sortable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setSortable($sortable);
        }
        return $this;
    }

    /**
     *
     * @param boolean $groupable
     * @return Result
     */
    function setGroupable($groupable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setGroupable($groupable);
        }
        return $this;
    }

    /**
     *
     * @param boolean $filterable
     * @return Result
     */
    function setFilterable($filterable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setFilterable($filterable);
        }
        return $this;
    }

    /**
     * Set recommended width for the column
     *
     * @throws Exception\InvalidArgumentException
     * @param float|int|string $width
     * @return Result
     */
    function setWidth($width)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setWidth($width);
        }
        return $this;
    }

    /**
     * Set table header for this column
     *
     * @throws Exception\InvalidArgumentException
     * @param string|null $header
     * @return Result
     */
    function setHeader($header)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setHeader($header);
        }
        return $this;
    }

    /**
     *
     * @return array
     */
    function toArray()
    {
        return $this->results;
    }
}
