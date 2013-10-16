<?php
namespace Soluble\FlexStore\Writer;
use Soluble\FlexStore\Writer\AbstractWriter;


class Json extends AbstractWriter {
	

	/**
	 * 
	 * @return \Zend\View\Model\JsonModel
	 */
	function getData() {
		$data = $this->source->getData();
		$d = array(
			'success'	 => true,
			'total'		 => $data->getTotalRows(), 
			'start'		 => $data->getSource()->getOptions()->getOffset(),
			'limit'		 => $data->getSource()->getOptions()->getLimit(),
			'data'		 => $data->toArray()
		
		);
		
		if ($this->debug) {
			$d['query'] = $data->getSource()->getQueryString();
		}
		return json_encode($d);
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