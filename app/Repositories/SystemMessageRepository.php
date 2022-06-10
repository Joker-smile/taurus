<?php

namespace App\Repositories;

use App\Http\Resources\Api\MessageResource;
use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\User;
use App\Utils\UmengPush;

class SystemMessageRepository extends AbstractRepository
{
    public function model(): string
    {
        return SystemMessage::class;
    }

    public function push($system_message)
    {
        if ($system_message->user_ids != 0) {
            $user_ids = explode(',', $system_message->user_ids);
            $users = User::query()->whereIn('id', $user_ids)->get(['id', 'is_push_message', 'client_type', 'device_token']);
        } else {
            $users = User::query()->get();
        }

        foreach ($users as $user) {
            $message = Message::query()->create([
                'type' => 2,
                'user_id' => $user->id,
                'title' => $system_message->title,
                'content' => $system_message->content,
                'system_message_id' => $system_message->id,
                'image_url' => $system_message->image_url
            ]);

            if ($user->is_push_message == 1) {
                $data = [
                    'tokens' => $user->device_token,
                    'title' => $message->title ?? '',
                    'content' => $message->content ?? '',
                    'activity' => '{"type":0,"name":"system_message"}',
                    'extra' => json_encode(new MessageResource($message))
                ];
                $umeng = app(UmengPush::class);
                switch ($user->client_type) {
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
        $system_message->status = 'success';
        $system_message->save();
    }
}
