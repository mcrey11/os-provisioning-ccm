<?php

namespace App\Traits;

use Lang;
use Module;

/**
 * Functionality to determine the status of jobs
 */
trait HasTickets
{
    /**
     * Add Ticket relation to an edit view. This method should be called inside
     * the view_has_many() method and adds a relationship panel to the edit
     * blade.
     *
     * @param  array  $ret
     * @return void
     */
    public function addViewHasManyTickets(&$ret, $tabName = 'Edit')
    {
        if (Module::collections()->has('Ticketsystem')) {
            $ret[$tabName]['Ticket']['class'] = 'Ticket';
            
            // Check if the entity uses the SpriSupplier trait
            if (Module::collections()->has('SpriSupplierApi') && method_exists($this, 'no_spri')) {
                $ret[$tabName]['Ticket']['relation'] = $this->no_spri()->get();
            } else {
                $ret[$tabName]['Ticket']['relation'] = $this->tickets;
            }
        }
    }

    public function searchTranslation($key, $object)
    {
        foreach (['dt_header', 'view', 'messages'] as $langFile) {
            $trans = '';

            if (Lang::has("$langFile.$key")) {
                $trans = trans("$langFile.$key");
            }

            if (Lang::has("$langFile.$object.$key")) {
                $trans = trans("$langFile.$object.$key");
            }

            if ($trans && ! is_array($trans)) {
                return $trans;
            }
        }

        return $key;
    }

    public function ticketInfoProperties()
    {
        return collect($this->attributes)->filter(function ($value, $key) {
            if ($value && ! in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                return true;
            }
        });
    }

    public function ticketInfos()
    {
        return $this->ticketInfoProperties()->mapWithKeys(function ($value, $attribute) {
            return [\App\Http\Controllers\BaseViewController::translate_label(ucfirst($attribute)) => $value];
        });
    }

    /**
     * Relation to Ticket if Ticketsystem is present.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        if (Module::collections()->has('Ticketsystem')) {
            return  $this->morphMany(\Modules\Ticketsystem\Entities\Ticket::class, 'ticketable');
        }

        return new \Illuminate\Database\Eloquent\Relations\HasMany($this->newQuery(), $this, '', '', '');
    }
}
