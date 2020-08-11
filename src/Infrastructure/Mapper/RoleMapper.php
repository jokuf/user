<?php


namespace Jokuf\User\Infrastructure\Mapper;


use Jokuf\User\Domain\Entity\Role;
use Jokuf\User\Domain\Entity\User;
use Jokuf\User\Infrastructure\MySqlDB;

class RoleMapper
{
    /**
     * @var MySqlDB
     */
    protected $db;
    /**
     * @var PermissionMapper
     */
    protected $permissionMapper;
    private $identityMap;

    /**
     * RoleMapper constructor.
     *
     * @param MySqlDB $db
     * @param PermissionMapper $permissionMapper
     */
    public function __construct(MySqlDB $db, PermissionMapper $permissionMapper) {

        $this->db = $db;
        $this->permissionMapper = $permissionMapper;
        $this->identityMap = [];
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
            $this->identityMap[$id] = new Role($id, $data['name'], $permissions);
        }

        return $this->identityMap[$id];
    }

    /**
     * @param int $userId
     *
     * @return Role[]
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
                $this->identityMap[$roleId] = new Role($data['id'], $data['name'], $permissions);
            }

            $roles[] = $this->identityMap[$roleId];
        }

        return $roles;
    }

    public function insert(Role $role)
    {
        $query = 'INSERT INTO `roles` (`name`) VALUES (:name)';

        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => $role->getName()]);
        $role->setId($this->db->lastInsertId());

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

        $this->identityMap[$role->getId()] = $role;

        return $role;
    }

    public function update(Role $role)
    {
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

    public function delete(Role $role): void
    {
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