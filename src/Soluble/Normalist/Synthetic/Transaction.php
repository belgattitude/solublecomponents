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
    const STATE_NULL = 'null';
    const STATE_STARTED = 'started';
    const STATE_ROLLBACKED = 'rollbacked';
    const STATE_COMMITTED = 'committed';
    const STATE_ERRORED = 'errored';
    
    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    protected $adapter;
    
    
    /**
     *
     * @var string state of current transaction
     */
    protected $state;


    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->state = self::STATE_NULL;
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
            if ($this->state == self::STATE_STARTED) {
                throw new Exception\TransactionException(__METHOD__ . " Starting transaction on an already started one is not permitted.");
            }
            $this->adapter->getDriver()->getConnection()->beginTransaction();
            $this->state = self::STATE_STARTED;
        } catch (\Exception $e) {
            throw new Exception\TransactionException(__METHOD__ . ". Cannot start transaction '{$e->getMessage()}'.");
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
            $this->state = self::STATE_COMMITTED;
        } catch (\Exception $e) {
            $this->state = self::STATE_ERRORED;
            throw new Exception\TransactionException(__CLASS__ . '::' . __METHOD__ . ". cannot commit transaction '{$e->getMessage()}'.");
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
            $this->state = self::STATE_ROLLBACKED;
        } catch (\Exception $e) {
            $this->state = self::STATE_ERRORED;
            throw new Exception\TransactionException(__CLASS__ . '::' . __METHOD__ . ". cannot rollback transaction '{$e->getMessage()}'.");
        }

        return $this;
    }
}
