<?php

namespace Soluble\FlexStore\Column;

use Soluble\FlexStore\Formatter\FormatterInterface;

interface ColumnSettableInterface
{
    
    
    /**
     * Set column datatype
     * @param string|\Soluble\FlexStore\Column\Type\AbstractType $type
     * @throws Exception\InvalidArgumentException when the type is not supported.
     */
    function setType($type);
    
    
    /**
     *
     * @param FormatterInterface $formatter
     */
    function setFormatter(FormatterInterface $formatter);
    
    /**
     *
     * @param boolean $excluded
     */
    function setExcluded($excluded = true);

    /**
     *
     * @param boolean $virtual
     */
    function setVirtual($virtual = true);
    
    /**
     *
     * @param boolean $editable
     */
    function setEditable($editable = true);

    /**
     *
     * @param boolean $hidden
     */
    function setHidden($hidden = true);
    
    
    /**
     *
     * @param boolean $sortable
     */
    function setSortable($sortable = true);

    
    
    /**
     *
     * @param boolean $groupable
     */
    function setGroupable($groupable = true);
    

    /**
     *
     * @param boolean $filterable
     */
    function setFilterable($filterable = true);

    
    /**
     * Set recommended width for the column
     *
     * @throws Exception\InvalidArgumentException
     * @param float|int|string $width
     */
    function setWidth($width);
    
    /**
     * Set table header for this column
     *
     * @throws Exception\InvalidArgumentException
     * @param string|null $header
     */
    function setHeader($header);
}
