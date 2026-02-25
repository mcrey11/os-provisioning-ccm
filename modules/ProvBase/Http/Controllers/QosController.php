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

namespace Modules\ProvBase\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Modules\ProvBase\Entities\ProvBase;
use Modules\ProvBase\Entities\Qos;
use Nwidart\Modules\Facades\Module;

class QosController extends \BaseController
{
    use \App\Traits\ControllerWithCustomFields;

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Qos;
        }

        // label has to be the same like column in sql table
        $ret = [];

        $ret[] = [
            'form_type' => 'text',
            'name' => 'name',
            'description' => 'Name',
        ];

        $requestType = Request::get('type') ?? $model->type ?? 'default';
        $types = $this->getTypeFieldValues();
        $hidden = count($types) < 2 ? 1 : 0;
        $ret[] = [
            'form_type' => 'select',
            'name' => 'type',
            'description' => 'Type',
            'value' => $types,
            'hidden' => $hidden,
        ];

        if (! in_array($requestType, ['smartont', 'calixont', 'zyxelont'])) {
            $ret[] = [
                'form_type' => 'text',
                'name' => 'ds_rate_max',
                'description' => 'DS Rate [MBit/s]',
            ];
            $ret[] = [
                'form_type' => 'text',
                'name' => 'us_rate_max',
                'description' => 'US Rate [MBit/s]',
            ];
            $ret[] = [
                'form_type' => 'text',
                'name' => 'ds_name',
                'description' => 'DS PPPoE Name',
            ];
            $ret[] = [
                'form_type' => 'text',
                'name' => 'us_name',
                'description' => 'US PPPoE Name',
            ];
        }

        if (Module::collections()->has('SmartOnt')) {
            $ret[] = [
                'form_type' => 'text',
                'name' => 'vlan_id',
                'description' => 'VLAN ID',
            ];
            if (config('smartont.flavor.active') == 'GESA') {
                $ret[] = [
                    'form_type' => 'text',
                    'name' => 'ont_line_profile_id',
                    'description' => 'ONT line profile ID',
                ];
            }
            $ret[] = [
                'form_type' => 'text',
                'name' => 'gem_port',
                'description' => 'GEM port',
            ];
            if (config('smartont.flavor.active') == 'GESA') {
                $ret[] = [
                    'form_type' => 'text',
                    'name' => 'traffic_table_in',
                    'description' => 'Traffic table in',
                ];
                $ret[] = [
                    'form_type' => 'text',
                    'name' => 'traffic_table_out',
                    'description' => 'Traffic table out',
                ];
            }
        }

        // Add custom fields for the qos model depending on the type
        $requestType = Request::get('type') ?? $model->type ?? null;
        Qos::setCustomFieldDefinitions($requestType);
        $model->expandCustomFields();
        $formMethods = Qos::getCustomFormMethods();

        // Check if form can be filled with default values
        if (! $model->exists) {
            if (Module::collections()->has('Calix') && boolval($formMethods)) {
                $calix = \Modules\Calix\Entities\Calix::first();
                $model->custom_field__service_type = $calix->default_service_type;
            }
        }

        foreach (Qos::getCustomFields() as $field) {
            $method = $formMethods[$field];
            $ret[] = $this->$method($model, $field, []);
        }

        return $ret;
    }

    protected function getTypeFieldValues()
    {
        $ret = [
            'default' => 'Default',
        ];

        if (Module::collections()->has('Calix')) {
            $ret['calixont'] = 'ONT (Calix OLT)';
        }

        if (Module::collections()->has('Zyxel')) {
            $ret['zyxelont'] = 'ONT (Zyxel OLT)';
        }

        if (Module::collections()->has('SmartOnt')) {
            $ret['smartont'] = 'Smart ONT';
        }

        return $ret;
    }

    /**
     * Take care of the custom fields after validating them
     *
     * @author Patrick Reichel
     */
    protected function prepare_input_post_validation($data)
    {
        Qos::setCustomFieldDefinitions($data['type']);
        Qos::collapseCustomFieldsInInput($data);

        $pb = ProvBase::first();
        $data['ds_rate_max_help'] = $data['ds_rate_max'] * 1000 * 1000 * $pb->ds_rate_coefficient;
        $data['us_rate_max_help'] = $data['us_rate_max'] * 1000 * 1000 * $pb->us_rate_coefficient;

        return parent::prepare_input_post_validation($data);
    }

    /**
     * Set nullable fields.
     *
     * @author Patrick Reichel
     */
    public function prepare_input($data)
    {
        Qos::setCustomFieldDefinitions($data['type'] ?? null);

        $data = parent::prepare_input($data);

        $data['vlan_id'] = $data['vlan_id'] ?? 0;

        if (in_array($data['type'], ['smartont', 'calixont', 'zyxelont'])) {
            $data['ds_rate_max'] = $data['ds_rate_max'] ?? 0;
            $data['us_rate_max'] = $data['us_rate_max'] ?? 0;
        }

        return $data;
    }
}
