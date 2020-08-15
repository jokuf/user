<?php


use Jokuf\User\Infrastructure\Factory\ActivityFactory;
use Jokuf\User\Infrastructure\Factory\PermissionFactotry;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use Jokuf\User\Infrastructure\Repository\PermissionRepository;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var PermissionRepository
     */
    private $mapper;
    /**
     * @var ActivityRepository
     */
    private $activityMapper;
    /**
     * @var PermissionFactotry
     */
    private $permissionFactory;
    /**
     * @var ActivityFactory
     */
    private $activityFactory;

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
        $this->activityMapper = new ActivityRepository(self::$db, $this->activityFactory);
        $this->mapper = new PermissionRepository(self::$db, $this->activityMapper, $this->permissionFactory);
    }


    public function testCreate() {
        $permission = $this->permissionFactory->createPermission(null, 'Create game');
        $permission = $this->mapper->insert($permission);

        $this->assertEquals(1, $permission->getId());
    }

    public function testRead()
    {
        $permission = $this->mapper->findForId(1);

        $this->assertEquals(1, $permission->getId());
    }

    public function testUpdate() {
        $permission = $this->mapper->findForId(1);

        $updated = $this->permissionFactory->createPermission($permission->getId(), 'jokuf');

        $this->mapper->update($updated);

        $stmt = self::$db->execute('SELECT * FROM permissions WHERE id=:id', [":id"=>  $permission->getId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($updated->getName(), $row['name']);

    }

    public function testDelete() {
        $this->expectException(Exception::class);

        $permisssion = $this->mapper->findForId(1);

        $this->mapper->delete($permisssion);

        $this->mapper->findForId($permisssion->getId());
    }

    public function testCreatePermissionAndAttachNewlyCreatedActivities() {
        $activity1  = $this->activityFactory->createActivity(null, 'POST', '/regex1');
        $activity2  = $this->activityFactory->createActivity(null, 'GET', '/regex2');
        $activity3  = $this->activityFactory->createActivity(null, 'DELETE', '/regex3');
        $activity4  = $this->activityFactory->createActivity(null, 'PUT', '/regex3');

        $permission = $this->permissionFactory->createPermission(null, 'Can enter', [$activity1, $activity2, $activity3, $activity4]);

        $this->activityMapper->insert($activity4);
        $permission = $this->mapper->insert($permission);

        $stmt = self::$db->execute('SELECT * FROM permission_activities WHERE permissionId=:id', [":id"=>  $permission->getId()]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(4, $rows);
    }
}
