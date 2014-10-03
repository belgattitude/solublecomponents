<?php

namespace Soluble\FlexStore\Column;

use Soluble\FlexStore\Renderer\RendererInterface;
use Soluble\FlexStore\Column\ColumnModel\Search;
use ArrayObject;

class ColumnModel
{

    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    /**
     *
     * @var Search
     */
    protected $search;

    /**
     *
     * @var ArrayObject
     */
    protected $renderers;

    public function __construct()
    {
       
        $this->columns = new ArrayObject();
        $this->renderers = new ArrayObject();
    }

    /**
     * Add a column renderer
     * 
     * @throws Exception\InvalidArgumentException
     * @param string $column
     * @param type $renderer
     */
    public function addRowRenderer($renderer)
    {
        $this->renderers->append($renderer);

        /*
          try {
          if (!$this->exists($column)) {
          throw new Exception\InvalidArgumentException("Column does not exists '$column'");
          }
          } catch (Exception\InvalidArgumentException $e) {
          throw new Exception\InvalidArgumentException(__METHOD__ . ": Cannot add renderer to unexistent column '$column'.");
          }

          if (!$this->renderers->offsetExists($column)) {
          $this->renderers->offsetSet($column, new ArrayObject());
          }

          $this->renderers->offsetGet($column)->append($renderer);
         */
    }

    function getRowRenderers()
    {
        return $this->renderers;
    }

    /**
     * Add a column to the column model
     *  
     * @param Column $column
     * @return ColumnModel
     */
    public function add(Column $column)
    {
        $this->columns->offsetSet($column->getName(), $column);
        return $this;
    }

    /**
     * Tells whether a column exists
     * 
     * @throws Exception\InvalidArgumentException 
     * @param string $column
     * @return boolean
     */
    public function exists($column)
    {
        if (!is_string($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name must be a valid string");
        }
        if ($column == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name cannot be empty");
        }
        return $this->columns->offsetExists($column);
    }
    
    

    /**
     * Return column that have been excluded in getData() and getColumns()
     * 
     * @return array
     */
    public function getExcluded()
    {
        $arr = array();
        foreach ($this->columns as $name => $column) {
            if ($column->isExcluded()) {
                $arr[] = $name;
            }
        }
        return $arr;
    }

    /**
     * Return column from identifier name
     *
     * @param string $column column name
     *  
     * @throws Exception\InvalidArgumentException 
     * @throws Exception\ColumnNotFoundException when column does not exists in model
     * @return Column
     */
    public function get($column)
    {
        if (!$this->exists($column)) {
            throw new Exception\ColumnNotFoundException(__METHOD__ . " Column '$column' not present in column model.");
        }
        return $this->columns->offsetGet($column);
    }

    /**
     * Sort columns in the order specified, columns that exists
     * in the dataset but not in the sorted_columns will be
     * appended to the end
     * 
     * @param array $sorted_columns
     * @return ColumnModel
     */
    public function sort(array $sorted_columns)
    {
        $diff = array_diff_assoc($sorted_columns, array_unique($sorted_columns));
        if (count($diff) > 0) {
            $cols = join(',', $diff);
            throw new Exception\DuplicateColumnException(__METHOD__ . " Duplicate column found in paramter sorted_columns : '$cols'");
        }
        $columns = array();

        foreach ($sorted_columns as $idx => $column) {
            if (!$this->exists($column)) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists.");
            }
            $columns[$column] = $this->get($column);
        }
        // Appending eventual non sorted columns at the end
        $columns = array_merge($columns, (array) $this->columns);
        $this->columns = new ArrayObject($columns);
        return $this;
    }

    /**
     * Set column that must be excluded in getData() and getColumns()
     * 
     * @param array|string|ArrayObject $columns column nams to exclude
     * @throws Exception\InvalidArgumentException     
     * @return ColumnModel
     */
    public function exclude($excluded_columns, $excluded = true)
    {
        if (!is_array($excluded_columns) && !is_string($excluded_columns) && !$excluded_columns instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' Requires $excluded_columns param to be array|ArrayObject|string');
        }
        $result = $this->search()->in((array) $excluded_columns);
        $result->setExcluded($excluded);
        
        return $this;
    }

    
    /**
     * Exclude all other columns that the one specified
     * Column sort is preserved in getData()
     * 
     * @param array $include_only_columns
     * @param bool $sort automatically apply sortColumns
     * @return ColumnModel
     */
    public function includeOnly(array $include_only_columns, $sort = true)
    {
        // trim column
        $include_only_columns = array_map('trim', $include_only_columns);

        foreach ($this->columns as $name => $column) {
            if (in_array($name, $include_only_columns)) {
                $this->exclude($name, false);
            } else {
                $this->exclude($name, true);
            }
        }
        if ($sort) {
            $this->sort($include_only_columns);
        }
        return $this;
    }


    /**
     * Return columns
     * 
     * @return ArrayObject
     */
    public function getColumns()
    {
        $arr = new ArrayObject;
        foreach ($this->columns as $key => $column) {
            if (!$column->isExcluded()) {
                $arr->offsetSet($key, $column);
            }
        }
        return $arr;
    }

    /**
     * @return ColumnModel\Search
     */
    public function search()
    {
        if ($this->search === null) {
            $this->search = new Search($this->columns);
        }
        return $this->search;
    }

}
