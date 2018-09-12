<?php

namespace controllers;

use Yansongda\Pay\Pay;

class AlipayController
{
    public $config = [
        'app_id' => '2016091700531963',
        // 通知地址
        'notify_url' => 'http://requestbin.fullcontact.com/1lw358y1',
        // 跳回地址
        'return_url' => 'http://localhost:9999/alipay/return',
        // 支付宝公钥
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1eJ+JVFK/kGDq3B56yQ70SAQzB0PJwmmOeDBKZZc7wYOvWF/gkoW9ajJS2dWLG02NdbmASzLFmND5mTFByRUCTU3mffGDiJuIL3k+j3Bej3Ul/QrsAZbLcgagO0oB0aDDHZciV55RWgvwJZNsa33jYZmdE2v7qhlRH6/UR8gqSwrQOdAQcsHPUgCyqIhEgxNgpWJaXWZ9h87CWiCPJkbe5LwCabetBQlNaHU86fOyXJMJdKoG3Do9XbHAd5gFi/zkUQJP9OLeeJojtiIBteLe3kqFeM38xlqJPq3dQJjDAgIj9980yPXikHsxvYTZRZGNPcYBHsZECTKIbp5TudL6QIDAQAB',
        // 商户应用密钥
        'private_key' => 'MIIEogIBAAKCAQEAroVhz0eUmrnR/2eMp5OG0PAwFX/BxqPAc6pwEp574diO+gtaRWhyYjUogjQ35bwD3fO6MJYNlZFEhIi1ZFazhEkazwu259in3cXcFM7GR7/fIw8lGiJoZ7vQBKhqkQddxhePQy9SwtFDoVVzQkmUVNf+nxDrlMq0CVHS8alcR2sQ13+rJxh63LWWY0Hg+meNhREf+vrzg7y3U8xsdpktJ/RqEc8OSiPYn5tPc2HvPnfw0M/cxOdc6xYoo5MpoiPMVKYjGDphBmR8DxQBsmnrxK4wrvh9nEJ3drUH0f8PoMga988q5N4Kjp0Dd3VUBTaXdKjMWsZVYaA9y3WnRnzotwIDAQABAoIBAE1PeQzBwOrp9kUWMhDqIYbdX++mMMk2MUML9anJ20cpD+1kqhClPEaVFeTYDQsQRwZDue9cCZiAScIMbY6NeejYGbAumFAMghCUXfI5x0xxiv+U7fKt22JYqMXndY3ZnYjrVuOESz1SRotpty1eOv96z6jXbgTz5t1aCgBT7jUUVY/uLQdPNhxf3M6tcnGkvy95jaEZBcpJ0iQ2LJ+kYsvMLqHSobLRHP3m5noyexTb82p3o1TnpQYVQ1tQQLamWTORxMlgObZZBrkp9XAYGXo7nKG08cv9MLdAKBaiMEu1oJyAPvDJPE4Xd8n4Or7Ed6XoRmpVO9Fz/dIMTzFQGAECgYEA1jchC3UMQYtnJu+7ldG+Giad8XZipQ5iysSgHTae3CTaRHzsnz85jXfBb8Xc+7gK2XVQL6iI/8g02/lzNq3M558pvMtWNAVI+lxDKM1f026x2n4C/WxWL9BVhOzFpka83BWBaPZkSFJI+D5Tg3anzwBA9eF6V0Fw6EoSwDZzyzcCgYEA0JAcn11t8tBNJ7jZK+qXYmbEePzh5HlOGrYuNPTpS0vpT0o9MPzIJwvhvGWOo784vvsuSgdSEoWesb9TjQebHBFyl0zGzYB70s2vxmT0kM1wvg3Qk2/qDTOFiiwHegS0lLdD2egyHOOfZvybLWZtAcj8tzNJQf/gwXQI8xNtjoECgYBL3VZcomVmySINDhYXJyL2S/rfyxeAcSnXue8iqXd1a6/JVZzPgSq2yVS+awqqMzHUddGiL0PlolfmF+AP21mYJiw36qNq5PxFfmtihemMDcX7JWmVqsqTQGspGlmdW6wDHbKpI6m8WzfwgtI82sbvbp2S9vnG8Sw1eBZP8Hjt3QKBgF7C685Y9AjcLgI7UalLiIb0LJbQR464sw0d83aqRImqrxIQqCbm48Yh9unTtvCdhJn5pHmMQ/UYcxdN2Bd++jHRl6A5CSb8FsacIB5jZl+YiH5B5p/mvJBM9YLnKGp/UtEzR4ftoT9RCO8RHSyOmj6yZGiUy9dJ/IPJRfuzuxaBAoGAe4+Hf7ilgd7qf8Rl1GxZL9oP/AT92ZEuzrujKZaQyp2Io8Dm7H1rbRkqct8u0wNTaOzCQ4W9OFZpaluQrd6/+vnwBanFcm7g0JRghtSPcfuKu35lgvpngM2/yHFSIsyL8HKLnP881jtp9mYqoHPqKTnm8KFHBM3FCTeIiQIdc40=',
        // 沙箱模式（可选）
        'mode' => 'dev',
    ];

    // 跳转到支付宝
    public function pay()
    {
        $order = [
            'out_trade_no' => time(), // 本地订单ID
            'total_amount' => '0.01', // 支付金额
            'subject' => 'test subject', // 支付标题
        ];

        $alipay = Pay::alipay($this->config)->web($order);

        $alipay->send();
    }

    // 支付完成跳回
    function return () {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！
        echo '<h1>支付成功！</h1> <hr>';
        echo '<pre>';
        var_dump($data->all());
    }

    // 接收支付完成的通知
    public function notify()
    {
        $alipay = Pay::alipay($this->config);
        try {
            $data = $alipay->verify(); // 是的，验签就这么简单！
            // 这里需要对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            echo '订单ID：' . $data->out_trade_no . "\r\n";
            echo '支付总金额：' . $data->total_amount . "\r\n";
            echo '支付状态：' . $data->trade_status . "\r\n";
            echo '商户ID：' . $data->seller_id . "\r\n";
            echo 'app_id：' . $data->app_id . "\r\n";
        } catch (\Exception $e) {
            echo '失败：';
            var_dump($e->getMessage());
        }
        // 返回响应
        $alipay->success()->send();
    }

    // 退款
    public function refund()
    {
        // 生成唯一退款订单号
        $refundNo = md5(rand(1, 99999) . microtime());
        try {
            // 退款
            $ret = Pay::alipay($this->config)->refund([
                'out_trade_no' => '1536743676', // 之前的订单流水号
                'refund_amount' => 0.01, // 退款金额，单位元
                'out_request_no' => $refundNo, // 退款订单号
            ]);
            if ($ret->code == 10000) {
                echo '退款成功！';
            }else {
                echo '失败！';
                var_dump($ret);
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}
