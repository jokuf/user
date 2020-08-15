<?php


use Jokuf\User\Infrastructure\Factory\ActivityFactory;
use Jokuf\User\Infrastructure\Factory\PermissionFactotry;
use Jokuf\User\Infrastructure\Factory\RoleFactory;
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
    private $mapper;
    /**
     * @var ActivityFactory
     */
    private $activityFactory;
    /**
     * @var PermissionFactotry
     */
    private $permissionFactory;
    /**
     * @var RoleFactory
     */
    private $roleFactory;

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
        $this->activityFactory = new ActivityFactory();
        $this->permissionFactory = new PermissionFactotry();
        $this->roleFactory = new RoleFactory();
        $this->mapper = new RoleRepository(
            self::$db, new PermissionRepository(
                self::$db, new ActivityRepository(
                    self::$db, $this->activityFactory),
                $this->permissionFactory),
            $this->roleFactory);
    }

    public function testCreate() {
        $role = $this->roleFactory->createRole(null, 'Create game');
        $role = $this->mapper->insert($role);


        $this->assertEquals(1, $role->getId());
    }

    public function testRead()
    {
        $role = $this->mapper->findForId(1);

        $this->assertEquals(1, $role->getId());
    }

    public function testUpdate() {
        $role = $this->mapper->findForId(1);

        $updatedRole = $this->roleFactory->createRole(1, 'sadsadfa');
        $this->mapper->update($updatedRole);

        $stmt = self::$db->execute('SELECT * FROM roles WHERE id=:id', [":id"=>  $updatedRole->getId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEquals($role->getName(), $row['name']);
        $this->assertEquals($updatedRole->getName(), $row['name']);
    }

    public function testDelete() {
        $this->expectException(Exception::class);

        $permisssion = $this->mapper->findForId(1);

        $this->mapper->delete($permisssion);

        $this->mapper->findForId($permisssion->getId());
    }

    public function testCreateRoleAndAttachPermissionWithSomeDummyActivities() {
        $role = $this->roleFactory->createRole(null, 'Admin');

        $permission = $this->permissionFactory->createPermission(null, 'Create game');
        $permission
            ->addActivity($this->activityFactory->createActivity(null, 'POST', ''));

        $permission2 = $this->permissionFactory->createPermission(null, 'Delete game');
        $permission2
            ->addActivity($this->activityFactory->createActivity(null, 'POST', ''));

        $role
            ->addPermission($permission)
            ->addPermission($permission2);

        $role = $this->mapper->insert($role);

        $stmt = self::$db->execute('SELECT * FROM role_permissions WHERE roleId=:id', [":id"=> $role->getId()]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $rows);
    }
}
