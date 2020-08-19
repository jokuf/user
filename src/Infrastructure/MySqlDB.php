<?php


namespace Jokuf\User\Infrastructure;


use PDO;

/** @noinspection PhpComposerExtensionStubsInspection */

class MySqlDB
{
    private static $inTransaction = false;
    /**
     * @var PDO
     */
    private $db;

    public function __construct(array $config) {
        /** @noinspection PhpComposerExtensionStubsInspection */
        $defaultOptions = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        $dsn      = $config['dsn'] ?? 'mysql:host';
        $charset  = $config['charset'] ?? 'utf8mb4';
        $user     = $config['user'] ?? '';
        $pass     = $config['pass'] ?? '';
        $options  = array_replace($defaultOptions, $config['options'] ?? []);

        if (isset($config['host'])) {
            $host = $config['host'];
            $port = $config['port'] ?? 3306;
            $dsn .= sprintf('=%s:%d;', $host, $port);
        }

        if (isset($config['database'])) {
            $dsn .= sprintf('dbname=%s;charset=%s', $config['database'], $charset);

        }

        $this->db = new PDO($dsn, $user, $pass, $options);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query($statement, $mode=PDO::FETCH_ASSOC)
    {
        $this->db->query($statement, $mode);
    }

    /**
     * @param string $q
     * @param array $params
     * @return string
     */
    public function insert(string $q, array $params) {
        $this->execute($q, $params);

        return $this->db->lastInsertId();
    }

    /**
     * @param string $q
     * @param array $params
     * @return bool|\PDOStatement
     */
    public function execute(string $q, array $params) {
        $stmt = $this->db->prepare($q);
        $stmt->execute($params);

        return $stmt;
    }

    public function prepare($statement, array $driver_options = array())
    {
        return $this->db->prepare($statement, $driver_options);
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function transactionStart() {
        if (true === self::$inTransaction) {
            throw new \Exception('Transaction already started');
        }

        $this->db->beginTransaction();
        self::$inTransaction = true;

    }

    public function transactionRevert() {
        if (false === self::$inTransaction) {
            throw new \Exception('Cannot revert not started transaction');
        }

        $this->db->rollBack();
        self::$inTransaction = false;
    }

    public function transactioCommit() {
        if (false === self::$inTransaction) {
            throw new \Exception('Cannot commit not started transaction');
        }
        $this->db->commit();
        self::$inTransaction = false;
    }
}