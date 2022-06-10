<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class AdminFilter extends ModelFilter
{
    public function nickName($nick_name)
    {
        return $this->where('nick_name', 'like', '%' . $nick_name . '%');
    }

    public function phone($phone)
    {
        return $this->where('phone', $phone);
    }

    public function depart($depart_id)
    {
        return $this->where('depart_id', $depart_id);
    }

    public function role($role_id)
    {
        return $this->where('role_id', $role_id);
    }

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

    public function isDelete($is_delete)
    {
        return $this->where('is_delete', $is_delete);
    }
}

