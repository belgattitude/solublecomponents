<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\Writer\Http\SimpleHeaders;
use DateTime;
use Soluble\FlexStore\Options;

class JsonWriter extends AbstractSendableWriter
{

    /**
     *
     * @var SimpleHeaders
     */
    protected $headers;
    
    
    /**
     *
     * @var int|string|null
     */
    protected $request_id;

    
    /**
     * Set origin request id
     * 
     * Value of request id will be returned in json encoded data
     * useful for autocompletion usage when synchronous requests
     * will return asynchronous responses.
     * 
     * @param int|string $request_id
     */
    public function setRequestId($request_id)
    {
        $this->request_id = $request_id;
    }
    
    /**
     * @param Options $options
     * @return string
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            $options = new Options();
        }

        // Get unformatted data when using json
        $options->getHydrationOptions()->disableFormatters();

        $data = $this->store->getData($options);
        $now = new DateTime();

        $d = array(
            'success' => true,
            'timestamp' => $now->format(DateTime::W3C),
            'total' => $data->getTotalRows(),
            'request_id' => $this->request_id,
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

        return json_encode($d);
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
