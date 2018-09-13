<?php

namespace controllers;

use Endroid\QrCode\QrCode;

class QrcodeController
{
    // 把一个字符串生成 二维码图片并显示
    public function qrcode()
    {
        $str = $_GET['code'];
        $qrCode = new QrCode($str);
        header('Content-Type:' . $qrCode->getContentType());
        echo $qrCode->writeString();
    }
}
