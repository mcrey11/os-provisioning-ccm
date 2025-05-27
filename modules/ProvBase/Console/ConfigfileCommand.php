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

use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConfigfileCommand extends Command implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'nms:configfile {filter?} {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make all configfiles';

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
     * @var string cm|mta|tr069
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

    protected function processArguments()
    {
        if ($this->argument('filter')) {
            $this->filter = $this->argument('filter');
        }
        if ($this->argument('id')) {
            $this->id = $this->argument('id');
        }
    }

    protected function sanitizeInput()
    {
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

        if ('qos' == $this->filter) {
            if (! $this->id) {
                $msg = 'If “qos” is passed as first argument the qos_id is needed, too. Exiting…';
                Log::error(__METHOD__.'(): '.$msg);
                $this->error($msg);
                exit(1);
            }

            return;
        }

        if ('configfile' == $this->filter) {
            if (! $this->id) {
                $msg = 'If “configfile” is passed as first argument the configfile_id is needed, too. Exiting…';
                Log::error(__METHOD__.'(): '.$msg);
                $this->error($msg);
                exit(1);
            }

            return;
        }

        if (! is_null($this->id)) {
            $this->warn('Passing an ID does not affect this filter. Building configfiles for all '.$this->filter.'…');
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->input) {
            $this->processArguments();
        }

        $this->sanitizeInput();

        $configfile = new \Modules\ProvBase\Entities\Configfile;
        $configfile->execute($this->filter, $this->id);
    }
}
