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
}
