<?php
namespace Soluble\FlexStore;


class Options {
	
	
	/**
	 *
	 * @var integer
	 */
	protected $limit;
	
	
	
	function __construct()
	{
		
	}
	

	/**
	 * Set the (maximum) number of results to return
	 *  
	 * @param int $limit
	 * @return \Soluble\FlexStore\Options
	 */
	function setLimit($limit) {
		$this->limit = (int) $limit;
		return $this;
	}

	/**
	 * @return integer
	 */
	function getLimit() {
		return $this->limit;
	}	
	
	/**
	 * Unset limit of results
	 * Provides fluent interface
	 *  
	 * @return \Soluble\FlexStore\Options
	 */
	function unsetLimit() {
		$this->limit = null;
		return $this;
	}

	/**
	 * Tells whether the option contains a limit
	 * @return boolean
	 */
	function hasLimit() {
		return ($this->limit > 0 && $this->limit !== null);
	}
	
	
	/**
	 * Set the offset (the record to start reading when using limit)
	 * @param int $offset
	 * @return \Soluble\FlexStore\Options
	 */
	function setOffset($offset) {
		$this->offset = (int) $offset;
		return $this;
	}

	/**
	 * Return the offset when using limit
	 * Offset gives the record number to start reading
	 * from when a paging query is in use
	 * @return int
	 */
	function getOffset() {
		return $this->offset;
	}
	
	

}