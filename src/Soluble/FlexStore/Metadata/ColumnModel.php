<?php

namespace Soluble\FlexStore\Metadata;

use ArrayObject;

class ColumnModel
{

    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    public function __construct(ArrayObject $columns)
    {
        $this->columns = $columns;
    }


    /**
     * @return ArrayObject
     */
    public function getColumns()
    {
        return $this->columns;
    }

}
