<?php

namespace Jokuf\User\Authorization;

interface ActivityRepositoryInterface
{
    public function findFromId(int $activityId): ActivityInterface;

    public function findForPermission(int $permissionId): array;

    public function insert(ActivityInterface $activity): ActivityInterface;

    public function update(ActivityInterface $activity): void;

    public function delete(ActivityInterface $activity): void;
}