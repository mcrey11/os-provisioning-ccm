<?php

/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Modules\ProvVoip\Entities;

use Illuminate\Support\Collection;
use Log;

class Phonenumber extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'phonenumber';

    // Add your validation rules here
    public function rules()
    {
        $rules = [
            'country_code' => ['required', 'regex:/^(?:\+|00)[1-9]\d{0,2}$/'],
            'prefix_number' => ['required', 'regex:/^\d+$/'],   // Validation rule 'integer' does not accept leading zeroes
            'number' => ['required', 'regex:/^\d+$/'],
            'mta_id' => ['required', 'exists:mta,id,deleted_at,NULL', 'min:1'],
            'port' => ['required', 'integer', 'min:0'],
            // inject id to rules (so it is passed to prepare_rules)
            'id' => $this->id ?: 0,
            /* 'active' => ['required', 'boolean'], */
            // TODO: check if password is secure and matches needs of external APIs (e.g. envia TEL)
        ];

        if (! \Module::collections()->has('ProvVoipEnvia')) {
            foreach (['username', 'sipdomain'] as $param) {
                $rules[$param][] = 'required';
            }
        }

        return $rules;
    }

    // Name of View
    public static function view_headline()
    {
        return 'Phonenumber';
    }

    // View Icon
    public static function view_icon()
    {
        return '<i class="fa fa-list-ol"></i>';
    }

    // AJAX Index list function
    // generates datatable content and classes for model
    public function view_index_label()
    {
        return [
            'table' => $this->table,
            'index_header' => [
                $this->table.'.number',
                'phonenumbermanagement.activation_date',
                'phonenumbermanagement.deactivation_date',
                'phonenr_state',
                'modem_city',
                'sipdomain',
                'reassignable',
                'username',
            ],
            'header' => 'Port '.$this->port.': '.$this->prefix_number.'/'.$this->number,
            'bsclass' => $this->get_bsclass(),
            'edit' => [
                'phonenumbermanagement.activation_date' => 'get_act',
                'phonenumbermanagement.deactivation_date' => 'get_deact',
                'phonenr_state' => 'get_state',
                'number' => 'build_number',
                'modem_city' => 'modem_city',
            ],
            'eager_loading' => ['phonenumbermanagement', 'mta.modem'],
            'sortsearch' => ['phonenr_state' => ['order' => 'false', 'search' => 'false'], 'modem_city' => ['order' => 'false', 'search' => 'false']],
            'filter' => ['phonenumber.number' => $this->number_query()],
        ];
    }

    public function number_query()
    {
        return "CONCAT(phonenumber.prefix_number,'/',phonenumber.number) ilike ?";
    }

    public function get_bsclass()
    {
        if ($this->reassignable) {
            return 'active';
        }

        // determine if this phonenumber should have a management
        $shouldHaveManagement = \Module::collections()->has('ProvVoipEnvia') ? true : false;

        if (! array_key_exists('phonenumbermanagement', $this->relations)) {
            $this->load('phonenumbermanagement:id,phonenumber_id,activation_date,deactivation_date');
        }

        if ($shouldHaveManagement && ! $this->phonenumbermanagement) {
            return 'warning';
        }

        if ($this->active) {
            return 'success';
        }

        return 'info';
    }

    public function get_state()
    {
        $management = $this->phonenumbermanagement;

        if (is_null($management)) {
            if ($this->active) {
                $state = 'Active.';
            } else {
                $state = 'Deactivated.';
            }
            $state .= ' No PhonenumberManagement existing!';
        } else {
            $act = $management->activation_date;
            $deact = $management->deactivation_date;

            if (! boolval($act)) {
                $state = 'No activation date set!';
            } elseif ($act > date('c')) {
                $state = 'Waiting for activation.';
            } else {
                if (! boolval($deact)) {
                    $state = 'Active.';
                } else {
                    if ($deact > date('c')) {
                        $state = 'Active. Deactivation date set but not reached yet.';
                    } else {
                        $state = 'Deactivated.';
                    }
                }
            }

            if (boolval($management->autogenerated)) {
                $state .= ' – PhonenumberManagement generated automatically!';
            }
        }

        return $state;
    }

    public function get_act()
    {
        $management = $this->phonenumbermanagement;

        $act = 'n/a';
        if ($management) {
            $act = $management->activation_date;
        }

        // reuse dates for view
        if (is_null($act)) {
            $act = '-';
        }

        return $act;
    }

    public function get_deact()
    {
        $management = $this->phonenumbermanagement;

        $deact = 'n/a';
        if ($management) {
            $deact = $management->deactivation_date;
        }

        // reuse dates for view
        if (is_null($deact)) {
            $deact = '-';
        }

        return $deact;
    }

    public function build_number()
    {
        return $this->prefix_number.'/'.$this->number;
    }

    public function asString()
    {
        if ($this->country_code == '0049' && $this->prefix_number[0] == 0) {
            return $this->prefix_number.$this->number;
        }

        $local = $this->prefix_number[0] == 0 ? substr($this->prefix_number, 1) : $this->prefix_number;

        return $this->country_code.$local.$this->number;
    }

    public function modem_city()
    {
        return $this->mta->modem->zip.' '.$this->mta->modem->city;
    }

    /**
     * ALL RELATIONS
     * link with mtas
     */
    public function mta()
    {
        return $this->belongsTo(Mta::class, 'mta_id');
    }

    // belongs to an mta
    public function view_belongs_to()
    {
        return $this->mta;
    }

    /**
     * Eager load relationships to prevent query duplication
     *
     * @return void
     */
    public function loadEditViewRelations()
    {
        $this->load([
            'phonenumbermanagement:id,phonenumber_id,activation_date,deactivation_date',
            'mta:id,modem_id,hostname,mac,configfile_id',
            'mta.modem:id,contract_id,name,salutation,company,department,firstname,lastname,street,house_number,zip,city,district,installation_address_change_date,mac,us_pwr,ont_state,internet_access',
            'mta.modem.contract:id,number,firstname,lastname,contract_start,group_contract,internet_access,has_telephony',
            'mta.modem.contract.modems:id,contract_id,name,salutation,company,department,firstname,lastname,street,house_number,zip,city,district,installation_address_change_date',
            'mta.modem.contract.modems.mtas:id,modem_id,hostname,mac',
        ]);
    }

    // View Relation.
    // see loadEditViewRelations() / add any new relation there
    public function view_has_many()
    {
        $ret = [];
        if (\Module::collections()->has('ProvVoip')) {
            $relation = $this->phonenumbermanagement;

            // can be created if no one exists, can be deleted if one exists
            if (is_null($relation)) {
                $ret['Edit']['PhonenumberManagement']['relation'] = new Collection();
                $ret['Edit']['PhonenumberManagement']['options']['hide_delete_button'] = 1;
            } else {
                $ret['Edit']['PhonenumberManagement']['relation'] = collect([$relation]);
                $ret['Edit']['PhonenumberManagement']['options']['hide_create_button'] = 1;
            }

            $ret['Edit']['PhonenumberManagement']['class'] = 'PhonenumberManagement';
        }

        if (\Module::collections()->has('ProvVoipEnvia')) {
            // TODO: auth - loading controller from model could be a security issue ?
            $ret['Edit']['EnviaAPI']['view']['view'] = 'provvoipenvia::ProvVoipEnvia.actions';
            $ret['Edit']['EnviaAPI']['view']['vars']['extra_data'] = \Modules\ProvVoip\Http\Controllers\PhonenumberController::_get_envia_management_jobs($this);
        }

        if (\Module::collections()->has('VoipMon')) {
            $ret['Monitoring']['Cdr']['class'] = 'Cdr';
            $ret['Monitoring']['Cdr']['relation'] = $this->cdrs()->orderBy('id', 'DESC')->get();
        }

        return $ret;
    }

    /**
     * Format MTAs for select 2 field and allow for seaching.
     *
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function select2Mtas(?string $search): \Illuminate\Database\Eloquent\Builder
    {
        return Mta::select('mta.id', 'mta.hostname', 'mta.mac', 'c.number', 'c.firstname', 'c.lastname')
            ->selectRaw('CONCAT(mta.hostname, \' (\' ,mta.mac, \') => \', c.number, \' - \', c.firstname, \' \', c.lastname) as text')
            ->join('modem as m', 'm.id', '=', 'mta.modem_id')
            ->join('contract as c', 'c.id', '=', 'm.contract_id')
            ->where('m.deleted_at', '=', null)
            ->where('c.deleted_at', '=', null)
            ->when($search, function ($query, $search) {
                return $query->where('mta.hostname', 'ilike', "%{$search}%")
                    ->orWhere('mta.mac', 'ilike', "%{$search}%")
                    ->orWhere('c.number', 'ilike', "%{$search}%")
                    ->orWhere('c.firstname', 'ilike', "%{$search}%")
                    ->orWhere('c.lastname', 'ilike', "%{$search}%");
            });
    }

    /**
     * Return a list of MTAs the current phonenumber can be assigned to.
     * special case activated envia TEL module:
     * - MTA has to belong to the same contract
     * - Installation address of current modem match installation address of new modem
     *
     * @author Patrick Reichel, Christian Schramm
     */
    public function mtasWhenEnviaEnabled()
    {
        if (! $this->exists) {
            return [null => trans('view.select.base', ['model' => trans('view.select.Mta')])];
        }

        $ret = [];
        $currentModem = $this->mta->modem;

        foreach ($this->mta->modem->contract->modems as $modem) {
            if ($this->isPhonenumberReassignmentAllowed($currentModem, $modem)) {
                foreach ($modem->mtas as $mta) {
                    $ret[$mta->id] = $mta->hostname.' ('.$mta->mac.')';
                }
            }
        }

        return $ret;
    }

    /**
     * Checks if a number can be reassigned to a given new modem
     *
     * @author Patrick Reichel, Christian Schramm
     */
    public function isPhonenumberReassignmentAllowed($currentModem, $newModem): bool
    {
        $intersect = array_intersect_assoc($currentModem->getAttributes(), $newModem->getAttributes());
        $check = ['salutation', 'company', 'department', 'firstname', 'lastname', 'street',
            'house_number', 'zip', 'city', 'district', 'installation_address_change_date',
        ];

        return ! (bool) array_diff_key(array_flip($check), $intersect);
    }

    /**
     * link to management
     */
    public function phonenumbermanagement()
    {
        return $this->hasOne(PhonenumberManagement::class);
    }

    /**
     * Phonenumbers can be related to EnviaOrders – if this module is active.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|null
     *                                                                    EnviaOrders if module ProvVoipEnvia is enabled, else null
     *
     * @author Patrick Reichel
     */
    public function enviaorders()
    {
        if (! \Module::collections()->has('ProvVoipEnvia')) {
            return optional();
        }

        return $this->belongsToMany(
            \Modules\ProvVoipEnvia\Entities\EnviaOrder::class,
            'enviaorder_phonenumber',
            'phonenumber_id',
            'enviaorder_id'
        )
            ->withTimestamps();
    }

    /**
     * Helper to detect if an envia TEL contract has been created for this phonenumber
     * You can either make a bool test against this method or get the id of a contract has been created
     *
     * @return misc:
     *			null if module ProvVoipEnvia is disabled
     *			false if there is no envia TEL contract
     *			external_contract_id for the contract the number belongs to
     *
     * @author Patrick Reichel
     */
    public function envia_contract_created()
    {
        // no envia module ⇒ no envia contracts
        if (! \Module::collections()->has('ProvVoipEnvia')) {
            return;
        }

        // the check is simple: if there is an external contract ID we can be sure that a contract has been created
        if (! is_null($this->contract_external_id)) {
            return $this->contract_external_id;
        } else {
            // take the most recent contract from modem
            // TODO: on handling of multiple contracts per modem: return all IDs
            $envia_contract = $this->mta->modem->enviacontracts->last();
            if ($envia_contract) {
                return $envia_contract->envia_contract_reference;
            } else {
                return false;
            }
        }
    }

    /**
     * Helper to detect if an envia TEL contract has been terminated for this phonenumber.
     * You can either make a bool test against this method or get the id of a contract if terminated
     *
     * @return misc:
     *			null if module ProvVoipEnvia is disabled
     *			false if there is no envia TEL contract or the contract is still active
     *			external_contract_id for the contract if terminated
     *
     * @author Patrick Reichel
     */
    public function envia_contract_terminated()
    {
        // no envia module ⇒ no envia contracts
        if (! \Module::collections()->has('ProvVoipEnvia')) {
            return;
        }

        // if there is no external id we assume that there is no envia contract
        if (is_null($this->contract_external_id)) {
            return false;
        }

        // as we are able to delete single phonenumbers from a contract (without deleting the contract if other numbers are attached)
        // we here have to count the numbers containing the current external contract id

        $envia_contract = \Modules\ProvVoipEnvia\Entities\EnviaContract::where('envia_contract_reference', '=', $this->contract_external_id)->first();

        // no contract – seems to be deleted
        if (is_null($envia_contract)) {
            return $envia_contract;
        }

        // no end date set: contract seems to be active
        if (is_null($envia_contract->external_termination_date) && is_null($envia_contract->end_date)) {
            return false;
        }

        return $this->contract_external_id;
    }

    /**
     * link to monitoring
     *
     * @author Ole Ernst
     */
    public function cdrs()
    {
        return $this->hasMany(\Modules\VoipMon\Entities\Cdr::class);
    }

    /**
     * Daily conversion (called by cron job)
     *
     * @author Patrick Reichel
     */
    public function daily_conversion()
    {
        $this->setActiveState();
        $this->setReassignableState();
    }

    /**
     * (De)Activate phonenumber depending on existance and (de)activation dates in PhonenumberManagement
     *
     * @author Patrick Reichel
     */
    public function setActiveState()
    {
        if (is_null($this->phonenumbermanagement)) {
            Log::debug('No PhonenumberManagement for phonenumber '.$this->prefix_number.'/'.$this->number.' (ID '.$this->id.') – will not change the active state.');

            return;
        }

        $this->active = $this->determineNextActiveState();

        if (! $this->isDirty('active')) {
            // Nothing to do
            return;
        }

        if ($this->active) {
            $this->reassignable = false;    // Active numbers are not reassignable
            Log::info('Activating phonenumber '.$this->prefix_number.'/'.$this->number.' (ID '.$this->id.').');
        } else {
            Log::info('Deactivating phonenumber '.$this->prefix_number.'/'.$this->number.' (ID '.$this->id.').');
        }

        $this->save();
    }

    protected function determineNextActiveState()
    {
        // Get the dates for this number
        $activationDate = $this->phonenumbermanagement->activation_date;
        $deactivationDate = $this->phonenumbermanagement->deactivation_date;

        if (! $activationDate) {
            // Activation date not set: inactive
            return false;
        }

        if ($activationDate > date('c')) {
            // Activation date not yet reached: inactive
            return false;
        }

        // From this point: activation date is today or in the past
        if (! $deactivationDate) {
            // No deactivation date: active
            return true;
        }

        if ($deactivationDate > date('c')) {
            // Deactivation date in the future: active
            return true;
        }

        // Deactivation date today or in the past: inactive
        return false;
    }

    /**
     * Check if a phonenumber is now reassignable.
     * This depends on a configurable timespan since the deactivition in the related managament.
     *
     * @author Patrick Reichel
     */
    public function setReassignableState()
    {
        // manually handled if no phonenumbermanagement
        if (! $this->phonenumbermanagement) {
            return;
        }

        // do not remove the reassignable flag
        if ($this->reassignable) {
            return;
        }

        // if number is still active it can not be reassigned
        if ($this->active) {
            return;
        }

        // no deactivation date set
        if (! $this->phonenumbermanagement->deactivation_date) {
            return;
        }

        $minWaitTime = config('provvoip.reassignableWaitTime');
        $dateTime = new \DateTime();
        $firstReassignable = $dateTime->sub(new \DateInterval('P'.$minWaitTime))->format('Y-m-d');

        // date for a possible reassignement not reached yet
        if ($firstReassignable < $this->phonenumbermanagement->deactivation_date) {
            return;
        }

        $this->reassignable = true;
        $this->save();

        Log::info('Phonenumber '.$this->prefix_number.'/'.$this->number.' (ID '.$this->id.') is now free for reassignment.');
    }

    /**
     * Dummy method to match BaseModel::delete() requirements
     *
     * We do not have to delete envia TEL orders here – this is later done by cron job.
     *
     * @author Patrick Reichel
     */
    public function deleteNtoMEnviaOrder($envia_order)
    {
        return $envia_order->delete();
    }

    /**
     * BOOT:
     * - init phone observer
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new \Modules\ProvVoip\Observers\PhonenumberObserver);
    }
}
