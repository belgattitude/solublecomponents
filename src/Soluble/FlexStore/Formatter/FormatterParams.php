<?php

namespace Soluble\FlexStore\Formatter;

class FormatterParams
{
    
    function __construct(array $properties)
    {
        $this->properties = $properties;
    }
    
    function toArray()
    {
        return $this->properties;
    }
}
