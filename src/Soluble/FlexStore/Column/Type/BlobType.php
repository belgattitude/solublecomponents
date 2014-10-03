<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\Type;

class BlobType extends AbstractType
{

    public function getName()
    {
        return Type::TYPE_BLOB;
    }

}
