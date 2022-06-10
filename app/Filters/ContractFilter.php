<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class ContractFilter extends ModelFilter
{
    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function userName($user_name)
    {
        return $this->whereHas('user', function ($q) use ($user_name) {
            $q->where('real_name', 'like', '%' . $user_name . '%');
        });
    }

    public function companyName($company_name)
    {
        return $this->whereHas('user', function ($q) use ($company_name) {
            $q->where('company_name', 'like', '%' . $company_name . '%');
        });
    }

    public function contractNumber($contract_number)
    {
        return $this->where('contract_number', $contract_number);
    }

    public function signYear($sign_year)
    {
        return $this->where('sign_year', $sign_year);
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

    public function contractTimes(array $contract_times)
    {
        if (in_array(null, $contract_times)) {
            return $this;
        }

        $contract_times[0] .= ' 00:00:00';
        $contract_times[1] .= ' 23:59:59';

        return $this->whereBetween('created_at', $contract_times);
    }
}
