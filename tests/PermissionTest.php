<?php


use Jokuf\User\Domain\Entity\Activity;
use Jokuf\User\Domain\Entity\Permission;
use Jokuf\User\Infrastructure\Mapper\ActivityMapper;
use Jokuf\User\Infrastructure\Mapper\PermissionMapper;
use Jokuf\User\Infrastructure\MySqlDB;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var PermissionMapper
     */
    private $mapper;
    /**
     * @var ActivityMapper
     */
    private $activityMapper;

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
        $this->activityMapper = new ActivityMapper(self::$db);
        $this->mapper = new PermissionMapper(self::$db, $this->activityMapper);
    }


    public function testCreate() {
        $permission = new Permission(null, 'Create game');
        $this->mapper->insert($permission);


        $this->assertEquals(1, $permission->getId());
    }

    public function testRead()
    {
        $permission = $this->mapper->findForId(1);

        $this->assertEquals(1, $permission->getId());
    }

    public function testUpdate() {
        $permission = $this->mapper->findForId(1);

        $name = $permission->getName();

        $permission->setName('jokuf');
        $this->mapper->update($permission);

        $stmt = self::$db->execute('SELECT * FROM permissions WHERE id=:id', [":id"=>  $permission->getId()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEquals($name, $row['name']);
        $this->assertEquals($permission->getName(), $row['name']);

    }

    public function testDelete() {
        $this->expectException(Exception::class);

        $permisssion = $this->mapper->findForId(1);

        $this->mapper->delete($permisssion);

        $this->mapper->findForId($permisssion->getId());
    }

    public function testCreatePermissionAndAttachNewlyCreatedActivities() {
        $activity1  = new Activity(null, '/test/asdfa/ffa/', 'POST', '/regex1');
        $activity2  = new Activity(null, '/test/asdfa/ffa1/', 'GET', '/regex2');
        $activity3  = new Activity(null, '/test/asdfa/ffa2/', 'DELETE', '/regex3');
        $activity4  = new Activity(null, '/test/asdfa/ffa2/', 'PUT', '/regex3');
        $permission = new Permission(null, 'Can enter', [$activity1, $activity2, $activity3, $activity4]);

        $this->activityMapper->insert($activity4);
        $this->mapper->insert($permission);

        $stmt = self::$db->execute('SELECT * FROM permission_activities WHERE permissionId=:id', [":id"=>  $permission->getId()]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertCount(4, $rows);
    }
}