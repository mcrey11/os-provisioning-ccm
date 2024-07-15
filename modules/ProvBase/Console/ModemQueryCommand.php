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
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvBase\Entities\ProvBase;

class ModemQueryCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = '
        nms:modem-query
        {--contract-id= : Filter modems by contract ID (not contract number!) - Standby contract -> 1}
        {--from= : Filter modems by configfile ID}
        {--online : Filter for modems seen on last PHY update cycle}
        {--city= : Filter modems by city (wildcard)}
        {--district= : Filter modems by district / installation (wildcard)}
        {--street= : Filter modems by street (wildcard)}
        {--model= : Filter modems by model (wildcard)}
        {--sw-rev= : Filter modems by software revision (wildcard)}
        {--apartment_nr=* : Filter modems by apartment_nr}
        {--file= : File containing one MAC address per line f modems to be processed}
        {--limit= : Maximum amount of modems to handle}
        {--oid=* : OID to query for}
        {--sleep= : Seconds to wait between modem restarts, no effect in dry-run mode}
        {--dry-run : Do not excute command, just print affected modems}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query modems for SNMP OIDs.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $roCommunity = ProvBase::first()->ro_community;
        $modems = Modem::query();

        if ($this->option('contract-id')) {
            $modems->where('contract_id', $this->option('contract-id'));
        }

        if ($this->option('from')) {
            Configfile::findOrFail($this->option('from'));
            $modems->where('configfile_id', $this->option('from'));
        }

        if ($this->option('online')) {
            $modems->where('phy_updated_at', '>=', now()->subMinute(5))->count();
        }

        foreach (['city', 'district', 'street'] as $filter) {
            if ($this->option($filter)) {
                $modems->where($filter, 'like', "%{$this->option($filter)}%");
            }
        }

        if ($this->option('model')) {
            $modems->where('model', 'like', "%{$this->option('model')}%");
        }

        if ($this->option('sw-rev')) {
            $modems->where('sw_rev', 'like', "%{$this->option('sw-rev')}%");
        }

        if ($this->option('apartment_nr')) {
            $modems->whereIn('apartment_nr', $this->option('apartment_nr'));
        }

        if ($this->option('file')) {
            $macs = explode(PHP_EOL, file_get_contents($this->option('file')));
            $macs = array_map(function ($mac) {
                return wordwrap(preg_replace('/[^a-f\d]/i', '', $mac), 2, ':', true);
            }, array_filter($macs));

            $modems->whereIn('mac', $macs);
        }

        $maxId = PHP_INT_MAX;
        if ($this->option('limit')) {
            // see https://techsemicolon.github.io/blog/2019/02/12/laravel-limiting-chunk-collection/
            $tmp = clone $modems;
            $maxId = $tmp->orderBy('id', 'asc')->offset($this->option('limit'))->limit(1)->select('id')->first()?->id;
            if (! $maxId) {
                $maxId = PHP_INT_MAX;
            }
        }

        $count = $modems->count();
        if ($this->option('limit') && $count > $this->option('limit')) {
            $count = $this->option('limit');
        }
        echo "# $count modems will be processed\n";

        $modems->where('id', '<', $maxId)->orderBy('id')->chunk(100, function ($modemChunk) use ($roCommunity) {
            foreach ($modemChunk as $modem) {
                foreach ($this->option('oid') as $oid) {
                    try {
                        echo "cm-{$modem->id};$oid;";
                        if (! $this->option('dry-run')) {
                            echo snmpget("cm-{$modem->id}", $roCommunity, $oid);
                        }
                        echo "\n";
                    } catch (\Exception $e) {
                        echo $e->getMessage()."\n";
                    }
                }

                if (! $this->option('dry-run')) {
                    sleep(intval($this->option('sleep')));
                }
            }
        });
    }
}
