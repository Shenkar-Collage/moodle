<?php

class Database
{
    private static ?PDO $pdo = null;
    private static string $prefix = '';

    public static function getInstance(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require DASHBOARD_ROOT . '/config.php';
            $db  = $cfg['db'];
            self::$prefix = $db['prefix'];

            if ($db['type'] === 'pgsql') {
                $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['dbname']}";
            } else {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4";
            }

            self::$pdo = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }

    public static function p(): string
    {
        if (self::$prefix === '') {
            $cfg = require DASHBOARD_ROOT . '/config.php';
            self::$prefix = $cfg['db']['prefix'];
        }
        return self::$prefix;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
