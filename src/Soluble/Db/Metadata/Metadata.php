<?php

namespace Soluble\Db\Metadata;

use Zend\Db\Adapter\Adapter;

class Metadata
{
    /**
     * Adapter
     *
     * @var Adapter
     */
    protected $adapter = null;

    /**
     * @var \Soluble\Db\Metadata\Source\AbstractSource
     */
    protected $source = null;

    /**
     * Constructor
     * @throws Exception\UnsupportedDriverException     
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->source = $this->createSourceFromAdapter($adapter);
    }


    /**
     *
     * @return \Soluble\Db\Metadata\Source\AbstractSource
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Automatically create source from adapter
     * 
     * @throws Exception\UnsupportedDriverException
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return \Soluble\Db\Metadata\Source\MysqlISMetadata
     */
    protected function createSourceFromAdapter(Adapter $adapter)
    {
        $adapter_name = strtolower($adapter->getPlatform()->getName());
        switch ($adapter_name) {
            case 'mysql':
                $source =  new Source\MysqlISMetadata($adapter);
                break;
            default:
                throw new Exception\UnsupportedDriverException("Currently only MySQL is supported, driver set '$adapter_name'");
        }

        return $source;
        
    }

}
