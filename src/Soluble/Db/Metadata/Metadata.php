<?php

namespace Soluble\Db\Metadata;

use Zend\Db\Adapter\Adapter;
use Soluble\Schema\Source as SourceSchema;

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
     * @param Adapter $adapter
     * @param string $schema database schema to use or null to current schema defined by the adapter
     * @return SchemaSource\AbstractSource
     */
    protected function createSourceFromAdapter(Adapter $adapter, $schema = null)
    {
        $adapter_name = strtolower($adapter->getPlatform()->getName());
        switch ($adapter_name) {
            case 'mysql':
                $conn = $adapter->getDriver()->getConnection()->getResource();
                $source =  new SourceSchema\Mysql\MysqlInformationSchema($conn, $schema);
                break;
            default:
                throw new Exception\UnsupportedDriverException("Currently only MySQL is supported, driver set '$adapter_name'");
        }

        return $source;
    }

    /**
     * Return underlying database adapter
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }
}
