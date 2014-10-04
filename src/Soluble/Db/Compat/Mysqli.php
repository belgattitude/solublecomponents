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

class Mysqli
{
    /**
     * Return a Zend\Db\Adapter\Adapter object from an existing mysqli connection
     * @param \Mysqli $mysqli
     * @return Adapter
     */
    public static function getAdapter(\Mysqli $mysqli)
    {
        $driver = new Driver\Mysqli\Mysqli($mysqli);
        $adapter = new Adapter($driver);
        return $adapter;
    }
}
