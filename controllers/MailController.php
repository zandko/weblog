<?php

namespace controllers;

class MailController
{
    public function send()
    {
        $redis = \libs\Redis::getInstance();

        $mail = new \libs\Main;

        ini_set('default_socket_timeout', -1);
        echo "开始";
        while (true) {
            // 1、先从队列中取消息
            $data = $redis->brpop('email', 0);

            // 取出消息并反序列化(转回数组)
            $message = json_decode($data[1], true);

            // 3、发邮件
            $mail->send($message['title'], $message['content'], $message['from']);
            echo "发送邮件成功！继续发送下一个\r\n";
        }

    }

}
