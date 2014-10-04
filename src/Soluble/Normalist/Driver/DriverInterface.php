<?php

namespace Soluble\Normalist\Driver;

use Soluble\Db\Driver\Exception;
use Soluble\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;

interface DriverInterface
{


    /**
     * @param Adapter $adapter
     * @param array|Traversable $params [alias,path,version]
     */
    public function __construct(Adapter $adapter, $params = array());


    /**
     * Get models definition according to options
     *
     * @throws Exception\ModelFileNotFoundException
     * @throws Exception\ModelFileCorruptedException
     * @return array
     */
    public function getModelsDefinition();


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

    /**
     * Get underlying database adapter
     *
     * @return Adapter
     */
    public function getDbAdapter();
}
