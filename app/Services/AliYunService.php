<?php

namespace App\Services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\SDK\Ocr\V20191230\Models\RecognizeIdentityCardRequest;
use AlibabaCloud\SDK\Ocr\V20191230\Ocr;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Log;
use Overtrue\EasySms\EasySms;

class AliYunService
{
    public function __construct()
    {
        AlibabaCloud::accessKeyClient(config('oss.key'), config('oss.secret'))
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
    }

    /** 生成人脸认证CertifyId
     * @param $data
     * @param $message
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function initFaceVerify($data, &$message)
    {
        try {
            $result = AlibabaCloud::rpc()
                ->product('Cloudauth')
                // ->scheme('https') // https | http
                ->version('2019-03-07')
                ->action('InitFaceVerify')
                ->method('POST')
                ->host('cloudauth.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'OuterOrderNo' => time(),
                        'ProductCode' => "ID_PRO",
                        'CertType' => 'IDENTITY_CARD',
                        'CertName' => $data['real_name'],
                        'CertNo' => $data['id_card_number'],
                        'MetaInfo' => $data['meta_info'],
                        'SceneId' => config('sms.face_auth_scenes'),
                        'Model' => 'PHOTINUS_LIVENESS'
                    ],
                ])
                ->request();
            $data = $result->toArray();
            Log::info('获取CertifyId:' . json_encode($data));
            if (empty($data['ResultObject'] ?? [])) {
                $message = $data['Message'] ?? '';
                return false;
            }

            return $data['ResultObject']['CertifyId'] ?? '';
        } catch (ClientException $e) {
            throw new \Exception($e->getErrorMessage());
        } catch (ServerException $e) {
            throw new \Exception($e->getErrorMessage());
        }
    }

    /**
     * 获取人脸认证结果
     * @param $data
     * @param $message
     * @return bool|string[]
     * @throws \Exception
     */
    public function describeFaceVerify($data, &$message)
    {
        try {
            $result = AlibabaCloud::rpc()
                ->product('Cloudauth')
                // ->scheme('https') // https | http
                ->version('2019-03-07')
                ->action('DescribeFaceVerify')
                ->method('POST')
                ->host('cloudauth.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'CertifyId' => $data['certify_id'],
                        'SceneId' => config('sms.face_auth_scenes'),
                    ],
                ])
                ->request();

            $data = $result->toArray();
            Log::info('验证CertifyId:' . json_encode($data));
            if (empty($data['ResultObject'] ?? [])) {
                $message = $data['Message'] ?? '';
                return false;
            }
            $message = config('AliErrorCode.describeFaceVerify.' . $data['ResultObject']['SubCode'] ?? 500);
            return [
                'passed' => $data['ResultObject']['Passed'] ?? 'F',
                'material_info' => $data['ResultObject']['MaterialInfo'] ?? '',
            ];
        } catch (ClientException $e) {
            throw new \Exception($e->getErrorMessage());
        } catch (ServerException $e) {
            throw new \Exception($e->getErrorMessage());
        }
    }

    /**
     * 发送短信
     * @param $phone
     * @param $content
     * @param $template
     * @param $code
     * @param $message
     * @return bool
     */
    public function sendSms($phone, $template, $code, $content, &$message)
    {
        $config = config('sms');
        $easySms = new EasySms($config);

        try {
            $easySms->send($phone, [
                'content' => $content,
                'template' => $template,
                'data' => [
                    'code' => $code
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('短信发送错误信息:' . json_encode($e->getExceptions()));
            $message = '发送失败，请稍后再试';
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @param string $type
     */
    public function idCardIdentify($url, $type = 'face')
    {
        $client = $this->createClient(config('oss.key'), config('oss.secret'));
        $recognizeIdentityCardRequest = new RecognizeIdentityCardRequest([
            "imageURL" => $url,
            "side" => $type
        ]);

        $result = $client->recognizeIdentityCard($recognizeIdentityCardRequest);

        return $result->body->data;
    }

    /**
     * 使用AK&SK初始化账号Client
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @return Ocr Client
     */
    protected function createClient($accessKeyId, $accessKeySecret)
    {
        $config = new Config([
            // 您的AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 您的AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // 访问的域名
        $config->endpoint = "ocr.cn-shanghai.aliyuncs.com";
        return new Ocr($config);
    }

}
