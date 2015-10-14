#!/bin/sh
#php -c ./config/php-xdebug.ini ../vendor/bin/phpunit
php -d zend_extension=xdebug.so /usr/local/bin/phpunit
