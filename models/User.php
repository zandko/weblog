<?php

namespace models;

use PDO;

class User extends Base
{
    public function getActiveUsers()
    {
        $redis = \libs\Redis::getInstance();
        $data = $redis->get('active_users');

        // 转回数组 (第二个参数 true)
        return json_decode($data, true);
    }

    // 计算活跃用户
    public function computeActiveUsers()
    {
        // 获取日志的分值
        $stmt = self::$pdo->prepare('SELECT user_id,COUNT(*)*5 fz FROM blogs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY user_id');
        $stmt->execute();

        $data1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 获取评论的分值
        $stmt = self::$pdo->prepare('SELECT user_id,COUNT(*)*3 fz FROM comments WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY user_id');
        $stmt->execute();

        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 获取点赞的分值
        $stmt = self::$pdo->prepare('SELECT user_id,COUNT(*) fz FROM blog_agrees WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY user_id');
        $stmt->execute();

        $data3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 合并数组
        $arr = [];

        // 合并第一个数组到空数组中
        foreach ($data1 as $v) {
            $arr[$v['user_id']] = $v['fz'];
        }

        // 合并第二个数组到空数组中
        foreach ($data2 as $v) {
            if (isset($arr[$v['user_id']])) {
                $arr[$v['user_id']] += $v['fz'];
            } else {
                $arr[$v['user_id']] = $v['fz'];
            }
        }

        // 合并第三个数组到空数组中
        foreach ($data3 as $v) {
            if (isset($arr[$v['user_id']])) {
                $arr[$v['user_id']] += $v['fz'];

            } else {
                $arr[$v['user_id']] = $v['fz'];
            }
        }

        // 倒序排序
        arsort($arr);

        // 取前20并保存键(第四个参数保留键)
        $data = array_slice($arr, 0, 20, true);

        // 取出前20用户的id
        // 从数组中取出所有的键
        $userId = array_keys($data);

        // 数组转字符串
        $userId = implode(',', $userId);

        // 取出用户的头像和email
        $stmt = self::$pdo->prepare("SELECT id,email,avatar FROM users WHERE id IN($userId)");

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 把计算结果存到redis
        $redis = \libs\Redis::getInstance();
        $redis->set('active_users', json_encode($data));
    }

    public function getAll()
    {
        $stmt = self::$pdo->query('SELECT * FROM users');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setAvatar($path)
    {
        $stmt = self::$pdo->prepare("UPDATE users SET avatar=? WHERE id=?");
        $data = $stmt->execute([
            $path,
            $_SESSION['id'],
        ]);

    }

    public function add($email, $password)
    {
        $stmt = self::$pdo->prepare("INSERT INTO users(email,password) VALUES(?,?)");
        return $stmt->execute([
            $email,
            $password,
        ]);
    }

    public function login($email, $password)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE email=? AND password=?");
        $stmt->execute([
            $email,
            $password,
        ]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['money'] = $user['money'];
            $_SESSION['avatar'] = $user['avatar'];
            return true;
        } else {
            return false;
        }
    }

    // 为用户增加金额
    public function addMoney($money, $user_id)
    {
        $stmt = self::$pdo->prepare("UPDATE users SET money=money+? WHERE id=?");
        return $stmt->execute([
            $money,
            $user_id,
        ]);
    }

    // 更新余额
    public function getMoney()
    {
        $stmt = self::$pdo->prepare("SELECT money FROM users WHERE id=?");
        $stmt->execute([
            $_SESSION['id'],
        ]);
        $money = $stmt->fetch(\PDO::FETCH_COLUMN);

        // 更新到session中
        $_SESSION['money'] = $money;
        return $money;
    }
}
