<?php


use Jokuf\User\Infrastructure\Factory\ActivityFactory;
use Jokuf\User\Infrastructure\Factory\PermissionFactotry;
use Jokuf\User\Infrastructure\Factory\RoleFactory;
use Jokuf\User\Infrastructure\Factory\UserFactory;
use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use Jokuf\User\Infrastructure\Repository\PermissionRepository;
use Jokuf\User\Infrastructure\Repository\RoleRepository;
use Jokuf\User\Infrastructure\Repository\UserRepository;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\User\UserInterface;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    protected static $db;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var RoleRepository
     */
    private $roleRepository;
    /**
     * @var PermissionRepository
     */
    private $permissionRepository;
    /**
     * @var ActivityRepository
     */
    private $activityRepository;
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
    /**
     * @var UserFactory
     */
    private $userFactory;

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
        $this->userFactory = new UserFactory();

        $this->activityRepository   = new ActivityRepository(self::$db, $this->activityFactory);
        $this->permissionRepository = new PermissionRepository(self::$db,$this->activityRepository, $this->permissionFactory);
        $this->roleRepository       = new RoleRepository(self::$db, $this->permissionRepository, $this->roleFactory);
        $this->userRepository       = new UserRepository(self::$db, $this->roleRepository, $this->userFactory);
    }


    public function testCanCreateNewUser(): void
    {
        /** @noinspection StaticInvocationViaThisInspection */
        $this->assertInstanceOf(
            UserInterface::class,
            $this->userFactory->createUser('test@email.com', 'test', 'test', 'test')
        );
    }

    public function testCreateUserAndSaveItToDb(): void
    {
        $user = $this->userFactory->createUser( 'test@email.com', 'test', 'test', 'test');
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
