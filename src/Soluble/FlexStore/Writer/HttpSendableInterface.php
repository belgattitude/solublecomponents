<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;

interface HttpSendableInterface
{
    /**
     * Return (default) headers for sending store data via http
     * @return \Soluble\FlexStore\Writer\Http\SimpleHeaders
     */
    public function getHttpHeaders();

    /**
     * Send the store data via http
     *
     * @param SimpleHeaders $headers
     * @param boolean $die_after
     */
    public function send(SimpleHeaders $headers = null, $die_after = true);
}
