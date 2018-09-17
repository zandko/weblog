<?php

namespace controllers;

use models\Order;
use models\User;

class UserController
{
    public function uploadbig()
    {
        // 总的数量
        $count = $_POST['count'];
        // 当前是第几块
        $i = $_POST['i'];
        // 每块大小
        $size = $_POST['size'];
        // 图片
        $img = $_FILES['img'];

        // 所有分块的名字
        $name = 'big_img_' . $_POST['img_name'];
        // 保存每个分片
        move_uploaded_file($img['tmp_name'], ROOT . 'tmp/' . $i);
        // 链接redis
        $redis = \libs\Redis::getInstance();
        // 上传图片数量+1
        $uploadedCount = $redis->incr($name);

        // 上传数量等于总的数量时合并文件
        if ($uploadedCount == $count) {
            // 以追回的方式创建并打开最终的大文件
            $fp = fopen(ROOT . 'public/uploads/big/' . $name . '.png', 'a');

            // 循环所有的分片
            for ($i = 0; $i < $count; $i++) {
                // 读取第i号文件并写到大文件中
                fwrite($fp, file_get_contents(ROOT . 'tmp/' . $i));
                // 删除第i号临时文件
                unlink(ROOT . 'tmp/' . $i);
            }

            // 关闭文件
            fclose($fp);
            // 从redis 中删除这个文件对应的编号这个变量
            $redis->del($name);
        }

    }

    // 多张上传
    public function uploadAll()
    {
        $root = ROOT . 'public/uploads/';
        $date = date('Y-m-d');

        if (!is_dir($root . $date)) {
            mkdir($root . $date, 0777);
        }

        foreach ($_FILES['images']['name'] as $k => $v) {
            $name = md5(time() . rand(1, 9999));

            $ext = strrchr($v, '.');

            $name = $name . $ext;

            move_uploaded_file($_FILES['images']['tmp_name'][$k], $root . $date . '/' . $name);
        }
    }

    public function album()
    {
        view('users.album');
    }

    public function setavatar()
    {
        // 先创建目录
        $root = ROOT . 'public/uploads/';
        // 当前日期文件夹
        $date = date('Y-m-d');

        // 判断存不存在，不存在则创建
        if (!is_dir($root . $date)) {
            mkdir($root . $date, 0777);
        }

        // 唯一文件名
        $name = md5(time() . rand(1, 9999));
        // 文件名后缀
        $ext = strrchr($_FILES['avatar']['name'], '.');

        $name = $name . $ext;
        // 移动图片
        move_uploaded_file($_FILES['avatar']['tmp_name'], $root . $date . '/' . $name);
    }

    public function avatar()
    {
        view('users.avatar');
    }

    public function orderStatus()
    {
        $sn = $_GET['sn'];
        // 获取的次数
        $try = 10;
        $model = new Order;

        do {
            // 查询订单信息
            $info = $model->findBySn($sn);
            // 如果订单未支付就等待1秒，并减少尝试的次数，如果已经支付就退出循环
            if ($info['status'] == 0) {
                sleep(1);
                $try--;
            } else {
                break;
            }

        } while ($try > 0); // 如果尝试的次数到达指定的次数就退出循环

        echo $info['status'];
    }

    // 充值界面
    public function charge()
    {
        view('users.charge');
    }

    public function money()
    {
        $user = new User;
        echo $user->getMoney();
    }

    public function docharge()
    {
        // 生成订单
        $money = $_POST['money'];
        $model = new Order;
        $model->create($money);
        message('充值订单已生成，请立即支付！', 2, '/user/orders');
    }

    // 列出所有的订单
    public function orders()
    {
        $order = new Order;
        $data = $order->search();

        view('users.order', $data);
    }

    // 删除订单
    public function delete()
    {
        $id = $_POST['id'];
        $order = new Order;
        $order->delete($id);
        message('删除成功', 2, '/user/orders');
    }

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
            message('登录成功!', 2, '/blog/index');
        } else {
            message('账号或者密码错误!', 2, '/user/login');
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
