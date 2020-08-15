<?php


namespace Jokuf\User\User;



interface UserInterface
{
    /**
     * @return mixed|null
     */
    public function getIdentity();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return string|null
     */
    public function getLastName();

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool;

    /**
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * @return RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @param RoleInterface $role
     */
    public function addRole(RoleInterface $role);

    /**
     * @param RoleInterface $role
     */
    public function removeRole(RoleInterface $role);
}