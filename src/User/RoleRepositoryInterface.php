<?php

namespace Jokuf\User\User;

interface RoleRepositoryInterface
{
    public function findForId(int $id);

    /**
     * @param int $userId
     *
     * @return RoleInterface[]
     */
    public function findForUser(int $userId): array;

    public function insert(RoleInterface $role): RoleInterface;

    public function update(RoleInterface $role);

    public function delete(RoleInterface $role): void;
}