<?php


namespace Jokuf\User;


use Jokuf\User\Authorization\ActivityInterface;

class Activity implements ActivityInterface
{
    protected $id;
    protected $method;
    protected $regex;

    /**
     * Activity constructor.
     *
     * @param $id
     * @param $method
     * @param $regex
     */
    public function __construct(?int $id, string $method, string $regex)
    {
        $this->id = $id;
        $this->method = $method;
        $this->regex = $regex;
    }

    /**
     * @return mixed
     */
    public function getId():?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMethod():string
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getRegex():string
    {
        return $this->regex;
    }
}