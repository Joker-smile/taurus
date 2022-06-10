<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    public function realName($real_name)
    {
        return $this->where('real_name', 'like', '%' . $real_name . '%');
    }

    public function companyName($company_name)
    {
        return $this->where('company_name', 'like', '%' . $company_name . '%');
    }

    public function phone($phone)
    {
        return $this->where('phone', $phone);
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }

    public function idCardNumber($id_card_number)
    {
        return $this->where('id_card_number', $id_card_number);
    }

    public function bankName($bank_name)
    {
        return $this->where('bank_name', 'like', '%' . $bank_name . '%');
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

    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function creditCode($credit_code)
    {
        return $this->where('credit_code', $credit_code);
    }

    public function bankLicense($bank_license)
    {
        return $this->where('bank_license', $bank_license);
    }

    public function accountNumber($account_number)
    {
        return $this->where('account_number', $account_number);
    }

    public function companyAddress($company_address)
    {
        return $this->where('company_address', $company_address);
    }

    public function id($id)
    {
        if (is_array($id)) {
            return $this->whereIn('id', $id);
        }

        return $this->where('id', $id);
    }

    public function admin($admin_id)
    {
        return $this->where('admin_id', $admin_id);
    }
}
