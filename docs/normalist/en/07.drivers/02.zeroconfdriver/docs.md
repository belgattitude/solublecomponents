---
title: ZeroConfDriver
taxonomy:
    category: docs
---

>>>>> The ZeroConfDriver is able to discover your models by reading of Information Schema internal database of MySQL/MariaDB  

### Usage

 
```php
<?php
use Soluble\Normalist\Driver\ZeroConfDriver;
    
//...    
$driverOptions = [
    'path' => '/path/to/generated/models/dir/', // default sys_get_temp_dir()
    'alias' => 'connection_identifier', // default to 'default'
    'version' => '0.4', // default to 'latest'
    'permissions' => 0666 
];

$driver = new ZeroConfDriver($adapter, $driverOptions);
```

### Parameters

The `$adapter` parameter must a be a valid `Zend\Db\Adapter\Adapter`, for extended documentation see the [Zend\Db\Adapter\Adapter](http://framework.zend.com/manual/current/en/modules/zend.db.adapter.html) reference guide.

The `$driverOptions` parameter is an optional array, its values can be

| key  | comment  | default  |
|---|---|---|
| path  | Path to the generated schema configuration cache file  | `sys_get_temp_dir()`  |
| alias | Connection alias in case of multiple database connections  | `"default"`  |
| version | Schema version to include in cache file name  | `"latest"`  |
| permissions | Cache file creation mask  | `0666`  |
  
>>>>>> The generated schema cache filename follow this rule **`<path>/normalist_zeroconf_cache_<alias>_<version>.php`** 

### Performance

For performance reason, the reading of information schema tables will only happen if no valid cache file is present. Otherwise the cache file will be taken.

  

### Limitations

Currently the ZeroConfDriver is limited to MySQL/MariaDB 5.1+ RDBMS




 
