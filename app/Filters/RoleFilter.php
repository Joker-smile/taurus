<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class RoleFilter extends ModelFilter
{
    public function status($status)
    {
        return $this->where('status', $status);
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

    public function name($name)
    {
        return $this->where('name', 'like', '%' . $name . '%');
    }
}
