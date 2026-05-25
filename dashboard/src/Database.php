<?php

class Database
{
    private static ?PDO   $pdo    = null;
    private static string $prefix = '';
    private static string $type   = 'pgsql';

    public static function getInstance(): PDO
    {
        if (self::$pdo !== null) return self::$pdo;

        $cfg = require DASHBOARD_ROOT . '/config.php';
        $db  = $cfg['db'];

        self::$prefix = $db['prefix'] ?? 'mdl_';
        // 'mariadb' and 'mysqli' both use the mysql PDO driver
        self::$type   = in_array($db['type'], ['pgsql'], true) ? 'pgsql' : 'mysql';

        $dsn = self::$type === 'pgsql'
            ? "pgsql:host={$db['host']};port={$db['port']};dbname={$db['dbname']}"
            : "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }

    /** Table prefix (e.g. 'mdl_') */
    public static function p(): string
    {
        if (self::$prefix === '') {
            $cfg = require DASHBOARD_ROOT . '/config.php';
            self::$prefix = $cfg['db']['prefix'] ?? 'mdl_';
        }
        return self::$prefix;
    }

    /** Returns 'pgsql' or 'mysql' (mariadb counts as mysql) */
    public static function type(): string
    {
        if (self::$pdo === null) self::getInstance();
        return self::$type;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
