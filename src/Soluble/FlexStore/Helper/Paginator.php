<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Helper;
use Soluble\FlexStore\Exception;
use Zend\Paginator\Paginator as ZendPaginator;

class Paginator extends ZendPaginator
{


    public function __construct($totalRows, $limit, $offset=0)
    {

        if (!is_integer($limit)) {
            throw new Exception\InvalidUsageException(__FUNCTION__ . ' expects limit to be an integer');
        }
        if ($limit < 1) {
            throw new Exception\InvalidUsageException(__FUNCTION__ . ' expects limit to be an integer greater than 0');
        }


        $adapter = new \Zend\Paginator\Adapter\Null($totalRows);
        parent::__construct($adapter);
        $this->setItemCountPerPage($limit);
        $this->setCurrentPageNumber(ceil(($offset + 1) / $limit));

    }
}
