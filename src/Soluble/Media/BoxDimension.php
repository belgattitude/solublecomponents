<?php
namespace Soluble\Media;

class BoxDimension {
	
	/**
	 * 
	 * @param int $width
	 * @param int $heigth
	 */
	function __construct($width=null, $height=null) {
		$this->setWidth($width);
		$this->setHeight($height);
		
	}
	
	
	/**
	 * 
	 * @param int $width
	 * @return \Soluble\Media\BoxDimension
	 */
	function setWidth($width) {
		$this->width = $width;
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	function getWidth() {
		return $this->width;
	}
	
	/**
	 * 
	 * @param int $height
	 * @return \Soluble\Media\BoxDimension
	 */
	function setHeight($height) {
		$this->height = $height;
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	function getHeight() {
		return $this->height;
	}
	
}