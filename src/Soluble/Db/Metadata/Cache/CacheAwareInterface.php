<?php
namespace Soluble\Db\Metadata\Cache;

use Zend\Cache\Storage\StorageInterface;

interface CacheAwareInterface
{
	/**
	 * 
	 * @param \Zend\Cache\Storage\StorageInterface $cache
	 */
	public function setCache(StorageInterface $cache);
}

