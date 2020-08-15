<?php


namespace Jokuf\User\User\Factory;


use Jokuf\User\User\RoleInterface;

interface RoleFactoryInterface
{
    public function createRole(?int $id, string $name, array $permissions=[]): RoleInterface;
}