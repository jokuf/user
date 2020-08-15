<?php


namespace Jokuf\User\Infrastructure\Factory;


use Jokuf\User\Activity;
use Jokuf\User\Authorization\ActivityInterface;

class ActivityFactory implements \Jokuf\User\Authorization\Factory\ActivityFactoryInterface
{

    public function createActivity(?int $id, string $method, string $regex): ActivityInterface
    {
        return new Activity($id, $method, $regex);
    }
}