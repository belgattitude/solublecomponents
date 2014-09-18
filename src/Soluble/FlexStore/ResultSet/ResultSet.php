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
     * Limited columns
     * @var array|null
     */
    protected $limitedColumns;
    
    
    /**
     * Tells whether columns names in limitColumns have been checked
     * for existence;
     * @var boolean
     */
    protected $limitedColumnsAreChecked;
    



    /**
     *
     * @var integer
     */
    protected $totalRows;

    /**
     * @var AbstractSource
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
     * By using limitColumns on a resultset, you ensure that only
     * those columns will be available/returned even if they are
     * more available in the original dataset 
     * 
     * Checks against column name existence can only be done 
     * lazily when current() is called. 
     *  
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DuplicateColumnException only when $ignore_duplicate_columns is false (default) 
     * @param array $limited_columns columns nams in an array
     * @param array $ignore_duplicate_columns check whether we can ignore duplicate columns
     * @return ResultSet
     */
    public function limitColumns(array $limited_columns, $ignore_duplicate_columns=false)
    {
        if (count($limited_columns) == 0) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ': $limited_columns parameter is empty.');
        }
        
        if (!$ignore_duplicate_columns) {
            $values = array_count_values($limited_columns);
            foreach($values as $column_name => $count) {
                if ($count > 1) {
                    throw new Exception\DuplicateColumnException(__METHOD__ . ": Duplicate column detected '$column_name' in column list.");
                }
            }
        } else {
            $limited_columns = array_unique($limited_columns);
        }
        
        // clean up with trim
        foreach($limited_columns as $idx => $column) {
            $limited_columns[$idx] = trim($column);
        }
        
        $this->limitedColumnsAreChecked = false;
        $this->limitedColumns = $limited_columns;        
        return $this;
    }


    /**
     * Reset eventual limited columns set by limitColumns() method
     * @return ResultSet
     */
    public function resetLimitColumns()
    {
        $this->limitedColumnsAreChecked = false;
        $this->limitedColumns = null;
        return $this;
    }

    /**
     * Return the current row as an array|ArrayObject.
     * If setLimitColumns() have been set, will only return 
     * the limited columns.
     * 
     * @throws Exception\UnknownColumnException
     * @return array|ArrayObject|null
     */
    public function current()
    {
        $data = $this->zfResultSet->current();
        
        // 2 cases when limited columns are set 
        
        if ($this->limitedColumns !== null) {
            
            // Step 1: check limited columns existence
            
            if (!$this->limitedColumnsAreChecked) {
                // check all limited columns
                // this check is made only for the first row.
                foreach($this->limitedColumns as $column) {
                    if (!array_key_exists($column, (array) $data)) {
                        $msg = __METHOD__ . ": Resultset has limited columns option and column '$column' does not exists in it";
                        throw new Exception\UnknownColumnException($msg);
                    }
                }
                $this->limitedColumnsAreChecked = true;
            }

            $d = new ArrayObject();
            //$lc = array_fill_keys($this->limitedColumns, null);
            //$t = array_intersect_key((array) $data, $lc);
            //$data->exchangeArray($t);
            foreach($this->limitedColumns as $column) {
                $d->offsetSet($column, $data[$column]);
            }
            
            
            if ($this->returnType === self::TYPE_ARRAYOBJECT) {
                $data = $d;
                unset($d);
            } else {
                $data = (array) $d; 
            }
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
                    __METHOD__ . ': Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array'
                );
            }
        }
        return $return;
    }

}
