<?php

namespace Soluble\FlexStore\ResultSet;
use Soluble\FlexStore\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\ResultSet as ZFResultSet;

use Iterator;


abstract class AbstractResultSet implements Iterator, ResultSetInterface
{

    /**
     *
     * @var ZFResultSet
     */
    protected $zfResultSet;

    public function __construct(ZFResultSet $resultSet)
    {
        $this->zfResultSet = $resultSet;
    }


    /**
     *
     * @return AbstractResultSet
     */
    public function buffer()
    {
        $this->zfResultSet->buffer();
        return $this;
    }


    /**
     *
     * @return boolean
     */
    public function isBuffered()
    {
        return $this->zfResultSet->isBuffered();
    }

    /**
     * Get the data source used to create the result set
     *
     * @return null|Iterator
     */
    public function getDataSource()
    {
        return $this->zfResultSet->getDataSource();
    }

    /**
     * Retrieve count of fields in individual rows of the result set
     *
     * @return int
     */
    public function getFieldCount()
    {
        return $this->zfResultSet->getFieldCount();
    }

    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    public function next()
    {
        $this->zfResultSet->next();
    }

    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->zfResultSet->key();
    }

    /**
     * Iterator: get current item
     *
     * @return array
     */
    public function current()
    {

        return $this->zfResultSet->current();
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->zfResultSet->valid();
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->zfResultSet->rewind();
    }

    /**
     * Countable: return count of rows
     *
     * @return int
     */
    public function count()
    {
        return $this->zfResultSet->count();
    }

    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        return $this->zfResultSet->toArray();
    }




}
