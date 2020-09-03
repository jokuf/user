<?php


namespace Jokuf\User\Exception;


use Throwable;

class UserShoildBeTakenFromTheRepositoryFirst extends \Exception
{
    public function __construct($message = "User not found! User should be get from repository!", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}