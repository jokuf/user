<?php


namespace Jokuf\User\Domain\Entity;


class User
{
    /** @var int */
    protected $id;

    /** @var Role[] */
    protected $roles;

    /**
     * User constructor.
     *
     * @param int|null $id
     */
    public function __construct(?int $id, array $roles=[])
    {
        $this->id = $id;
        $this->roles = $roles;
    }

    public function setId(int $id)
    {
        if (null !== $this->id) {
            throw new \LogicException('User id already set');
        }

        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Role[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return array_merge(... array_map(function (Role $role) {
            return $role->getPermissions();
        }, $this->roles));
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;
    }

    /**
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        if (false !== $key = array_search($role, $this->roles, true)) {
            array_splice($this->roles, $key, 1);
        }
    }
}