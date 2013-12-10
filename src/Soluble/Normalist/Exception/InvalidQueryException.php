<?php

namespace Soluble\Normalist\Exception;

class InvalidQueryException extends ErrorException
{
	/**
	 * @var string
	 */
	protected $sql_string;
	
	/**
	 * 
	 * @return \Soluble\Normalist\Exception\InvalidQueryException
	 */
	public function setSQLString($sql_string) {
		
		$this->sql_string = $sql_string;
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getSQLString() {
		return $this->sql_string;
	}
}
