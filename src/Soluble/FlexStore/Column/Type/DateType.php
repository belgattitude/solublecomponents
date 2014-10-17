<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\ColumnType;

class DateType extends AbstractType
{

    public function getName()
    {
        return ColumnType::TYPE_DATE;
    }
}
