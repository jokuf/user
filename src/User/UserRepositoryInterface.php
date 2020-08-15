<?php

namespace Jokuf\User\User;


use Jokuf\User\User\Exception\UserNotFoundException;
use Jokuf\User\User\Exception\UserShoildBeTakenFromTheRepositoryFirst;

/**
 * Class UserRepository
 * @package Jokuf\User\Infrastructure\Repository
 */
interface UserRepositoryInterface
{
    /**
     * @param string $email
     * @return UserInterface
     * @throws UserNotFoundException
     */
    public function findByEmail(string $email): UserInterface;

    /**
     * @param int $id
     * @return UserInterface
     * @throws UserNotFoundException
     */
    public function findById(int $id): UserInterface;

    /**
     * @param UserInterface $user
     */
    public function insert(UserInterface $user): UserInterface;

    /**
     * @param UserInterface $user
     * @throws UserShoildBeTakenFromTheRepositoryFirst
     */
    public function update(UserInterface $user): void;

    /**
     * @param UserInterface $user
     */
    public function delete(UserInterface $user):void;
}