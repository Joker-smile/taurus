<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class DeviceTypeFilter extends ModelFilter
{
    public function groups()
    {
        return $this->whereHas('groups', function ($q) {
            return $q->where('status', 1)->select('id');
        });
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }
}
