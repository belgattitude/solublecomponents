# sol·u·ble

[![Build Status](https://travis-ci.org/belgattitude/solublecomponents.png?branch=master)](https://travis-ci.org/belgattitude/solublecomponents)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/badges/quality-score.png?s=6f3ab91f916bf642f248e82c29857f94cb50bb33)](https://scrutinizer-ci.com/g/belgattitude/solublecomponents/)
[![Dependency Status](https://www.versioneye.com/user/projects/52cc2674ec137549700001f3/badge.png)](https://www.versioneye.com/user/projects/52cc2674ec137549700001f3)
[![Latest Stable Version](https://poser.pugx.org/soluble/solublecomponents/v/stable.png)](https://packagist.org/packages/soluble/solublecomponents)
[![License](https://poser.pugx.org/soluble/solublecomponents/license.png)](https://packagist.org/packages/soluble/solublecomponents)

**Sol·u·ble** provide high-quality, well-tested, standards-compliant libraries that *dissolve smoothly* in any PHP 5.3+ project. 

This is the main repository for soluble components. Please refer to indivual components for more information 
 
## Components

| Component     | Description            | Packagist  | Quality  | 
| :------------ |:---------------------- | :--------| :-------:|
| `FlexStore`   | Versatile data provider, SQL query to JSON/XML/Excel/Datatables.......          | [![Latest Stable Version](https://poser.pugx.org/soluble/flexstore/v/stable.svg)](https://packagist.org/packages/soluble/flexstore)    | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-flexstore/?branch=master) |
| `Normalist`   | Normalize database access across projects                   |  [![Latest Stable Version](https://poser.pugx.org/soluble/normalist/v/stable.svg)](https://packagist.org/packages/soluble/normalist)   |
| `Db`          | Database access core libraries and metadata schema readers.  | [![Latest Stable Version](https://poser.pugx.org/soluble/db/v/stable.svg)](https://packagist.org/packages/soluble/db)       | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-db/?branch=master) |
| `Spreadsheet` | Core Excel library       | [![Latest Stable Version](https://poser.pugx.org/soluble/spreadsheet/v/stable.svg)](https://packagist.org/packages/soluble/spreadsheet)      |
| `Japha`       | Japha                  | [![Latest Stable Version](https://poser.pugx.org/soluble/japha/v/stable.svg)](https://packagist.org/packages/soluble/japha)   | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/soluble-japha/?branch=master) |


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




[![Total Downloads](https://poser.pugx.org/soluble/solublecomponents/downloads.png)](https://packagist.org/packages/soluble/solublecomponents)