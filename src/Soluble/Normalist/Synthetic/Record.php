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
        $ci = $this->table->getColumnsInformation();
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
        $primary = $this->primaryKey;
        if ($this[$primary] != '') {
            // update
            $pkvalue = $this[$primary];
            $predicate = array($primary => $pkvalue);
            $data = $this->toArray();
            unset($data[$primary]);


            $affectedRows = $this->tableManager->update($this->tableName, $data, $predicate);
            if ($affectedRows > 1) {
                // Should never happen
                throw new Exception\ErrorException("Saving record returned more than one affected row");
            }
            $record = $this->tableManager->find($this->tableName, $pkvalue);

        } else {
            // insert
            $record = $this->tableManager->insert($this->tableName, $this->toArray());
        }

        $this->data = new \ArrayObject($record->toArray());
        $this->clean = true;
        return $this;
    }

    /**
     * @throws \Exception
     * @return \Soluble\Normalist\Synthetic\Record
     */
    public function delete()
    {
        $primary = $this->primaryKey;
        $id = $this[$primary];
        if (!$this->clean) {
            throw new \Exception("Cannot delete record '$id', it is not in clean state (not in saved in database state)");
        }
        return $this->tableManager->delete($this->tableName, $id);
    }


    /**
     * Get field value
     * @param string $field
     * @return mixed
     * @throws Exception\FieldNotFoundException
     */
    public function get($field)
    {
        return $this->offsetGet($field);
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
     *
     * @param string $field
     * @return \Soluble\Normalist\Record
     */
    public function offsetUnset($field)
    {
        $this->data->offsetUnset($field);
        return $this;
    }

    /**
     *
     * @param string $parent_table
     */
    public function getParent($parent_table)
    {
        $relations = $this->tableManager->getRelations($this->tableName);
        //$rels = array();
        foreach($relations as $column => $parent) {
            if ($parent['table_name'] == $parent_table) {
                // @todo, check the case when
                // table has many relations to the same parent
                // we'll have to throw an exception
                $record = $this->tableManager->findOneBy($parent_table, array(
                    $parent['column_name'] => $this->get($column)
                ));
                return $record;
            }
        }
        return false;
    }


    /**
     *
     * @param string $field
     * @param mixed $value
     */
    public function __set($field, $value)
    {
        $this->data->offsetSet($field, $value);
    }

    /**
     *
     * @param string $field
     * @return mixed
     * @throws Exception\FieldNotFoundException
     */
    public function __get($field)
    {
        return $this->data->offsetGet($field);
    }


    /*
    public function __call($method, $arguments)
    {
        if (preg_match('/related/', $method)) {


        }
    }
    */

}
