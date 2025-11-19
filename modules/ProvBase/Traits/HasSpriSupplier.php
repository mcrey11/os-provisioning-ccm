<?php

namespace Modules\ProvBase\Traits;

use Module;

/**
 * Stub trait for HasSpriSupplier functionality.
 * 
 * This ensures the trait file exists during RPM installation when SpriSupplierApi
 * may not be installed yet. The methods delegate to SpriSupplierApi functionality
 * when available, otherwise fall back gracefully.
 * 
 * This allows ProvBase to work whether SpriSupplierApi is installed or not.
 */
trait HasSpriSupplier
{
    /**
     * Filter tickets to only include those with S/PRI business cases.
     *
     * @param  array  $businessCases  Array of business case names (e.g., ['NEU', 'KUE-AG']). Empty array means all S/PRI business cases.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spri($businessCases = [])
    {
        // If SpriSupplierApi module is not available, fall back to regular tickets
        if (! Module::collections()->has('Ticketsystem') || ! Module::collections()->has('SpriSupplierApi')) {
            return $this->tickets();
        }

        // Try to use SpriTicket model if SpriSupplierApi is available
        if (class_exists('Modules\SpriSupplierApi\Entities\SpriTicket')) {
            $spriTicketModel = \Modules\SpriSupplierApi\Entities\SpriTicket::class;
            $query = $this->morphMany($spriTicketModel, 'ticketable');

            if (empty($businessCases)) {
                $query->whereHas('tickettypes.spriBusinessCases');
            } else {
                $query->whereHas('tickettypes.spriBusinessCases', function ($q) use ($businessCases) {
                    $q->whereIn('case', $businessCases);
                });
            }

            return $query;
        }

        return $this->tickets();
    }

    /**
     * Filter tickets to exclude those with S/PRI business cases.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function noSpri()
    {
        // If SpriSupplierApi module is not available, fall back to regular tickets
        if (! Module::collections()->has('Ticketsystem') || ! Module::collections()->has('SpriSupplierApi')) {
            return $this->tickets();
        }

        // Try to filter out S/PRI business cases if SpriSupplierApi is available
        if (class_exists('Modules\SpriSupplierApi\Entities\SpriTicket')) {
            return $this->tickets()->whereDoesntHave('tickettypes.spriBusinessCases');
        }

        return $this->tickets();
    }
}

