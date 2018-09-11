<?php
namespace controllers;

use PDO;

class MockController
{
    public function users()
    {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog', 'root', '123456');
        $pdo->exec('SET NAMES utf8');

        // 清空表，并且重置 ID
        $pdo->exec('TRUNCATE users');

        for ($i = 0; $i < 20; $i++) {
            $email = rand(50000, 99999) . '@163.com';
            $password = md5('123456');
            // echo "INSERT INTO blogs (title,content,display,is_show,created_at) VALUES('$title','$content',$display,$is_show,'$date')";
            // die;
            $pdo->exec("INSERT INTO users (email,password) VALUES('$email','$password')");
        }

    }

    public function blog()
    {
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog', 'root', '123456');
        $pdo->exec('SET NAMES utf8');

        // 清空表，并且重置 ID
        $pdo->exec('TRUNCATE blogs');

        for ($i = 0; $i < 300; $i++) {
            $title = $this->getChar(rand(20, 50));
            $content = $this->getChar(rand(50, 150));
            $display = rand(10, 500);
            $is_show = rand(0, 1);
            $date = rand(1233333399, 1535592288);
            $date = date('Y-m-d H:i:s', $date);
            $user_id = rand(1, 20);
            $pdo->exec("INSERT INTO blogs (title,content,display,is_show,created_at,user_id) VALUES('$title','$content',$display,$is_show,'$date','$user_id')");
        }
    }

    private function getChar($num) // $num为生成汉字的数量

    {
        $b = '';
        for ($i = 0; $i < $num; $i++) {
            // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
            $a = chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0));
            // 转码
            $b .= iconv('GB2312', 'UTF-8', $a);
        }
        return $b;
    }
}
