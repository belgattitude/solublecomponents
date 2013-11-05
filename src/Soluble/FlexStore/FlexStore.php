<?php
/**
 * @author Vanvelthem Sébastien
 */


namespace Soluble\FlexStore;
use Soluble\FlexStore\Source; 
use Soluble\FlexStore\Exception; 

class FlexStore implements FlexStoreInterface {
	

	
	/**
	 *
	 * @var Source\SourceInterface
	 */
	protected $source;
	
	
	/**
	 * 
	 * @param Source/SourceInterface|array $source
	 * @param array $parameters 
	 */
  	function __construct($source, array $parameters=null) {

		//$flexStore = new FlexStore('zend\select', array('select' => $select, 'adapter' => $adapter));
		
        if (is_string($source)) {
			$type = $source;
			if (!is_array($parameters)) {
				throw new Exception\InvalidArgumentException(__FUNCTION__ . ' parameters must be a valid array');
			}
            $source = $this->createSource($type, $parameters);
        } elseif (!$source instanceof Source\SourceInterface) {
            throw new Exception\InvalidArgumentException(
                'The supplied or instantiated source object does not implement Soluble\FlexStore\Source\SourceInterface'
            );
        }
        $this->source = $source;	
		
	}
	
	
	/**
	 * 
	 * @return Source\SourceInterface
	 */
	public function getSource() {
		return $this->source;
	}


	/**
	 * 
	 * @param string $type
	 * @param array $parameters
	 * @return \Soluble\FlexStore\Source\Zend\Select
	 * @throws Exception\UnsupportedFeatureException
	 */
	protected function createSource($type, array $parameters) {
        $sourceName = strtolower($type);
        switch ($sourceName) {
            case 'zend\select':
                $source = new Source\Zend\SelectSource($parameters);
                break;
            default:
				throw new Exception\UnsupportedFeatureException(__FUNCTION__ . " source '$sourceName' is currently unsupported");
        }
        return $source;
	}
	
	
	
	
	
}