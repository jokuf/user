<?php


namespace Jokuf\User\Core\Entity;


use Jokuf\Contract\User\UserInterface;

class AnonymousUser implements UserInterface
{
    /**
     * @return int|mixed|null
     */
    public function getIdentity()
    {
        return 0;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return 'Anonymous';
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return '';
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return [];
    }

    public function setId(int $id)
    {
        // TODO: Implement setId() method.
    }
}