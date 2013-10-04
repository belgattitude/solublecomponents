<?php

namespace Soluble\Normalist\Service;

use Soluble\Normalist\SyntheticTable;
use Soluble\Db\Metadata\Cache\CacheAwareInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class SyntheticTableFactory implements FactoryInterface
{
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \Soluble\Normalist\SyntheticTable
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
		$adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		$syntheticTable = new SyntheticTable($adapter);
		if ($serviceLocator->has('Cache\SolubleDbMetadata')) {
			$md = $syntheticTable->getMetadata();
			if ($md instanceof CacheAwareInterface) {
				$md->setCache($serviceLocator->get('Cache\SolubleDbMetadata'));
			}
		}		
		return $syntheticTable;
    }
}