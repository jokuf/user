<?php


use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use Jokuf\User\Infrastructure\Repository\PermissionRepository;
use Jokuf\User\Infrastructure\Repository\RoleRepository;
use Jokuf\User\Infrastructure\Repository\UserRepository;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\User;
use Jokuf\User\User\UserInterface;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected static $db;
    /**
     * @var UserRepository
     */
    private $userRepository;

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
        $activityRepository = new ActivityRepository(self::$db);
        $permissionRepository = new PermissionRepository(self::$db, $activityRepository);
        $roleRepository = new RoleRepository(self::$db, $permissionRepository);
        $this->userRepository = new UserRepository(self::$db, $roleRepository);
    }


    public function testCanCreateNewUser(): void
    {
        /** @noinspection StaticInvocationViaThisInspection */
        $this->assertInstanceOf(
            UserInterface::class,
            new User(null, 'test@email.com', 'test', 'test', 'test')
        );
    }

    public function testCreateUserAndSaveItToDb(): void
    {
        $user = new User(null, 'test@email.com', 'test', 'test', 'test');
        $user = $this->userRepository->insert($user);
        $this->assertEquals(1, $user->getIdentity());
    }

    /**
     * @depends testCreateUserAndSaveItToDb
     */
    public function testGetUser(): void
    {
        $user = $this->userRepository->findById(1);

        $this->assertInstanceOf(UserInterface::class, $user);
    }

    /**
     * @depends testGetUser
     *
     */
    public function testDeleteUser(): void
    {
        $this->expectException(Exception::class);
        $user = $this->userRepository->findById(1);

        $this->userRepository->delete($user);

        $this->userRepository->findById(1);
    }
}
