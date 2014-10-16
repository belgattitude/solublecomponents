<?php

namespace Soluble\FlexStore\ResultSet;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Helper\Paginator;
use Soluble\FlexStore\Options\HydrationOptions;
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
     * @var integer
     */
    protected $totalRows;

    /**
     * @var AbstractSource
     */
    protected $source;

    /**
     *
     * @var HydrationOptions
     */
    protected $hydrationOptions;
    
    /**
     *
     * @var boolean
     */
    protected $hydrate_options_initialized = false;

    /**
     * @var ArrayObject
     */
    protected $hydration_formatters;

    /**
     * @var ArrayObject
     */
    protected $hydration_renderers;

    /**
     * @var ArrayObject|null
     */
    protected $hydrated_columns;

    /**
     * @var ArrayObject
     */
    protected $hydration_virtual_columns;
    
    
    /**
     * Return source column model
     * 
     * @throws Exception\RuntimeException
     * @return ColumnModel
     */
    public function getColumnModel()
    {
        if ($this->source === null) {
            throw new Exception\RuntimeException(__METHOD__ . " Prior to get column model, a source must be set.");
        }
        $this->hydrate_options_initialized = false;
        return $this->source->getColumnModel();
    }

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
     * @param HydrationOptions $hydrationOptions
     * @return ResultSet
     */
    public function setHydrationOptions(HydrationOptions $hydrationOptions)
    {
        $this->hydrationOptions = $hydrationOptions;
        return $this;
    }

    /**
     *
     * @return HydrationOptions
     */
    public function getHydrationOptions()
    {
        return $this->hydrationOptions;
    }
    
    
    /**
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator(
                    $this->getTotalRows(), $this->getSource()->getOptions()->getLimit(), $this->getSource()->getOptions()->getOffset()
            );
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
     * @param ArrayObject $row
     * @return null
     */
    protected function initColumnModelHydration(ArrayObject $row)
    {

        $this->hydration_formatters = new ArrayObject();
        $this->hydration_renderers = new ArrayObject();
        $this->hydrated_columns = null;
        $this->hydration_virtual_columns = new ArrayObject();


        if ($this->source->hasColumnModel()) {
            $cm = $this->getColumnModel();

            // 1. Initialize columns hydrators
            if ($this->getHydrationOptions()->isFormattersEnabled()) {
                $formatters = $cm->getUniqueFormatters();
                if ($formatters->count() > 0) {
                    $this->hydration_formatters = $formatters;
                }
            }

            // 2. Initialize hydrated columns

            if ($this->getHydrationOptions()->isColumnExclusionEnabled()) {                        
                $columns = $cm->getColumns();

                // Performance:
                // Only if column model definition differs from originating 
                // source row definition.
                $hydrated_columns = array_keys((array) $columns);
                $row_columns = array_keys((array) $row);
                if ($hydrated_columns != $row_columns) {
                    $this->hydrated_columns = new ArrayObject($hydrated_columns);
                }
            } 

            // 3. Initialize row renderers
            if ($this->getHydrationOptions()->isRenderersEnabled()) {            

                // If renderers enabled, always populate virtual columns
                // in the original data row in order for renderers to
                // check if columns exists

                $virtual_columns = $cm->search()->findVirtual()->toArray();
                $this->hydration_virtual_columns = new ArrayObject($virtual_columns);
                
                $this->hydration_renderers = $cm->getRowRenderers();
            }
        }
        $this->hydrate_options_initialized = true;
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
        $row = $this->zfResultSet->current();

        if (!$this->hydrate_options_initialized) {
            $this->initColumnModelHydration($row);
        }
        
        // 1. If virtual columns are in use, let's add them to the row
        //    definition
        foreach($this->hydration_virtual_columns as $virtual) {
                // initialize virtual columns
                $row->offsetSet($virtual, null);
        }

        // 2. Row renderers
        foreach ($this->hydration_renderers as $renderer) {
            $renderer->apply($row);
        }        

        // 3. Formatters
        foreach ($this->hydration_formatters as $formatters) {
            foreach ($formatters['columns'] as $column) {
                $row[$column] = $formatters['formatter']->format($row[$column], $row);
            }
        }

        // 4. Process column hydration
        if ($this->hydrated_columns !== null) {
            $d = new ArrayObject();
            foreach ($this->hydrated_columns as $column) {
                $d->offsetSet($column, isset($row[$column]) ? $row[$column] : null);
            }
            $row->exchangeArray($d);
        }

        if ($this->returnType === self::TYPE_ARRAY) {
            return (array) $row;
        }
        return $row;
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

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        $valid =  $this->zfResultSet->valid();
        if (!$valid) {
            $this->hydrate_options_initialized = false;
        }
        return $valid;
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->hydrate_options_initialized = false;
        $this->zfResultSet->rewind();
    }    
    
}
