<?php

namespace Jokuf\User\User;


use Jokuf\User\Authorization\PermissionInterface;

interface RoleInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * @param mixed $permission
     */
    public function addPermission(PermissionInterface $permission): RoleInterface;

    /**
     * @param mixed $permission
     */
    public function removePermission(PermissionInterface $permission): RoleInterface;
}