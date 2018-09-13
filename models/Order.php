<?php

namespace models;

class Order extends Base
{
    // 下订单
    public function create($money)
    {
        $flake = new \libs\Snowflake(1023);
        $data = $stmt = self::$pdo->prepare("INSERT INTO orders(user_id,money,sn) VALUES(?,?,?)");
        $stmt->execute([
            $_SESSION['id'],
            $money,
            $flake->nextId(),
        ]);
    }

    // 搜索订单
    public function search()
    {   
        // 取出当前用户的订单
        $where = 'user_id=' . $_SESSION['id'];

        $odby = 'created_at';
        $odway = 'desc';

        $prepage = 15;
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $pages = ($page - 1) * $prepage;
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM orders WHERE $where");
        $stmt->execute();
        $count = $stmt->fetch(\PDO::FETCH_COLUMN);

        $pageCount = ceil($count / $prepage);

        $btns = "";
        for ($i = 1; $i <= $pageCount; $i++) {
            $params = getUrlParams(['page']);
            $btns .= "<a href='?{$params}page=$i'> $i </a>";
        }

        $stmt = self::$pdo->prepare("SELECT * FROM orders WHERE $where ORDER BY $odby $odway LIMIT $pages,$prepage");
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return [
            'btns' => $btns,
            'data' => $data,
        ];
    }

    // 根据编号从数据库中取出订单信息
    public function findBySn($sn)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM orders WHERE sn =?");
        $stmt->execute([
            $sn,
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // 设置订单为已支付的状态
    public function setPay($sn)
    {
        $stmt = self::$pdo->prepare("UPDATE orders SET status=1,pay_time=now() WHERE sn=?");
        $stmt->execute([
            $sn,
        ]);
    }
}
