<?php
// 使用 redis 保存 SESSION
ini_set('session.save_handler', 'redis');
// 设置 redis 服务器的地址、端
ini_set('session.save_path', 'tcp://127.0.0.1:6379?database=3');

session_start();

define('ROOT', dirname(__FILE__) . '/../');

// composer 自动加载文件
require ROOT . 'vendor/autoload.php';

/**
 * 类的自动加载
 */
function autoLoadClass($class)
{
    require ROOT . str_replace('\\', '/', $class) . '.php';
}

spl_autoload_register('autoLoadClass');

/**
 * 解析 url
 */
if (php_sapi_name() == 'cli') {
    $controller = ucfirst($argv[1]) . 'Controller';
    $action = $argv[2];
} else {
    if (isset($_SERVER['PATH_INFO'])) {
        $pathInfo = $_SERVER['PATH_INFO'];
        $pathInfo = explode('/', $pathInfo);
        $controller = ucfirst($pathInfo[1]) . 'Controller';
        $action = $pathInfo[2];
    } else {
        $controller = 'IndexController';
        $action = 'index';
    }
}

$fullController = 'controllers\\' . $controller;

$_C = new $fullController;
$_C->$action();

/**
 * 视图函数
 */
function view($viewFileName, $data = [])
{
    if ($data) {
        extract($data);
    }

    require ROOT . 'views/' . str_replace('.', '/', $viewFileName) . '.html';
}

function getUrlParams($data = [])
{

    foreach ($data as $v) {
        unset($_GET[$v]);
    }

    $str = '';

    foreach ($_GET as $k => $v) {
        $str .= "$k=$v&";
    }
    return $str;
}

// 获取配置文件
function config($name)
{
    static $config = null;

    // 引入配置文件
    if ($config === null) {
        $config = require ROOT . 'config.php';
    }

    return $config[$name];
}

function redirect($url)
{
    header('Location:' . $url);
    exit;
}

// 跳回上一个页面
function back()
{
    redirect($_SERVER['HTTP_REFERER']);
}

function message($message, $type, $url, $seconds = 5)
{
    if ($type == 0) {
        echo "<script>alert('{$message}');location.href='{$url}'</script>";
        exit;
    } else if ($type == 1) {
        view('common.success', [
            'message' => $message,
            'url' => $url,
            'seconds' => $seconds,
        ]);
    } else if ($type == 2) {
        // 把消息保存到session
        $_SESSION['_MESS_'] = $message;
        // 跳转到下一个页面
        redirect($url);
    }
}

