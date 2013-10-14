<?php

namespace Soluble\FlexStore\ResultSet;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Helper\Paginator;
use ArrayObject;

class ResultSet extends AbstractResultSet
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY  = 'array';

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
     * Return type to use when returning an object from the set
     *
     * @var ResultSet::TYPE_ARRAYOBJECT|ResultSet::TYPE_ARRAY
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;

	
	/**
	 *
	 * @var integer
	 */
	protected $totalRows;
	
	/**
	 * @var \Soluble\FlexStore\Source\AbstractSource
	 */
	protected $source;
	
    /**
     * Constructor
     *
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */
    public function __construct($returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
    {
        $this->returnType = (in_array($returnType, array(self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT))) ? $returnType : self::TYPE_ARRAYOBJECT;
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype(($arrayObjectPrototype) ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS));
        }
    }
	
	/**
	 * 
	 * @return \Soluble\FlexStore\Helper\Paginator
	 */
	function getPaginator()
	{
		if ($this->paginator === null) {
			$this->paginator = new Paginator($this->getTotalRows(), 
											 $this->getSource()->getOptions()->getLimit(), 
											 $this->getSource()->getOptions()->getOffset());
		}
		return $this->paginator;
	}
	
	

	/**
	 * 
	 * @param \Soluble\FlexStore\Source\AbstractSource $source
	 * @return \Soluble\FlexStore\ResultSet\ResultSet
	 */
	function setSource(AbstractSource $source)
	{
		$this->source = $source;
		return $this;
	}
	
	/**
	 * 
	 * @return \Soluble\FlexStore\Source\AbstractSource
	 */
	function getSource()
	{
		return $this->source;
	}
	
	
	/**
	 * Set the total rows 
	 * @param int $totalRows
	 * @return \Soluble\FlexStore\ResultSet\ResultSet
	 */
	function setTotalRows($totalRows)
	{
		$this->totalRows = (int) $totalRows;
		return $this;
	}
	
	
	/**
	 * @return int
	 */
	function getTotalRows()
	{
		return $this->totalRows;
	}
	
	
	
	

    /**
     * Set the row object prototype
     *
     * @param  ArrayObject $arrayObjectPrototype
     * @throws Exception\InvalidArgumentException
     * @return ResultSet
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        if (!is_object($arrayObjectPrototype)
            || (!$arrayObjectPrototype instanceof ArrayObject && !method_exists($arrayObjectPrototype, 'exchangeArray'))

        ) {
            throw new Exception\InvalidArgumentException('Object must be of type ArrayObject, or at least implement exchangeArray');
        }
        $this->arrayObjectPrototype = $arrayObjectPrototype;
        return $this;
    }

    /**
     * Get the row object prototype
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->arrayObjectPrototype;
    }

    /**
     * Get the return type to use when returning objects from the set
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @return array|\ArrayObject|null
     */
    public function current()
    {
        $data = parent::current();

        if ($this->returnType === self::TYPE_ARRAYOBJECT && is_array($data)) {
            /** @var $ao ArrayObject */
            $ao = clone $this->arrayObjectPrototype;
            if ($ao instanceof ArrayObject || method_exists($ao, 'exchangeArray')) {
                $ao->exchangeArray($data);
            }
            return $ao;
        }
        return $data;
    }
	
	
}
