<?php
/**
 *  Soluble Components (http://belgattitude.github.io/solublecomponents)
 *
 *  @link      http://github.com/belgattitude/solublecomponents for the canonical source repository
 *  @copyright Copyright (c) 2013-2014 Sébastien Vanvelthem
 *  @license   https://github.com/belgattitude/solublecomponents/blob/master/LICENSE.txt MIT License
 */

namespace Soluble\Normalist\Synthetic;

use Zend\Db\Adapter\Adapter;

class Transaction
{
    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    protected $adapter;


    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Start a new transaction
     *
     * @throws Exception\TransactionException
     * @return Transaction
     */
    public function start()
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();
        } catch (\Exception $e) {
            throw new Exception\TransactionException(__CLASS__ . '::' . __METHOD_ . ". cannot start transaction '{$e->getMessage()}'.");
        }
        return $this;
    }

    /**
     * Commit changes
     *
     * @throws Exception\TransactionException
     * @return Transaction
     */
    public function commit()
    {
        try {
            $this->adapter->getDriver()->getConnection()->commit();
        } catch (\Exception $e) {
            throw new Exception\TransactionException(__CLASS__ . '::' . __METHOD_ . ". cannot commit transaction '{$e->getMessage()}'.");
        }

        return $this;
    }

    /**
     * Rollback transaction
     *
     * @throws Exception\TransactionException
     * @return Transaction
     */
    public function rollback()
    {
        try {
            $this->adapter->getDriver()->getConnection()->rollback();
        } catch (\Exception $e) {
            throw new Exception\TransactionException(__CLASS__ . '::' . __METHOD_ . ". cannot rollback transaction '{$e->getMessage()}'.");
        }

        return $this;
    }



}
