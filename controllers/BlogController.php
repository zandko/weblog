<?php

namespace controllers;

use models\Blog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BlogController
{
    public function agreements_list()
    {
        $id = $_GET['id'];
        $blog = new Blog;
        $data = $blog->agreementsList($id);

        echo json_encode([
            'status' => '200',
            'data' => $data,
        ]);

    }
    // 点赞
    public function agreements()
    {
        $id = $_GET['id'];
        // 判断登录
        if (!isset($_SESSION['id'])) {
            echo json_encode([
                'status_code' => '403',
                'message' => '必须先登录',
            ]);
            exit;
        }

        // 判断是否已经点过这篇日志
        $model = new Blog;
        $ret = $model->agree($id);
        if ($ret) {
            echo json_encode([
                'status_code' => '200',
            ]);
            exit;
        } else {
            echo json_encode([
                'status_code' => '403',
                'message' => '已经点过了',
            ]);
            exit;
        }

    }

    public function makeExcel()
    {
        // 数据库中取出数据
        $blog = new Blog;
        $data = $blog->getNew();

        // 获取当前标签页
        $spreadsheet = new Spreadsheet();
        // 获取当前工作
        $sheet = $spreadsheet->getActiveSheet();

        // 设置第1行内容
        $sheet->setCellValue('A1', '标题');
        $sheet->setCellValue('B1', '内容');
        $sheet->setCellValue('C1', '发表时间');
        $sheet->setCellValue('D1', '是发公开');

        // 从第2行写入数据
        $i = 2;
        foreach ($data as $v) {
            $sheet->setCellValue('A' . $i, $v['title']);
            $sheet->setCellValue('B' . $i, $v['content']);
            $sheet->setCellValue('C' . $i, $v['created_at']);
            $sheet->setCellValue('D' . $i, $v['is_show'] == 1 ? '公开' : '私有');

            $i++;
        }
        $date = date('Ymd');
        // 生成 Excel 文件
        $writer = new Xlsx($spreadsheet);
        $writer->save(ROOT . 'excel/' . $date . '.xlsx');

        // 下载文件路径
        $file = ROOT . 'excel/' . $date . '.xlsx';
        // 下载时文件名
        $fileName = '最新的10条日志-' . $date . '.xlsx';

        //告诉浏览器这是一个文件流格式的文件
        Header("Content-type: application/octet-stream");
        //请求范围的度量单位
        Header("Accept-Ranges: bytes");
        //Content-Length是指定包含于请求或响应中数据的字节长度
        Header("Accept-Length: " . filesize($file));
        //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
        Header("Content-Disposition: attachment; filename=" . $fileName);

        // 读取并输出文件内容
        readfile($file);
    }

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
