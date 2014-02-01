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
     *
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
     * Create source from adapter
     *
     * @param  Adapter $adapter
     * @return \Soluble\Db\Metadata\Source\AbstractSource
     */
    protected function createSourceFromAdapter(Adapter $adapter)
    {
        switch ($adapter->getPlatform()->getName()) {
            case 'MySQL':
                return new Source\MysqlISMetadata($adapter);
        }

        throw new \Exception('cannot create source from adapter');
    }

}
