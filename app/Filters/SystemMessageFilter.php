<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class SystemMessageFilter extends ModelFilter
{
    public function clientType($client_type)
    {
        return $this->where('client_type', $client_type);
    }

    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }

    public function title($title)
    {
        return $this->where('title', 'like', '%' . $title . '%');
    }

    public function dateRange(array $date_range)
    {
        if (in_array(null, $date_range)) {
            return $this;
        }

        $date_range[0] .= ' 00:00:00';
        $date_range[1] .= ' 23:59:59';

        return $this->whereBetween('created_at', $date_range);
    }
}
