<?php
// 使用 redis 保存 SESSION
ini_set('session.save_handler', 'redis');
// 设置 redis 服务器的地址、端
ini_set('session.save_path', 'tcp://127.0.0.1:6379?database=3');

session_start();

// 验证CSRF令牌  如果用户以post方式访问网站时 需要验证令牌
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     if (!isset($_POST['_token'])) {
//         die('违法操作！');
//     }
//     if ($_POST['_token'] != $_SESSION['token']) {
//         die('违法操作');
//     }
// }

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

// 过滤xs
function e($content)
{
    return htmlspecialchars($content);
}

// 使用 htmlpurifer 过滤
function hpe($content)
{
    // 一直保存在内存中（直到脚本执行结束）
    static $purifier = null;

    // 只有第一次调用时才会创建新的对象
    if ($purifier === null) {
        // 1. 生成配置对象
        $config = \HTMLPurifier_Config::createDefault();

        // 2. 配置
        // 设置编码
        $config->set('Core.Encoding', 'utf-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        // 设置缓存目录
        $config->set('Cache.SerializerPath', ROOT . 'cache');
        // 设置允许的 HTML 标签
        $config->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,ol[start],li,p[style],br,span[style],img[width|height|alt|src],*[style|class],pre,hr,code,h2,h3,h4,h5,h6,blockquote,del,table,thead,tbody,tr,th,td');
        // 设置允许的 CSS
        $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,margin,width,height,font-family,text-decoration,padding-left,color,background-color,text-align');
        // 设置是否自动添加 P 标签
        $config->set('AutoFormat.AutoParagraph', true);
        // 设置是否删除空标签
        $config->set('AutoFormat.RemoveEmpty', true);

        // 3. 过滤
        // 创建对象
        $purifier = new \HTMLPurifier($config);
    }

    // 过滤
    $clean_html = $purifier->purify($content);

    return $clean_html;
}

function csrf()
{
    // 生成随机的字符串
    if (!isset($_SESSION['token'])) {
        $token = md5(rand(1, 99999) . microtime());
        $_SESSION['token'] = $token;
    }

    return $_SESSION['token'];
}

// 生成令牌隐藏域
function csrf_filed()
{
    $csrf = isset($_SESSION['token']) ? $_SESSION['token'] : csrf();
    echo "<input type='hidden' name='_token' value='{$csrf}'>";
}