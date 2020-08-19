<?php

namespace Jokuf\User\User;


interface UserServiceInterface
{
    public function save(UserInterface $user): void;

    public function find(string $email, string $password): ?UserInterface;

    public function delete(UserInterface $user): void;

    public function findByEmail(string $email): ?UserInterface;
}