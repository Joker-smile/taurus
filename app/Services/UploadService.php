<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile as File;
use OSS\Core\OssException;
use OSS\OssClient;

class UploadService
{
    /**
     * @throws OssException
     * @throws \Exception
     * @example [
     *
     * $imageName = storage_path().'/'.'test'.'.jpg';
     * file_put_contents($imageName,file_get_contents('http://s.mtaso.com/worker/img/apps/test.jpg'));
     * $result = $upload->upload(new UploadedFile($imageName,basename($imageName)),'/worker/img/apps/','test2');
     * ]
     */
    public function upload(File $file, string $filename = '', $type = 'avatar')
    {
        $key = config('oss.key');
        $secret = config('oss.secret');
        $endpoint = config('oss.endpoint');
        $bucket = config('oss.bucket');

        switch ($type) {
            case 'avatar':
                $directory = 'avatars';
                break;
            case 'device':
                $directory = 'devices';
                break;
            case 'business_license':
                $directory = 'businessLicenses';
                break;
            case 'id_card':
                $directory = 'idCards';
                break;
            case 'message':
                $directory = 'messages';
                break;
            case 'feedback':
                $directory = 'feedback';
                break;
            case 'contract':
                $directory = 'contracts';
                break;
            case 'produce':
                $directory = 'produces';
                break;
        }

        $client = new OssClient($key, $secret, $endpoint);
        if (!$filename) {
            $filename = trim($directory, '/') . '/' . $file->getClientOriginalName();
        } else {
            $filename = trim($directory, '/') . '/' . $filename . '.' . $file->extension();
        }
        $mime = $file->getClientMimeType();

        try {
            $result = $client->uploadFile($bucket, $filename, $file->path(), [
                'Content-Type' => $mime,
            ]);
        } catch (OssException $e) {
            throw new Exception($e->getMessage(), 400);
        }

        return $this->parseUrl($result['info']['url'], $filename, $type);
    }

    /**
     * 删除oss上的文件
     * @param $filename :'worker/img/apps/0trMAxSweYxaBHM9DduUGr7WtiBnlZyrnSymfyhu.jpg' 要具体到文件路径跟文件后缀;
     * @return bool
     * @throws OssException
     */
    public function delete($filename)
    {
        $key = config('oss.key');
        $secret = config('oss.secret');
        $endpoint = config('oss.endpoint');
        $bucket = config('oss.bucket');
        $client = new OssClient($key, $secret, $endpoint);

        try {
            $result = $client->deleteObject($bucket, $filename);
        } catch (OssException $e) {
            throw new Exception($e->getMessage(), 400);
        }
        if ($result['info'] ?? '') return true;

        return false;
    }

    private function parseUrl(string $oss_url, string $filename, $type)
    {
        $oss_url = str_replace('http://', 'https://', $oss_url);
        if (config('oss.domain') && $type != 'id_card') {
            return config('oss.domain') . '/' . $filename;
        }

        return $oss_url;
    }
}
