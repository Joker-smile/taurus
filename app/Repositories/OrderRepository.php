<?php

namespace App\Repositories;

use App\Http\Resources\Api\MessageResource;
use App\Http\Resources\Api\OrderResource;
use App\Models\Message;
use App\Models\Order;
use App\Utils\UmengPush;

class OrderRepository extends AbstractRepository
{
    public function model(): string
    {
        return Order::class;
    }

    public function push($order, $type)
    {
        switch ($type) {
            case 1:
                $title = '您有一条租赁订单';
                break;
            case 2:
                $title = '您的租赁订单已完成';
                break;
            case 3:
                $title = '您的租赁订单有新的结算信息';
                break;
            case 4:
                $title = '您的租赁订单已结算';
                break;
            default:
                $title = '';
                break;
        }

        $message = Message::query()->create([
            'type' => 1,
            'user_id' => $order->lessor_id,
            'title' => $title,
            'content' => $order->lessor_number,
            'order_id' => $order->id,
            'order_data' => new OrderResource($order)
        ]);
        $lessor = $order->lessor;
        if (!$lessor || !$lessor->device_token) {
            return;
        }

        $data = [
            'tokens' => $lessor->device_token,
            'title' => $title,
            'content' => $order->lessor_number,
            'activity' => '{"type":0,"name":"user_message"}',
            'extra' => json_encode(new MessageResource($message))
        ];

        $umeng = app(UmengPush::class);
        switch ($lessor->client_type) {
            case 1:
                $umeng->pushToAndroid($data);
                break;
            case 2:
                $umeng->pushToIOS($data);
                break;
            default:
                break;
        }
    }
}
