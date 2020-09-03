<?php


namespace Jokuf\User\Exception;


use Throwable;

class UserNotFoundException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("UserEntity not found!", $code, $previous);
    }
}