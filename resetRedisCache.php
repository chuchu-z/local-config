<?php

/**
* @Author      chuchu-z
* @DateTime    2021-03-12
*
* 查看 redis key 脚本
*
* 字体颜色:
* 30m 黑色字
* 31m 红色字
* 32m 绿色字
* 33m 黄色字
* 34m 蓝色字
* 35m 紫色字
* 36m 天蓝字
* 37m 白色字
**/

namespace task_server;

use Aex\Lib\Helpers\Cache;

require_once __DIR__ . "/../comm_lib/AutoLoader.php";

class resetRedisCache
{
    public function main()
    {
        date_default_timezone_set('Asia/Chongqing');
        $now = date('Y-m-d H:i:s');

        /**
         * $params 脚本接收参数
         * k redis key
         * d 删除
         * r 重置
         * i init
         */
        $params = getopt('k:d:r:i:t:p:');

        $keys = isset($params["k"]) ? trim($params["k"]) : '';

        if (empty($keys)) {
            die("[{$now}] - 请输入要删除或重置的key [ php resetRedisCache.php -k key name]" . PHP_EOL);
        }

        /**
         * 选择初始化哪一个redis, 默认 default, 其他: depth, sns
         */
        $init = 'default';
        if (isset($params["i"])) {
            $init = isset($params["i"]) ? trim($params["i"]) : 'default';
        }

        /**
         * 选择初始化哪一个redis, 默认depth
         */
        $type = 'get';
        if (isset($params["t"])) {
            $type = isset($params["t"]) ? trim($params["t"]) : 'get';
        }

        $redis = Cache::init('Redis', $init)->getResource();
        $redisKeys = $redis->keys(strtolower($keys) . "*");

        $count = count($redisKeys);

        echo PHP_EOL . '| 获取该 keys 结果: ' . sprintf( "\033[1;35m%s\033[0m", $count ) . PHP_EOL;

        if ($count <= 0) die;

        $keyStr = "\033[0;31m%s\033[0m";
        $LineStr = "\033[0;34m%s\033[0m";
        foreach ($redisKeys as $redisKey) {
            echo PHP_EOL;
            echo sprintf( $LineStr, sprintf("%'-100s", '-') ) . PHP_EOL;
            echo sprintf( $LineStr, sprintf("%-2s", '|') ) . sprintf($keyStr, $redisKey) . PHP_EOL;
            echo sprintf( $LineStr, sprintf("%'-100s", '-') ) . PHP_EOL;
            echo $this->getFormatData($redis, $redisKey, $type);
            echo sprintf( $LineStr, sprintf("%'-100s", '-') ) . PHP_EOL;
            echo $this->getTime($redis->ttl($redisKey), $LineStr);
            echo sprintf( $LineStr, sprintf("%'-100s", '-') ) . PHP_EOL;
            echo PHP_EOL;
        }

        fwrite(STDOUT, sprintf( "\033[1;32m%s\033[0m", '请选择重置[r] 或是删除[d] : ') );
        $input = fgets(STDIN);//从控制台读取输入

        $continue = true;
        while ($continue) {
            $input = trim($input);
            if ($input != 'd' && $input != 'r') {
                fwrite(STDERR, sprintf( "\033[1;32m%s\033[0m", '请选择重置[r] 或是删除[d] : ') );
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

        exit($input == 'd' ? "删除完成" . PHP_EOL : "重置完成" . PHP_EOL);
    }

    public function getTime($time, $LineStr)
    {

        if ($time <= 0) {
            return sprintf( $LineStr, sprintf("%-2s", '|') ) .  sprintf( "\033[1;36m%s\033[0m", "剩余时间: $time" ) . PHP_EOL;
        }

        if ($time > 86400) {
            $day = floor($time / 86400);
            $hour = floor(($time - $day * 86400) / 3600);
            $minute = floor((($time - $day * 86400) - $hour * 3600) / 60);
            $endTime = sprintf( $LineStr, sprintf("%-2s", '|') ) . sprintf( "\033[1;36m%s\033[0m", "剩余时间: $day 天 $hour 时 $minute 分" ) . PHP_EOL;
        }

        if ($time < 86400) {
            $hour = floor($time / 3600);
            $minute = floor(($time - $hour * 3600) / 60);
            $endTime = sprintf( $LineStr, sprintf("%-2s", '|') ) . sprintf( "\033[1;36m%s\033[0m", "剩余时间: $hour 时 $minute 分" ) . PHP_EOL;
        }

        if ($time < 3600) {
            $minute = floor($time / 60);
            $endTime = sprintf( $LineStr, sprintf("%-2s", '|') ) . sprintf( "\033[1;36m%s\033[0m", "剩余时间: $minute 分" ) . PHP_EOL;
        }

        if ($time < 60) {
            $endTime = sprintf( $LineStr, sprintf("%-2s", '|') ) . sprintf( "\033[1;36m%s\033[0m", "剩余时间: $time 秒" ) . PHP_EOL;
        }
        return $endTime;
    }

    public function getFormatData($redis, $redisKey, $type = 'get')
    {
        $LineStr = "\033[0;34m%s\033[0m";
        if ($type == 'get') {
            $data = $redis->get($redisKey);
            $jsonData = json_decode($data, true);
            if ($jsonData && json_last_error() === JSON_ERROR_NONE) {
                $data = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
            }
            return sprintf( $LineStr, sprintf("%-2s", '|') ) . $data . PHP_EOL;
        }
        $data = $redis->{$type}($redisKey, 0, -1);
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        return sprintf( $LineStr, sprintf("%-2s", '|') ) . sprintf( "\033[1;36m%s\033[0m", $data ) . PHP_EOL;
    }

}


(new resetRedisCache)->main();
