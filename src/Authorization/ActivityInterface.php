<?php

namespace Jokuf\User\Authorization;

interface ActivityInterface
{
    /**
     * @return mixed
     */
    public function getId(): ?int;

    /**
     * @return mixed
     */
    public function getMethod(): string;

    /**
     * @return mixed
     */
    public function getRegex(): string;
}