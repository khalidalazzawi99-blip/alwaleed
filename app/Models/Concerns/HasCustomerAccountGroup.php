<?php

namespace App\Models\Concerns;

use App\Models\Customer;

trait HasCustomerAccountGroup
{
    public function scopeForCustomerGroup($query, string $group)
    {
        return $query->where(function ($query) use ($group) {
            $query->whereHas('customer', fn ($customer) => $customer->forAccountGroup($group));
            if ($group === Customer::GROUP_GENERAL) {
                $query->orWhereNull('customer_id');
            }
        });
    }
}
