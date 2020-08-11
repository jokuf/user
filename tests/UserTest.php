<?php


use Jokuf\User\Domain\Entity\Permission;
use Jokuf\User\Domain\Entity\User;
use Jokuf\User\Infrastructure\Mapper\ActivityMapper;
use Jokuf\User\Infrastructure\Mapper\PermissionMapper;
use Jokuf\User\Infrastructure\Mapper\RoleMapper;
use Jokuf\User\Infrastructure\Mapper\UserMapper;
use Jokuf\User\Infrastructure\MySqlDB;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected static $db;
    /**
     * @var UserMapper
     */
    private $userMapper;
    /**
     * @var RoleMapper
     */
    private $roleMapper;
    /**
     * @var PermissionMapper
     */
    private $permissionMapper;
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
        $this->activityMapper   = new ActivityMapper(self::$db);
        $this->permissionMapper = new PermissionMapper(self::$db,$this->activityMapper);
        $this->roleMapper       = new RoleMapper(self::$db, $this->permissionMapper);
        $this->userMapper       = new UserMapper(self::$db, $this->roleMapper);
    }


    public function testCanCreateNewUser(): void
    {
        /** @noinspection StaticInvocationViaThisInspection */
        $this->assertInstanceOf(
            User::class,
            new User(null)
        );
    }

    public function testCreateUserAndSaveItToDb(): void
    {
        $user = new Jokuf\User\Domain\Entity\User(null);
        $this->userMapper->insert($user);
        $this->assertEquals(1, $user->getId());
    }

    /**
     * @depends testCreateUserAndSaveItToDb
     */
    public function testGetUser(): void
    {
        $user = $this->userMapper->findById(1);

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @depends testGetUser
     *
     */
    public function testDeleteUser(): void
    {
        $this->expectException(Exception::class);
        $user = $this->userMapper->findById(1);

        $this->userMapper->delete($user);

        $this->userMapper->findById(1);
    }
}