<?php


use Jokuf\User\Domain\Entity\Activity;
use Jokuf\User\Infrastructure\Mapper\ActivityMapper;
use Jokuf\User\Infrastructure\MySqlDB;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{
    /**
     * @var MySqlDB
     */
    private static $db;
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
    }

    public function testCanCreateActivity() {
        $this->assertInstanceOf(Activity::class, new Activity(null, '/', 'POST', '[]'));
    }

    public function testCreateAndSaveActivity() {
        $activity = new Activity(null, '/', 'POST', '/regex');

        $this->activityMapper->insert($activity);

        $this->assertEquals(1, $activity->getId());
    }

    public function testGetActivityFromDb() {
        $activity = $this->activityMapper->findFromId(1);

        $this->assertEquals(1, $activity->getId());
    }

    public function testUpdateActivity() {
        $activity = $this->activityMapper->findFromId(1);

        $initialValues = [
            'method' => $activity->getMethod(),
            'regex' => $activity->getRegex()
        ];

        $activity->setRegex('asdfa');
        $activity->setMethod('asdfa');

        $this->activityMapper->update($activity);

        $stmt = self::$db->execute('SELECT * FROM activities WHERE id=:id', [":id"=>  $activity->getId()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEquals($initialValues['method'], $row['method']);
        $this->assertNotEquals($initialValues['regex'], $row['regex']);

        $this->assertEquals($row['method'], $activity->getMethod());
        $this->assertEquals($row['regex'], $activity->getRegex());
    }

    public function testDeleteActivity()
    {
        $this->expectException(Exception::class);

        $activity = $this->activityMapper->findFromId(1);

        $this->activityMapper->delete($activity);

        $this->activityMapper->findFromId($activity->getId());
    }

}