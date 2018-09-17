<?php

namespace models;

class Blog extends Base
{
    public function getNew()
    {
        $stmt = self::$pdo->prepare("SELECT * FROM blogs ORDER BY created_at DESC LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // 为某一个日志生成静态页面
    public function makeHtml($id)
    {
        // 1、取出日志的信息
        $blog = $this->find($id);
        // 2、打开缓冲区、并且加载视图到缓冲区
        ob_start();
        view('blogs.content', [
            'blogs' => $blog,
        ]);
        // 3、从缓冲区中取出视图并写到静态页中
        $str = ob_get_clean();
        file_put_contents(ROOT . 'public/contents/' . $id . '.html', $str);
    }

    // 删除静态页
    public function deleteHtml($id)
    {
        @unlink(ROOT . 'public/contents/' . $id . '.html');
    }

    public function find($id)
    {
        $stmt = self::$pdo->prepare("SELECT * FROM blogs WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function edit($title, $content, $is_show, $id)
    {
        $stmt = self::$pdo->prepare("UPDATE blogs SET title=?,content=?,is_show=? WHERE id=?");
        $data = $stmt->execute([
            $title,
            $content,
            $is_show,
            $id,
        ]);
    }

    public function delete($id)
    {
        $stmt = self::$pdo->prepare("DELETE FROM blogs WHERE id =? AND user_id =?");
        $stmt->execute([
            $id,
            $_SESSION['id'],
        ]);

    }

    public function add($title, $content, $is_show)
    {
        $stmt = self::$pdo->prepare("INSERT INTO blogs(title,content,is_show,user_id) VALUES(?,?,?,?)");

        $ret = $stmt->execute([
            $title,
            $content,
            $is_show,
            $_SESSION['id'],
        ]);

        if (!$ret) {
            echo "发表失败!";
            echo "<pre>";
            $error = $stmt->errorInfo();
            var_dump($error);
            exit;
        }
        // 返回新插入的记录的ID
        return self::$pdo->lastInsertId();
    }

    public function search()
    {
        // 取出当前用户的日志
        $where = 'user_id=' . $_SESSION['id'];
        $value = [];

        if (isset($_GET['keyword']) && $_GET['keyword']) {
            $where .= " AND (title LIKE ? OR content LIKE ?) ";
            $value[] = '%' . $_GET['keyword'] . '%';
            $value[] = '%' . $_GET['keyword'] . '%';
        }

        if (isset($_GET['start_date']) && $_GET['start_date']) {
            $where .= " AND created_at >= ? ";
            $value[] = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && $_GET['end_date']) {
            $where .= " AND created_at <= ? ";
            $value[] = $_GET['end_date'];
        }

        if (isset($_GET['is_show']) && ($_GET['is_show'] == 1 || $_GET['is_show'] === '0')) {
            $where .= " AND is_show = ? ";
            $value[] = $_GET['is_show'];
        }

        $odby = 'created_at';
        $odway = 'desc';

        if (isset($_GET['odby']) && $_GET['odby'] == 'display') {
            $odby = 'display';
        }

        if (isset($_GET['odway']) && $_GET['odway'] == 'asc') {
            $odway = 'asc';
        }

        $prepage = 15;
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $pages = ($page - 1) * $prepage;
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM blogs WHERE $where");
        $stmt->execute($value);
        $count = $stmt->fetch(\PDO::FETCH_COLUMN);

        $pageCount = ceil($count / $prepage);

        $btns = "";
        for ($i = 1; $i <= $pageCount; $i++) {
            $params = getUrlParams(['page']);
            $btns .= "<a href='?{$params}page=$i'> $i </a>";
        }

        $stmt = self::$pdo->prepare("SELECT * FROM blogs WHERE $where ORDER BY $odby $odway LIMIT $pages,$prepage");
        $stmt->execute($value);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return [
            'btns' => $btns,
            'blogs' => $data,
        ];
    }

    public function content_to_html()
    {
        $stmt = self::$pdo->query("SELECT * FROM blogs");

        $blogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        ob_start();
        foreach ($blogs as $v) {
            view('blogs.content', [
                'blogs' => $v,
            ]);

            $str = ob_get_contents();
            file_put_contents(ROOT . 'public/contents/' . $v['id'] . '.html', $str);
            ob_clean();
        }

    }

    public function index_index()
    {
        $stmt = self::$pdo->query("SELECT * FROM blogs WHERE is_show=1 ORDER BY  id  DESC LIMIT 20");
        $blogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $blogs;
    }

    public function index_html()
    {
        $stmt = self::$pdo->query("SELECT * FROM blogs WHERE is_show=1 ORDER BY  id  DESC LIMIT 20");
        $blogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        ob_start();
        view('index.index', [
            'blog' => $blogs,
        ]);

        $str = ob_get_contents();
        file_put_contents(ROOT . 'public/index.html', $str);
    }

    // 从数据库中取出日志的浏览量
    public function getDisplay($id)
    {
        // 连接redis
        $redis = \libs\Redis::getInstance();

        $key = "blog-{$id}";

        // 判断blog_displays 这个hash 中有没有一个键是 blog-$id
        if ($redis->hexists('blog_displays', $key)) {
            $newNum = $redis->hincrby('blog_displays', $key, 1);
            return $newNum;
        } else {
            // 从数据库中取出浏览量
            $stmt = self::$pdo->prepare('SELECT display FROM blogs WHERE id=?');
            $stmt->execute([$id]);
            $display = $stmt->fetch(\PDO::FETCH_COLUMN);
            $display++;
            // 加到redis
            $redis->hset('blog_displays', $key, $display);
            return $display;
        }

    }

    public function displayToDb()
    {
        $redis = \libs\Redis::getInstance();

        $data = $redis->hgetall('blog_displays');

        // 更新回数据库
        foreach ($data as $k => $v) {
            $id = str_replace('blog-', '', $k);
            self::$pdo->exec("UPDATE blogs SET display={$v} WHERE id={$id}");
        }
    }

}
