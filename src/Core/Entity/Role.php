<?php


namespace Jokuf\User\Core\Entity;


use Jokuf\Contract\Authorization\PermissionInterface;
use Jokuf\Contract\Authorization\RoleInterface;

class Role implements RoleInterface
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var array */
    protected $permissions;

    public function __construct(?int $id, string $name, array $permissions=[])
    {
        $this->id = $id;
        $this->name = $name;
        $this->permissions = $permissions;
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
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param mixed $permission
     * @return RoleInterface
     */
    public function addPermission(PermissionInterface $permission): RoleInterface
    {
        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * @param mixed $permission
     * @return RoleInterface
     */
    public function removePermission(PermissionInterface $permission): RoleInterface
    {
        if (false !== $key = array_search($permission, $this->permissions, true)) {
            array_splice($this->permissions, $key, 1);
        }

        return $this;
    }
}