<?php
namespace Soluble\FlexStore\Metadata\Source;

use Soluble\FlexStore\Metadata\Exception;
use Soluble\FlexStore\Metadata\Column;
use Soluble\FlexStore\Metadata\Column\Types;

use ArrayObject;

class MysqliMetadataSource  {


	/**
	 * @var \Mysqli
	 */
	protected $mysqli;

	public function __construct(\Mysqli $mysqli) 
	{
		$this->mysqli = $mysqli;
		//$this->store  = $store;
	}
	
	
	function getColumnsMetadata($sql)
	{
		$metadata = new ArrayObject();
		$fields = $this->readFields($sql);
		$type_map = $this->getDatatypeMapping();
		
		
		foreach($fields as $idx => $field) {
			
			$name = $field->orgname;
			$tableName = $field->orgtable;
			$schemaName = $field->db;
			
			$datatype = $field->type;
			if (!$type_map->offsetExists($datatype)) {
				throw new Exception\UnsupportedDatatypeException("Datatype '$datatype' not yet supported by " . __CLASS__);
			}
			
			$datatype = $type_map->offsetGet($datatype);
			
			$column = Column\Type::createColumnDefinition($datatype, $name, $tableName, $schemaName);
			
			$column->setAlias($field->name);
			$column->setTableAlias($field->table);
			$column->setCatalog($field->catalog);
			$column->setOrdinalPosition($idx + 1);
			$column->setDataType($datatype);
			$column->setIsNullable(!($field->flags & MYSQLI_NOT_NULL_FLAG) > 0 && ($field->orgtable != ''));
			$column->setIsPrimary(($field->flags & MYSQLI_PRI_KEY_FLAG) > 0);
			$column->setColumnDefault($field->def);
			$column->setNativeDataType($nativeDataType);
			
			if ($column instanceof Column\Definition\NumericColumnInterface) {
				$column->setNumericUnsigned(($field->flags & MYSQLI_UNSIGNED_FLAG) > 0);
			} 
			
			if ($column instanceof Column\Definition\IntegerColumn) {
				$column->setIsAutoIncrement(($field->flags & MYSQLI_AUTO_INCREMENT_FLAG) > 0);
			}

			if ($column instanceof Column\Definition\DecimalColumn) {
				// salary DECIMAL(5,2)
				// In this example, 5 is the precision and 2 is the scale.
				// Standard SQL requires that DECIMAL(5,2) be able to store any value 
				// with five digits and two decimals, so values that can be stored in 
				// the salary column range from -999.99 to 999.99. 
				
				$column->setNumericPrecision($field->length - $field->decimals + 1);
				$column->setNumericScale($field->decimals);
				
			}
			
			if ($column instanceof Column\Definition\StringColumn) {
				$column->setCharacterMaximumLength($field->length);
			}
			
			if ($column instanceof Column\Definition\BlobColumn) {
				$column->setCharacterOctetLength($field->length);
			}
			
			$metadata[$column->getAlias()] = $column;

		}
		
		return $metadata;
	}
	
	
	
	
	/**
	 * 
	 * @param string $sql
	 * @throws Exception\ConnectionException
	 */
	protected function readFields($sql)
	{
		if (trim($sql) == '') {
			throw new Exception\EmptyQueryException();
		}
 		
		if ($this->mysqli->connect_error) {
			$errno = $this->mysqli->connect_errno;
			$message = $this->mysqli->connect_error;
			throw new Exception\ConnectionException("Connection error: $message ($errno)");
		}
		
		$stmt = $this->mysqli->prepare($sql);

		if (!$stmt) {
			$message = $this->mysqli->error;
			throw new Exception\InvalidQueryException("Sql is not correct : $message");
		}
		$stmt->execute();
		$result = $stmt->result_metadata();
		$metaFields = $result->fetch_fields();
		$result->close();
		$stmt->close();
		return $metaFields;
	}
	
	


	/**
	 * Optimization, will add false condition to the query
	 * so the metadata loading will be faster
	 *
	 * 
	 * @param string $sql query string
	 * @return string
	 */
	protected function makeQueryEmpty($sql) {
		// see the reason why in Vision_Store_Adapter_ZendDbSelect::getMetatData
		$sql = str_replace("('__innerselect'='__innerselect')", '(1=0)', $sql);
		return $sql;
	}


