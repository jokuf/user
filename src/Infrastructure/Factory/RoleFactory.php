<?php


namespace Jokuf\User\Infrastructure\Factory;


use Jokuf\User\Role;
use Jokuf\User\User\Factory\RoleFactoryInterface;
use Jokuf\User\User\RoleInterface;

class RoleFactory implements RoleFactoryInterface
{
    public function createRole(?int $id, string $name, array $permissions=[]): RoleInterface
    {
        return new Role($id, $name, $permissions);
    }
}