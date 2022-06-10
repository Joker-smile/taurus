<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class DeviceFilter extends ModelFilter
{
    public function status($status)
    {
        return $this->where('status', $status);

    }

    public function lessorName($lessor_name)
    {
        return $this->whereHas('user', function ($q) use ($lessor_name) {
            $q->where('real_name', 'like', '%' . $lessor_name . '%');
        });
    }

    public function name($name)
    {
        return $this->where('name', 'like', '%' . $name . '%');
    }

    public function BrandName($brand_name)
    {
        return $this->where('brand_name', 'like', '%' . $brand_name . '%');
    }

    public function specificationModel($specification_model)
    {
        return $this->where('specification_model', 'like', '%' . $specification_model . '%');

    }

    public function companyAddress($company_address)
    {
        return $this->whereHas('user', function ($q) use ($company_address) {
            $q->where('company_address', $company_address);
        });
    }

    public function reviewTime(array $review_time)
    {
        if (in_array(null, $review_time)) {
            return $this;
        }

        $review_time[0] .= ' 00:00:00';
        $review_time[1] .= ' 23:59:59';

        return $this->whereBetween('review_time', $review_time);
    }

    public function user($user_id)
    {
        return $this->where('user_id', $user_id);
    }

    public function isDelete($is_delete)
    {
        return $this->where('is_delete', $is_delete);
    }

    public function type($type_id)
    {
        return $this->where('type_id', $type_id);
    }

    public function group($group_id)
    {
        return $this->where('group_id', $group_id);
    }

}
