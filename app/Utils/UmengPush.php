<?php

namespace App\Utils;

use GuzzleHttp\Client;

class UmengPush
{
    // 单播；
    const UNICAST = 'unicast';
    // 列播；
    const LISTCAST = 'listcast';
    // 广播；
    const BROADCAST = 'broadcast';

    protected $ios_app_key;
    protected $ios_secret;
    protected $android_app_key;
    protected $android_secret;
    protected $push_url;//  添加友盟推送消息接口

    public function __construct()
    {
        $this->android_app_key = config('umeng.android_app_key');
        $this->android_secret = config('umeng.android_secret');
        $this->ios_app_key = config('umeng.ios_app_key');
        $this->ios_secret = config('umeng.ios_secret');
        $this->push_url = config('umeng.push_url');

        return $this;
    }

    /**
     * 推送信息到安卓
     * @param array $param
     * @param string $type
     * @return bool
     */
    public function pushToAndroid(array $param, $type = self::UNICAST)
    {
        $url = $this->push_url;
        $method = 'POST';
        $time = time();
        if (!($tokens = $param['tokens'] ?? '') && ($type == self::UNICAST || $type == self::LISTCAST)) return false;
        $body = [
            "ticker" => "筑融宝",    // 必填，通知栏提示文字
            "title" => $param['title'] ?? '',    // 必填，通知标题
            "text" => $param['content'] ?? '',    // 必填，通知文字描述
            "after_open" => "go_custom",    // 可选，默认为"go_app"，值可以为:
            //   "go_app": 打开应用
            //   "go_url": 跳转到URL
            //   "go_activity": 打开特定的activity
            //   "go_custom": 用户自定义内容。
            // "url"           => "xx",    // 当after_open=go_url时，必填。
            // 通知栏点击后跳转的URL，要求以http或者https开头
            "activity" => "xx",    // 当after_open=go_activity时，必填。
            // 通知栏点击后打开的Activity
            "custom" => json_decode($param['activity'] ?? '')  // 当display_type=message时, 必填
            // 当display_type=notification且
            // after_open=go_custom时，必填
            // 用户自定义内容，可以为字符串或者JSON格式。
        ];
        $payload = [
            "display_type" => "notification",    // 必填，消息类型: notification(通知)、message(消息)
            "body" => $body
        ];
        $payload['extra'] = $param['extra'] ?? '{}';
        $data = [
            "appkey" => $this->android_app_key,        // 必填，应用唯一标识
            "timestamp" => $time,    // 必填，时间戳，10位或者13位均可，时间戳有效期为10分钟
            "type" => $type,        // 必填，消息发送类型,其值可以为:
            //   unicast-单播
            //   listcast-列播，要求不超过500个device_token
            //   filecast-文件播，多个device_token可通过文件形式批量发送
            //   broadcast-广播
            //   groupcast-组播，按照filter筛选用户群, 请参照filter参数
            //   customizedcast，通过alias进行推送，包括以下两种case:
            //     - alias: 对单个或者多个alias进行推送
            //     - file_id: 将alias存放到文件后，根据file_id来推送
            // 当type=listcast时, 必填, 要求不超过500个, 以英文逗号分隔
            "payload" => $payload,
            "mipush" => "true",    // 可选，默认为false。当为true时，表示MIUI、EMUI、Flyme系统设备离线转为系统下发
            "mi_activity" => "com.zrbapp.anddroid.base.MainActivity",    // 可选，mipush值为true时生效，表示走系统通道时打开指定页面acitivity的完整包路径。
        ];
        if ($tokens) $data["device_tokens"] = $tokens;   // 当type=unicast时, 必填, 表示指定的单个设备
        $jsonData = json_encode($data);
        $sign = md5($method . $url . $jsonData . $this->android_secret);
        $url .= '?sign=' . $sign;

        return $this->curlPostJson($url, $jsonData);
    }

    /**
     * 推送消息到ios
     * @param array $param
     * @param string $type
     * @return bool
     */
    public function pushToIOS(array $param, $type = self::UNICAST)
    {
        $url = $this->push_url;
        $method = 'POST';
        $time = time();
        if (!($tokens = $param['tokens'] ?? '') && ($type == self::UNICAST || $type == self::LISTCAST)) return false;

        $alert = [
            "title" => $param['title'] ?? '',
            "body" => $param['content'] ?? '',
        ];
        $aps = [
            "alert" => $alert, // 当content-available=1时(静默推送)，可选; 否则必填。
            "content-available" => 1,  // 可选，代表静默推送
        ];
        $payload['aps'] = $aps;
        $payload['activity'] = json_decode($param['activity'] ?? '');
        $payload['extra'] = $param['extra'] ?? '{}';
        $data = [
            "appkey" => $this->ios_app_key,    // 必填，应用唯一标识
            "timestamp" => $time, // 必填，时间戳，10位或者13位均可，时间戳有效期为10分钟
            "type" => $type,      // 必填，消息发送类型,其值可以为:
            //   unicast-单播
            //   listcast-列播，要求不超过500个device_token
            //   filecast-文件播，多个device_token可通过文件形式批量发送
            //   broadcast-广播
            //   groupcast-组播，按照filter筛选用户群, 请参照filter参数
            "payload" => $payload,  // 必填，JSON格式，具体消息内容(iOS最大为2012B)
            "description" => "xx",      // 可选，发送消息描述，建议填写。
            "production_mode" => $param['env'] ?? true
        ];

        if ($tokens) $data["device_tokens"] = $tokens;   // 当type=unicast时, 必填, 表示指定的单个设备
        $jsonData = json_encode($data);
        $sign = md5($method . $url . $jsonData . $this->ios_secret);
        $url .= '?sign=' . $sign;

        return $this->curlPostJson($url, $jsonData);
    }

    protected function curlPostJson($url, $data)
    {
        $client = new Client();
        $response = $client->request('POST', $url, [
            'body' => $data,
            'timeout' => 10,
            'headers' => [
                'Content-Type: application/json'
            ]
        ]);

        return $response->getBody()->getContents();
    }

}
