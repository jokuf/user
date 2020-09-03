<?php


use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use Jokuf\User\Infrastructure\Repository\PermissionRepository;
use Jokuf\User\Infrastructure\Repository\RoleRepository;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var RoleRepository
     */
    private $repository;

    public static function setUpBeforeClass(): void
    {
        $config = [
            'user' => 'jokuf',
            'pass' => 'Admin00',
        ];

        $schema = file_get_contents(dirname(__DIR__).'/schema.sql');

        self::$db = new MySqlDB($config);

        self::$db->query("CREATE DATABASE IF NOT EXISTS test_user_db");

        self::$db->query("USE test_user_db");

        self::$db->query($schema);
    }

    /**
     * @afterClass
     */
    public static function tearDownCleanUpDatabase(): void
    {
        self::$db->query("DROP DATABASE IF EXISTS test_user_db");
        self::$db = null;
    }

    public function setUp(): void
    {
        $this->repository = new RoleRepository(
            self::$db, new PermissionRepository(
                self::$db, new ActivityRepository(
                    self::$db)));
    }

    public function testCreate() {
        $role = new \Jokuf\User\Role(null, 'Create game');
        $role = $this->repository->insert($role);


        $this->assertEquals(1, $role->getId());
    }

    public function testRead()
    {
        $role = $this->repository->findForId(1);

        $this->assertEquals(1, $role->getId());
    }

    public function testUpdate() {
        $role = $this->repository->findForId(1);

        $updatedRole = new \Jokuf\User\Role(1, 'sadsadfa');
        $this->repository->update($updatedRole);

        $stmt = self::$db->execute('SELECT * FROM roles WHERE id=:id', [":id"=>  $updatedRole->getId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEquals($role->getName(), $row['name']);
        $this->assertEquals($updatedRole->getName(), $row['name']);
    }

    public function testDelete() {
        $this->expectException(Exception::class);

        $permisssion = $this->repository->findForId(1);

        $this->repository->delete($permisssion);

        $this->repository->findForId($permisssion->getId());
    }

    public function testCreateRoleAndAttachPermissionWithSomeDummyActivities() {
        $role = new \Jokuf\User\Role(null, 'Admin');

        $permission = new \Jokuf\User\Permission(null, 'Create game');
        $permission
            ->addActivity(new \Jokuf\User\Activity(null, 'POST', ''));

        $permission2 = new \Jokuf\User\Permission(null, 'Delete game');
        $permission2
            ->addActivity(new \Jokuf\User\Activity(null, 'POST', ''));

        $role
            ->addPermission($permission)
            ->addPermission($permission2);

        $role = $this->repository->insert($role);

        $stmt = self::$db->execute('SELECT * FROM role_permissions WHERE roleId=:id', [":id"=> $role->getId()]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $rows);
    }
}
