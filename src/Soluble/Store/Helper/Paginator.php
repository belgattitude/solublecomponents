<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\Store\Helper;

use Zend\Paginator\Paginator as ZendPaginator;

class Paginator extends ZendPaginator
{


	function __construct($totalRows, $limit, $offset) {
		
		$adapter = new \Zend\Paginator\Adapter\Null($totalRows);
		parent::__construct($adapter);
		$this->setItemCountPerPage($limit);
		$this->setCurrentPageNumber(ceil(($offset + 1) / $limit));
		
	}
}
