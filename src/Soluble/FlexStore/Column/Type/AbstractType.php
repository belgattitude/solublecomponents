<?php

namespace Soluble\FlexStore\Column\Type;

abstract class AbstractType
{
    /**
     * @return string
     */
    abstract public function getName();

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
