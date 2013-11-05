<?php
namespace Soluble\FlexStore\Writer\Zend;
use Zend\View\Model\JsonModel as ZendJsonModel;
use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Writer\AbstractWriter;
use Soluble\FlexStore\Writer\SendHeaders;


class JsonModel extends AbstractWriter {
	
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
		
		if ($this->options['debug']) {
			$d['query'] = $data->getSource()->getQueryString();
		}
		$json = new ZendJsonModel($d);
		return $json;
	}
	
	/**
	 * 
	 * @param \Soluble\FlexStore\Writer\SendHeaders $headers
	 * @throws \Exception
	 */
	function send(SendHeaders $headers) {
		throw new \Exception("Not supported");
	}
	
}