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
    
    /**
     * Return the list of columns in order to run the renderer
     * @return array
     */
    function getRequiredColumns();
    
};
