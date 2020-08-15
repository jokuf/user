<?php


namespace Jokuf\User\Authorization\Factory;


use Jokuf\User\Authorization\ActivityInterface;

interface ActivityFactoryInterface
{
    public function createActivity(?int $id, string $method, string $regex): ActivityInterface;
}