<?php
namespace Smart\Data\Store\Writer\Zend;
use Zend\View\Model\JsonModel as ZendJsonModel;
use Smart\Data\Store\Adapter\Adapter;
use Soluble\FlexStore\Writer\AbstractWriter;
use Soluble\FlexStore\Writer\SendHeaders;


class JsonModel extends AbstractWriter {
	
	/**
	 *
	 * @var \Smart\Data\Store\Adapter\Adapter
	 */
	protected $store; 
	
	function __construct(Adapter $store) {
		$this->store = $store;
	}
	

	/**
	 * 
	 * @return \Zend\View\Model\JsonModel
	 */
	function getData() {
		$data = $this->store->getData();
		$json = new ZendJsonModel(array(
			'success'	 => true,
			'total'		 => $data->getTotalRows(), 
			'start'		 => $data->getStore()->getOptions()->getOffset(),
			'limit'		 => $data->getStore()->getOptions()->getLimit(),
			'data'		 => $data->toArray(),
			'query'		 => $data->getStore()->getQueryString()
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