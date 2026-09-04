<?php

namespace Modules\BillingBase\Helpers;

use Modules\ProvBase\Entities\Contract;

class BillingAnalysis
{
    public static function getContractData()
    {
        return [
            'total' => Contract::where('contract_start', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('contract_end')
                      ->orWhere('contract_end', '>', now());
                })
                ->count(),
        ];
    }
}
