<?php


namespace Jokuf\User\User\Factory;


use Jokuf\User\User\UserInterface;

interface UserFactoryInterface
{
    public function createUser(string $email, string $name, string $lastName, string $password, array $roles=[]): UserInterface;
    public function loadUser(int $id, string $email, string $name, string $lastName, string $password, array $roles=[]): UserInterface;
}