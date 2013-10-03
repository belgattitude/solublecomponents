<?php
namespace Soluble\Db\Metadata\Exception;
use Soluble\Db\Metadata\Exception\ExceptionInterface;

class NoPrimaryKeyException extends \ErrorException implements ExceptionInterface
{
}
