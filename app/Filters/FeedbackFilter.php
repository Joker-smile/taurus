<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class FeedbackFilter extends ModelFilter
{
    public function clientType($client_type)
    {
        return $this->where('client_type', $client_type);
    }

    public function phone($phone)
    {
        return $this->whereHas('user', function ($q) use ($phone) {
            $q->where('phone', $phone);
        });
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

    public function user($user_id)
    {
        return $this->where('user_id', $user_id);
    }
}
