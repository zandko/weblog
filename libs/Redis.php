<?php

namespace libs;

class Redis
{
    private static $redis = null;
    private function __clone()
    {}
    private function __construct()
    {}
    public static function getInstance()
    {
        if (self::$redis === null) {
            $config = config('redis');
            self::$redis = new \Predis\Client([
                'scheme' => $config['scheme'],
                'host' => $config['host'],
                'port' => $config['port'],
            ]);
        }
        return self::$redis;
    }
}
