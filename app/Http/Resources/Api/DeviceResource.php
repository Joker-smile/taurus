<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'group_id' => $this->group_id,
            'type' => $this->type ? $this->type->name : '',
            'group' => $this->group ? $this->group->name : '',
            'name' => $this->name,
            'specification_model' => $this->specification_model,
            'brand_name' => $this->brand_name,
            'license_number' => $this->license_number,
            'new_rate' => $this->new_rate,
            'remark' => $this->remark,
            'status' => $this->status,
            'images' => $this->images,
            'number' => $this->number,
            'review_time' => (string)$this->review_time,
            'review_message' => $this->review_message
        ];
    }
}
