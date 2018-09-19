<?php

namespace models;

use PDO;

class Comment extends Base
{
    public function add($content, $blog_id)
    {
        $stmt = self::$pdo->prepare("INSERT INTO comments(content,blog_id,user_id) VALUES(?,?,?)");
        $stmt->execute([
            $content,
            $blog_id,
            $_SESSION['id'],
        ]);
    }

    public function getComment($blog_id)
    {
        $stmt = self::$pdo->prepare("SELECT a.*,b.email,b.avatar FROM comments a LEFT JOIN users b ON a.user_id = b.id WHERE a.blog_id=? ORDER BY a.id DESC");

        $stmt->execute([
            $blog_id,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
