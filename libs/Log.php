<?php

namespace libs;

class Log
{
    private $fp;
    // 参数:日志文件名
    public function __construct($fileName)
    {
        // 打开日志文件
        $this->fp = fopen(ROOT . 'logs/' . $fileName . '.log' , 'a');
    }

    // 向日志文件中追加内容
    public function log($content)
    {
        fwrite($this->fp, $content);
    }
}
