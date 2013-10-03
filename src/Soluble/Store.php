<?php
/**
 * @author Vanvelthem Sébastien
 */


namespace Smart\Data;

class Store {
	
    /**
     * Get a new adapter
     *
	 * @return Smart\Data\Adapter\Adapter
     */
    public static function factory($adapterType, $adapter)
    {
        if (Zend_Loader::isReadable('Vision/Store/Adapter/' . ucfirst($adapter). '.php')) {
            $adapter = 'Vision_Store_Adapter_' . ucfirst($adapter);
        }

        if (!class_exists($adapter)) Zend_Loader::loadClass($adapter);
        
        $this->_adapter = new $adapter($datasource, $translate, $options);
        if (!$this->_adapter instanceof Vision_Store_Adapter_Abstract) {
            require_once 'Vision/Store/Exception.php';
            throw new Vision_Store_Exception("Adapter " . $adapter . " does not extend Vision_Store_Adapter_Abstract");
        }
    }
    
  	
}