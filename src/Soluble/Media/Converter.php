<?php
namespace Soluble\Media;

use Zend\Db\Adapter\Adapter;
use Zend\Cache\Storage\StorageInterface;

class Converter
{
    /**
     *
     * @var boolean
     */
    protected $cacheEnabled = false;

    /**
     *
     * @var Zend\Cache\Storage\StorageInterface
     */
    protected $cacheStorage;

    public function __construct()
    {
    }

    /**
     * @return Converter\ConverterInterface
     */
    public function createConverter($key, array $params = array())
    {
        switch(strtolower($key)) {
            case 'image':
                $converter = new Converter\ImageConverter($params);
                break;

            default:
                throw new \Exception("Only image converter is supported");
        }
        if ($this->cacheStorage !== null) {
            $converter->setCache($this->cacheStorage);
        }
        return $converter;
    }

    /**
     *
     * @param StorageInterface $storage
     * @return \Soluble\Media\Converter\ImageConverter
     */
    public function setCache(StorageInterface $storage)
    {
        $this->cacheStorage = $storage;
        $this->cacheEnabled = true;
        return $this;
    }

    /**
     * Unset cache (primarly for unit testing)
     * @return \Soluble\Media\Converter\ImageConverter
     */
    public function unsetCache()
    {
        $this->cacheEnabled = false;
        $this->cacheStorage = null;
        return $this;
    }
}
