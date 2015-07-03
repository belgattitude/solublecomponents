<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\ColumnType;

class IntegerType extends AbstractType
{
    public function getName()
    {
        return ColumnType::TYPE_INTEGER;
    }
}
