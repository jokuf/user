<?php


use Jokuf\User\Domain\Entity\Activity;
use Jokuf\User\Domain\Entity\Permission;
use Jokuf\User\Domain\Entity\Role;
use Jokuf\User\Infrastructure\Mapper\ActivityMapper;
use Jokuf\User\Infrastructure\Mapper\PermissionMapper;
use Jokuf\User\Infrastructure\Mapper\RoleMapper;
use Jokuf\User\Infrastructure\MySqlDB;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var RoleMapper
     */
    private $mapper;

    public static function setUpBeforeClass(): void
    {
        $config = [
            'user' => 'root',
            'pass' => '',
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
        $this->mapper = new RoleMapper(self::$db, new PermissionMapper(self::$db, new ActivityMapper(self::$db)));
    }

    public function testCreate() {
        $role = new Role(null, 'Create game');
        $this->mapper->insert($role);


        $this->assertEquals(1, $role->getId());
    }

    public function testRead()
    {
        $role = $this->mapper->findForId(1);

        $this->assertEquals(1, $role->getId());
    }

    public function testUpdate() {
        $role = $this->mapper->findForId(1);

        $name = $role->getName();

        $role->setName('jokuf');
        $this->mapper->update($role);

        $stmt = self::$db->execute('SELECT * FROM roles WHERE id=:id', [":id"=>  $role->getId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEquals($name, $row['name']);
        $this->assertEquals($role->getName(), $row['name']);
    }

    public function testDelete() {
        $this->expectException(Exception::class);

        $permisssion = $this->mapper->findForId(1);

        $this->mapper->delete($permisssion);

        $this->mapper->findForId($permisssion->getId());
    }

    public function testCreateRoleAndAttachPermissionWithSomeDummyActivities() {
        $role = new Role(null, 'Admin');

        $permission = new Permission(null, 'Create game');
        $permission
            ->addActivity(new Activity(null,'/adapter/slot/create', 'POST', ''));

        $permission2 = new Permission(null, 'Delete game');
        $permission2
            ->addActivity(new Activity(null,'/adapter/slot/delete', 'POST', ''));

        $role
            ->addPermission($permission)
            ->addPermission($permission2);

        $this->mapper->insert($role);

        $stmt = self::$db->execute('SELECT * FROM role_permissions WHERE roleId=:id', [":id"=> $role->getId()]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertCount(2, $rows);


        $stmt = self::$db->execute('SELECT * FROM permission_activities WHERE permissionId=:id', [":id"=>  $permission->getId()]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertCount(1, $rows);

        $stmt = self::$db->execute('SELECT * FROM permission_activities WHERE permissionId=:id', [":id"=>  $permission2->getId()]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertCount(1, $rows);

    }
}