<?php


use Jokuf\User\Authorization\ActivityInterface;
use Jokuf\User\Infrastructure\Factory\ActivityFactory;
use Jokuf\User\Infrastructure\MySqlDB;
use Jokuf\User\Infrastructure\Repository\ActivityRepository;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
    /**
     * @var ActivityRepository
     */
    private $activityMapper;
    /**
     * @var ActivityFactory
     */
    private $factory;

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
        $this->activityMapper   = new ActivityRepository(self::$db);
    }

    public function testCanCreateActivity() {
        $this->assertInstanceOf(ActivityInterface::class, new \Jokuf\User\Activity(null, 'POST', '[]'));
    }

    public function testCreateAndSaveActivity() {
        $activity = new \Jokuf\User\Activity(null,  'POST', '/regex');
        $activity = $this->activityMapper->insert($activity);

        $this->assertEquals(1, $activity->getId());
    }

    public function testGetActivityFromDb() {
        $activity = $this->activityMapper->findFromId(1);

        $this->assertEquals(1, $activity->getId());
    }

    public function testUpdateActivity() {
        $activity = $this->activityMapper->findFromId(1);
        $updatedActivity = new \Jokuf\User\Activity(1, 'asdsafa', 'sdfasa');

        $this->activityMapper->update($updatedActivity);

        $stmt = self::$db->execute('SELECT * FROM activities WHERE id=:id', [":id"=>  $activity->getId()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($row['method'], $updatedActivity->getMethod());
        $this->assertEquals($row['regex'], $updatedActivity->getRegex());
    }

    public function testDeleteActivity()
    {
        $this->expectException(Exception::class);
        $activity = $this->activityMapper->findFromId(1);
        $this->activityMapper->delete($activity);
        $this->activityMapper->findFromId($activity->getId());
    }

}
