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
    public function apply(ArrayObject $row);
    
    /**
     * Return the list of columns in order to run the renderer
     * @return array
     */
    public function getRequiredColumns();
};
