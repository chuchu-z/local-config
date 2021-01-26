<?php

namespace task_server;

require_once __DIR__ . "/../comm_lib/AutoLoader.php";

class resetRedisCache
{
    public function main()
    {
        date_default_timezone_set('Asia/Chongqing');
        $now = date('Y-m-d H:i:s');

        /**
         * $params 脚本接收参数
         *
         * f postMan导出的json文件
         */
        $params = getopt('f:h:');

        $file = isset($params["f"]) ? trim($params["f"]) : '';
        $host = isset($params["h"]) ? trim($params["h"]) : '';

        if (empty($file)) {
            exit("[{$now}] - 请输入要转换的文件路径" . PHP_EOL);
        }

        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

        if (!file_exists($file)) {
            exit("[{$now}] - 文件不存在" . PHP_EOL);
        }

        $post = json_decode(file_get_contents($file), true);

        if (!$post) {
            exit("[{$now}] - 文件内容错误" . PHP_EOL);
        }

        $apiList = $this->getApiList($post['item']);
        $markdownContent = $this->getMarkdownContent($host);

        $line = '|%s|%s|[%s]|';
        foreach ($apiList as $key => $value) {

            /**
             * markdown接口文档列表
             */
            $markdownContent .= sprintf($line, $value['name'], $value['method'], $value['url']) . PHP_EOL;

            $value['login'] = '否';
            $value['description'] = !empty($value['description']) ? $value['description'] : $value['name'];

            foreach (['query', 'formdata'] as $query) {
                if (isset($value[$query])) {
                    foreach ($value[$query] as $k => $v) {
                        if ($v['key'] == 'token' && $v['value']) {
                            $value['login'] = '是';
                        }
                    }
                }
                $value[$query] = $this->buildTableLine($value, $query);
            }

            $value['status'] = '';
            $value['response'] = '| 无 |';
            $value['responseSuccessJson'] = '';
            $value['responseErrorJson'] = '';

            if ($value['responses']) {
                foreach ($value['responses'] as $type => $response) {
                    switch ($type) {
                        case 0:
                            $value['responseSuccessJson'] = $response['body'];
                            break;
                        default:
                            $value['responseErrorJson'] = $response['body'];
                            break;
                    }

                    if ($value['responseSuccessJson']) {
                        $responseSuccess = json_decode($value['responseSuccessJson'], true);
                        $resData = $responseSuccess['data'];
                        if (count($resData) == count($resData, 1)) {
                            $value['response'] = $this->responseParams( array_keys($resData) );
                        }
                    }

                    if ($value['responseErrorJson']) {
                        $responseError = json_decode($value['responseErrorJson'], true);
                        $value['status'] .= $this->responseStatus($responseError['code'], $responseError['msg']);
                    }

                }
            }

            $apiFile = $value['name'] . '.md';
            file_put_contents($apiFile, $this->formWork($value['description'], $value['url'], $host, $value['method'], $value['login'], $value['query'], $value['formdata'], $value['response'], $value['status'], $value['responseSuccessJson'], $value['responseErrorJson']));
        }

        $markdownListFile = $post['info']['name'] . '.md';
        file_put_contents($markdownListFile, $markdownContent, FILE_APPEND);

        exit("转换成功" . PHP_EOL);
    }

    public function getApiList($post)
    {
        $data = $apiList = [];
        foreach ($post as $key => $value) {
            $data = array_merge($data, $this->format($value['item']));
        }

        foreach ($data as $key => $value) {
            $apiList[] = $this->arrayFlatten($value);
        }
        return $apiList;
    }

    public function getMarkdownContent($host)
    {
        return <<<EOF
#### Host
**测试环境host：192.168.1.3 $host**

## 接口列表

|接口名称|请求方式|接口路径|
|:-:|:-:|:-:|

EOF;
    }

    public function format($items)
    {
        foreach ($items as $key => $value) {

            if (isset($value['item'])) {
                $list[] = $this->format($value['item']);
                continue;
            }

            $item['name'] = $value['name'];
            $item['description'] = $value['description'] ?? '';
            $item['method'] = $value['request']['method'];

            $raw = $value['request']['url']['raw'];
            $item['url'] = substr($raw, stripos($raw, '/'));

            if ($item['method'] == 'GET') {
                if (stripos($item['url'], '?') !== false) {
                    $item['url'] = substr($item['url'], 0, stripos($item['url'], '?'));
                }
                $item['query'] = $value['request']['url']['query'] ?? [];
            }

            if ($item['method'] == 'POST') {
                $item['formdata'] = $value['request']['body']['formdata'];
            }

            $item['responses'] = '';

            if (isset($value['response'])) {
                $item['responses'] = $this->formatResponse($value['response']);
            }

            $list[] = $item;
            unset($item);
        }

        return $list;
    }

    public function formatResponse($responses)
    {
        $data = [];
        foreach ($responses as $key => $response) {
            $data[$key]['name'] = $response['name'];
            $data[$key]['body'] = $response['body'];
        }
        return $data;
    }

    public function arrayFlatten($array)
    {
        if (isset($array[0])) {
            return $this->arrayFlatten($array[0]);
        }
        return $array;
    }

    public function buildTableLine($params, $key)
    {
        if (!isset($params[$key]) || empty($params[$key])) {
            return '| 无 |';
        }

        $formdata = '';
        $str = '| %s | %s| %s | %s |';
        foreach ($params[$key] as $value) {
            $formdata .= sprintf($str, $value['key'], isset($value['disabled']) ? 'false' : 'true' , gettype($value['value']), $value['description'] ?? '') . PHP_EOL;
        }
        return $formdata;
    }

    public function responseParams($params)
    {
        $content = <<<EOF
|`code` |int |状态码 |
|`msg` |string |提示信息 |
|`data` |object |返回内容 |

EOF;
        $line = '|`data.%s` | %s| %s |';
        foreach ($params as $key => $value) {
            $content .= sprintf($line, $value, gettype($value) , '') . PHP_EOL;
        }
        return $content;
    }

    public function responseStatus($code, $msg)
    {
        return sprintf('| %s | %s |', $code, $msg) . PHP_EOL;
    }

    public function formWork($description, $url, $host, $method, $login, $query, $formdata, $response, $status, $responseSuccessJson, $responseErrorJson)
    {
        return <<<EOF
**简要描述：**

- `$description`

**请求URL：**

- `$url`

**Host：**

- `测试环境host： 192.168.1.3   $host`

**请求方式：**

- `$method`

**是否需要登陆：**

- `$login`

**uri参数：**

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
$query

**body参数[参数格式:json]数：**

|参数名|必选|类型|说明|
|:----    |:---|:----- |-----   |
$formdata

**返回参数[返回格式:json]：**

|参数名|类型|说明|
|:-----  |:----- |-----                           |
$response

**成功返回示例**

```json
$responseSuccessJson
```

**失败返回示例**

```json
$responseErrorJson
```

**状态码**

|状态码|说明|
|:-----  |-----
| 20000 | 成功 |
$status

- `更多返回错误代码请看首页的错误代码描述`

EOF;
    }

}

(new resetRedisCache)->main();
