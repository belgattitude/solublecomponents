<?php

namespace Soluble\FlexStore\Formatter;

use ArrayObject;

interface FormatterInterface
{

    /**
     * Format 
     * 
     * @param mixed $value
     * @param ArrayObject $row
     * @return void
     */
    public function format($value, ArrayObject $row);
    
    /**
     * Return formatted value
     * 
     * @param type $value
     * @param ArrayObject $row
     * @return mixed
     */
    //public function getFormatted($value, ArrayObject $row);
}
