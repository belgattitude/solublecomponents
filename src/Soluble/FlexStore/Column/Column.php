<?php

namespace Soluble\FlexStore\Column;

class Column {
    
    /**
     *
     * @var string
     */
    protected $name;
    
    
    /**
     *
     * @var boolean
     */
    protected $excluded = false;
    
    /**
     *
     * @var boolean 
     */
    protected $hidden = false;
    
    /**
     *
     * @var string
     */
    protected $header;
    
    /**
     *
     * @var float|null
     */
    protected $width;
    
    
    /**
     *
     * @var string
     */
    protected $type;
    
    /**
     *
     * @var boolean
     */
    protected $sortable;
    
    /**
     * @var boolean
     */
    protected $filterable;
    
    /**
     *
     * @var boolean
     */
    protected $groupable;
    

    /**
     *
     * @var array
     */
    protected $defaults = array(
        'filterable' => true,
        'groupable' => true,
        'sortable' => true,
        'hidden' => false,
        'excluded' => false
    );
    

    /**
     * Constructor
     * @param string $name
     * @throws Exception\InvalidArgumentException
     */
    function __construct($name)
    {
        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name must be a string");
        }
        if (trim($name) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name cannot be empty");
        }
        $this->name = $name;
        $this->setHeader($name);
        $this->setDefaults();
    }
    
    /**
     * Set defaults
     */
    protected function setDefaults()
    {
        $this->sortable     = $this->defaults['sortable'];
        $this->groupable    = $this->defaults['groupable'];
        $this->filterable   = $this->defaults['filterable'];
        $this->hidden       = $this->defaults['hidden'];
        $this->excluded     = $this->defaults['excluded'];
        
    }
    
    /**
     * Get the name of the column
     * 
     * @return string 
     */
    function getName()
    {
        return $this->name;
    }
    
    /**
     * 
     * @param boolean $excluded
     * @return Column
     */
    function setExcluded($excluded=true)
    {
        $this->excluded = (bool) $excluded;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    function isExcluded()
    {
        return $this->excluded;
    }
    
    /**
     * 
     * @param boolean $hidden
     * @return Column
     */
    function setHidden($hidden=true)
    {
        $this->hidden = (bool) $hidden;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    function isHidden()
    {
        return (bool) $this->hidden;
    }

    
    /**
     * 
     * @param boolean $sortable
     * @return Column
     */
    function setSortable($sortable=true)
    {
        $this->sortable = (bool) $sortable;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    function isSortable()
    {
        return (bool) $this->sortable;
    }

    
    
    /**
     * 
     * @param boolean $groupable
     * @return Column
     */
    function setGroupable($groupable=true)
    {
        $this->groupable = (bool) $groupable;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    function isGroupable()
    {
        return (bool) $this->groupable;
    }    

    /**
     * 
     * @param boolean $filterable
     * @return Column
     */
    function setFilterable($filterable=true)
    {
        $this->filterable = (bool) $filterable;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    function isFilterable()
    {
        return (bool) $this->filterable;
    }    
        
    
    /**
     * Set recommended width for the column
     * 
     * @throws Exception\InvalidArgumentException
     * @param float|int|string $width
     * @return Column
     */
    function setWidth($width)
    {
        if (!is_scalar($width)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Width parameter must be scalar.");
        }
        $this->width = $width;
        return $this;
    }
    
    /**
     * 
     * @return float|int|string
     */
    function getWidth()
    {
        return $this->width;
    }
    
    /**
     * Set table header for this column
     * 
     * @throws Exception\InvalidArgumentException
     * @param string|null $header
     * @return Column
     */
    function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }    
    
    /**
     * 
     * @return string|null
     */
    function getHeader()
    {
        return $this->header;
    }
    
    /**
     * 
     * @return string
     */
    function __toString()
    {
        return $this->name;
    }
}