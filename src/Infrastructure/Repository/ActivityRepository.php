<?php


namespace Jokuf\User\Infrastructure\Repository;


use Jokuf\User\Activity;
use Jokuf\User\Authorization\ActivityInterface;
use Jokuf\User\Authorization\ActivityRepositoryInterface;
use Jokuf\User\Authorization\Exception\PermissionDeniedException;
use Jokuf\User\Infrastructure\MySqlDB;

class ActivityRepository implements ActivityRepositoryInterface
{
    /**
     * @var MySqlDB
     */
    private $db;

    private $identityMap;


    public function __construct(MySqlDB $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $activityId
     * @return ActivityInterface
     * @throws \Exception
     */
    public function findFromId(int $activityId): ActivityInterface
    {
        if (isset($this->identityMap[$activityId])) {
            return $this->identityMap[$activityId];
        }

        $query = 'SELECT * FROM activities WHERE id=:id';

        $stmt = $this->db->execute($query, [':id' => $activityId]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new \Exception('Activity not found');
        }

        $activity =  new Activity($activityId, $row['method'], $row['regex']);

        $this->identityMap[$activityId] = $activity;

        return $activity;
    }

    /**
     * @param int $permissionId
     * @return array
     */
    public function findForPermission(int $permissionId): array
    {
        $activities = [];
        $query = '
            SELECT a.* FROM activities a 
            RIGHT JOIN permission_activities pa ON pa.activityId = a.id
            WHERE 
                pa.permissionId = :permissionId';

        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':permissionId' => $permissionId
        ]);

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $actId = $data['id'];

            if (!isset($this->identityMap[$actId])) {
                $this->identityMap[$actId] = new Activity($actId, $data['method'], $data['regex']);
            }

            $activities[] = $this->identityMap[$actId];
        }

        return $activities;
    }

    /**
     * @param ActivityInterface $activity
     * @return ActivityInterface
     */
    public function insert(ActivityInterface $activity): ActivityInterface
    {
        $query = "
            INSERT INTO `activities` 
                (`method`, `regex`) 
            VALUES 
                (:method, :regex);";

        $activityId = $this->db->insert($query, [
           ':method' => $activity->getMethod(),
           ':regex' => $activity->getRegex()
        ]);

        $activity->setId($activityId);
        $this->identityMap[$activity->getId()] = $activity;

        return $activity;
    }

    /**
     * @param ActivityInterface $activity
     * @throws \Exception
     */
    public function update(ActivityInterface $activity): void
    {
        if (!isset($this->identityMap[$activity->getId()])) {
            throw new \Exception("Cannot update not registered activity. ");
        }

        $query = '
            UPDATE 
                `activities` 
            SET 
                `method`=:method,
                `regex`=:regex 
            WHERE 
                id=:id';

        $this->db->execute($query, [
            ':method' => $activity->getMethod(),
            ':regex' => $activity->getRegex(),
            ':id' => $activity->getId()
        ]);

    }

    /**
     * @param ActivityInterface $activity
     * @throws PermissionDeniedException
     */
    public function delete(ActivityInterface $activity): void
    {
        if (!isset($this->identityMap[$activity->getId()])) {
            throw new PermissionDeniedException("Cannot delete not registered activity. ");
        }
        $query = '
            DELETE FROM 
                permission_activities 
            WHERE 
                activityId=:activityId
        ';

        $this->db->execute($query,[':activityId' => $activity->getId()]);

        $query = '
            DELETE FROM 
                activities 
            WHERE 
                id=:activityId
        ';

        $this->db->execute($query, [':activityId' => $activity->getId()]);

        unset($this->identityMap[$activity->getId()]);
    }
}