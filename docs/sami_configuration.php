<?php

require __DIR__ . '/../vendor/autoload.php';

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in('../src')
;

return new Sami($iterator, array(
    'title' => 'Soluble API',
    'theme' => 'enhanced',
    'build_dir' => __DIR__.'/sphinx/source/_static/API/SAMI',
    'cache_dir' => __DIR__.'/_build/cache',
    'default_opened_level' => 2,
));