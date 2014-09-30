<?php

namespace Soluble\FlexStore\Column;

use Soluble\Db\Metadata\Column\Definition\AbstractColumnDefinition;
use Soluble\FlexStore\Renderer\RendererInterface;
use ArrayObject;

class ColumnModel
{

    /**
     *
     * @var ArrayObject
     */
    protected $config;

    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    /**
     *
     * @var ArrayObject
     */
    protected $renderers;

    /*
     * A columns is
     *  - [price_sale]['translation']['header']         = 
     *  - [price_sale]['translation']['label']          = 
     *  - [price_sale]['translation']['description']    =
     * 
     *  - [price_sale]['display']['renderer']      = Soluble\Renderer\RendererInterface
     *  - [price_sale]['display']['align']         = [left|right|center]
     *  - [price_sale]['display']['width']         = xxx
     * 
     *  - [price_sale]['datatype'] = Soluble\Renderer\RendererInterface
     *  - [price_sale]['datatype']['metadata']   = 'ALL metadata attributes'
     * 
     *  - [price_sale]['validator'] = ['email', 'nospace', 'notblank', 'regex', 'minlength'] 
     */

    public function __construct()
    {
        $this->config = new ArrayObject(array('columns' => new ArrayObject()));
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
     * 
     * @param string $column
     * @param array|ArrayObject $definition
     * @throws Exception\InvalidArgumentException when column nam is not correct
     * @return ColumnModel
     */
    public function addColumn($column, $definition)
    {
        if (!is_string($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name must be a valid string");
        }

        $column = trim($column);
        if ($column == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name cannot be empty");
        }
        $this->config['columns'][$column] = $definition;
        $this->columns->offsetSet($column, new Column($column));
        return $this;
    }

    /**
     * Set column that must be excluded in getData() and getColumns()
     * 
     * @param array $columns column nams to exclude
     * @throws Exception\InvalidArgumentException     
     * @return ColumnModel
     */
    public function setExcluded(array $excluded_columns, $excluded = true)
    {

        foreach ($excluded_columns as $column) {
            $column = trim($column);
            $this->addColumnParam($column, 'excluded', $excluded);
            if ($excluded) {
                $this->columns->offsetUnset($column);
            } else {
                $this->columns->offsetSet($column, $this->getColumn($column));
            }
        }
        return $this;
    }

    /**
     * Return column that have been excluded in getData() and getColumns()
     * 
     * @return array
     */
    public function getExcluded()
    {
        $excluded = array();
        foreach ($this->getColumnsConfig() as $column => $config) {
            if (array_key_exists('params', $config) && $config['params']['excluded']) {
                $excluded[] = $column;
            }
        }
        return $excluded;
    }
    
    /**
     * Return column from identifier name
     * 
     * @throws Exception\InvalidArgumentException 
     * @throws Exception\ColumnNotFoundException when column does not exists in model
     * @param string $column column name
     * @return Column
     */
    public function getColumn($column)
    {
        if (!is_string($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column parameter must be a string.");
        }
        
        if (!$this->hasColumn($column)) {
            throw new Exception\ColumnNotFoundException(__METHOD__ . " Column '$column' not present in column model.");
        }
        return $this->columns->offsetGet($column);
    }
    
    /**
     * Whether the column identifier name exists in the column model
     * 
     * @throws Exception\InvalidArgumentException 
     * @param string $column
     * @return boolean
     */
    public function hasColumn($column)
    {
        if (!is_string($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column parameter must be a string.");
        }
        return $this->columns->offsetExists($column);
    }

    /**
     * Sort columns in the order specified, columns that exists
     * in the dataset but not in the sorted_columns will be
     * appended to the end
     * 
     * @param array $sorted_columns
     * @return ColumnModel
     */
    public function sortColumns(array $sorted_columns)
    {
        $diff = array_diff_assoc($sorted_columns, array_unique($sorted_columns));
        if (count($diff) > 0) {
            $cols = join(',', $diff);
            throw new Exception\DuplicateColumnException(__METHOD__ . " Duplicate column found in paramter sorted_columns : '$cols'");
        }
        $columns = array();
        foreach ($sorted_columns as $idx => $column) {
            if (!$this->hasColumn($column)) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists.");
            }
            $columns[$column] = $this->getColumn($column);
        }
        // Appending eventual non sorted columns at the end
        $columns = array_merge($columns, (array) $this->columns);
        $this->columns = new \ArrayObject($columns);
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
        return array_key_exists($column, $this->config['columns']);
    }

    /**
     * Exclude all other columns that the one specified
     * Column sort is preserved in getData()
     * 
     * @param array $include_only_columns
     * @param bool $sort automatically apply sortColumns
     * @return ColumnModel
     */
    public function setIncludeOnly(array $include_only_columns, $sorted = true)
    {
        $columns = $this->getColumnsConfig();
        // trim column
        $include_only_columns = array_map('trim', $include_only_columns);

        foreach ($columns as $column => $config) {
            if (in_array($column, $include_only_columns)) {
                $this->setExcluded(array($column), false);
            } else {
                $this->setExcluded(array($column), true);
            }
        }
        if ($sorted) {
            $this->sortColumns($include_only_columns);
        }
        return $this;
    }

    /**
     * Add column paramter
     * @param string $column
     * @param string $key
     * @param mixed $value
     * @throws Exception\InvalidArgumentException
     */
    protected function addColumnParam($column, $key, $value)
    {
        if (!array_key_exists($column, $this->getColumnsConfig())) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in columnModel");
        }
        $this->config['columns'][$column]['params'][$key] = $value;
    }

    /**
     * 
     * @throws Exception\InvalidArgumentException
     * @param string $column
     * @param string $key
     * @return mixed
     */
    protected function getColumnParam($column, $key)
    {
        if (!$this->exists($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in dataset.");
        }


        return $this->config['columns']['params'][$key];
    }

    /**
     * Return columns
     * 
     * @return ArrayObject
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Return column definition
     * 
     * @throws Exception\InvalidArgumentException
     * @param string $column
     * @return AbstractColumnDefinition
     */
    public function getColumnDefinition($column)
    {
        if (!$this->exists($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in dataset.");
        }
        return $this->config['columns'][$column]['definition'];
    }

    /**
     * Return columns underlying configuration
     *
     * @return ArrayObject
     */
    public function getColumnsConfig()
    {
        return $this->config['columns'];
    }

}
