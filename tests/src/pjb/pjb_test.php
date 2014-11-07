<?php

define("JAVA_HOSTS", "127.0.0.1:8083");
define("JAVA_DISABLE_AUTOLOAD", false);
define('JAVA_PREFER_VALUES', true);

require_once(dirname(__FILE__) . '/Java.inc');


$params = new Java("java.util.HashMap");
var_dump($params->__signature);

