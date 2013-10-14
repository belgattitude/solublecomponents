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
		$json = new ZendJsonModel(array(
			'success'	 => true,
			'total'		 => $data->getTotalRows(), 
			'start'		 => $data->source->getOptions()->getOffset(),
			'limit'		 => $data->source->getOptions()->getLimit(),
			'data'		 => $data->toArray(),
			'query'		 => $data->source->getQueryString()
		));
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