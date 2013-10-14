<?php
namespace Soluble\FlexStore\Writer\Zend;
use Soluble\FlexStore\Writer\AbstractWriter;
use Zend\Json\Encoder;
use Soluble\FlexStore\Writer\SendHeaders;

class Json extends AbstractWriter {


	/**
	 * 
	 * @return \Zend\View\Model\JsonModel
	 */
	function getData() {
		$data = $this->source->getData();
		$json = Encoder::encode(array(
			'success'	 => true,
			'total'		 => $data->getTotalRows(), 
			'start'		 => $data->getSource()->getOptions()->getOffset(),
			'limit'		 => $data->getSource()->getOptions()->getLimit(),
			'data'		 => $data->toArray(),
			'query'		 => $data->getSource()->getQueryString()
		));
		return $json;
	}
	
	/**
	 * 
	 * @param \Soluble\FlexStore\Writer\SendHeaders $headers
	 */
	function send(SendHeaders $headers=null) {
		if ($headers === null) $headers = new SendHeaders();
		ob_end_clean();
		$headers->setContentType('application/json; charset=utf-8');
		$headers->printHeaders();
		$json = $this->getData();
		echo $json;
	}
	
}