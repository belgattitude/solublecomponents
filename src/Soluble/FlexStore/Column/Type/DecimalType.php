<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\Type;

class DecimalType extends AbstractType
{

    public function getName()
    {
        return Type::TYPE_DECIMAL;
    }
}
