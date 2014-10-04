<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\Type;

class TimeType extends AbstractType
{

    public function getName()
    {
        return Type::TYPE_TIME;
    }
}
