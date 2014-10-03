<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\Type;

class BitType extends AbstractType
{

    public function getName()
    {
        return Type::TYPE_BIT;
    }

}
