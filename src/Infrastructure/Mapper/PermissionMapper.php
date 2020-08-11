<?php


namespace Jokuf\User\Infrastructure\Mapper;


use Jokuf\User\Domain\Entity\Permission;
use Jokuf\User\Domain\Entity\Role;
use Jokuf\User\Infrastructure\MySqlDB;

class PermissionMapper
{
    /**
     * @var ActivityMapper
     */
    protected $activityMapper;
    private $identityMap;
    /**
     * @var MySqlDB
     */
    private $db;

    public function __construct(MySqlDB $db, ActivityMapper $activityMapper)
    {
        $this->db = $db;
        $this->activityMapper = $activityMapper;
        $this->identityMap = [];
    }

    public function findForId(int $permId)
    {
        if (!isset($this->identityMap[$permId])) {
            $query = 'SELECT p.* FROM permissions p WHERE p.id=:permissionId';
            $stmt  = $this->db->execute($query, [':permissionId' => $permId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                throw new \Exception("Permission with id $permId not found.");
            }

            $activities = $this->activityMapper->findForPermission($permId);
            $this->identityMap[$permId] = new Permission($permId, $row['name'], $activities);
        }


        return $this->identityMap[$permId];
    }

    /**
     * @param int $roleId
     *
     * @return Permission[]
     */
    public function findForRole(int $roleId): array
    {
        $permissions = [];
        $query       = 'SELECT p.* FROM permissions p RIGHT JOIN role_permissions rp ON rp.permissionId = p.id WHERE rp.roleId = :roleId';
        $stmt        = $this->db->prepare($query);
        $stmt->execute([
            ':roleId' => $roleId
        ]);

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $permId = $data['id'];

            if (!isset($this->identityMap[$permId])) {
                $activities = $this->activityMapper->findForPermission($permId);
                $this->identityMap[$permId] = new Permission($permId, $data['name'], $activities);
            }

            $permissions[] = $this->identityMap[$permId];
        }

        return $permissions;
    }

    public function insert(Permission $permission): void
    {
        $query = 'INSERT INTO `permissions` (`name`) VALUES (:name);';
        $stmt        = $this->db->prepare($query);
        $stmt->execute([
            ':name' => $permission->getName()
        ]);

        $permission->setId($this->db->lastInsertId());

        $this->makeActivityRelations($permission);

        $this->identityMap[$permission->getId()] = $permission;
    }

    public function update(Permission $permission): void
    {
        $this->dropAllActivityRelations($permission);

        $this->makeActivityRelations($permission);

        $query = 'UPDATE `permissions` SET `name`=:name WHERE id=:id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => $permission->getName(), ':id' => $permission->getId()]);
    }

    public function delete(Permission $permission): void
    {
        // recreate all relations
        $query = 'DELETE FROM permission_activities WHERE permissionId=:permissionId';
        $stmt  = $this->db->prepare($query);

        $stmt->execute([
            ':permissionId' => $permission->getId()
        ]);

        // recreate all relations
        $query = 'DELETE FROM permissions WHERE id=:permissionId';
        $stmt  = $this->db->prepare($query);

        $stmt->execute([
            ':permissionId' => $permission->getId()
        ]);

        // recreate all relations
        $query = 'DELETE FROM role_permissions WHERE id=:permissionId';
        $stmt  = $this->db->prepare($query);

        $stmt->execute([
            ':permissionId' => $permission->getId()
        ]);


        unset($this->identityMap[$permission->getId()]);

    }

    /**
     * @param Permission $permission
     *
     * @return void
     */
    protected function makeActivityRelations(Permission $permission): void
    {
        foreach ($permission->getActivities() as $activity) {
            if (null === $activity->getId()) {
                $this->activityMapper->insert($activity);
            }

            $stmt = $this->db->prepare('INSERT INTO permission_activities (permissionId, activityId) VALUES (:permissionId, :activityId)');
            $stmt->execute([
                ':permissionId' => $permission->getId(),
                ':activityId'   => $activity->getId()
            ]);
        }
    }

    /**
     * @param Permission $permission
     *
     * @return int
     */
    protected function dropAllActivityRelations(Permission $permission): int
    {
        // recreate all relations
        $query = 'DELETE FROM permission_activities WHERE permissionId=:permissionId';
        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ':permissionId' => $permission->getId()
        ]);

        return $stmt->rowCount();
    }
}