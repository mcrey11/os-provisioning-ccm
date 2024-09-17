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

namespace Modules\ProvVoip\Http\Controllers;

use Bouncer;
use Modules\ProvVoip\Entities\Phonenumber;

class PhonenumberController extends \BaseController
{
    /**
     * if set to true a create button on index view is available - set to true in BaseController as standard
     */
    protected $index_create_allowed = false;
    protected $save_button_name = 'Save / Restart';

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Phonenumber;
        }

        $hasProvVoipEnvia = \Module::collections()->has('ProvVoipEnvia');
        $provVoip = \Modules\ProvVoip\Entities\ProvVoip::first();

        $roOption = $model->contract_external_id && $hasProvVoipEnvia ? ['readonly'] :
        ['placeholder' => 'Leave empty on phonenumbers to be created.'];
        $ajaxOption = [
            'class' => 'select2-ajax',
            'ajax-route' => route('Phonenumber.select2', ['relation' => 'mtas']),
        ];

        $help = 'Can be used to assign the phonenumber (and related data) to another MTA.';
        if ($hasProvVoipEnvia) {
            $help .= 'MTA has to belong to the same contract and modem installation addresses have to be equal.';
            $ajaxOption = [];
        }

        // label has to be the same like column in sql table
        $ret = [
            [
                'form_type' => 'select',
                'name' => 'mta_id',
                'description' => 'MTA',
                'value' => $hasProvVoipEnvia ?
                    $model->mtasWhenEnviaEnabled() :
                    $this->setupSelect2Field($model, 'Mta'),
                'hidden' => 'C',
                'help' => $help,
                'options' => $ajaxOption,
            ],
            [
                'form_type' => 'text',
                'name' => 'port',
                'description' => 'Port',
                'space' => 1,
            ],
            [
                'form_type' => 'text',
                'name' => 'country_code',
                'description' => 'International prefix',
                'help' => 'Usually, 4 digit number required for international calls.',
                'autocomplete' => [],
                'init_value' => $provVoip->default_country_code,
            ],
            [
                'form_type' => 'text',
                'name' => 'prefix_number',
                'description' => 'Prefix Number',
                'help' => 'Has to be available on modem address.',
            ],
            [
                'form_type' => 'text',
                'name' => 'number',
                'description' => 'Number',
                'help' => 'The phonenumber to port or a free number given by your provider.',
                'space' => 1,
            ],
            [
                'form_type' => 'text',
                'name' => 'username',
                'description' => 'Username',
                'options' => $roOption,
            ],
            [
                'form_type' => 'text',
                'name' => 'password',
                'description' => 'Password',
                'options' => $hasProvVoipEnvia ? ['placeholder' => 'Autofilled if empty.'] : [],
            ],
            [
                'form_type' => 'text',
                'name' => 'sipdomain',
                'description' =>'SIP domain',
                'autocomplete' => [],
                'init_value' => $hasProvVoipEnvia ? '' : ($model->exists ? $model->sipdomain : $provVoip->default_sip_registrar),
                'options' =>  $roOption,
                'space' => 1,
            ],
        ];

        $ret[] = $this->getActiveCheckbox($model);
        $ret[] = $this->getReassignableCheckbox($model);

        return $ret;
    }

    /**
     * Helper to create the array holding the “active” checkbox
     *
     * @author Patrick Reichel
     */
    protected function getActiveCheckbox($phonenumber)
    {
        // with a managament attached the active state is handle by this
        // and: once a phonenumber is marked as reassignable it cannot be activated again
        if ($phonenumber->phonenumbermanagement || $phonenumber->reassignable) {
            $symbolStyle = 'font-size: 1.4em; padding-top:0.4em; padding-left: 4.8em';
            if ($phonenumber->active) {
                $activeState = '1';
                $activeSymbol = '<div style="color: #080; '.$symbolStyle.'">✔</div>';
            } else {
                $activeState = '0';
                $activeSymbol = '<div style="color: #f00; '.$symbolStyle.'">✘</div>';
            }

            return [
                'form_type' => 'html',
                'name' => 'active',
                'description' => 'Active',
                'html' => '<div class="col-md-7 order-3">
                        <input name="active" type="hidden" id="active" value="'.$activeState.'">'.$activeSymbol.'
                    </div>',
                'help' => trans('helper.Phonenumber_ActiveWithManagement'),
            ];
        }

        // default checkbox on phonenumbers w/o management (this includes new ones)
        return [
            'form_type' => 'checkbox',
            'name' => 'active',
            'description' => 'Active',
            'help' => trans('helper.Phonenumber_ActiveWithoutManagement'),
        ];
    }

    /**
     * Helper to create the array holding the “active” checkbox
     *
     * @author Patrick Reichel
     */
    protected function getReassignableCheckbox($phonenumber)
    {
        // do not show on new or active phonenumbers
        if (! $phonenumber->exists || $phonenumber->active) {
            return [
                'form_type' => 'checkbox',
                'name' => 'reassignable',
                'description' => 'Reassignable',
                'hidden' => '1',
                'help' => trans('helper.Phonenumber_ReassignableWithoutManagement'),
            ];
        }

        // with a managament attached the reassignable state is handle by daily conversion
        // and: once a phonenumber is marked as reassignable that cannot be reverted again
        // (because for that we would need to check for other numbers – possible race condition)
        if ($phonenumber->phonenumbermanagement || $phonenumber->reassignable) {
            $symbolStyle = 'font-size: 1.4em; padding-top:0.4em; padding-left: 4.8em';
            if ($phonenumber->reassignable) {
                $reassignableState = '1';
                $reassignableSymbol = '<div style="color: #080; '.$symbolStyle.'">✔</div>';
                $help = trans('helper.Phonenumber_ReassignableFinal');
            } else {
                $reassignableState = '0';
                $reassignableSymbol = '<div style="color: #f00; '.$symbolStyle.'">✘</div>';
                $help = trans('helper.Phonenumber_ReassignableWithManagement');
            }

            return [
                'form_type' => 'html',
                'name' => 'reassignable',
                'description' => 'Reassignable',
                'html' => '<div class="col-md-7 order-3">
                        <input name="reassignable" type="hidden" id="reassignable" value="'.$reassignableState.'">'.$reassignableSymbol.'
                    </div>',
                'help' => trans($help),
            ];
        }

        // default (= no management, number not active or reassignable)
        return [
            'form_type' => 'checkbox',
            'name' => 'reassignable',
            'description' => 'Reassignable',
            'help' => trans('helper.Phonenumber_ReassignableWithoutManagement'),
        ];
    }

    /**
     * Adds the check for unique ports per MTA.
     *
     * @author Patrick Reichel
     */
    public function prepare_rules($rules, $data)
    {
        // check if there is an phonenumber id (= updating), else set to -1 (a not used database id)
        $id = $rules['id'];
        if (! $id) {
            $id = -1;
        }

        // remove id from rules
        unset($rules['id']);

        // verify that the chosen port is unique for this mta
        $rules['port'][] = 'unique:phonenumber,port,'.$id.',id,deleted_at,NULL,mta_id,'.$data['mta_id'];

        // a phonenumber can only exist once for the same country_code/prefix_number combination
        $rules['number'][] = 'unique:phonenumber,number,'.$id.',id,deleted_at,NULL,country_code,'.$data['country_code'].',prefix_number,'.$data['prefix_number'];

        return parent::prepare_rules($rules, $data);
    }

    /**
     * Get all management jobs for envia TEL
     *
     * @author Patrick Reichel
     *
     * @param  $phonenumber  current phonenumber object
     * @return array containing linktexts and URLs to perform actions against REST API
     */
    public static function _get_envia_management_jobs($phonenumber)
    {
        if (Bouncer::cannot('view', 'Modules\ProvVoipEnvia\Entities\ProvVoipEnvia')) {
            return;
        }

        $provvoipenvia = new \Modules\ProvVoipEnvia\Entities\ProvVoipEnvia();

        return $provvoipenvia->get_jobs_for_view($phonenumber, 'phonenumber');
    }
}
