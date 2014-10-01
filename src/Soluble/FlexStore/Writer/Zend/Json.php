<?php

namespace Soluble\FlexStore\Writer\Zend;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;
use Zend\Json\Encoder;
use Soluble\FlexStore\Writer\AbstractSendableWriter;
use Soluble\FlexStore\Writer\Http\SendHeaders;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\Options;

class Json extends AbstractSendableWriter
{

    /**
     *
     * @var SimpleHeaders
     */
    protected $headers;

    /**
     *
     * @param Options $options
     * @return \Zend\View\Model\JsonModel
     */
    public function getData(Options $options=null)
    {
        $data = $this->source->getData($options);
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
        return Encoder::encode($d);
    }

    /**
     * Return default headers for sending store data via http 
     * @return SimpleHeaders
     */
    public function getHttpHeaders()
    {
        if ($this->headers === null) {
            $this->headers = new SimpleHeaders();
            $this->headers->setContentType('application/json', 'utf-8');
            //$this->headers->setContentDispositionType(SimpleHeaders::DIPOSITION_ATTACHEMENT);
        }
        return $this->headers;
    }

}
