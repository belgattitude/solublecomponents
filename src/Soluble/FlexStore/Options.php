<?php

namespace Soluble\FlexStore;

class Options
{

    /**
     *
     * @var integer|null
     */
    protected $limit;

    /**
     *
     * @var integer|null
     */
    protected $offset;

    public function __construct()
    {
        
    }

    /**
     * Set the (maximum) number of results to return
     *
     * @param int $limit
     * @param int $offset
     * @return Options
     */
    public function setLimit($limit, $offset=null)
    {
        if ($limit === null) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ": limit parameter cannot be null, use unsetLimit instead.");
        }
        $l = filter_var($limit, FILTER_VALIDATE_INT);
        if (!is_int($l)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ": limit parameter must be an int.");
        }        
        $this->limit = $l;
        if ($offset !== null) {
            $this->setOffset($offset);
        }
        return $this;
    }

    /**
     *
     * @return integer|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Unset limit of results
     * Provides fluent interface
     *
     * @return Options
     */
    public function unsetLimit()
    {
        $this->limit = null;
        return $this;
    }

    /**
     * Tells whether the option contains a limit
     * @return boolean
     */
    public function hasLimit()
    {
        return $this->limit !== null;
    }

    /**
     * Tells whether the option contains an offset
     * @return boolean
     */
    public function hasOffset()
    {
        return $this->offset !== null;
    }    
    
    /**
     * Set the offset (the record to start reading when using limit)
     * @param int $offset
     * @return Options
     */
    public function setOffset($offset)
    {
        if ($offset === null) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ": offset parameter cannot be null, use unsetOffset instead.");
        }
        $o = filter_var($offset, FILTER_VALIDATE_INT);
        if (!is_int($o)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ": offset parameter must be an int.");
        }        
        $this->offset = $o;
        return $this;
    }

    /**
     * Return the offset when using limit
     * Offset gives the record number to start reading
     * from when a paging query is in use
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Unset previously set offset
     * @return \Soluble\FlexStore\Options
     */
    public function unsetOffset()
    {
        $this->offset = null;
        return $this;
    }

}
