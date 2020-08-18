<?php


namespace Jokuf\User\Infrastructure\Repository;


use Jokuf\User\Authorization\ActivityRepositoryInterface;
use Jokuf\User\Authorization\Exception\PermissionDeniedException;
use Jokuf\User\Authorization\PermissionInterface;
use Jokuf\User\Authorization\PermissionRepositoryInterface;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @var ActivityRepository
     */
    protected $activityMapper;
    /**
     * @var PermissionInterface[]
     */
    private $identityMap;
    /**
     * @var MySqlDB
     */
    private $db;


    public function __construct(MySqlDB $db, ActivityRepositoryInterface $activityMapper)
    {
        $this->db = $db;
        $this->activityMapper = $activityMapper;
        $this->identityMap = [];
    }

    /**
     * @param int $permId
     * @return PermissionInterface|Permission|mixed
     * @throws \Exception
     */
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
     * @return PermissionInterface[]
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

    /**
     * @param PermissionInterface $permission
     * @return PermissionInterface
     */
    public function insert(PermissionInterface $permission): PermissionInterface
    {
        $query = 'INSERT INTO `permissions` (`name`) VALUES (:name);';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':name' => $permission->getName()
        ]);

        $permissionId = $this->db->lastInsertId();
        $permission->setId($permissionId);
        $this->makeActivityRelations($permission);
        $this->identityMap[$permission->getId()] = $permission;

        return $permission;
    }

    /**
     * @param PermissionInterface $permission
     * @throws PermissionDeniedException
     */
    public function update(PermissionInterface $permission): void
    {
        if (!isset($this->identityMap[$permission->getId()])) {
            throw new PermissionDeniedException("Cannot update not registered permission. ");
        }

        $this->dropAllActivityRelations($permission);

        $this->makeActivityRelations($permission);

        $query = 'UPDATE `permissions` SET `name`=:name WHERE id=:id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => $permission->getName(), ':id' => $permission->getId()]);
    }

    /**
     * @param PermissionInterface $permission
     * @throws PermissionDeniedException
     */
    public function delete(PermissionInterface $permission): void
    {
        if (!isset($this->identityMap[$permission->getId()])) {
            throw new PermissionDeniedException("Cannot delete not registered permission. ");
        }
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
     * @param PermissionInterface $permission
     *
     * @return void
     */
    protected function makeActivityRelations(PermissionInterface $permission): void
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
     * @param PermissionInterface $permission
     *
     * @return int
     */
    protected function dropAllActivityRelations(PermissionInterface $permission): int
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