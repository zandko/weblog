<?php

namespace controllers;

use models\User;

class UserController
{
    public function register()
    {
        view('users.register');
    }

    public function login()
    {
        view('users.login');
    }

    public function logout()
    {
        // 清空session
        $_SESSION = [];
        // redirect('/');
        message('退出成功', 2, '/');
    }

    public function dologin()
    {
        $email = $_POST['email'];
        $password = md5($_POST['password']);
        // echo $password;
        // die;
        $user = new User;
        $data = $user->login($email, $password);
        if ($data) {
            message('登录成功!',2,'/blog/index');
        } else {
            message('账号或者密码错误!',2,'/user/login');
        }
    }

    public function store()
    {
        // 1、接收表单
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        // 2、生成激活码(随机的字符串)
        $code = md5(rand(1, 99999));

        // 3、保存到 redis
        $redis = \libs\Redis::getInstance();
        // 序列化 (数组 转成 JSON 字符串)
        $value = json_encode([
            'email' => $email,
            'password' => $password,
        ]);
        // 键名
        $key = "temp_user:{$code}";
        $redis->setex($key, 300, $value);

        // 4、把激活码发送到用户的邮箱中
        // 从邮箱地址取出姓名
        $name = explode('@', $email);
        $from = [$email, $name[0]];
        $message = [
            'title' => '账号激活',
            'content' => "点击以下链接进行激活：<br>点击激活:<p><a href='http://localhost:9999/user/active_user?code={$code}'>http://localhost:9999/user/active_user?code={$code}</a></p>",
            'from' => $from,
        ];

        $message = json_encode($message);

        // 连接redis
        $redis = \libs\Redis::getInstance();

        $redis->lpush('email', $message);
        echo "ok";
    }

    public function active_user()
    {
        // 接收激活码
        $code = $_GET['code'];
        // 到redis 取出账号
        $redis = \libs\Redis::getInstance();
        // 拼出名字
        $key = 'temp_user:' . $code;
        // 取出数据
        $data = $redis->get($key);

        // 判断有没有
        if ($data) {
            // 从redis中删除激活码
            $redis->del($key);
            // 反序列化(转回数组)
            $data = json_decode($data, true);
            // 插入到数据库中
            $user = new User;
            $user->add($data['email'], $data['password']);

            // 跳转到登录页面
            header('Location:/user/login');
        } else {
            die("激活码无效!");
        }
    }
}
