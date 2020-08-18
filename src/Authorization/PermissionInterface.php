<?php

namespace Jokuf\User\Authorization;

interface PermissionInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function setId(int $id);
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return ActivityInterface[]
     */
    public function getActivities(): array;

    /**
     * @param ActivityInterface $activity
     */
    public function addActivity(ActivityInterface $activity):self;

    /**
     * @param ActivityInterface $activity
     */
    public function removeActivity(ActivityInterface $activity): void;
}