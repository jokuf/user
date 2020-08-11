<?php


namespace Jokuf\User\Domain\Entity;


class Role
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var array */
    protected $permissions;

    protected $isNew;

    public function __construct(?int $id, string $name, array $permissions=[])
    {
        $this->id = $id;
        $this->name = $name;
        $this->permissions = $permissions;
    }

    public function setId(int $id)
    {
        if (null !== $this->id) {
            throw new \LogicException('Role id already set');
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
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param mixed $permission
     */
    public function addPermission(Permission $permission): self
    {
        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * @param mixed $permission
     */
    public function removePermission(Permission $permission): self
    {
        if (false !== $key = array_search($permission, $this->permissions, true)) {
            array_splice($this->permissions, $key, 1);
        }

        return $this;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}