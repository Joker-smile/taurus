<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class YongYouService
{
    public function bankAccountAuth($data, $message = '')
    {
        $api_code = config('app.yong_you_bank_auth_code');
        $url = config('app.yong_you_bank_auth_url');
        $params = [
            'idNumber' => $this->trimAll($data['id_card_number']),
            'userName' => $data['real_name'],
            'cardNo' => $this->trimAll($data['account_number']),

        ];
        Log::info('银行卡验证信息:' . json_encode($params));
        $header = ['apicode' => $api_code, 'Content-Type' => 'application/json'];
        $client = new Client();

        try {
            $response = $client->request('POST', $url, [
                'body' => json_encode($params),
                'timeout' => 10,
                'headers' => $header
            ]);
        } catch (\Exception $exception) {
            $message = '未知服务错误,请稍后再试';
            Log::error('银行卡号认证错误:' . $exception->getMessage());

            return $message;
        }

        $result = json_decode($response->getBody()->getContents());
        if ($result->code != '400100') {
            $message = $result->message;
        }

        return $message;
    }

    protected function trimAll($str)//删除空格
    {
        $old_char = array(" ", "　", "\t", "\n", "\r");
        $new_char = array("", "", "", "", "");

        return str_replace($old_char, $new_char, $str);

    }
}
