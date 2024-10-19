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

use App\Http\Controllers\BaseController;
use Illuminate\Validation\Rule;
use Modules\ProvBase\Entities\FirmwareUpgrade;

class FirmwareUpgradeController extends BaseController
{
    protected $many_to_many = [
        [
            'field' => 'fromconfigfile_ids',
        ],
    ];

    /**
     * defines the formular fields for the edit and create view
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new FirmwareUpgrade();
        }

        $fromConfigfiles = $model->fromConfigfile()->pluck('configfile_id', 'configfile_id')->toArray();
        $toConfigfiles = $model->configfile()->pluck('id', 'id')->toArray();

        if (! $model->exists) {
            $defaults = $this->getStartDateDefaults();
            $model->start_date = $defaults['date'];
            $model->start_time = $defaults['time'];
        }

        $form = [
            [
                'form_type' => 'date',
                'name' => 'start_date',
                'description' => trans('view.firmwareUpgrade.startDate'),
                'options' => ['placeholder' => 'YYYY-MM-DD'],
                'help' => trans('helper.start_date'),
            ],
            [
                'form_type' => 'time',
                'name' => 'start_time',
                'description' => trans('view.firmwareUpgrade.startTime'),
                'options' => ['placeholder' => 'HH:MM'],
                'help' => trans('helper.start_time'),
            ],
            [
                'form_type' => 'checkbox',
                'name' => 'restart_only',
                'description' => trans('view.firmwareUpgrade.restartOnly'),
                'help' => trans('helper.restart_only'),
            ],
            [
                'form_type' => 'text',
                'name' => 'cron_string',
                'description' => trans('view.firmwareUpgrade.cronString'),
                'options' => ['placeholder' => '* * * * *'],
                'help' => trans('helper.cron_string'),
            ],
            [
                'form_type' => 'text',
                'name' => 'batch_size',
                'description' => trans('view.firmwareUpgrade.batchSize'),
                'help' => trans('helper.batch_size'),
            ],
            [
                'form_type' => 'select',
                'name' => 'fromconfigfile_ids[]',
                'description' => trans('view.firmwareUpgrade.fromConfigfile'),
                'value' => $this->setupSelect2FieldForPivotTable($model, 'fromConfigfile', 'Configfile'),
                'options' => [
                    'class' => 'select2-ajax',
                    'multiple' => 'multiple',
                    'data-allow-clear' => 'true',
                    'ajax-route' => route('FirmwareUpgrade.select2', ['relation' => 'configfiles']),
                ],
                'selected' => $fromConfigfiles,
            ],
            [
                'form_type' => 'select',
                'name' => 'to_configfile_id',
                'description' => trans('view.firmwareUpgrade.toConfigfile'),
                'value' => $this->setupSelect2Field($model, 'Configfile'),
                'options' => [
                    'class' => 'select2-ajax',
                    'data-allow-clear' => 'true',
                    'ajax-route' => route('FirmwareUpgrade.select2', ['relation' => 'configfiles']),
                ],
                'selected' => $toConfigfiles,
            ],
            [
                'form_type' => 'textarea',
                'name' => 'firmware_match_string',
                'description' => trans('view.firmwareUpgrade.enterRegexp'),
                'options' => ['rows' => '5'],
                'help' => trans('helper.firmware_match_string'),
            ],
            [
                'form_type' => 'text',
                'name' => 'finished_date',
                'description' => trans('view.firmwareUpgrade.finishDate'),
                'options' => ['readonly' => 'readonly'],
                'help' => trans('helper.finished_time'),
            ],
        ];

        return $form;
    }

    private function getStartDateDefaults()
    {
        $now = now()->addDay();

        return [
            'date' => $now->toDateString(),
            'time' => $now->setHour(3)->setMinutes(30)->format('H:i'),
        ];
    }

    public function prepare_rules($rules, $data)
    {
        $rules['to_configfile_id'] = [
            isset($data['restart_only']) && $data['restart_only'] ? 'nullable' : 'required',
            'integer',
            'min:1',
            Rule::notIn($data['fromconfigfile_ids'] ?? []),
        ];

        $rules['firmware_match_string'] = ['nullable', function ($attribute, $value, $fail) {
            $lines = preg_split('/\r\n|\r|\n/', $value);

            // Validate each line as a valid string
            foreach ($lines as $line) {
                if (! is_string($line) || mb_strlen($line) > 255) {
                    $fail($attribute.' contains a line that is not a valid string or is too long.');
                }
            }
        }];

        return parent::prepare_rules($rules, $data);
    }
}
