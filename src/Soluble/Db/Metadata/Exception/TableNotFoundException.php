<?php
namespace Soluble\Db\Metadata\Exception;
use Soluble\Db\Metadata\Exception\ExceptionInterface;

class TableNotFoundException extends \ErrorException implements ExceptionInterface
{
}
