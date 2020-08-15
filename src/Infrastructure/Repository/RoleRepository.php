<?php


namespace Jokuf\User\Infrastructure\Repository;


use Jokuf\User\Authorization\Exception\PermissionDeniedException;
use Jokuf\User\Infrastructure\Factory\RoleFactory;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Role;
use Jokuf\User\User\RoleInterface;

class RoleRepository
{
    /**
     * @var MySqlDB
     */
    protected $db;
    /**
     * @var PermissionRepository
     */
    protected $permissionMapper;
    private $identityMap;
    /**
     * @var RoleFactory
     */
    private $factory;

    /**
     * RoleRepository constructor.
     *
     * @param MySqlDB $db
     * @param PermissionRepository $permissionMapper
     */
    public function __construct(MySqlDB $db, PermissionRepository $permissionMapper, RoleFactory $factory) {

        $this->db = $db;
        $this->permissionMapper = $permissionMapper;
        $this->identityMap = [];
        $this->factory = $factory;
    }

    public function findForId(int $id) {
        if (!isset($this->identityMap[$id])) {
            $query = 'SELECT * FROM roles r WHERE r.id = :id';

            $stmt  = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $id
            ]);

            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                throw new \Exception('Role not found');
            }

            $permissions = $this->permissionMapper->findForRole($id);
            $this->identityMap[$id] = $this->factory->createRole($id, $data['name'], $permissions);
        }

        return $this->identityMap[$id];
    }

    /**
     * @param int $userId
     *
     * @return RoleInterface[]
     */
    public function findForUser(int $userId): array
    {
        $roles = [];
        $query = 'SELECT r.* FROM roles r RIGHT JOIN user_roles ur ON ur.roleId = r.id  WHERE ur.userId = :userId';

        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':userId' => $userId
        ]);

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $roleId = $data['id'];
            if (!isset($this->identityMap[$roleId])) {
                $permissions = $this->permissionMapper->findForRole($data['id']);
                $this->identityMap[$roleId] = $this->factory->createRole($data['id'], $data['name'], $permissions);
            }

            $roles[] = $this->identityMap[$roleId];
        }

        return $roles;
    }

    public function insert(RoleInterface $role): RoleInterface
    {
        if (isset($this->identityMap[$role->getId()])) {
            throw new PermissionDeniedException();
        }

        $query = 'INSERT INTO `roles` (`name`) VALUES (:name)';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => $role->getName()]);
        $roleId = $this->db->lastInsertId();
        $role = $this->factory->createRole($roleId, $role->getName(), $role->getPermissions());
        $this->identityMap[$role->getId()] = $role;

        $query = 'DELETE FROM role_permissions WHERE roleId=:roleId';
        $stmt = $this->db->prepare($query);
        $stmt->execute([ ':roleId' => $role->getId()]);

        foreach ($role->getPermissions() as $permission) {
            if (null === $permission->getId()) {
                $role->removePermission($permission);
                $permission = $this->permissionMapper->insert($permission);
                $role->addPermission($permission);
            }

            $stmt = $this->db->prepare('INSERT INTO role_permissions (roleId, permissionId) VALUES (:roleId, :permissionId)');
            $stmt->execute([
                'roleId' => $role->getId(),
                'permissionId' => $permission->getId()
            ]);
        }

        return $role;
    }

    public function update(RoleInterface $role)
    {
        if (!isset($this->identityMap[$role->getId()])) {
            throw new \Exception("Cannot update not registered role. ");
        }

        $query = 'UPDATE `roles` SET `name`=:name WHERE id=:id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => $role->getName(), ':id' => $role->getId()]);


        $query = 'DELETE FROM role_permissions WHERE roleId=:roleId';
        $stmt = $this->db->prepare($query);
        $stmt->execute([ ':roleId' => $role->getId()]);

        foreach ($role->getPermissions() as $permission) {
            if (null === $permission->getId()) {
                $this->permissionMapper->insert($permission);
            }

            $stmt = $this->db->prepare('INSERT INTO role_permissions (roleId, permissionId) VALUES (:roleId, :permissionId)');
            $stmt->execute([
                'roleId' => $role->getId(),
                'permissionId' => $permission->getId()
            ]);
        }
    }

    public function delete(RoleInterface $role): void
    {
        if (!isset($this->identityMap[$role->getId()])) {
            throw new \Exception("Cannot delete not registered role. ");
        }
        // recreate all relations
        $query = 'DELETE FROM user_roles WHERE roleId=:roleId';
        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':roleId' => $role->getId()
        ]);

        $query = 'DELETE FROM roles WHERE id=:roleId';
        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':roleId' => $role->getId()
        ]);

        unset($this->identityMap[$role->getId()]);
    }
}