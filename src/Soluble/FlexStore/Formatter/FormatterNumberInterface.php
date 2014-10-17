<?php

namespace Soluble\FlexStore\Formatter;

interface FormatterNumberInterface
{
    
    /**
     * Set decimals
     *
     * @param int $decimals
     */
    public function setDecimals($decimals);
    
    /**
     *
     * @return int
     */
    public function getDecimals();
}
