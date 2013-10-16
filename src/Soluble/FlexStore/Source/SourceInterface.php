<?php

namespace Soluble\FlexStore\Source;
use Soluble\FlexStore\Options;

interface SourceInterface {
	
	
	/**
	 * 
	 * @param Soluble\FlexStore\Options $options
	 * @return Soluble\FlexStore\ResultSet\ResultSet
	 */
	public function getData(Options $options = null);	
}