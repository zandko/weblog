<?php

namespace models;

class Order extends Base
{
    // 下订单
    public function create($money)
    {   
        $flake = new \libs\Snowflake(1023);
        $data=$stmt = self::$pdo->prepare("INSERT INTO orders(user_id,money,sn) VALUES(?,?,?)");
        $stmt -> execute([
            $_SESSION['id'],
            $money,
            $flake->nextId(),
        ]); 
    }
}