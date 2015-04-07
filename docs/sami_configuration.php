<?php



require __DIR__ . '/../vendor/autoload.php';

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in('../src')
;

error_reporting(E_ALL & ~E_NOTICE);

return new Sami($iterator, array(
    'title' => 'Soluble API',
//    'theme' => 'enhanced',
//    'theme' => 'symfony',
    'build_dir' => __DIR__.'/sphinx/source/_static/API/SAMI',
    'cache_dir' => __DIR__.'/_build/cache',
    'default_opened_level' => 2,
    //'template_dirs'        => array(__DIR__.'/themes/symfony'),
));