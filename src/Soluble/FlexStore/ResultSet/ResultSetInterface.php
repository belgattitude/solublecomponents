<?php

namespace Soluble\FlexStore\ResultSet;

use Countable;
use Traversable;


interface ResultSetInterface extends Traversable, Countable
{

    /**
     * Field terminology is more correct as information coming back
     * from the database might be a column, and/or the result of an
     * operation or intersection of some data
     * @abstract
     * @return mixed
     */
    public function getFieldCount();
}
