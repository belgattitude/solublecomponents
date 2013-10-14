<?php

namespace Soluble\Flexstore\Writer;
use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Writer\SendHeaders;

abstract class AbstractWriter {
	
	
	/**
	 *
	 * @var \Soluble\FlexStore\Source\AbstractSource
	 */
	protected $source; 
	
	/**
	 *
	 * @var array
	 */
	protected $params;
	
	/**
	 * 
	 * @param array $params
	 */
	function __construct(AbstractSource $source=null, array $params=null) {
		if ($source !== null) {
			$this->setSource($source);
		}
		$this->params = $params;
	}
	
	
	/**
	 * 
	 * @param \Soluble\FlexStore\Source\AbstractSource $source
	 * @return \Soluble\FlexStore\Writer\Json
	 */
	function setSource(AbstractSource $source) {
		$this->source = $source;
		return $this;
	}
	
	
	/**
	 * 
	 */
	abstract function getData();
	

	/**
	 * @param SendHeaders $headers
	 * @return void
	 */
	abstract function send(SendHeaders $headers=null);
	
	
}