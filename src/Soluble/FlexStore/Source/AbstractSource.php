<?php
/**
 *
 * @author Vanvelthem Sébastien
 */
namespace Soluble\FlexStore\Source;

use Soluble\FlexStore\Options;
use Soluble\FlexStore\Metadata\Reader\AbstractMetadataReader;
use Soluble\FlexStore\Column\ColumnModel;

abstract class AbstractSource implements SourceInterface
{
    /**
     * @var \Soluble\FlexStore\Options
     */
    protected $options;


    /**
     * Column model
     * @var ColumnModel
     */
    protected $columnModel;

    /**
     * @var string|int
     */
    protected $identifier;


    /**
     *
     * @var AbstractMetadataReader
     */
    protected $metadataReader;


/*
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }


    public function unsetColumns()
    {
        $this->columns = null;
        return $this;
    }
*/
    /**
     *
     * @return \Soluble\FlexStore\Options
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = new Options();
        }
        return $this->options;
    }


    /**
     *
     * @param Options $options
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    abstract public function getData(Options $options = null);





    /**
     * Set the primary key / unique identifier in the store
     *
     * @param string|integer $identifier column name of the primary key
     * @return AbstractSource
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Return the primary key / unique identifier in the store
     * Null if not applicable
     *
     * @return string|integer|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }


    /**
     * Return column model
     * 
     * @return ColumnModel
     */
    public function getColumnModel()
    {
        if ($this->columnModel === null) {
            $this->loadDefaultColumnModel();
        }
        return $this->columnModel;
    }
    
    /**
     * Set column model associated with the datasource
     * 
     * @param ColumnModel $columnModel
     * @return AbstractSource
     */
    public function setColumnModel(ColumnModel $columnModel)
    {
        $this->columnModel = $columnModel;
        return $this;
    }


    /**
     * Default column model initialization
     */
    abstract public function loadDefaultColumnModel();


    /**
     *
     * @param AbstractMetadataReader $metadataReader
     * @return AbstractSource
     */
    public function setMetadataReader(AbstractMetadataReader $metadataReader)
    {
        $this->metadataReader = $metadataReader;
        return $this;
    }

    /**
     *
     * @return AbstractMetadataReader
     */
    public function getMetadataReader()
    {
        return $this->metadataReader;
    }

}