	/**
	 * 
	 * @return ArrayObject
	 */
	protected function getDatatypeMapping() {

		// ALL the following fields are not supported by Vision_Store
		// Maybe todo in a later release or choose to map them to approximative
		// types (i.e. MYSQLI_YEAR could be a Vision_Store_Metadata::DATATYPE_INTEGER) ?
		/*
		  MYSQLI_TYPE_NULL
		  MYSQLI_TYPE_YEAR
		  MYSQLI_TYPE_ENUM
		  MYSQLI_TYPE_SET
		  MYSQLI_TYPE_GEOMETRY
		 */

		$mapping = new ArrayObject();

		// texts
		$mapping->offsetSet(MYSQLI_TYPE_STRING, Column\Type::TYPE_STRING);
		$mapping->offsetSet(MYSQLI_TYPE_CHAR, Column\Type::TYPE_STRING);
		$mapping->offsetSet(MYSQLI_TYPE_VAR_STRING, Column\Type::TYPE_STRING);

		// enum
		$mapping->offsetSet(MYSQLI_TYPE_ENUM, Column\Type::TYPE_STRING);

		// BLOBS ARE CURRENTLY SENT AS TEXT
		// I DIDN'T FIND THE WAY TO MAKE THE DIFFERENCE !!!
		
		
		$mapping->offsetSet(MYSQLI_TYPE_TINY_BLOB, Column\Type::TYPE_BLOB);
		$mapping->offsetSet(MYSQLI_TYPE_MEDIUM_BLOB, Column\Type::TYPE_BLOB);
		$mapping->offsetSet(MYSQLI_TYPE_LONG_BLOB, Column\Type::TYPE_BLOB);
		$mapping->offsetSet(MYSQLI_TYPE_BLOB, Column\Type::TYPE_BLOB);



		// integer
		$mapping->offsetSet(MYSQLI_TYPE_TINY, Column\Type::TYPE_INTEGER);
		$mapping->offsetSet(MYSQLI_TYPE_SHORT, Column\Type::TYPE_INTEGER);
		$mapping->offsetSet(MYSQLI_TYPE_INT24, Column\Type::TYPE_INTEGER);
		$mapping->offsetSet(MYSQLI_TYPE_LONG, Column\Type::TYPE_INTEGER);
		$mapping->offsetSet(MYSQLI_TYPE_LONGLONG, Column\Type::TYPE_INTEGER);

		// timestamps
		$mapping->offsetSet(MYSQLI_TYPE_TIMESTAMP, Column\Type::TYPE_DATETIME);
		$mapping->offsetSet(MYSQLI_TYPE_DATETIME, Column\Type::TYPE_DATETIME);

		// dates
		$mapping->offsetSet(MYSQLI_TYPE_DATE, Column\Type::TYPE_DATE);
		$mapping->offsetSet(MYSQLI_TYPE_NEWDATE, Column\Type::TYPE_DATE);

		// time
		$mapping->offsetSet(MYSQLI_TYPE_TIME, Column\Type::TYPE_TIME);

		// decimals
		$mapping->offsetSet(MYSQLI_TYPE_DECIMAL, Column\Type::TYPE_DECIMAL);
		$mapping->offsetSet(MYSQLI_TYPE_NEWDECIMAL, Column\Type::TYPE_DECIMAL);
		
		$mapping->offsetSet(MYSQLI_TYPE_FLOAT, Column\Type::TYPE_FLOAT);
		$mapping->offsetSet(MYSQLI_TYPE_DOUBLE, Column\Type::TYPE_FLOAT);

		// boolean
		// When available (PHP5.3, add MYSQLI_TYPE_BOOLEAN)		
		$mapping->offsetSet(MYSQLI_TYPE_BIT, Column\Type::TYPE_BOOLEAN);

		return $mapping;
	}

	/**
	 * Return defined columns
	 * @return array
	 */
	function getColumns() {
		return array_keys((array) $this->reallyLoadMetadata());
	}

}