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

namespace Modules\ProvBase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Modem;

class ConfigfileCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'nms:configfile
        {filter? : cm|mta|tr069|configfile|qos - Only build configfiles of devices from type cm|mta|tr069 or all devices filtered via configfile or qos ID }
        {--I|id= : Modem, MTA or QoS ID }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build configfiles';

    /**
     * Filters that can be handled by this command.
     */
    protected $possibleFilters = [
        'cm',
        'configfile',
        'mta',
        'qos',
        'tr069',
    ];

    /**
     * Filter (from argument) to only build cable modem or mta configfiles
     *
     * @var string see possibleFilters above
     */
    protected $filter = '';

    /**
     * ID to build
     */
    protected $id = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($filter = '', $id = null)
    {
        $this->filter = $filter;
        $this->id = $id;

        parent::__construct();
    }

    /**
     * Creates all configfiles for all modems.
     * Logic from ConfigfileCommand nms:configfile.
     */
    public function handle()
    {
        Log::debug(__METHOD__.'() called with $filter “'.$this->filter.'” and $id “'.$this->id.'”');

        if ($this->input) {
            $this->processArguments();
        }

        $this->sanitizeInput();

        match ($this->filter) {
            'qos' => $this->buildConfigfilesByQosId(),
            'configfile' => $this->buildConfigfilesByConfigfileId(),
            default => $this->buildConfigfilesForDevices(),
        };
    }

    protected function processArguments()
    {
        if ($this->argument('filter')) {
            $this->filter = $this->argument('filter');
        }

        if ($this->option('id')) {
            $this->id = $this->option('id');
        }
    }

    protected function sanitizeInput()
    {
        if (! is_null($this->id)) {
            try {
                $this->id = intval($this->id);
                if ($this->id < 1) {
                    throw new \Exception;
                }
            } catch (\Throwable $ex) {
                Log::error(__METHOD__.'(): Parameter $this->id has to be null or a positive integer, '.$this->id.' given');

                exit;
            }
        }

        if (! $this->filter) {
            // build configfiles for all devices
            return;
        }

        if (! in_array($this->filter, $this->possibleFilters)) {
            $msg = 'Argument “filter” needs to be on of ['.implode(', ', $this->possibleFilters).'], “'.$this->filter.'” given. Exiting…';
            Log::error(__METHOD__.'(): '.$msg);
            $this->error($msg);

            exit(1);
        }

        if ($this->filter == 'qos') {
            if (! $this->id) {
                $msg = 'If “qos” is passed as first argument the optional qos_id needs to be specified, too. Exiting…';
                Log::error(__METHOD__.'(): '.$msg);
                $this->error($msg);

                exit(1);
            }

            return;
        }

        if ($this->filter == 'configfile') {
            if (! $this->id) {
                $msg = 'If “configfile” is passed as first argument the configfile_id is needed, too. Exiting…';
                Log::error(__METHOD__.'(): '.$msg);
                $this->error($msg);

                exit(1);
            }

            return;
        }
    }

    private function buildCfForSingleDevice()
    {
        $modem = Modem::find($this->option('id'));

        if ($modem) {
            $modem->make_configfile();
        }

        if (\Module::collections()->has('ProvVoip')) {
            $mta = \Modules\ProvVoip\Entities\Mta::find($this->option('id'));

            if ($mta) {
                $mta->make_configfile();
            }
        }

        if (! $modem && (! \Module::collections()->has('ProvVoip') || ! $mta)) {
            $this->error('Error: Could neither find modem nor MTA with ID: '.$this->option('id'));
        }
    }

    /**
     * Re(Build) all configfiles related to a given QoS ID
     */
    protected function buildConfigfilesByQosId()
    {
        if (! $this->id) {
            Log::error(__METHOD__.'(): $this->id cannot be “'.$this->id.'”');

            return;
        }

        foreach (Modem::TYPES as $type) {
            $modemQuery = Modem::join('configfile', 'configfile.id', 'modem.configfile_id')
                ->where('configfile.device', $type)
                ->where('modem.qos_id', $this->id)
                ->whereNull('configfile.deleted_at')
                ->select('modem.*');

            self::build_configfiles($modemQuery, $type);
        }
    }

    /**
     * Re(Build) all configfiles related to a given Configfile ID
     */
    protected function buildConfigfilesByConfigfileId()
    {
        if (! $this->id) {
            Log::error(__METHOD__.'(): $this->id cannot be “'.$this->id.'”');

            return;
        }

        $cf = Configfile::find($this->id);

        if (! $cf) {
            Log::warning(__METHOD__.'(): No configfile with id '.$this->id);

            return;
        }

        $cf->build_corresponding_configfiles();
        $cf->search_children(1);
    }

    /**
     * Re(Build) all configfiles related given device(s)
     */
    protected function buildConfigfilesForDevices()
    {
        if ($this->option('id')) {
            $this->buildCfForSingleDevice();

            return;
        }

        // Modem
        foreach (Modem::TYPES as $type) {
            if (! $this->filter || $this->filter == $type) {
                $modemQuery = Modem::join('configfile', 'configfile.id', 'modem.configfile_id')
                    ->where('configfile.device', $type)
                    ->whereNull('configfile.deleted_at')
                    ->select('modem.*');

                self::build_configfiles($modemQuery, $type);
            }
        }

        if (! \Module::collections()->has('ProvVoip')) {
            return;
        }

        // MTA
        if (! $this->filter || $this->filter == 'mta') {
            $mtaQuery = \Modules\ProvVoip\Entities\Mta::query();

            self::build_configfiles($mtaQuery, 'mta');
        }
    }

    /**
     * @param array  Objects of Modem or Mta
     */
    public static function build_configfiles($deviceQuery, $type)
    {
        $type = strtoupper($type);
        $num = (clone $deviceQuery)->count();

        Log::info("Building $num $type configfiles");

        $deviceQuery->chunk(1000, function ($devices) use ($num, $type) {
            static $i = 1;

            foreach ($devices as $device) {
                echo "$type: create config files: $i/$num\r";
                $device->make_configfile();

                $i++;
            }
        });

        echo "\n";
    }
}
