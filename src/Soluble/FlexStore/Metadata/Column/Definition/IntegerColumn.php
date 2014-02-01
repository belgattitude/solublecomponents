<?php
namespace Soluble\FlexStore\Metadata\Column\Definition;


class IntegerColumn extends AbstractColumn implements NumericColumnInterface
{
    /**
     *
     * @var bool
     */
    protected $numericUnsigned = null;

    /**
     *
     * @var bool
     */
    protected $isAutoIncrement;

    /**
     * @return bool
     */
    public function getNumericUnsigned()
    {
        return $this->numericUnsigned;
    }

    /**
     * @param  bool $numericUnsigned
     * @return \Soluble\FlexStore\Metadata\Column\IntegerColumn
     */
    public function setNumericUnsigned($numericUnsigned)
    {
        $this->numericUnsigned = $numericUnsigned;
        return $this;
    }


    /**
     * @return bool
     */
    public function isNumericUnsigned()
    {
        return $this->numericUnsigned;
    }

    /**
     * @return bool $isAutoIncrement
     */
    public function getIsAutoIncrement()
    {
        return $this->isAutoIncrement;
    }

    /**
     * @param bool $isAutoIncrement to set
     * @return \Soluble\FlexStore\Metadata\Column\AbstractColumn
     */
    public function setIsAutoIncrement($isAutoIncrement)
    {
        $this->isAutoIncrement = $isAutoIncrement;
        return $this;
    }

    /**
     * @return bool $isAutoIncrement
     */
    public function isAutoIncrement()
    {
        return $this->isAutoIncrement;
    }


}
