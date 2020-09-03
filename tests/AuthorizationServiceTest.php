<?php


use Jokuf\User\Core\Entity\Activity;
use Jokuf\User\Core\Entity\Permission;
use Jokuf\User\Core\Entity\Role;
use Jokuf\User\Core\Entity\User;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Interactor\UserInteractor;
use Jokuf\User\Service\AuthorizationService;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var UserInteractor
     */
    private $userService;

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
        $this->userService= new UserInteractor(self::$db);
    }

    public function testAuthenticateExpectedReturnTrue() {
        $service    = new AuthorizationService($this->userService);
        $role       = new Role(null, 'Administrator');
        $permission = new Permission(null, 'Create game');
        $permission
            ->addActivity(new Activity(null, 'POST', '/api/v1/roles/[0-9]+/users'));

        $role->addPermission($permission);

        $user = new User(null, 'iordanov_@mail.bg', 'Radoslav', 'Yordanov', 'hashedpass');
        $user->addRole($role);

        $this->userService->save($user);

        $this->assertTrue($service->authorize($user, '/api/v1/roles/1/users','POST'), 'Test authenticate service return true');
    }

    public function testFindOrCreateTokenMethodReturnsValidGuestJWTTokenWhenInvalidTokenIsProvided()
    {
        $service = new AuthorizationService($this->userService);
        $user = $service->getUserFromToken('sadsadafa');
        $token = $service->createTokenFor($user);

        $this->assertEquals($token, $service->createTokenFor($service->getUserFromToken($token)));
    }
}
