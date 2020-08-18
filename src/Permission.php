<?php


namespace Jokuf\User;


use Jokuf\User\Authorization\ActivityInterface;
use Jokuf\User\Authorization\PermissionInterface;

class Permission implements PermissionInterface
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var ActivityInterface[] */
    protected $activities;

    public function __construct(?int $id, string $name, array $activities=[])
    {
        $this->id = $id;
        $this->name = $name;
        $this->activities = $activities;
    }

    public function setId(int $id) {
        if ($this->id) {
            throw new \UnexpectedValueException('Id already set');
        }

        $this->id = $id;
    }
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ActivityInterface[]
     */
    public function getActivities(): array
    {
        return $this->activities;
    }

    /**
     * @param ActivityInterface $activity
     * @return PermissionInterface
     */
    public function addActivity(ActivityInterface $activity): PermissionInterface
    {
        $this->activities[] = $activity;

        return $this;
    }

    /**
     * @param ActivityInterface $activity
     */
    public function removeActivity(ActivityInterface $activity): void
    {
        if (false !== $key = array_search($activity, $this->activities, true)) {
            array_splice($this->activities, $key, 1);
        }
    }
}