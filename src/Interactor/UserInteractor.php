<?php


namespace Jokuf\User\Interactor;


use Jokuf\Contract\User\UserInterface;
use Jokuf\Contract\User\UserServiceInterface;
use Jokuf\User\Exception\UserNotFoundException;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use Jokuf\User\Infrastructure\Repository\PermissionRepository;
use Jokuf\User\Infrastructure\Repository\RoleRepository;
use Jokuf\User\Infrastructure\Repository\UserRepository;

class UserInteractor implements UserServiceInterface
{
    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var MySqlDB
     */
    private $db;

    public function __construct(MySqlDB $db)
    {
        $this->db = $db;

        $this->repository =
            new UserRepository(
                $this->db,
                new RoleRepository(
                    $this->db,
                    new PermissionRepository(
                        $this->db,
                        new ActivityRepository(
                            $this->db
                        )
                    )));

    }

    public function save(UserInterface $user): void
    {
        $this->db->transactionStart();
        try {

            if (null === $user->getIdentity()) {
                $this->repository->insert($user);
            } else {
                $this->repository->update($user);
            }

            $this->db->transactioCommit();
        } catch (\Throwable $t) {
            $this->db->transactionRevert();

            throw new \Exception($t);
        }

    }

    public function delete(UserInterface $user): void
    {
        $this->repository->delete($user);
    }

    /**
     * @param string $email
     * @param string $password
     * @return UserInterface
     */
    public function find(string $email, string $password): ?UserInterface
    {
        try {
            $user = $this->repository->findByEmail($email);

            if (true === $user->verifyPassword($password)) {
                return $user;
            }
        } catch (UserNotFoundException $e) {
        }

        return null;
    }


    public function findByEmail(string $email): ?UserInterface
    {
        try {
            return $this->repository->findByEmail($email);
        } catch (UserNotFoundException $e) {

        }

        return null;
    }
}