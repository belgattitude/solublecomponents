<?php
namespace Soluble\FlexStore\Writer;
use Soluble\FlexStore\Writer\AbstractWriter;


class SimpleXmlWriter extends AbstractWriter {

	
	/**
	 * @var array
	 */
	protected $options = array(
		/**
		 * XML tag for response
		 */
		'body_tag' => 'response',
		/**
		 * XML tag for rows
		 */
		'row_tag' => 'row',
		
		'encoding' => 'UTF-8'
	);

	/**
	 * 
	 * @param string $row_tag
	 * @return \Soluble\FlexStore\Writer\SimpleXmlWriter
	 */
	function setRowTag($row_tag) {
		$this->options['row_tag'] = $row_tag;
		return $this;
	}

	/**
	 * 
	 * @param string $body_tag
	 * @return \Soluble\FlexStore\Writer\SimpleXmlWriter
	 */
	function setBodyTag($body_tag) {
		$this->options['body_tag'] = $body_tag;
		return $this;
	}
	
	
	/**
	 * 
	 * @return string xml encoded data
	 */
	public function getData() {
		$data = $this->source->getData();
		$bt = $this->options['body_tag'];
		$encoding = $this->options['encoding'];
		$xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"$encoding\" ?><$bt></$bt>");

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
   /**
    * 
    * @param type $result
    * @param type $xml
    */
   protected function createXmlNode($result, &$xml)
   {
        foreach($result as $key => $value) {
			
            if (is_array($value)) {
                if (!is_numeric($key)) {
                  $subnode = $xml->addChild("$key");
                  $this->createXmlNode($value, $subnode);
                } else {
					$v = array($this->options['row_tag'] => $value);
                    $this->createXmlNode($v, $xml);
                }
            } else {
				
				$xml->addChild("$key", htmlspecialchars($value, ENT_XML1, $this->options->encoding));				
                
            }
        }
   }	

	/**
	 * 
	 * @param \Soluble\FlexStore\Writer\SendHeaders $headers
	 */
	public function send(SendHeaders $headers=null) {
		if ($headers === null) $headers = new SendHeaders();
		ob_end_clean();
		$headers->setContentType('application/xml; charset=utf-8');
		$headers->printHeaders();
		$xml = $this->getData();
		echo $xml;
	}
}