<?php
namespace Soluble\FlexStore\Metadata\Column\Definition;

interface NumericColumnInterface
{
    /**
     * @return bool
     */
    public function getNumericUnsigned();

    /**
     * @param  bool $numericUnsigned
     * @return ColumnObject
     */
    public function setNumericUnsigned($numericUnsigned);


    /**
     * @return bool
     */
    public function isNumericUnsigned();

}
