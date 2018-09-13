<?php
namespace controllers;

use Endroid\QrCode\QrCode;
use models\Order;
use models\User;
use Yansongda\Pay\Pay;

class WxpayController
{
    protected $config = [
        'app_id' => 'wx426b3015555a46be', // 公众号 APPID
        'mch_id' => '1900009851',
        'key' => '8934e7d15453e97507ef794cf7b0519d',
        'notify_url' => 'http://requestbin.fullcontact.com/r6s2a1r6',
    ];

    public function pay()
    {
        // 接收订单编号
        $sn = $_POST['sn'];
        // 取出订单信息
        $order = new Order;
        // 根据订单编号取出订单信息
        $data = $order->findBySn($sn);

        if ($data['status'] == 0) {
            // 调用微信接口
            $order = [
                'out_trade_no' => $data['sn'],
                'total_fee' => $data['money'] * 100, // **单位：分**
                'body' => '智聊系统用户充值 ：' . $data['money'] . '元',
                // 'openid' => 'onkVf1FjWS5SBIixxxxxxx',
            ];

            $pay = Pay::wechat($this->config)->scan($order);

            if ($pay->return_code == 'SUCCESS' && $pay->result_code == 'SUCCESS') {
                // 加载视图,并把支付码的字符串发到页面中
                view('users.wxpay', [
                    'code' => $pay->code_url,
                    'sn' => $sn,
                ]);
            } else {
                die('订单状态不允许支付');
            }
        }

    }

    public function notify()
    {
        $log = new \libs\Log('wxpay');
        // 记录日志
        $log->log('接收到微信的消息');

        $pay = Pay::wechat($this->config);

        try {
            $data = $pay->verify(); // 是的，验签就这么简单！

            if ($data->result_code == 'SUCCESS' && $data->return_code == 'SUCCESS') {
                // 记录日志
                $log->log('支付成功');

                // 更新订单状态
                $order = new Order;
                // 获取订单信息
                $orderInfo = $order->findBySn($data->out_trade_no);

                if ($orderInfo['status'] == 0) {

                    // 开始事物
                    $order->startTrans();

                    // 设置订单为已支付状态
                    $ret1 = $order->setPay($data->out_trade_no);

                    // 更新用户余额
                    $user = new User;
                    $ret2 = $user->addMoney($orderInfo['money'], $orderInfo['user_id']);

                    // 判断
                    if ($ret1 && $ret2) {
                        // 提交事物
                        $order->commit();
                    } else {
                        // 回滚事物
                        $order->rollback();
                    }
                }
            }

        } catch (Exception $e) {
            // 记录日志
            $log->log('验证失败！' . $e->getMessage());
            var_dump($e->getMessage());
        }

        $pay->success()->send();
    }

    public function qrcode()
    {
        $qrCode = new QrCode('weixin://wxpay/bizpayurl?pr=hpVOrJ1');
        header('Content-Type: ' . $qrCode->getContentType());
        echo $qrCode->writeString();
    }
}
