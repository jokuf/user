<?php


namespace Jokuf\User\Authorization\Factory;


use Jokuf\User\Authorization\PermissionInterface;

interface PermissionFactoryInterface
{
    public function createPermission(?int $id, string $name, array $activities=[]): PermissionInterface;
}