<?php

namespace libs;

class Main
{
    public $mailer = null;
    public function __construct()
    {
        $config = config('email');
        // 设置邮件服务器账号
        $transport = (new \Swift_SmtpTransport($config['host'], $config['port']))
            ->setUsername($config['name'])
            ->setPassword($config['pass'])
        ;
        // 创建发邮件对象
        $this->mailer = new \Swift_Mailer($transport);
    }

    public function send($title, $content, $to)
    {
        $config = config('email');
        // 创建邮件消息
        $message = (new \Swift_Message($title)) // 标题
        ->setFrom([$config['from_email'] => $config['from_name']]) //发件人
            ->setTo([$to[0], $to[0] => $to[1]]) //收件人
            ->setBody($content, 'text/html')
        ;

        // 发送邮件
        if ($config['mode'] == 'debug') {
            // 获取邮件的内容
            $message = $message->toString();
            // 把邮件的内容记录到日志中
            $log = new Log('email');
            $log->log($message);
        } else {
            $this->mailer->send($message);

        }

    }
}
