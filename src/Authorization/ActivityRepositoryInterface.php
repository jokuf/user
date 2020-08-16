<?php

namespace Jokuf\User\Authorization;

interface ActivityRepositoryInterface
{
    /**
     * @param int $activityId
     * @return ActivityInterface
     */
    public function findFromId(int $activityId): ActivityInterface;

    /**
     * @param int $permissionId
     * @return array
     */
    public function findForPermission(int $permissionId): array;

    /**
     * @param ActivityInterface $activity
     * @return ActivityInterface
     */
    public function insert(ActivityInterface $activity): ActivityInterface;

    /**
     * @param ActivityInterface $activity
     */
    public function update(ActivityInterface $activity): void;

    /**
     * @param ActivityInterface $activity
     */
    public function delete(ActivityInterface $activity): void;
}