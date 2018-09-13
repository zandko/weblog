<?php

namespace models;

use PDO;

class Base
{
    public static $pdo = null;
    public function __construct()
    {
        if (self::$pdo === null) {
            $config = config('db');
            self::$pdo = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['user'], $config['pass']);
            self::$pdo->exec('SET NAMES ' . $config['charset']);

        }
    }

    // 开始事物
    public function startTrans()
    {
        self::$pdo->exec('START TRANSACTION');
    }

    // 提交事物
    public function commit()
    {
        self::$pdo->exec('commit');
    }

    // 回滚事物
    public function rollback()
    {
        self::$pdo->exec('rollback');
    }
}
