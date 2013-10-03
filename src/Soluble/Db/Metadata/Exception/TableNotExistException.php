<?php
namespace Soluble\Db\Metadata\Exception;
use Soluble\Db\Metadata\Exception\ExceptionInterface;

class TableNotExistException extends \ErrorException implements ExceptionInterface
{
}
