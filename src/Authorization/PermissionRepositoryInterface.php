<?php

namespace Jokuf\User\Authorization;


interface PermissionRepositoryInterface
{
    /**
     * @param int $permId
     * @return mixed
     */
    public function findForId(int $permId);

    /**
     * @param int $roleId
     *
     * @return PermissionInterface[]
     */
    public function findForRole(int $roleId): array;

    /**
     * @param PermissionInterface $permission
     * @return PermissionInterface
     */
    public function insert(PermissionInterface $permission): PermissionInterface;

    /**
     * @param PermissionInterface $permission
     */
    public function update(PermissionInterface $permission): void;

    /**
     * @param PermissionInterface $permission
     */
    public function delete(PermissionInterface $permission): void;
}