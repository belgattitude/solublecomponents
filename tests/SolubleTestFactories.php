<?php
use Zend\Db\Adapter\Adapter;
use Zend\Cache\StorageFactory;
use Soluble\Normalist\Synthetic\TableManager;
use Soluble\Normalist\Driver;
use Symfony\Component\Process\Process;

class SolubleTestFactories
{
    /**
     * @var array
     */
    protected static $_adapter_instances = array();

    /**
     * @var array
     */
    protected static $_cache_instances = array();
    
    
    
    /**
     *
     * @return array
     */
    public static function getLibXLLicense()
    {
        return array(
                    'name' => $_SERVER['LIBXL_LICENSE_NAME'],
                    'key'  => $_SERVER['LIBXL_LICENSE_KEY']
            );
    }
    
    /**
     *
     * @param Adapter $adapter
     * @param Driver\DriverInterface
     * @return TableManager
     */
    public static function getTableManager(Adapter $adapter = null, Driver\DriverInterface $driver = null)
    {
        if ($adapter === null) {
            $adapter = self::getDbAdapter();
        }
        
        if ($driver === null) {
            $options = array(
                
            );
            $driver = new Driver\ZeroConfDriver($adapter, $options);
        }
        $tm = new TableManager($driver);
        return $tm;
    }

    /**
     *
     * @param array $mysql_config (driver,hostname,username,password,database)
     * @param string $driver force driver to be Pdo_Mysql, Mysqli
     * @return \Zend\Db\Adapter\Adapter
     */
    public static function getDbAdapter(array $mysql_config = null, $driver = null)
    {
        if ($mysql_config === null) {
            if ($mysql_config === null) {
                /**
                 * Those values must be defined in phpunit.xml configuration file
                 */
                $mysql_config = self::getDatabaseConfig();
            }
            if ($driver !== null) {
                $mysql_config['driver']   = $driver;
            } else {
                $mysql_config['driver']   = $_SERVER['MYSQL_DRIVER'];
            }
        }

        $key = md5(serialize($mysql_config));
        if (!array_key_exists($key, self::$_adapter_instances)) {
            self::$_adapter_instances[$key] = new Adapter($mysql_config);
        }
        return self::$_adapter_instances[$key];
    }

    public static function getDatabaseConfig()
    {
        $mysql_config = array();
        $mysql_config['hostname'] = $_SERVER['MYSQL_HOSTNAME'];
        $mysql_config['username'] = $_SERVER['MYSQL_USERNAME'];
        $mysql_config['password'] = $_SERVER['MYSQL_PASSWORD'];
        $mysql_config['database'] = $_SERVER['MYSQL_DATABASE'];
        $mysql_config['driver_options'] = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            );
        $mysql_config['options'] = array(
            'buffer_results' => true
        );
        $mysql_config['charset'] = 'UTF8';
        
        return $mysql_config;
    }
    
    /**
     * @return \Zend\Cache\StorageInterface
     */
    public static function getCacheStorage(array $storageFactoryOptions = null)
    {
        if ($storageFactoryOptions == null) {
            $cache_dir = self::getCachePath();

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
    
    /**
     * @return string
     */
    
    public static function getCachePath()
    {
        $cache_dir = $_SERVER['PHPUNIT_CACHE_DIR'];
        if (!preg_match('/^\//', $cache_dir)) {
            $cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cache_dir;
        }
        return $cache_dir;
    }
}
