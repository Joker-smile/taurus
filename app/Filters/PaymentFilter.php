<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class PaymentFilter extends ModelFilter
{
    public function paymentNumber($payment_number)
    {
        return $this->where('payment_number', $payment_number);
    }

    public function collectionNumber($collection_number)
    {
        return $this->where('collection_number', $collection_number);
    }

    public function lessorNumber($lessor_number)
    {
        return $this->whereHas('order', function ($q) use ($lessor_number) {
            $q->where('type', 'finance')->where('lessor_number', 'like', '%' . $lessor_number . '%');
        });
    }

    public function lesseeNumber($lessee_number)
    {
        return $this->whereHas('order', function ($q) use ($lessee_number) {
            $q->where('type', 'finance')->where('lessee_number', 'like', '%' . $lessee_number . '%');
        });
    }

    public function payer($payer)
    {
        return $this->where('payer', 'like', '%' . $payer . '%');
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

    public function status($status)
    {
        if (is_array($status)) {
            return $this->whereIn('status', $status);
        }

        return $this->where('status', $status);
    }

    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function companyName($company_name)
    {
        return $this->whereHas('lessee', function ($q) use ($company_name) {
            $q->where('type', 'lessee')->where('company_name', 'like', '%' . $company_name . '%');
        });
    }

    public function lessorName($lessor_name)
    {
        return $this->whereHas('lessor', function ($q) use ($lessor_name) {
            $q->where('type', 'lessor')->where('real_name', 'like', '%' . $lessor_name . '%');
        });
    }

    public function id($id)
    {
        if (is_array($id)) {
            return $this->whereIn('id', $id);
        }

        return $this->where('id', $id);
    }

    public function lessor($lessor_id)
    {
        return $this->where('lessor_id', $lessor_id);
    }

    public function lessee($lessee_id)
    {
        return $this->where('lessee_id', $lessee_id);
    }
}
