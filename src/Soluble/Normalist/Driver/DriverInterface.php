<?php

namespace Soluble\Normalist\Driver;

use Soluble\Db\Driver\Exception;
use Soluble\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;


interface DriverInterface
{
    
    
    /**
     * Get models definition according to options
     * 
     * @throws Exception\ModelFileNotFoundException
     * @throws Exception\ModelFileCorruptedException
     * @return array
     */
    public function getModelsDefinition();

    /**
     * Save or overwritte model definition file
     * 
     * @throws Exception\ModelFileNotWritableException
     * @param array $models_definition
     * @return DriverInterface
     */
    public function saveModelsDefinition(array $models_definition);
    
    /**
     * Return metadata reader
     * 
     * @return Source\AbstractSource
     */
    public function getMetadata();
    
    /**
     * Set metadata reader
     * 
     * @param Source\AbstractSource $metadata
     * @return DriverInterface
     */
    public function setMetadata(Source\AbstractSource $metadata);
    
    
            
    
}