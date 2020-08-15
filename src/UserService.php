<?php


namespace Jokuf\User;


use Jokuf\User\Infrastructure\Repository\UserRepository;
use Jokuf\User\User\Exception\UserNotFoundException;
use Jokuf\User\User\UserInterface;

class UserService implements User\UserService
{
    const BCRYPT_DEFAULT_COST = 12;
    /**
     * @var UserRepository
     */
    private $mapper;

    private $authenticatedUsers;

    public function __construct(UserRepository $repository)
    {
        $this->mapper = $repository;
        $this->authenticatedUsers = [];
    }

    public function create(string $email, string $password, string $name, string $lastName) {
        $user = $this->mapper->findByEmail($email);
        $this->mapper->insert($user);
    }

    /**
     * @param string $email
     * @param string $password
     * @return UserInterface
     */
    public function find(string $email, string $password): UserInterface
    {
        try {
            $user = $this->mapper->findByEmail($email);

            if (true === $user->verifyPassword($password)) {
                return $user;
            }
        } catch (UserNotFoundException $e) {
        }

        return new AnonymousUser();
    }

    public function isAuthenticated(UserInterface $user) {
        return in_array($user, $this->authenticatedUsers, true);
    }
}