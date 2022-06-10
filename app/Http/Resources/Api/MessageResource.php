<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'is_read' => $this->is_read,
            'order' => $this->order_data ?? '',
            'created_at' => (string)$this->created_at,
        ];
    }
}
