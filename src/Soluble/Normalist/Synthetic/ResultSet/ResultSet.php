<?php
namespace Soluble\Normalist\Synthetic\ResultSet;

use Soluble\Normalist\Synthetic\Table;
use Soluble\Normalist\Synthetic\Record;

use Soluble\Db\Sql\Select;

use Iterator;
use Countable;

class ResultSet implements Iterator, Countable
{

    /**
     *
     * @var Zend\Db\ResultSet\ResultSet
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
     * @param Select $select Originating select object
     * @param Table $table Originating table
     */
    public function __construct(Select $select, Table $table)
    {
        $this->select = $select;
        $this->table = $table;
        $this->dataSource = $select->execute();
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
     * @return array
     */
    public function current()
    {
        $data = $this->dataSource->current();
        $record = $this->table->record($data, $ignore=true);
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
        $this->count = count($this->dataSource);
        return $this->count;
    }

}
