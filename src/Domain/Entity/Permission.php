<?php


namespace Jokuf\User\Domain\Entity;


class Permission
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var Activity[] */
    protected $activities;

    public function __construct(?int $id, string $name, array $activities=[])
    {
        $this->id = $id;
        $this->name = $name;
        $this->activities = $activities;
    }


    public function setId(int $id)
    {
        if (null !== $this->id) {
            throw new \LogicException('Permission id already set');
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
     * @return Activity[]
     */
    public function getActivities(): array
    {
        return $this->activities;
    }

    /**
     * @param Activity $activity
     */
    public function addActivity(Activity $activity)
    {
        $this->activities[] = $activity;
    }

    /**
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity): void
    {
        if (false !== $key = array_search($activity, $this->activities, true)) {
            array_splice($this->activities, $key, 1);
        }
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}