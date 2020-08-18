<?php


namespace Jokuf\User;


use Jokuf\User\User\RoleInterface;
use Jokuf\User\User\UserInterface;

class User implements UserInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $email;

    /** @var string */
    protected $password;

    /** @var string|null */
    protected $token;

    /** @var RoleInterface[] */
    protected $roles;

    /**
     * UserEntity constructor.
     * @param int|null $id
     * @param string $email
     * @param string $name
     * @param string $lastName
     * @param string $password
     * @param RoleInterface[] $roles
     */
    public function __construct(?int $id, string $email, string $name, string $lastName, string $password, array $roles=[])
    {
        $this->name = $name;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->id = $id;
    }

    public function setId(int $id) {
        if ($this->id) {
            throw new \UnexpectedValueException('Id already set');
        }

        $this->id = $id;
    }

    /**
     * @return int|mixed|null
     */
    public function getIdentity()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * @return bool
     */
    public function isAuthenticated():bool
    {
        return true;
    }

    /**
     * @return RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param RoleInterface $role
     */
    public function addRole(RoleInterface $role)
    {
        $this->roles[] = $role;
    }

    /**
     * @param RoleInterface $role
     */
    public function removeRole(RoleInterface $role)
    {
        if (false !== $key = array_search($role, $this->roles, true)) {
            array_splice($this->roles, $key, 1);
        }
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}