<?php
namespace Soluble\FlexStore\Writer;
use Soluble\FlexStore\Writer\AbstractWriter;


class SimpleXmlWriter extends AbstractWriter {

	protected $row_tag = 'row';
	protected $body_tag = 'response';


	/**
	 * 
	 * @param string $row_tag
	 * @return \Soluble\FlexStore\Writer\SimpleXmlWriter
	 */
	function setRowTag($row_tag) {
		$this->row_tag = $row_tag;
		return $this;
	}
	
	/**
	 * 
	 * @return string xml encoded data
	 */
	function getData() {
		$data = $this->source->getData();
		$bt = $this->body_tag;
		$xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><$bt></$bt>");

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
		$this->createXmlNode($d, $xml);
        
        
		return $xml->asXML();
	}
	
   protected function createXmlNode($result, &$xml)
   {
        foreach($result as $key => $value) {
			
            if (is_array($value)) {
                if (!is_numeric($key)) {
                  $subnode = $xml->addChild("$key");
                  $this->createXmlNode($value, $subnode);
                } else {
					$v = array($this->row_tag => $value);
                    $this->createXmlNode($v, $xml);
                }
            } else {
				
                $xml->addChild("$key", "$value");
            }
        }
   }	

	/**
	 * 
	 * @param \Soluble\FlexStore\Writer\SendHeaders $headers
	 */
	function send(SendHeaders $headers=null) {
		if ($headers === null) $headers = new SendHeaders();
		ob_end_clean();
		$headers->setContentType('application/xml; charset=utf-8');
		$headers->printHeaders();
		$json = $this->getData();
		echo $json;
	}
}