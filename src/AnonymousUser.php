<?php


namespace Jokuf\User;


use Jokuf\User\User\RoleInterface;
use Jokuf\User\User\UserInterface;

class AnonymousUser implements UserInterface
{
    public function getIdentity()
    {
        return null;
    }

    public function getName()
    {
        return 'Anonymous';
    }

    public function getLastName()
    {
        return '';
    }

    public function getEmail()
    {
        return '';
    }

    public function getPassword()
    {
        return '';
    }

    public function verifyPassword(string $password): bool
    {
        return false;
    }

    public function isAuthenticated(): bool
    {
        return false;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function addRole(RoleInterface $role)
    {
    }

    public function removeRole(RoleInterface $role)
    {
    }
}