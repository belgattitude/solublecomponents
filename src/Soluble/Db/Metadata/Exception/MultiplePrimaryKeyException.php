<?php
namespace Soluble\Db\Metadata\Exception;

use Soluble\Db\Metadata\Exception\ExceptionInterface;

class MultiplePrimaryKeyException extends \ErrorException implements ExceptionInterface
{
}
