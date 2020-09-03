<?php


namespace Jokuf\User\Infrastructure\Repository;


use Jokuf\User\User;
use Jokuf\Contract\Authorization\RoleRepositoryInterface;
use Jokuf\Contract\User\UserInterface;
use Jokuf\Contract\User\UserRepositoryInterface;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Exception\UserNotFoundException;
use Jokuf\User\Exception\PermissionDeniedException;
use Jokuf\User\Exception\UserShoildBeTakenFromTheRepositoryFirst;

/**
 * Class UserRepository
 * @package Jokuf\User\Infrastructure\Repository
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * @var MySqlDB
     */
    protected $db;

    /** @var RoleRepository */
    protected $roleMapper;

    /**
     * @var UserInterface[]
     */
    protected $identityMap;

    /**
     * UserRepository constructor.
     *
     * @param MySqlDB $db
     * @param RoleRepositoryInterface $roleMapper
     */
    public function __construct(MySqlDB $db, RoleRepositoryInterface $roleMapper)
    {
        $this->db = $db;
        $this->roleMapper = $roleMapper;
        $this->identityMap = [];
    }

    /**
     * @param string $email
     * @return UserInterface
     * @throws UserNotFoundException
     */
    public function findByEmail(string $email): UserInterface
    {
        $q = "SELECT id FROM users WHERE email=:email";

        $stmt = $this->db->execute($q, [":email" => $email]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->findById($row['id'] ?? -1);
    }

    /**
     * @param int $id
     * @return UserInterface
     * @throws UserNotFoundException
     */
    public function findById(int $id): UserInterface
    {
        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        $sql = "SELECT * FROM users WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new UserNotFoundException();
        }

        $roles = $this->roleMapper->findForUser($id);

        $this->identityMap[$id] = new User($id, $row['email'], $row['name'], $row['lastName'], $row['password'], $roles);

        return $this->identityMap[$id];
    }


    /**
     * @param UserInterface $user
     */
    public function insert(UserInterface $user): UserInterface
    {
        if (false === $user->isAuthenticated()) {
            throw new \Exception('Anonymous user cannot be saved to the db');
        }

        $q = 'INSERT INTO `users` (`email`, `name`, `lastName`, `password`) VALUES (:email, :name, :lastName, :pass);';
        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':email' => $user->getEmail(),
            ':name' => $user->getName(),
            ':lastName' => $user->getLastName(),
            ':pass' => $user->getPassword()
        ]);

        $user->setId($this->db->lastInsertId());
        $this->saveUserRoles($user);
        $this->identityMap[$user->getIdentity()] = $user;

        return $user;
    }

    /**
     * @param UserInterface $user
     * @throws UserShoildBeTakenFromTheRepositoryFirst|PermissionDeniedException
     */
    public function update(UserInterface $user): void
    {
        if (false === $user->isAuthenticated()) {
            throw new \Exception('Anonymous user cannot be updated');
        }

        if (!isset($this->roleMapper[$user->getIdentity()])) {
            throw new UserShoildBeTakenFromTheRepositoryFirst();
        }
        $this->saveUserRoles($user);
    }

    /**
     * @param UserInterface $user
     * @throws \Exception
     */
    public function delete(UserInterface $user): void
    {
        if (false === $user->isAuthenticated()) {
            throw new \Exception('Anonymous user cannot be deleted');
        }

        if (!isset($this->identityMap[$user->getIdentity()])) {
            throw new \Exception("[IdentityMap] Cannot delete not registered user");
        }

        $q = 'DELETE FROM user_roles WHERE userId=:userId';
        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':userId' => $user->getIdentity()
        ]);


        $q = 'DELETE FROM users WHERE id=:userId';

        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':userId' => $user->getIdentity()
        ]);

        unset($this->identityMap[$user->getIdentity()]);
    }

    /**
     * @param UserInterface $user
     * @throws PermissionDeniedException
     */
    private function saveUserRoles(UserInterface $user): void
    {
        $q = 'DELETE FROM user_roles WHERE userId=:userId';
        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':userId' => $user->getIdentity()
        ]);

        foreach ($user->getRoles() as $role) {
            if (null === $role->getId()) {
                $this->roleMapper->insert($role);
            }

            $stmt = $this->db->prepare('INSERT INTO user_roles (userId, roleId) VALUES (:userId, :roleId)');
            $stmt->execute([
                ':userId' => $user->getIdentity(),
                ':roleId' => $role->getId()
            ]);
        }
    }
}