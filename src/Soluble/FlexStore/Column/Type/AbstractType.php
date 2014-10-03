<?php

namespace Soluble\FlexStore\Column\Type;

//use Soluble\FlexStore\Column\Type;

abstract class AbstractType
{

    /**
     *
     * @var string
     */
    protected $name = null;

    /**
     * @return string
     */
    abstract function getName();

    /**
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

}
