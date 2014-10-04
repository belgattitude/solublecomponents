<?php
/**
 *  Soluble Components (http://belgattitude.github.io/solublecomponents)
 *
 *  @link      http://github.com/belgattitude/solublecomponents for the canonical source repository
 *  @copyright Copyright (c) 2013-2014 Sébastien Vanvelthem
 *  @license   https://github.com/belgattitude/solublecomponents/blob/master/LICENSE.txt MIT License
 */
namespace Soluble\Db\Compat;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver;

class PDO
{
    /**
     * Return a Zend\Db\Adapter\Adapter object from an existing pdo connection
     * @param \PDO $pdo
     * @return Adapter
     */
    public static function getAdapter(\PDO $pdo)
    {
        $driver = new Driver\Pdo\Pdo($pdo);
        $adapter = new Adapter($driver);
        return $adapter;
    }
}
