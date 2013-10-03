<?php
namespace Smart\Data\Store\Writer;
use Smart\Data\Store\Adapter\Adapter;
use Zend\Json\Encoder;
use Zend\Http\Response;
use Zend\Http\Headers;

class Json {
	
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
		$json = Encoder::encode(array(
			'success'	 => true,
			'total'		 => $data->getTotalRows(), 
			'start'		 => $data->getStore()->getOptions()->getOffset(),
			'limit'		 => $data->getStore()->getOptions()->getLimit(),
			'data'		 => $data->toArray(),
			'query'		 => $data->getStore()->getQueryString()
		));
		return $json;
	}
	
	function send() {
		// So fast !!! - 100ms
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8', $replace=true);
		$json = $this->getData();
		echo $json;
		die();
		
		// So slow - 400ms
		/*
		$response = new Response();
		$response->setStatusCode(Response::STATUS_CODE_200);
		$response->getHeaders()->addHeaders(array(
			'Content-Type' => 'application/json; charset=utf-8',
		));
		$response->setContent($this->getData());
		return $response;
  	    */
	}
	
}