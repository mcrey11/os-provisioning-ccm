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

class ModemRestartCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = '
        nms:modem-restart
        {--contract-id= : Filter modems by contract ID (not contract number!) - Standby contract -> 1}
        {--from= : Filter modems by configfile ID}
        {--online : Filter for modems seen on last PHY update cycle}
        {--city= : Filter modems by city (wildcard)}
        {--district= : Filter modems by district / installation (wildcard)}
        {--street= : Filter modems by street (wildcard)}
        {--apartment_nr=* : Filter modems by apartment_nr}
        {--file= : File containing one MAC address per line f modems to be processed}
        {--limit= : Maximum amount of modems to handle}
        {--to= : Target configfile id, restart modems only if not specified}
        {--sleep= : Seconds to wait between modem restarts, no effect in dry-run mode}
        {--dry-run : Do not excute command, just print affected modems}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart modems and change configfiles.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
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

        $toId = null;
        if ($this->option('to')) {
            $toId = Configfile::findOrFail($this->option('to'))->id;
        }

        $count = $modems->count();
        if ($this->option('limit') && $count > $this->option('limit')) {
            $count = $this->option('limit');
        }
        echo "$count modems will be processed\n";

        $modems->where('id', '<', $maxId)->orderBy('id')->chunk(100, function ($modemChunk) use ($toId) {
            foreach ($modemChunk as $modem) {
                if ($toId) {
                    echo "moving cm-{$modem->id} from {$modem->configfile_id} to $toId\n";
                    if (! $this->option('dry-run')) {
                        $modem->configfile_id = $toId;
                        $modem->save();
                    }
                } else {
                    echo "restarting cm-{$modem->id}\n";
                    if (! $this->option('dry-run')) {
                        $modem->restart_modem();
                    }
                }

                if (! $this->option('dry-run')) {
                    sleep(intval($this->option('sleep')));
                }
            }
        });
    }
}
