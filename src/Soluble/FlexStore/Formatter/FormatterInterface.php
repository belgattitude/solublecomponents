<?php

namespace Soluble\FlexStore\Formatter;

use ArrayObject;

interface FormatterInterface
{

    /**
     * 
     * @param mixed $value
     * @param ArrayObject $row
     */
    public function format($value, ArrayObject $row);
}
