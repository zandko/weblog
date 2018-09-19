<?php

namespace controllers;

use models\Comment;

class CommentController
{
    // 发表评论
    public function comments()
    {
        // 1.判断有没有登录
        if (!isset($_SESSION['id'])) {
            echo json_encode([
                'status_code' => '401',
                'message' => '未登录！',
            ]);

            exit;
        }

        // 2.接收表单
        $data = file_get_contents('php://input');
        // 转成数组
        $_POST = json_decode($data, true);

        $content = e($_POST['content']);
        $blog_id = $_POST['blog_id'];

        // 3.发表评论
        $comment = new Comment;
        $comment->add($content, $blog_id);

        // 4.返回数据
        echo json_encode([
            'status_code' => '200',
            'message' => '发表成功！',
            'data' => [
                'content' => $content,
                'avatar' => $_SESSION['avatar'],
                'email' => $_SESSION['email'],
                'created_at' => date("Y-m-d H:i:s"),
            ],

        ]);
    }

    // 获取评论列表
    public function comment_list()
    {
        // 1.接收日志id
        $blog_id = $_GET['id'];
        
        // 2.获取日志的评论
        $comment = new Comment;
        $data = $comment->getComment($blog_id);

        // 转成json
        echo json_encode([
            'status_code' => '200',
            'data' => $data,
        ]);
    }
}
