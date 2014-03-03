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

class Adodb
{
    /**
     * Return a Zend\Db\Adapter\Adapter connection from an existing ADODb connection
     *
     * @throws Exception\UnsupportedDriverException
     *
     * @param \ADOConnection $adoConnection
     * @return Adapter
     */
    public static function getAdapter(\ADOConnection $adoConnection)
    {
        $connectionId = $adoConnection->_connectionID;
        if (!$connectionId) {
            throw new Exception\AdoNotConnectedException(__METHOD__ . ". Error: Invalid usage, adodb connection must be connected before use (see connect(), pconnect()");
        }
        $driver_class = strtolower(get_class($connectionId));

        switch($driver_class) {
            case 'mysqli' :

                $adapter = Mysqli::getAdapter($connectionId);
                break;

            case 'pdo':
                $adapter = PDO::getAdapter($connectionId);
                break;
            //@codeCoverageIgnoreStart
            default:

                throw new Exception\UnsupportedDriverException("Driver '$driver_class' not supported");
            //@codeCoverageIgnoreEnd
        }
        return $adapter;
    }

}
