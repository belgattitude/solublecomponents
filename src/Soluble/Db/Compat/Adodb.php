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
     * @throws Exception\AdoNotConnectedException when connection is not initialized
     *
     * @param \ADOConnection $adoConnection
     * @return Adapter
     */
    public static function getAdapter(\ADOConnection $adoConnection)
    {
        $adoConnectionDriver = strtolower(get_class($adoConnection));
        switch ($adoConnectionDriver) {
            //case 'adodb_mysqlt':
            case 'adodb_mysqli':
                $connectionId = self::getADOConnectionId($adoConnection);
                $adapter = Mysqli::getAdapter($connectionId);
                break;
            case 'adodb_pdo':
            case 'adodb_pdo_mysql':
                $connectionId = self::getADOConnectionId($adoConnection);
                $adapter = PDO::getAdapter($connectionId);
                break;
            default:
                throw new Exception\UnsupportedDriverException(__METHOD__ . ". Driver '$adoConnectionDriver' not supported");
        }
        return $adapter;
    }

    /**
     * Return internal adodb internal connection id
     * @throws Exception\AdoNotConnectedException when connection is not initialized
     * @param \ADOConnection $adoConnection
     * @return \MySQLI|\PDO
     */
    protected static function getADOConnectionId(\ADOConnection $adoConnection)
    {
        $connectionId = $adoConnection->_connectionID;
        if (!$connectionId) {
            throw new Exception\AdoNotConnectedException(__METHOD__ . ". Error: Invalid usage, adodb connection must be connected before use (see connect(), pconnect()");
        }
        return $connectionId;
    }
}
