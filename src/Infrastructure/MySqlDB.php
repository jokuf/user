<?php


namespace Jokuf\User\Infrastructure;


use PDO;

/** @noinspection PhpComposerExtensionStubsInspection */

class MySqlDB extends PDO
{
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

        /** @noinspection PhpComposerExtensionStubsInspection */
        parent::__construct($dsn, $user, $pass, $options);

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param string $q
     * @param array $params
     * @return string
     */
    public function insert(string $q, array $params) {
        $this->execute($q, $params);

        return $this->lastInsertId();
    }

    /**
     * @param string $q
     * @param array $params
     * @return bool|\PDOStatement
     */
    public function execute(string $q, array $params) {
        $stmt = $this->prepare($q);
        $stmt->execute($params);

        return $stmt;
    }
}