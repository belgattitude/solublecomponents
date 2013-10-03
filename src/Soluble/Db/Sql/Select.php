<?php

namespace Soluble\Db\Sql;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select as ZendDbSqlSelect;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;

class Select extends ZendDbSqlSelect implements AdapterAwareInterface
{
	
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	

	/**
	 * Set database adapter
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return \Soluble\Db\Sql\Select
	 */
	function setDbAdapter(Adapter $adapter) {
		$this->sql = null;
		$this->adapter = $adapter;
		return $this;
	}
	
	
	/**
	 * Execute the query
	 * @return  
	 */
	function execute()
	{
		$sql = new Sql($this->adapter);
		$sql_string = $sql->getSqlStringForSqlObject($this);
		return $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
	}
	
	
}
