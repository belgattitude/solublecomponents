---
title: Setting up TableManager
taxonomy:
    category: docs
---

>>>>> `Synthetic\TableManager` holds the database configuration and acts as the central object to retrieve and work with synthetic tables. 
 
### Usage example

The following example illustrate how to setup the TableManager object with a [singleton](http://en.wikipedia.org/wiki/Singleton_pattern) implementation.

```php
<?php
use Zend\Db\Adapter\Adapter;    
use Soluble\Normalist\ZeroConfDriver;
use Soluble\Normalist\Synthetic\TableManager;

class MyExampleSingletonConfig {

    protected static $tmInstance;

    /**
     * @return TableManager
     */
    public static function getTableManager() {
    
        if (self::$tmInstance === null) {
            // 1. Setting up adapter
            $adapterConfig = [
                'driver'    => 'Mysqli',        // or Pdo_Mysql
                'hostname'  => 'localhost',     // 'localhost' by default
                'username'  => 'db_user',
                'password'  => 'db_password',
                'database'  => 'db_name'
            ];
            
            $adapter = new Adapter($adapterConfig);
            
            // 2. Create a driver
            $driver = new ZeroConfDriver($adapter);
            
            // 3. Instanciate the table manager
            self::$tmInstance = new TableManager($driver);
        } 
        return self::$tmInstance;
    }
}

```

You can now retrieve the TableManager from the MyExampleSingletonConfig object.

```php
<?php
require_once ./MyExampleSingletonConfig.php;    
    
$tm = MyExampleSingletonConfig::getTableManager();
$tm->getTable('user')->find(1);   
    
```

### Parameter

The TableManager requires the `$driver` parameter.

| name  | type  | description  |
|---|---|---|
| $driver  | `Soluble\Normalist\Driver\DriverInterface` | The database schema driver, the current bundled implementation is the [ZeroConfDriver](../../drivers/zeroconfdriver) providing autodiscovery of models for Mysql/MariaDb.  | 

>>>>> The bundled [ZeroConfDriver](../../drivers/zeroconfdriver) requires an `$adapter` ([Zend\Db\Adapter\Adapter](http://framework.zend.com/manual/current/en/modules/zend.db.adapter.html)) and a recommended `$driverOptions` parameters. See its [documentation](../drivers/zeroconfdriver)  


### Implementations

>>>> The TableManager object must be instanciated only once (at least once per different database connections if many). 

 Normalist does not enforce a particular strategy to maintain uniqueness of the TableManager instance. 
 The example above use intentionnaly the [singleton](http://en.wikipedia.org/wiki/Singleton_pattern) as it may be more readable. Please also consider the [following strategies](../../integration/configuration-strategies). 
    


