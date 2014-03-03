<?php
/**
 *
 * @author Vanvelthem Sébastien
 */
namespace Soluble\FlexStore\Source;

use Soluble\FlexStore\Options;


abstract class AbstractSource implements SourceInterface
{
    /**
     * @var Options
     */
    protected $options;


    /**
     * columns to retrieve when calling getData
     * @var array
     */
    protected $columns;

    /**
     * @var string|int
     */
    protected $identifier;


    /**
     *
     * @param array $columns
     * @return AbstractSource
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }


    /**
     *
     * @return AbstractSource
     */
    public function unsetColumns()
    {
        $this->columns = null;
        return $this;
    }

    /**
     *
     * @return Options
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
     * @return Soluble\FlexStore\ResultSet\ResultSet
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
     *
     * @return string
     */
    abstract public function getQueryString();

}
