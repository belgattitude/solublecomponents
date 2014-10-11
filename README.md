# sol·u·ble

[![Build Status](https://travis-ci.org/belgattitude/solublecomponents.png?branch=master)](https://travis-ci.org/belgattitude/solublecomponents)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/)
[![Total Downloads](https://poser.pugx.org/soluble/solublecomponents/downloads.png)](https://packagist.org/packages/soluble/solublecomponents)
[![Dependency Status](https://www.versioneye.com/user/projects/52cc2674ec137549700001f3/badge.png)](https://www.versioneye.com/user/projects/52cc2674ec137549700001f3)
[![Latest Stable Version](https://poser.pugx.org/soluble/solublecomponents/v/stable.png)](https://packagist.org/packages/soluble/solublecomponents)
[![License](https://poser.pugx.org/soluble/solublecomponents/license.png)](https://packagist.org/packages/soluble/solublecomponents)

`sol·u·ble` provide high-quality, well-tested, standards-compliant libraries that can be dissolved in any project. 

The idea behind `sol·u·ble` is to provide a set of independent libraries to speed up, modernize and enrich PHP projects development.

By independant, sol·u·ble components try to avoid being linked to a particular framework, meaning that you may use it in different code bases or frameworks. 
Every components can be installed separately through composer or download.
 
This repository contains all sol·u·ble components. 



## Components

| Component     | Description            | Status     |
| :------------ |:---------------------- | :---------:|
| `FlexStore`   | Versatile data provider                     | beta       |
| `Normalist`   | Normalize database access                       | alpha      |
| `Db`          | Core database library    | beta       |
| `Spreadsheet` | Core Expre library       | alpha      |
| `Japha`       | Japha                  | progress   |
| `Media`       | Japha                  | deprecated |

Soluble\Normalist
-----------------

Normalist has been designed to provide an alternative to standard ORM's by 
allowing models to be dynamically guessed from your database structure, which 
make them usable without previous definition. Its beautiful API is inspired by Doctrine, Laravel Eloquent and 
Zend Framework 2, offers simple and intuitive methods to play with your database.

Soluble\FlexStore
-----------------

Soluble\Imediate
----------------

Soluble\Japha

-------------

Soluble\Db
----------

Common database utilities used in various soluble components


## Installation

Soluble components can be installed via composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```sh
php composer.phar require soluble/solublecomponents:0.*
```

Alternatively you can install components individually.

```sh
php composer.phar require soluble/normalist:0.*
```

## Documentation

Documentation is hosted on [Read the docs](http://soluble.readthedocs.org)

## Coding standards

Please follow the following guides and code standards:

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)




