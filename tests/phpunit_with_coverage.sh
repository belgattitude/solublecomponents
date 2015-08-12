#!/bin/sh
#php -c ./config/php-xdebug.ini /usr/local/bin/phpunit
#php -c ./config/php-xdebug.ini ../vendor/bin/phpunit
php -d zend_extension=xdebug.so ../vendor/bin/phpunit
