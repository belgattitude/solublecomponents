<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\Type;

class DatetimeType extends AbstractType
{

    public function getName()
    {
        return Type::TYPE_DATETIME;
    }

}
