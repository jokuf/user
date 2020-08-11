<?php


namespace Jokuf\User\Domain\Entity;


class Activity
{
    protected $id;
    protected $url;
    protected $method;
    protected $regex;

    /**
     * Activity constructor.
     *
     * @param $id
     * @param $url
     * @param $method
     * @param $regex
     */
    public function __construct($id, $method, $regex)
    {
        $this->id = $id;
        $this->method = $method;
        $this->regex = $regex;
    }

    public function setId(int $id)
    {
        if (null !== $this->id) {
            throw new \LogicException('Activity id already set');
        }

        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /**
     * @param mixed $regex
     */
    public function setRegex($regex): void
    {
        $this->regex = $regex;
    }
}