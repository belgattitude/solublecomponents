<?php

namespace Soluble\FlexStore\ResultSet;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Helper\Paginator;
use ArrayObject;

class ResultSet extends AbstractResultSet
{


    /**
     *
     * @var Paginator
     */
    protected $paginator;

    /**
     *
     * @var boolean
     */
    protected $columnsChecked = false;

    /**
     *
     * @var array
     */
    protected $columns;

    /**
     *
     * @var integer
     */
    protected $totalRows;

    /**
     * @var \Soluble\FlexStore\Source\AbstractSource
     */
    protected $source;




    /**
     *
     * @param AbstractSource $source
     * @return ResultSet
     */
    public function setSource(AbstractSource $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     *
     * @return AbstractSource
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator($this->getTotalRows(),
                                             $this->getSource()->getOptions()->getLimit(),
                                             $this->getSource()->getOptions()->getOffset());
        }
        return $this->paginator;
    }




    /**
     * Set the total rows
     * @param int $totalRows
     * @return ResultSet
     */
    public function setTotalRows($totalRows)
    {
        $this->totalRows = (int) $totalRows;
        return $this;
    }


    /**
     * @return int
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     *
     * @param array $columns
     * @return ResultSet
     */
    public function setColumns(array $columns)
    {
        $this->columnsChecked = false;
        $this->columns = $columns;

        return $this;
    }


    /**
     *
     * @return ResultSet
     */
    public function unsetColumns()
    {
        $this->columnsChecked = false;
        $this->columns = null;
        return $this;
    }

    /**
     *
     * @throws Exception\UnknownColumnException
     * @return array|\ArrayObject|null
     */
    public function current()
    {

        $data = $this->zfResultSet->current();
        if ($this->columns !== null) {

            $d = new \ArrayObject();
            if (!$this->columnsChecked) {
                foreach($this->columns as $column) {
                    //if (!$data->offsetExists($column)) {
                    if (!array_key_exists($column, $data)) {
                        $msg = "Column '$column' does not exists";
                        throw new Exception\UnknownColumnException($msg);
                    }
                }
                $this->columnsChecked;
            }
            foreach($this->columns as $column) {
                $d[$column] = $data[$column];
            }
            $data = $d;

        }
        return $data;
    }


    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        $return = array();
        foreach ($this as $row) {
            if (is_array($row)) {
                $return[] = $row;
            } elseif (method_exists($row, 'toArray')) {
                $return[] = $row->toArray();
            } elseif (method_exists($row, 'getArrayCopy')) {
                $return[] = $row->getArrayCopy();
            } else {
                throw new Exception\RuntimeException(
                    'Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array'
                );
            }
        }
        return $return;
    }

}
