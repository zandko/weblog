<?php

namespace libs;

/**
 * 这个项目中无论使用多少次这个类,其实只需要一个对象,如果多个对象就是浪费内存
 * 单例:三私一公 (无论new多少个都只有一个对象)
 */

class Uploader
{
    // 单例模式
    // 不允许被new来生成对象
    private function __construct()
    {}
    // 不允许克隆
    private function __clone()
    {}
    // 保存唯一的对象(只有静态的属性属于这个类是唯一的)
    private static $_obj = null;
    // 提供一个公开的方法获取对象
    public static function make()
    {
        if (self::$_obj === null) {
            // 生成对象
            self::$_obj = new self;
        }

        return self::$_obj;
    }

    private $_root = ROOT . 'public/uploads/';
    private $_ext = ['image/jpeg', 'image/jpg', 'image/ejpeg', 'image/png', 'image/gif'];
    private $_maxSize = 1024 * 1024 * 1.8;
    private $_file;
    private $_subDir;
    // 上传图片
    //   表单中文名
    // 参数二 保存到的二级目录
    public function upload($name, $subdir)
    {
        // 把用户图片的信息保存到属性上
        $this->_file = $_FILES[$name];
        $this->_subDir = $subdir;

        if (!$this->_checkSize()) {
            die('图片尺寸不正确！');
        }

        if (!$this->_checkType()) {
            die('图片类型不正确！');
        }
        // 创建目录
        $dir = $this->_makeDir();

        // 生成唯一的名字
        $name = $this->_makeName();

        // 移动图片
        move_uploaded_file($this->_file['tmp_name'], $this->_root . $dir . $name);

        // 返回上传之后的图片路径
        return $dir . $name;
    }

    // 创建目录
    private function _makeDir()
    {
        $dir = $this->_subDir . '/' . date('Ymd');
        if (!is_dir($this->_root . $dir)) {
            mkdir($this->_root . $dir, 0777, TRUE);
        }
        return $dir.'/';
    }

    // 生成唯一的名子

    
    private function _makeName()
    {
        $name = md5(time() . rand(1, 9999));
        $ext = strrchr($this->_file['name'], '.');
        return $name . $ext;
    }

    private function _checkType()
    {
        return in_array($this->_file['type'], $this->_ext);
    }

    private function _checkSize()
    {
        return $this->_file['size'] < $this->_maxSize;
    }
}
