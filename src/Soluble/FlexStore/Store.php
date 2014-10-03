<?php
/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore;
use Soluble\FlexStore\Source;
use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Options;

class Store implements FlexStoreInterface
{
    /**
     *
     * @var Source\SourceInterface
     */
    protected $source;


    /**
     *
     * @param Source\SourceInterface $source
     */
    public function __construct(Source\SourceInterface $source)
    {
        $this->source = $source;
    }


    /**
     *
     * @return Source\SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }
    
    /**
     * Return the underlying store data as a resultset
     * 
     * @throws Exception\EmptyQueryException when query is empty
     * @throws Exception\ErrorException whenever an error occured
     * @param Options $options
     * @return Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(Options $options=null)
    {
        return $this->source->getData($options);
    }
    
    
    /**
     * 
     * @return Soluble\FlexStore\Column\ColumnModel
     */
    public function getColumnModel()
    {
        return $this->source->getColumnModel();
    }

}
