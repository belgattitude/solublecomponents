<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Exception;


use ArrayAccess;

class Record implements ArrayAccess
{
    /**
     *
     * @var \ArrayObject
     */
    protected $data;

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     *
     * @var \Soluble\Normalist\Synthetic\Table
     */
    protected $table;


    /**
     *
     * @var string
     */
    protected $primary_keys;

    /**
     *
     * @var boolean
     */
    protected $clean;

    /**
     *
     * @param \Soluble\Normalist\Synthetic\Table $table
     * @param array $data
     */
    public function __construct(Table $table, array $data, $check_columns=true)
    {

        $this->clean     = true;
        $this->tableName = $table->getTableName();
        $this->table     = $table;

        $this->primary_keys = $table->getPrimaryKeys();
        //$this->data = new \ArrayObject($data);
        $this->setData($data);
    }

    /**
     *
     * @param array $data
     * @param boolean $check_columns
     * @return Record
     * @throws Exception\InvalidColumnException
     */
    public function setData($data, $check_columns=true)
    {
        $d = new \ArrayObject();
        $ci = $this->getTable()->getColumnsInformation();
        $columns = array_keys($ci);
        foreach ($data as $column => $value) {
            if (in_array($column, $columns)) {
                $d->offsetSet($column, $value);
            } elseif ($check_columns) {
                throw new Exception\InvalidColumnException("Column '$column' does not exists in table '$table'");
            }
        }
        $this->data = $d;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->data;
    }


    /**
     * @throws \Exception
     * @return \Soluble\Normalist\SyntheticRecord
     */
    public function save()
    {
        
        $table   = $this->getTable();
        $primary = $table->getPrimaryKey();
        
        $pkvalue = $this->offsetGet($primary);
        
        if ($pkvalue === null) {

            // INSERT
            $record = $table->insert($this->toArray());

        } else {
            
            // TODO implement dirty records
            $predicate = array($primary => $pkvalue);
            $data = $this->toArray();
            unset($data[$primary]);
            $table->update($data, $predicate);
            $record = $table->findOneBy($predicate);
            
        }

        $this->data = new \ArrayObject($record->toArray());
        $this->clean = true;
        return $this;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function delete()
    {
        $table   = $this->getTable();
        $primary = $table->getPrimaryKey();
        
        $pk_value = $this->offsetGet('primary');
        if (!$this->clean) {
            $tableName = $table->getTableName();
            throw new \Exception("Cannot delete record '$pk_value' on table '$tableName', it is not in clean state (not in saved state in database)");
        }
        $this->table->delete($pk_value);
        unset($this);
    }


    /**
     *
     * @param string $field
     * @return boolean
     */
    public function offsetExists($field)
    {
        return $this->data->offsetExists($field);
    }

    /**
     * Get field value
     * @param string $field
     * @return mixed
     * @throws Exception\FieldNotFoundException
     */
    public function offsetGet($field)
    {
        if (!$this->data->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        return $this->data->offsetGet($field);
    }

    /**
     *
     * @param string $field
     * @param mixed $value
     * @return \Soluble\Normalist\Record
     */
    public function offsetSet($field, $value)
    {
        $this->clean = false;
        $this->data->offsetSet($field, $value);
        return $this;
    }

    /**
     * Always throws a LogicException
     * 
     * @throws Exception\LogicException
     * @param string $field
     * @return void
     */
    public function offsetUnset($field)
    {
        throw new Exception\LogicException("Cannot unset record fields");
    }

    /**
     *
     * @param string $parent_table
     */
    public function getParent($parent_table)
    {
        $table = $this->getTable();
        $tableName = $table->getTableName();
        $relations = $table->getTableManager()->getRelations($tableName);
        //$rels = array();
        foreach($relations as $column => $parent) {
            if ($parent['table_name'] == $parent_table) {
                // @todo, check the case when
                // table has many relations to the same parent
                // we'll have to throw an exception
                $record = $table->getTableManager()->findOneBy($parent_table, array(
                    $parent['column_name'] => $this->get($column)
                ));
                return $record;
            }
        }
        return false;
    }


    /**
     *
     * @throws Exception\FieldNotFoundException
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function __set($field, $value)
    {
        if (!$this->data->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        $this->data->offsetSet($field, $value);
    }

    /**
     * Magical method
     * 
     * @param string $field
     * @throws Exception\FieldNotFoundException
     * @return mixed
     */
    public function __get($field)
    {
        
        if (!$this->data->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        return $this->data->offsetGet($field);
    }
    
    
    /**
     * Return underlying table instance
     * 
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }


    /*
    public function __call($method, $arguments)
    {
        if (preg_match('/related/', $method)) {


        }
    }
    */

}
