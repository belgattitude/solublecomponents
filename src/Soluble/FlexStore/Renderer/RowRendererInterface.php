<?php

namespace Soluble\FlexStore\Renderer;
use ArrayObject;

interface RowRendererInterface
{
    
    /**
     * Modify row
     * 
     * @param ArrayObject
     * @return void
     */
    function apply(ArrayObject $row);
    
};
