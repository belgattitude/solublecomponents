<?php

namespace Soluble\FlexStore\Writer\Zend;

use Zend\View\Model\JsonModel as ZendJsonModel;
use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Writer\AbstractSendableWriter;
use Soluble\FlexStore\Writer\SendHeaders;
use Soluble\FlexStore\Source\QueryableSourceInterface;

class JsonModel extends AbstractWriter
{

    /**
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getData()
    {
        $data = $this->source->getData();
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
