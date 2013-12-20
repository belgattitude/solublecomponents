<?php
/**
 *
 * @author Vanvelthem Sébastien
 */
namespace Soluble\FlexStore\Source;

use Soluble\FlexStore\Options;


abstract class AbstractSource implements SourceInterface {

	/**
	 * @var Soluble\FlexStore\Options
	 */
	protected $options;
	

	/**
	 * columns to retrieve when calling getData
	 * @var array
	 */
	protected $columns;
	
	
	/**
	 * 
	 * @param array $columns
	 * @return \Soluble\FlexStore\Source\AbstractSource
	 */
	public function setColumns(array $columns) 
	{
		$this->columns = $columns;
		return $this;
	}
	
	
	/**
	 * 
	 * @return \Soluble\FlexStore\Source\AbstractSource
	 */
	public function unsetColumns() 
	{
		$this->columns = null;
		return $this;
	}	
	
	/**
	 * 
	 * @return Soluble\FlexStore\Options
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
	 * @param Soluble\FlexStore\Options $options
	 * @return Soluble\FlexStore\ResultSet\ResultSet
	 */
	abstract public function getData(Options $options = null);

	



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