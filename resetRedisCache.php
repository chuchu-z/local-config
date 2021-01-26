<?php

namespace task_server;

use Aex\Lib\Helpers\Cache;

require_once __DIR__ . "/../comm_lib/AutoLoader.php";

class resetRedisCache
{
    public function main()
    {

        $redis = Cache::init('Redis')->getResource();
        date_default_timezone_set('Asia/Chongqing');
        $now = date('Y-m-d H:i:s');

        /**
         * $params 脚本接收参数
         * u 用户ID
         * t 任务ID
         * i 邀请任务
         */
        $params = getopt('k:d:r:');

        $keys = isset($params["k"]) ? trim($params["k"]) : '';

        if (empty($keys)) {
            die("[{$now}] - 请输入要删除或重置的key [ php resetRedisCache.php -k key name]" . PHP_EOL);
        }

        $redisKeys = $redis->keys($keys."*");
        $count = count($redisKeys);

        echo '获取该 keys 结果: ' . $count . PHP_EOL;

        if ($count <= 0) die;

        $keyStr = "\033[0;31m%s\033[0m";
        foreach ($redisKeys as $redisKey) {
            echo PHP_EOL;
            echo sprintf("%'-100s", '-') . PHP_EOL;
            echo sprintf("%-2s", '|') . sprintf($keyStr, $redisKey) . PHP_EOL;
            echo sprintf("%'-100s", '-') . PHP_EOL;
            echo sprintf("%-2s", '|') . $redis->get($redisKey) . PHP_EOL;
            echo sprintf("%'-100s", '-') . PHP_EOL;
            echo $this->getTime($redis->ttl($redisKey));
            echo sprintf("%'-100s", '-') . PHP_EOL;
            echo PHP_EOL;
        }

        fwrite(STDOUT, '请选择重置[r] 或是删除[d] ? : ');
        $input = fgets(STDIN);//从控制台读取输入

        $continue = true;
        while ($continue) {
            $input = trim($input);
            if ($input != 'd' && $input != 'r') {
                fwrite(STDERR, '请选择重置[r]或是删除[d] ? : ');
                $input = fgets(STDIN);
            } else {
                $continue = false;
            }
        }

        if ($input == 'd') {
            foreach ($redis->keys($keys."*") as $redisKey) {
                $redis->del($redisKey);
            }
        }

        if ($input == 'r') {
            foreach ($redis->keys($keys."*") as $redisKey) {
                $redis->set($redisKey, '');
            }
        }

        exit("重置完成" . PHP_EOL);
    }

    public function getTime($time)
    {
        if ($time <= 0) {
            return sprintf("%-2s", '|') . "剩余时间: $time" . PHP_EOL;
        }

        if ($time > 86400) {
            $day = floor($time / 86400);
            $hour = floor(($time - $day * 86400) / 3600);
            $minute = floor((($time - $day * 86400) - $hour * 3600) / 60);
            return sprintf("%-2s", '|') . "剩余时间: $day 天 $hour 时 $minute 分" . PHP_EOL;
        }

        if ($time < 86400) {
            $hour = floor($time / 3600);
            $minute = floor(($time - $hour * 3600) / 60);
            return sprintf("%-2s", '|') . "剩余时间: $hour 时 $minute 分" . PHP_EOL;
        }

        if ($time < 3600) {
            $minute = floor($time / 60);
            return sprintf("%-2s", '|') . "剩余时间: $minute 分" . PHP_EOL;
        }

        if ($time < 60) {
            return sprintf("%-2s", '|') . "剩余时间: $time 秒" . PHP_EOL;
        }
    }


}


(new resetRedisCache)->main();
