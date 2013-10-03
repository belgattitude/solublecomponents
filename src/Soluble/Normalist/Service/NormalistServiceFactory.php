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
	 * @return \Soluble\Normalist\Table
	 */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['tablemanager']) ? $config['tablemanager'] : array();
		if (empty($config)) {
			throw new \Exception("Cannot locate table manager configuration, please review your configuration.");
		}
		
		$adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		
		$tableManager = new Table($adapter);
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