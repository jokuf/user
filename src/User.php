<?php


namespace Jokuf\User;


use Jokuf\User\User\RoleInterface;
use Jokuf\User\User\UserInterface;

class User implements UserInterface
{
    private const BCRYPT_DEFAULT_COST = 12;

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

    /** @var Role[] */
    protected $roles;

    /**
     * UserEntity constructor.
     * @param int|null $id
     * @param string $email
     * @param string $name
     * @param string $lastName
     * @param string $password
     * @param Role[] $roles
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

    public function getIdentity()
    {
        return $this->id;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLastName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isAuthenticated():bool
    {
        return true;
    }

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

    public function getPassword()
    {
        return $this->password;
    }
}