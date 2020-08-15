<?php

namespace Jokuf\User\Authorization;


interface PermissionRepositoryInterface
{
    public function findForId(int $permId);

    /**
     * @param int $roleId
     *
     * @return PermissionInterface[]
     */
    public function findForRole(int $roleId): array;

    public function insert(PermissionInterface $permission): PermissionInterface;

    public function update(PermissionInterface $permission): void;

    public function delete(PermissionInterface $permission): void;
}