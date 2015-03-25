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
    public function setType($type);
    
    
    /**
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter);
    
    /**
     *
     * @param boolean $excluded
     */
    public function setExcluded($excluded = true);

    /**
     *
     * @param boolean $virtual
     */
    public function setVirtual($virtual = true);
    
    /**
     *
     * @param boolean $editable
     */
    public function setEditable($editable = true);

    /**
     *
     * @param boolean $hidden
     */
    public function setHidden($hidden = true);
    
    
    /**
     *
     * @param boolean $sortable
     */
    public function setSortable($sortable = true);

    
    
    /**
     *
     * @param boolean $groupable
     */
    public function setGroupable($groupable = true);
    

    /**
     *
     * @param boolean $filterable
     */
    public function setFilterable($filterable = true);

    
    /**
     * Set recommended width for the column
     *
     * @throws Exception\InvalidArgumentException
     * @param float|int|string $width
     */
    public function setWidth($width);
    
    /**
     * Set table header for this column
     *
     * @throws Exception\InvalidArgumentException
     * @param string|null $header
     */
    public function setHeader($header);
}
