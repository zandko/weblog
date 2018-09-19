<?php

namespace controllers;

use models\Blog;
use models\User;

class IndexController
{
    public function index()
    {

        // 取最新的日志
        $blog = new Blog;
        $blogs = $blog->getNew();

        // 取活跃用户
        $user = new User;
        $users = $user->getActiveUsers();

        // 显示页面
        view('index.index', [
            'blogs' => $blogs,
            'users' => $users,
        ]);
    }
}
