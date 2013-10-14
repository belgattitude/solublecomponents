<?php

namespace Soluble\FlexStore\ResultSet;

use Countable;
use Traversable;

interface ResultSetInterface extends Traversable, Countable
{

    /**
     * @abstract
     * @return mixed
     */
    public function getFieldCount();
}
