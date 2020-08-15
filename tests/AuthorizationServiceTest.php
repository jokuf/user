<?php


use Jokuf\User\Activity;
use Jokuf\User\AuthorizationService;
use Jokuf\User\Infrastructure\Factory\ActivityFactory;
use Jokuf\User\Infrastructure\Factory\PermissionFactotry;
use Jokuf\User\Infrastructure\Factory\RoleFactory;
use Jokuf\User\Infrastructure\Factory\UserFactory;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use Jokuf\User\Infrastructure\Repository\PermissionRepository;
use Jokuf\User\Infrastructure\Repository\RoleRepository;
use Jokuf\User\Infrastructure\Repository\UserRepository;
use Jokuf\User\Permission;
use Jokuf\User\Role;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var UserRepository
     */
    private $mapper;

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

        $this->mapper=
            new UserRepository(
                self::$db,
                new RoleRepository(
                    self::$db,
                    new PermissionRepository(
                        self::$db,
                        new ActivityRepository(
                            self::$db,
                            new ActivityFactory()
                        ),
                        new PermissionFactotry()
                    ), new RoleFactory()),
            new UserFactory());
    }

    public function testAuthenticateExpectedReturnTrue() {
        $service    = new AuthorizationService($this->mapper);
        $role       = new Role(null, 'Administrator');
        $permission = new Permission(null, 'Create game');
        $permission
            ->addActivity(new Activity(null, 'POST', '/api/v1/roles/[0-9]+/users'));

        $role->addPermission($permission);

        $user = new Jokuf\User\User(null, 'iordanov_@mail.bg', 'Radoslav', 'Yordanov', 'hashedpass');
        $user->addRole($role);

        $user = $this->mapper->insert($user);

        $this->assertTrue($service->authorize($user, '/api/v1/roles/1/users','POST'), 'Test authenticate service return true');
    }

    public function testFindOrCreateTokenMethodReturnsValidGuestJWTTokenWhenInvalidTokenIsProvided()
    {
        $service = new AuthorizationService($this->mapper);
        $user = $service->getUserFromToken('sadsadafa');
        $token = $service->createTokenFor($user);

        $this->assertEquals($token, $service->createTokenFor($service->getUserFromToken($token)));
    }
}
