<?php
namespace Soluble\Normalist\Synthetic\ResultSet;

use Soluble\Normalist\Synthetic\Table;
use Soluble\Normalist\Synthetic\Record;
use Soluble\Normalist\Synthetic\Exception;
use Soluble\Db\Sql\Select;
use Iterator;
use Countable;

class ResultSet implements Iterator, Countable
{
    /**
     *
     * @var \Zend\Db\ResultSet\ResultSet
     */
    protected $dataSource;

    /**
     * @var Table
     */
    protected $table;

    /**
     *
     * @var Select
     */
    protected $select;

    /**
     * @var null|int
     */
    protected $count = null;

    /**
     * @var int
     */
    protected $position = 0;


    /**
     *
     * @var boolean
     */
    protected $has_complete_record_definition;

    /**
     *
     * @param Select $select Originating select object
     * @param Table $table Originating table
     * @param boolean $has_complete_record_definition
     */
    public function __construct(Select $select, Table $table, $has_complete_record_definition = false)
    {
        $this->select = $select;
        $this->table = $table;
        $this->has_complete_record_definition = $has_complete_record_definition;
        $this->dataSource = $select->execute();
    }

    /**
     * This method allows the results to be iterable multiple times
     * for database drivers that does not support rewind() method.
     * PDO_Mysql for example does not provide backward scrolling resultset,
     * They are forward only. MySQLi provides backward scrolling so this method
     * should not be used.
     *
     * @throws Zend\Db\ResultSet\Exception\RuntimeException Buffering must be enabled before iteration is started
     *
     * @return ResultSet
     */
    public function buffer()
    {
        $this->dataSource->buffer();
        return $this;
    }


    /**
     * Return an array version of the resultset
     * @return array
     */
    public function toArray()
    {
        return $this->dataSource->toArray();
    }

    /**
     * Return an json version of the resultset
     * @return string Json encoded version
     */
    public function toJson()
    {
        return json_encode($this->dataSource->toArray());
    }


    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    public function next()
    {
        $this->dataSource->next();
        $this->position++;
    }

    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: get current item
     *
     * @throws Exception\LogicException whenever a record cannot be instanciated, due to missing column specs
     * @return Record
     */
    public function current()
    {
        $data = $this->dataSource->current();

        if (!$this->has_complete_record_definition) {
            $data_columns   = array_keys($this->table->getColumnsInformation());
            $record_columns = array_keys((array) $data);
            $matches = array_intersect($data_columns, $record_columns);
            if (count($matches) != count($data_columns)) {
                $missings = join(',', array_diff($data_columns, $record_columns));
                $msg = __METHOD__ . ": Cannot create a Record due to incomplete or aliased column definition (missing: $missings).";
                $msg .= "Check whether columns have been modified in TableSearch::columns() method, or use an toArray(), toJson()... version of the ResultSet.";
                throw new Exception\LogicException($msg);
            }
            $this->has_complete_record_definition = true;
        }

        $record = $this->table->record($data, $ignore = true);
        $record->setState(Record::STATE_CLEAN);
        return $record;
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->dataSource->valid();
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Countable: return count of rows
     *
     * @return int
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = $this->dataSource->count();
        }
        return $this->count;
    }
}
