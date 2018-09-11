<?php

namespace controllers;

use models\Blog;

class BlogController
{
    // 显示添加日志的表单
    public function create()
    {
        view('blogs.create');
    }

    public function store()
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $blog->add($title, $content, $is_show);
        message('发表成功!',2,'/blog/index');
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

        echo $blog->getDisplay($id);
    }

    public function displayToDb()
    {
        $blog = new Blog;
        $blog->displayToDb();
    }
}
