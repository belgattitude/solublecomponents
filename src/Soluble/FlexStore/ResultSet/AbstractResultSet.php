<?php

namespace Soluble\FlexStore\ResultSet;

use Soluble\FlexStore\ResultSet\ResultSetInterface;
use Zend\Db\ResultSet\ResultSet as ZFResultSet;

use Iterator;
use ArrayObject;

abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY  = 'array';
    
    /**
     * Return type to use when returning an object from the set
     *
     * @var ZFResultSet::TYPE_ARRAYOBJECT|ZFResultSet::TYPE_ARRAY
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;

    /**
     * Allowed return types
     *
     * @var array
     */
    protected $allowedReturnTypes = array(
        self::TYPE_ARRAYOBJECT,
        self::TYPE_ARRAY,
    );
    
 

    /**
     * @var ArrayObject
     */
    protected $arrayObjectPrototype = null;


    /**
     *
     * @var ZFResultSet
     */
    protected $zfResultSet;

    
    /**
     * Constructor
     *
     * @param ZFResultSet      $resultSet
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */
    public function __construct(ZFResultSet $resultSet, $returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
    {
        $this->zfResultSet = $resultSet;
        $this->returnType = (in_array($returnType, array(self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT))) ? $returnType : self::TYPE_ARRAYOBJECT;
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype(($arrayObjectPrototype) ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS));
        }
    }

    /**
     * Set the row object prototype
     *
     * @param  ArrayObject $arrayObjectPrototype
     * @throws Exception\InvalidArgumentException
     * @return AbstractResultSet
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        $this->zfResultSet->setArrayObjectPrototype($arrayObjectPrototype);
        return $this;
    }

    /**
     * Get the row object prototype
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->zfResultSet->getArrayObjectPrototype();
    }

    /**
     * Get the return type to use when returning objects from the set
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->zfResultSet->getReturnType();
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
