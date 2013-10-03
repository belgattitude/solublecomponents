<?php
/**
 *
 * @author Vanvelthem Sébastien
 */
namespace Soluble\Store\Adapter;

use Soluble\Store\Options;

abstract class Adapter {

	/**
	 * @var \Soluble\Store\Options
	 */
	protected $options;
	

	/**
	 * 
	 * @return \Soluble\Store\Options
	 */
	function getOptions()
	{
		if ($this->options === null) {
			$this->options = new Options();
		}
		return $this->options;
	}


	/**
	 * 
	 * @param \Soluble\Store\Options $options
	 * @return \Soluble\Store\ResultSet\ResultSet;
	 */
	abstract public function getData(Soluble\Store\Options $options = null);

	

	/**
	 * Return the total affected rows (SQL_CALC_FOUND_ROWS)
	 * Must be called after getData
	 * @throws Vision_Store_Exception
	 * @return int
	 */
	function getTotalCount() {
		if ($this->totalCount === null) {
			throw new Vision_Store_Exception("Total count cannot be called before getData.");
		}
		return $this->totalCount;
	}

	/**
	 * Set the primary key / unique identifier in the store
	 * 
	 * @param string $identifier column name of the primary key 
	 * @return Vision_Store_Adapter_Abstract
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * Return the primary key / unique identifier in the store
	 * Null if not applicable
	 * 
	 * @return string|null column name
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * 
	 * @return string
	 */
	abstract public function getQueryString();

}