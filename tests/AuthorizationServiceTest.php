<?php


use Jokuf\User\AuthorizationService;
use Jokuf\User\Domain\Entity\Activity;
use Jokuf\User\Domain\Entity\Permission;
use Jokuf\User\Domain\Entity\Role;
use Jokuf\User\Domain\Entity\User;
use Jokuf\User\Infrastructure\Mapper\ActivityMapper;
use Jokuf\User\Infrastructure\Mapper\PermissionMapper;
use Jokuf\User\Infrastructure\Mapper\RoleMapper;
use Jokuf\User\Infrastructure\Mapper\UserMapper;
use Jokuf\User\Infrastructure\MySqlDB;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var UserMapper
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
        $this->mapper =
            new UserMapper(
                self::$db, new RoleMapper(
                    self::$db, new PermissionMapper(
                        self::$db, new ActivityMapper(
                            self::$db ))));
    }

    public function testAuthenticateExpectedReturnTrue() {
        $service    = new AuthorizationService();
        $role       = new Role(null, 'Administrator');
        $permission = new Permission(null, 'Create game');
        $permission
            ->addActivity(new Activity(null, 'POST', '/api/v1/roles/[0-9]+/users'));

        $role->addPermission($permission);

        $user = new User(null);
        $user->addRole($role);

        $this->mapper->insert($user);

        $this->assertTrue($service->authorize($user, '/api/v1/roles/1/users','POST'), 'Test authenticate service return true');
    }
}