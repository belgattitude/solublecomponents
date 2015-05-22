---
title: Configuration strategies
taxonomy:
    category: docs, integration
---

>>>>> Various strategies can be adopted to set up and maintain an unique TableManager instance. 

  


In order to work with synthetic tables and records, normalist requires to setup the [TableManager](../../components/table-manager) object.  
t has been designed Normalist does not propose intentionally any particular strategy to ensure only one instanciation of the TableManager.    

| implementation  | type  | description  |
|---|---|---|
| require_once | | df |
| container |   | df |
| singleton |   | df |

| global functions |   |
## Some implementations ideas

### Configuration with file inclusion

```php
<?php
/**
 * Configuration file
 * ------------------
 * ./configs/normalist.config.php
 */

return [
        'adapter' => [
            'driver'    => 'Mysqli',        // or Pdo_Mysql
            'hostname'  => 'localhost',     // 'localhost' by default
            'username'  => 'db_user',
            'password'  => 'db_password',
            'database'  => 'db_name'
        ],
        'driverOptions' => [
            'path' => '/my/project/model/path
        ]
    ];
    
```

Later on

```php

```

### Dependency injection with Pimple

[Pimple](http://pimple.sensiolabs.org/) is a simple and popular dependency injection container.

Without going into detail of various initialization techniques (file inclusions, bootstrap in index.php...), the following code snippets illustrate the main logic   


```php
<?php 
/**
 * TableManager initialization
 * ---------------------------
 * This file must be included once at the beginning of your bootstrap
 * sequence. 
 */
            
use Pimple\Container;
use Zend\Db\Adapter\Adapter;
use Soluble\Normalist\Driver;
use Soluble\Normalist\Synthetic\TableManager;
    
$container = new Container();

$container['normalist_config'] = function(Container $c) {
    $config = [
        'adapter' => [
            'driver'    => 'Mysqli',        // or Pdo_Mysql
            'hostname'  => 'localhost',     // 'localhost' by default
            'username'  => 'db_user',
            'password'  => 'db_password',
            'database'  => 'db_name'
        ],
        'driverOptions' => [
            'path' => '/my/project/model/path'
        ]
    ];
    // alternatively you can include a config file
    // > $adapterConfig = include ./configs/normalist.config.php;
    return $config;
};

$container['adapter'] = function(Container $c) {
    $adapterConfig = $c['normalist_config']['adapter'];
    return new Adapter($adapterConfig);
};

$container['table_manager'] = function(Container $c) {
    $driverOptions = $c['normalist_config']['driverOptions'];    
    $driver = new Driver\ZeroConfDriver($c['adapter'], $driverOptions);
    return new TableManager($driver);
};

``` 

Later on, you can retrieve the TableManager or Adapter instances across your project

```php
<?php
// eventual requires...    
                 
use Pimple\Container;
    
$container = new Container();

$tm = $container['table_manager'];
$record = $tm->get('user')->find(1);

$adapter = $container['adapter'];
///...

```


