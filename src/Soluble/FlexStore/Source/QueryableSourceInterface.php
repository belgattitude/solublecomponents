<?php
namespace Soluble\FlexStore\Source;

interface QueryableSourceInterface
{
    /**
     * Return underlying query (sql) string
     * @return string
     */
    public function getQueryString();
}
