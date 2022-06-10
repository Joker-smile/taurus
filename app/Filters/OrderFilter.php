<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

class OrderFilter extends ModelFilter
{
    public function id($id)
    {
        if (is_array($id)) {
            return $this->whereIn('id', $id);
        }
        return $this->where('id', $id);
    }

    public function review($review_id)
    {
        return $this->where('review_id', $review_id);
    }

    public function lease($lease_id)
    {
        return $this->where('lease_id', $lease_id);
    }

    public function produce($produce_id)
    {
        return $this->where('produce_id', $produce_id);
    }

    public function salesman($salesman)
    {
        return $this->where('salesman', 'like', '%' . $salesman . '%');
    }

    public function lessor($lessor_id)
    {
        return $this->where('lessor_id', $lessor_id);
    }

    public function lessee($lessee_id)
    {
        return $this->where('lessee_id', $lessee_id);
    }

    public function status($status)
    {
        if (is_array($status)) {
            return $this->whereIn('status', $status);
        }

        return $this->where('status', $status);
    }

    public function isDelete($is_delete)
    {
        return $this->where('is_delete', $is_delete);
    }

    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function projectName($project_name)
    {
        return $this->where('project_name', 'like', '%' . $project_name . '%');
    }

    public function lesseeName($lessee_name)
    {
        return $this->whereHas('lessee', function ($q) use ($lessee_name) {
            $q->where('type', 'lessee')->where('real_name', 'like', '%' . $lessee_name . '%');
        });
    }

    public function companyName($company_name)
    {
        return $this->whereHas('lessee', function ($q) use ($company_name) {
            $q->where('type', 'lessee')->where('company_name', 'like', '%' . $company_name . '%');
        });
    }

    public function companyAddress($company_address)
    {
        return $this->whereHas('lessor', function ($q) use ($company_address) {
            $q->where('type', 'lessor')->where('company_address', $company_address);
        });
    }

    public function lessorName($lessor_name)
    {
        return $this->whereHas('lessor', function ($q) use ($lessor_name) {
            $q->where('type', 'lessor')->where('real_name', 'like', '%' . $lessor_name . '%');
        });
    }

    public function lesseeContractNumber($lessee_contract_number)
    {
        return $this->where('lessee_contract_number', $lessee_contract_number);
    }

    public function lessorContractNumber($lessor_contract_number)
    {
        return $this->where('lessor_contract_number', $lessor_contract_number);
    }

    public function constructionSite($construction_site)
    {
        return $this->where('construction_site', 'like', '%' . $construction_site . '%');
    }

    public function deviceName($device_name)
    {
        return $this->where('device_name', 'like', '%' . $device_name . '%');
    }

    public function brandName($brand_name)
    {
        return $this->where('brand_name', 'like', '%' . $brand_name . '%');
    }

    public function specificationModel($specification_model)
    {
        return $this->where('specification_model', 'like', '%' . $specification_model . '%');
    }

    public function rentalPrices($rental_prices)
    {
        if (in_array(null, $rental_prices)) {
            return $this;
        }

        $prices = [];
        foreach ($rental_prices as $price) {
            $prices[] = $price * 100;
        }

        return $this->whereBetween('rental_price', $prices);
    }

    public function entryTimes($entry_times)
    {
        if (in_array(null, $entry_times)) {
            return $this;
        }

        $entry_times[0] .= ' 00:00:00';
        $entry_times[1] .= ' 23:59:59';

        return $this->whereBetween('entry_time', $entry_times);
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

    public function rentNumber($rent_number)
    {
        return $this->where('rent_number', 'like', '%' . $rent_number . '%');
    }

    public function lessorNumber($lessor_number)
    {
        return $this->where('lessor_number', 'like', '%' . $lessor_number . '%');
    }

    public function lesseeNumber($lessee_number)
    {
        return $this->where('lessee_number', 'like', '%' . $lessee_number . '%');
    }

    public function produceNumber($produce_number)
    {
        return $this->where('produce_number', 'like', '%' . $produce_number . '%');
    }

    public function lesseeSettleNumber($lessee_settle_number)
    {
        return $this->where('lessee_settle_number', 'like', '%' . $lessee_settle_number . '%');
    }

    public function lessorSettleNumber($lessor_settle_number)
    {
        return $this->where('lessor_settle_number', 'like', '%' . $lessor_settle_number . '%');
    }

    public function lesseeFinanceNumber($lessee_finance_number)
    {
        return $this->where('lessee_finance_number', 'like', '%' . $lessee_finance_number . '%');
    }

    public function lessorFinanceNumber($lessor_finance_number)
    {
        return $this->where('lessor_finance_number', 'like', '%' . $lessor_finance_number . '%');
    }

    public function orderProgress($order_progress)
    {
        if (!is_array($order_progress)) {
            return $this;
        }

        if ($order_progress['order_progress'] == 'processing') {
            return $this->where('type', 'produce')
                ->where('status', '!=', 'review_success')
                ->where('lessor_id', $order_progress['lessor_id'])
                ->orWhere('type', 'settle')
                ->where('status', '!=', 'review_success')
                ->where('lessor_id', $order_progress['lessor_id']);
        }

        if ($order_progress['order_progress'] == 'finished') {
            return $this->where('type', 'finance')
                ->whereDoesntHave('payments', function ($q) {
                    return $q->where('type', 'payment');
                })
                ->where('lessor_id', $order_progress['lessor_id']);
        }

        if ($order_progress['order_progress'] == 'settle') {
            return $this->where('type', 'finance')
                ->where('status', 'review_success')
                ->whereHas('payments', function ($q) {
                    return $q->where('type', 'payment');
                })
                ->where('lessor_id', $order_progress['lessor_id']);
        }

        return $this->where('lessor_id', $order_progress['lessor_id'])
            ->where('type', 'produce')
            ->where('status', '!=', 'review_success')
            ->where('lessor_id', $order_progress['lessor_id'])
            ->orWhere('type', 'settle')
            ->where('status', '!=', 'review_success')
            ->where('lessor_id', $order_progress['lessor_id'])
            ->orWhere('type', 'finance')
            ->whereDoesntHave('payments')
            ->where('lessor_id', $order_progress['lessor_id'])
            ->orWhere('type', 'finance')
            ->where('status', 'review_success')
            ->whereHas('payments')
            ->where('lessor_id', $order_progress['lessor_id']);
    }

}
