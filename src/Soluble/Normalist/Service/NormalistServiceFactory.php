<?php

namespace Soluble\Normalist\Service;

use Soluble\Normalist\Table;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class NormalistTableManagerFactory implements FactoryInterface
{
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \Soluble\Normalist\SyntheticTable
	 */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['Soluble\Normalist']) ? $config['Soluble\Normalist'] : array();
		if (empty($config)) {
			throw new \Exception("Cannot locate Soluble\Normalist configuration, please review your configuration.");
		}
		
		$adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		
		$tableManager = new SyntheticTable($adapter);
		$metadata = $tableManager->getMetadata();

		$mdconfig = $config['metadata_cache'];
		if ($metadata instanceof CacheAwareInterface && is_array($mdconfig)) { 
				$cache  = StorageFactory::adapterFactory($mdconfig['adapter']);
				$cache->setOptions((array) $mdconfig['options']);
				if (is_array($mdconfig['plugins'])) {
					foreach($mdconfig['plugins'] as $name => $options) {
						$plugin = StorageFactory::pluginFactory($name, $options);
						$cache->addPlugin($plugin);				
					}
				}
				$metadata->setCache($cache);  
		}
		return $tableManager;
    }
}