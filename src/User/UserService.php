<?php

namespace Jokuf\User\User;


interface UserService
{
    public function create(string $email, string $password, string $name, string $lastName);

    public function find(string $email, string $password): UserInterface;

    public function isAuthenticated(UserInterface $user);
}