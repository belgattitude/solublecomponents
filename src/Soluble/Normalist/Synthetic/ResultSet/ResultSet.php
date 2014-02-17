<?php
namespace Soluble\Normalist\Synthetic\ResultSet;

use Soluble\Normalist\Synthetic\Table;
use Soluble\Normalist\Synthetic\Record;
use Soluble\Normalist\Synthetic\Exception;
use Zend\Db\Sql\Select;

use Iterator;

class ResultSet implements Iterator
{

    /**
     *
     * @var Zend\Db\ResultSet\ResultSet
     */
    protected $dataSource;

    /**
     *
     * @param Select $select
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
     */
    public function __construct(Select $select, Table $table)
    {
        $this->select = $select;
        $this->table = $table;
        $this->dataSource = $select->execute();
    }



    public function toArray()
    {
        return $this->dataSource->toArray();
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
