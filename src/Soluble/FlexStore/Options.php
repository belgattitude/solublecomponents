<?php
namespace Soluble\FlexStore;


class Options
{
    /**
     *
     * @var integer
     */
    protected $limit;


    /**
     *
     * @var integer
     */
    protected $offset;



    public function __construct()
    {

    }


    /**
     * Set the (maximum) number of results to return
     *
     * @param int $limit
     * @return Options
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     *
     * @return integer
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
        return ($this->limit > 0 && $this->limit !== null);
    }


    /**
     * Set the offset (the record to start reading when using limit)
     * @param int $offset
     * @return Options
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Return the offset when using limit
     * Offset gives the record number to start reading
     * from when a paging query is in use
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

}
