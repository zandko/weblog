<?php

namespace controllers;

class ToolController
{
    public function users()
    {
        $model = new \models\User;
        $data = $model->getAll();
        echo json_encode([
            'status_code' => '200',
            'data' => $data,
        ]);

    }

    public function login()
    {
        if (config('mode') != 'dev') {
            die('非法访问');
        }
        $email = $_GET['email'];
        $_SESSION = [];

        // 重新登录
        $user = new \models\User;
        $user->login($email, md5('123456'));
    }
}
