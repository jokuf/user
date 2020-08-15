<?php


namespace Jokuf\User\Infrastructure\Factory;


use Jokuf\User\Authorization\PermissionInterface;
use Jokuf\User\Permission;

class PermissionFactotry implements \Jokuf\User\Authorization\Factory\PermissionFactoryInterface
{

    public function createPermission(?int $id, string $name, array $activities = []): PermissionInterface
    {
        return new Permission($id, $name, $activities);
    }
}