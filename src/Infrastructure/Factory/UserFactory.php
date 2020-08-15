<?php


namespace Jokuf\User\Infrastructure\Factory;


use Jokuf\User\User;
use Jokuf\User\User\Factory\UserFactoryInterface;
use Jokuf\User\User\UserInterface;

class UserFactory implements UserFactoryInterface
{
    public function createUser(
        string $email,
        string $name,
        string $lastName,
        string $password,
        array $roles=[]
    ): UserInterface
    {
        return new User(null, $email, $name, $lastName, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $roles);
    }

    public function loadUser(int $id, string $email, string $name, string $lastName, string $password, array $roles = []): UserInterface
    {
        return new User($id, $email, $name, $lastName, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $roles);
    }
}