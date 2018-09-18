<?php

namespace controllers;

use libs\Redis;
use models\Redbag;

class RedbagController
{
    // 初始化
    public function init()
    {
        $redis = Redis::getInstance();
        // 初始化库存量
        $redis->set('redbag_stock', 20);
        // 初始化空的集合
        $key = 'redbag_' . date('Ymd');
        $redis->sadd($key, '-1');
        // 设置过期时间
        $redis->expire($key, 3900);

    }

    // 监听队列
    public function makeOrder()
    {
        $redis = Redis::getInstance();
        $model = new Redbag;

        // 设置socket 永不超时
        ini_set('default_socket_timeout', -1);

        echo "开始监听红包队列...\r\n";

        // 循环监听一个列表
        while (true) {
            // 从队列中取数据,设置永不超时
            $data = $redis->brpop('redbag_orders', 0);
            /**
             * 返回的数据是一个数组用户的id
             */
            // 处理数据
            $userId = $data[1];

            // 下订单
            $model->create($userId);

            echo "========有人抢了红包!\r\n";
        }
    }

    // 抢夺红包
    public function rob()
    {
        // 1.判断有没有登录
        if (!isset($_SESSION['id'])) {
            echo json_encode([
                'status_code' => '401',
                'message' => '未登录!',
            ]);

            exit;
        }

        // 2.判断当前是否是9-10点之间
        if (date('H') < 9 || date('H') > 10) {
            echo json_encode([
                'status_code' => '403',
                'message' => '时间段不允许!',
            ]);

            exit;
        }

        // 3. 判断今天是否已经抢过
        $key = 'redbag_' . date('Ymd');
        $redis = Redis::getInstance();
        $exists = $redis->sismember($key, $_SESSION['id']);
        if ($exists) {
            echo json_encode([
                'status_code' => '403',
                'message' => '今天已经抢过了',
            ]);

            exit;
        }

        // 4.减少库存量(-1) 并返回减完之后的值
        $stock = $redis->decr('redbag_stock');
        if ($stock < 0) {
            echo json_encode([
                'statuc_code' => '403',
                'message' => '今天的红包已经减完了',
            ]);
            exit;
        }

        // 5.下单(放到队列))
        $redis->lpush('redbag_orders', $_SESSION['id']);

        // 6.把ID放到集合中(代表已经抢过了)
        $redis->sadd($key, $_SESSION['id']);

        echo json_encode([
            'status_code' => '200',
            'message' => '恭喜你~抢到了本站的红包~',
        ]);
    }

    public function rob_view()
    {
        view('redbag/rob');
    }

}
