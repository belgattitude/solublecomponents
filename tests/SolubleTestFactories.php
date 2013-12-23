<?php
use Zend\Db\Adapter\Adapter;
use Zend\Cache\StorageFactory;

class SolubleTestFactories {

	/**
	 * @var array
	 */
	static protected $_adapter_instances = array();

	/**
	 * @var array
	 */
	static protected $_cache_instances = array();
	
	
	/**
	 * 
	 * @param array $mysql_config (driver,hostname,username,password,database)
	 * @return \Zend\Db\Adapter\Adapter
	 */
	static function getDbAdapter(array $mysql_config=null) {
		
		if ($mysql_config === null) {
			/**
			 * Those values must be defined in phpunit.xml configuration file
			 */
			$mysql_config = array();
			$mysql_config['driver']   = $_SERVER['MYSQL_DRIVER'];
			$mysql_config['hostname'] = $_SERVER['MYSQL_HOSTNAME'];
			$mysql_config['username'] = $_SERVER['MYSQL_USERNAME'];
			$mysql_config['password'] = $_SERVER['MYSQL_PASSWORD'];
			$mysql_config['database'] = $_SERVER['MYSQL_DATABASE'];
			$mysql_config['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");
			$mysql_config['charset'] = 'UTF8';

		}
		
		$key = md5(serialize($mysql_config));
		if (!array_key_exists($key, self::$_adapter_instances)) {
			self::$_adapter_instances[$key] = new Adapter($mysql_config);
		}
		return self::$_adapter_instances[$key];
		
	}
	
	/**
	 * @return \Zend\Cache\StorageInterface
	 */
	static function getCacheStorage(array $storageFactoryOptions=null) {
			if ($storageFactoryOptions == null) {
				$cache_dir = $_SERVER['PHPUNIT_CACHE_DIR'];
				if (!preg_match('/^\//', $cache_dir)) {
					$cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cache_dir;
				}
				
				$cache_config = array();
				$cache_config['adapter'] = 'filesystem';
				$cache_config['options'] = array(
						'cache_dir' => $cache_dir,
						'ttl' => 0,
						'dir_level' => 1,
						'dir_permission' => 0777,
						'file_permission' => 0666
						
					);
				$cache_config['plugins'] = array(
					'exception_handler' => array('throw_exceptions' => true)
				);
			}
			$key = md5(serialize($cache_config));
			if (!array_key_exists($key, self::$_cache_instances)) {
				self::$_cache_instances[$key] = StorageFactory::factory($cache_config);
			}
			return self::$_cache_instances[$key];
		
	} 
	
}
