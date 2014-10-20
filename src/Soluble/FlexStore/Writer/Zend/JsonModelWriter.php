<?php

namespace Soluble\FlexStore\Writer\Zend;

use Zend\View\Model\JsonModel as ZendJsonModel;
use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Writer\AbstractSendableWriter;
use Soluble\FlexStore\Writer\SendHeaders;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\Options;
use Soluble\FlexStore\Writer\AbstractWriter;

class JsonModelWriter extends AbstractWriter
{

    /**
     *
     * @param Options $options
     * @return \Zend\View\Model\JsonModel
     */
    public function getData(Options $options = null)
    {
        $data = $this->store->getData($options);
        $d = array(
            'success' => true,
            'total' => $data->getTotalRows(),
            'start' => $data->getSource()->getOptions()->getOffset(),
            'limit' => $data->getSource()->getOptions()->getLimit(),
            'data' => $data->toArray()
        );

        if ($this->options['debug']) {
            $source = $data->getSource();
            if ($source instanceof QueryableSourceInterface) {
                $d['query'] = $source->getQueryString();
            }
        }

        $json = new ZendJsonModel($d);
        return $json;
    }
}
