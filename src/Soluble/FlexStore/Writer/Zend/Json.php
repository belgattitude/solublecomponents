<?php
namespace Soluble\FlexStore\Writer\Zend;
use Soluble\FlexStore\Writer\AbstractWriter;
use Zend\Json\Encoder;
use Soluble\FlexStore\Writer\SendHeaders;
use Soluble\FlexStore\Source\QueryableSourceInterface;

class Json extends AbstractWriter
{
    /**
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getData()
    {
        $data = $this->source->getData();
        $d = array(
            'success'	 => true,
            'total'		 => $data->getTotalRows(),
            'start'		 => $data->getSource()->getOptions()->getOffset(),
            'limit'		 => $data->getSource()->getOptions()->getLimit(),
            'data'		 => $data->toArray()
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
     *
     * @param \Soluble\FlexStore\Writer\SendHeaders $headers
     */
    public function send(SendHeaders $headers=null)
    {
        if ($headers === null) $headers = new SendHeaders();
        ob_end_clean();
        $headers->setContentType('application/json; charset=utf-8');
        $headers->printHeaders();
        $json = $this->getData();
        echo $json;
    }

}
