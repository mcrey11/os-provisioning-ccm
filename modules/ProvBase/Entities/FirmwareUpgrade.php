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

namespace Modules\ProvBase\Entities;

class FirmwareUpgrade extends \BaseModel
{
    public $table = 'firmware_upgrade';

    public static $name = 'Firmware Upgrade';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = [
        'fromconfigfile_ids',
    ];

    protected $fillable = [
        'start_date',
        'start_time',
        'finished_date',
        'cron_string',
        'batch_size',
        'to_configfile_id',
        'restart_only',
        'firmware_match_string',
    ];

    public static function boot()
    {
        parent::boot();

        self::observe(new \Modules\ProvBase\Observers\FirmwareUpgradeObserver);
    }

    /**
     * See FirmwareUpgradeController::prepare_rules as well
     */
    public function rules()
    {
        return [
            'batch_size' => ['nullable', 'integer', 'min:1'],
            'cron_string' => ['required_with:batch_size'],
            'fromconfigfile_ids' => ['required', 'array'],
            'restart_only' => ['boolean', 'prohibits:batch_size'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
        ];
    }

    // Name of View
    public static function view_headline()
    {
        return self::$name;
    }

    // link title in index view
    public function view_index_label()
    {
        return ['table' => $this->table,
            'index_header' => [
                $this->table.'.start_date',
                $this->table.'.start_time',
                $this->table.'.finished_date',
                $this->table.'.cron_string',
                $this->table.'.batch_size',
            ],
            'bsclass' => $this->get_bsclass(),
            'header' => $this->label(),
            'edit' => [],
            'eager_loading' => ['configfile'],
            'globalFilter' => ['sw_rev' => e(session('filter_data', ''))],
        ];
    }

    public function label()
    {
        $fromConfigfilesCount = $this->fromConfigfile()->count();
        $toConfigfileName = $this->configfile ? $this->configfile->name : '';
        $fromText = $fromConfigfilesCount == 1
            ? ucwords(trans('view.from')).' '.$this->fromConfigfile()->first()->name
            : "{$fromConfigfilesCount} configfiles";

        if ($this->restart_only || $toConfigfileName === null) {
            $restartOnlyText = $this->restart_only ? trans('view.firmwareUpgrade.restartOnly') : '';

            return "$restartOnlyText: {$fromText}";
        }

        return $fromText.' '.trans('view.to').' '.$toConfigfileName;
    }

    /**
     * Relation to the pivot `firmware_upgrade_configfile` table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function fromConfigfile()
    {
        return $this->belongsToMany(Configfile::class, 'firmware_upgrade_configfile', 'firmware_upgrade_id', 'configfile_id');
    }

    public function configfile()
    {
        return $this->belongsTo(Configfile::class, 'to_configfile_id');
    }

    /**
     * Format Configfile for edit view select field and allow for searching.
     *
     * @param  string|null  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function select2Configfiles(?string $search): \Illuminate\Database\Eloquent\Builder
    {
        return Configfile::select('id', 'name as text')
            ->withCount('firmwareUpgrades as count')
            ->when($search, function ($query, $search) {
                foreach (['name'] as $field) {
                    $query = $query->orWhere($field, 'ilike', "%{$search}%");
                }

                return $query;
            });
    }

    public function get_bsclass()
    {
        return $this->finished_date ? 'success' : 'info';
    }
}
