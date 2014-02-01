<?php

namespace Soluble\Media\Converter\Service;

use Soluble\Media\Converter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class MediaConverterFactory implements FactoryInterface
{
    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Soluble\Media\Converter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        /*
        $config = $serviceLocator->get('Config');
        $config = isset($config['Soluble\Normalist']) ? $config['Soluble\Normalist'] : array();
        if (empty($config)) {
            throw new \Exception("Cannot locate Soluble\Normalist configuration, please review your configuration.");
        }
        */

        $converter = new Converter();
        if ($serviceLocator->has('Cache\SolubleMediaConverter')) {
            $converter->setCache($serviceLocator->get('Cache\SolubleMediaConverter'));
        }
        return $converter;
    }
}
