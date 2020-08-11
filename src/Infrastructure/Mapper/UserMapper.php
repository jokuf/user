<?php


namespace Jokuf\User\Infrastructure\Mapper;

use Jokuf\User\Domain\Entity\User;
use Jokuf\User\Infrastructure\MySqlDB;

class UserMapper
{
    /**
     * @var MySqlDB
     */
    protected $db;

    /** @var RoleMapper */
    protected $roleMapper;

    protected $identityMap;

    /**
     * UserMapper constructor.
     *
     * @param MySqlDB $db
     * @param RoleMapper $roleMapper
     */
    public function __construct(MySqlDB $db, RoleMapper $roleMapper)
    {
        $this->db = $db;
        $this->roleMapper = $roleMapper;
        $this->identityMap = [];
    }

    public function findById(int $id): ?User
    {
        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        $sql = "SELECT * FROM users WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new \Exception("User not found");
        }

        $roles = $this->roleMapper->findForUser($id);

        $this->identityMap[$id] = new User($id, $roles);

        return $this->identityMap[$id];
    }


    public function insert(User $user)
    {
        $q = 'INSERT INTO `users` () VALUES ();';
        $stmt = $this->db->prepare($q);
        $stmt->execute([]);
        $user->setId($this->db->lastInsertId());

        $this->saveUserRoles($user);
        $this->identityMap[$user->getId()] = $user;
    }

    public function update(User $user)
    {
        $this->saveUserRoles($user);
    }

    public function delete(User $user)
    {
        $q = 'DELETE FROM user_roles WHERE userId=:userId';
        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':userId' => $user->getId()
        ]);


        $q = 'DELETE FROM users WHERE id=:userId';

        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':userId' => $user->getId()
        ]);

        unset($this->identityMap[$user->getId()]);
    }

    private function saveUserRoles(User $user): void
    {
        $q = 'DELETE FROM user_roles WHERE userId=:userId';
        $stmt = $this->db->prepare($q);
        $stmt->execute([
            ':userId' => $user->getId()
        ]);

        foreach ($user->getRoles() as $role) {
            if (null === $role->getId()) {
                $this->roleMapper->insert($role);
            }

            $stmt = $this->db->prepare('INSERT INTO user_roles (userId, roleId) VALUES (:userId, :roleId)');
            $stmt->execute([
                ':userId' => $user->getId(),
                ':roleId' => $role->getId()
            ]);
        }
    }
}