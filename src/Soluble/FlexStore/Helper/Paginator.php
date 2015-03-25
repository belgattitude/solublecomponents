<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Helper;

use Soluble\FlexStore\Exception;
use Zend\Paginator\Paginator as ZendPaginator;

class Paginator extends ZendPaginator
{


    /**
     *
     * @param integer $totalRows
     * @param integer $limit
     * @param integer $offset
     * @throws Exception\InvalidUsageException
     */
    public function __construct($totalRows, $limit, $offset = 0)
    {
        $totalRows = filter_var($totalRows, FILTER_VALIDATE_INT);
        $limit = filter_var($limit, FILTER_VALIDATE_INT);
        $offset = filter_var($offset, FILTER_VALIDATE_INT);

        if (!is_int($limit) || $limit < 0) {
            throw new Exception\InvalidUsageException(__METHOD__ . ' expects limit to be an integer greater than 0');
        }
        if (!is_int($totalRows) || $totalRows < 0) {
            throw new Exception\InvalidUsageException(__METHOD__ . " expects total rows to be an integer greater than 0");
        }
        if (!is_int($offset) || $offset < 0) {
            throw new Exception\InvalidUsageException(__METHOD__ . ' expects offset to be an integer greater than 0');
        }
        


        $adapter = new \Zend\Paginator\Adapter\Null($totalRows);
        parent::__construct($adapter);
        $this->setItemCountPerPage($limit);
        $this->setCurrentPageNumber(ceil(($offset + 1) / $limit));
    }
}
