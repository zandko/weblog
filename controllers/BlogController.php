<?php

namespace controllers;

use models\Blog;

class BlogController
{
    // 显示私有日志
    public function content()
    {
        // 1、接收ID，并取出日志信息
        $id = $_GET['id'];
        $model = new Blog;
        $blog = $model->find($id);

        // 2、判断这个日志是不是我的日志
        if ($_SESSION['id'] != $blog['user_id']) {
            die('无权访问！');
        }

        // 3、加载视图
        view('blogs.content', [
            'blogs' => $blog,
        ]);
    }

    // 显示添加日志的表单
    public function create()
    {
        view('blogs.create');
    }

    public function delete()
    {
        $id = $_POST['id'];
        $blog = new Blog;
        $blog->delete($id);
        // 将静态页删掉
        $blog->deleteHtml($id);
        message('删除成功', 2, '/blog/index');
    }

    public function edit()
    {
        $id = $_GET['id'];
        $blog = new Blog;
        $data = $blog->find($id);
        view('blogs.edit', [
            'blog' => $data,
        ]);
    }

    public function doedit()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];
        $id = $_POST['id'];

        $blog = new Blog;
        $blog->edit($title, $content, $is_show, $id);

        // 如果日志是公开的就生成静态页
        if ($is_show == 1) {
            $blog->makeHtml($id);
        } else {
            // 如果改为私有，就要将原来的静态页删掉
            $blog->deleteHtml($id);
        }

        message('修改成功！', 2, '/blog/index');
    }

    public function store()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $id = $blog->add($title, $content, $is_show);

        // 如果日志是公开的就生成静态页
        if ($is_show == 1) {
            $blog->makeHtml($id);
        }

        message('发表成功!', 2, '/blog/index');
    }

    // 日志列表
    public function index()
    {
        $blog = new Blog;
        $data = $blog->search();
        view('blogs.index', $data);
    }

    // 为所有的日志生成详情页;
    public function content_to_html()
    {
        $blog = new Blog;
        $blog->content_to_html();
    }

    public function index_html()
    {
        $blog = new Blog;
        $blog->index_html();
    }

    public function display()
    {
        // 接收日志ID
        $id = (int) $_GET['id'];
        $blog = new Blog; 

        // 把浏览量+1
        $display = $blog->getDisplay($id);

        // 返回多个数据必须要用 JSON
        echo json_encode([
            'display' => $display,
            'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
        ]);
    }

    public function displayToDb()
    {
        $blog = new Blog;
        $blog->displayToDb();
    }
}
