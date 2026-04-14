<?php

namespace Modules\HfcReq\Console;

use DB;
/* use Exception; */
use Illuminate\Console\Command;
use Log;
use Modules\HfcReq\Entities\NetElement;

class NetelementAtIcingaUpdaterCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    /* protected $name = 'nms:icinga-netelement'; */

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates netelement config for icinga2; give Netelement ID or “all” as argument';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nms:icinga-netelement
                            {netelement : a netelement ID or “all”}';

    /**
     * The path to store the icinga configfiles in
     *
     * @var string
     */
    protected $configPath = '/etc/icinga2/conf.d/nmsprime/netelements';

    /**
     * Entry point to command.
     *
     * @author Patrick Reichel
     */
    public function handle()
    {
        $this->processArgument();
        $this->runQuery();

        if (is_int($this->netelementId) && ! $this->netelements) {
            // if single ID is given and netelement does not exist: delete config
            $this->deleteConfig($this->netelementId);
            $this->reloadIcinga();
            exit(0);
        }

        if ($this->netelementId == 'all') {
            // on working on all netelements: Delete whole config first
            $this->deleteAllConfigs();
        }

        $this->createConfig();
        $this->reloadIcinga();
    }

    /**
     * Read and validate the given argument
     *
     * @author Patrick Reichel
     */
    protected function processArgument()
    {
        $netelement = $this->argument('netelement');

        // process all netelements
        if (strtolower($netelement) == 'all') {
            $this->netelementId = 'all';

            return;
        }

        if (! is_numeric($netelement)) {
            $this->error('Argument needs to be an number');
            exit(1);
        }

        $id = intval($netelement);
        $this->netelementId = $id;
    }

    /**
     * Render and execute database query.
     *
     * @author Patrick Reichel
     */
    protected function runQuery()
    {
        $id = null;
        if ($this->netelementId != 'all') {
            $id = $this->netelementId;
        }
        $query = view('hfcreq::DbQueries.getNetelementDataForIcinga', compact(['id']))->render();

        $this->netelements = DB::select($query);
    }

    /**
     * Create the icinga configuration from data and blade template.
     *
     * @author Patrick Reichel
     */
    protected function createConfig()
    {
        $s = microtime(true);

        foreach ($this->netelements as $ne) {
            // typecast to text adds netmask – remove it
            $ne->ip = explode('/', $ne->ip)[0];
            $config = view('hfcreq::Icinga2.hostconf', compact(['ne']))->render();
            $this->writeConfig($config, $ne->id);
        }

        $e = microtime(true);
        $diff = round($e - $s, 3);

        Log::info(__CLASS__.": Writing icinga config for netelements to $this->configPath/ took $diff s");
    }

    /**
     * Write an icinga config file.
     *
     * @author Patrick Reichel
     */
    protected function writeConfig($config, $id)
    {
        $configFile = "$this->configPath/$id.conf";
        $msg = "Writing $configFile";
        $this->line($msg);
        Log::debug(__CLASS__.": $msg");
        try {
            file_put_contents($configFile, $config);
        } catch (\Throwable $ex) {
            $msg = "Error writing $configFile: ".$ex->getMessage();
            $this->error($msg);
            Log::error(__CLASS__.": $msg");
            exit(1);
        }
        chmod($configFile, 0750);
    }

    /**
     * Deletes all icinga config files
     *
     * @author Patrick Reichel
     */
    protected function deleteAllConfigs()
    {
        try {
            $msg = "Deleting all configfiles in $this->configPath";
            $this->info($msg);
            Log::info(__CLASS__.": $msg");
            array_map('unlink', glob($this->configPath.'/*.conf'));
        } catch (\Throwable $ex) {
            $msg = "Error deleting all configfiles in $this->configPath: ".$ex->getMessage();
            $this->error($msg);
            Log::error(__CLASS__.": $msg");
            exit(1);
        }
    }

    /**
     * Delete an icinga config file
     *
     * @author Patrick Reichel
     */
    protected function deleteConfig($id)
    {
        $configFile = "$this->configPath/$id.conf";
        if (! is_file($configFile)) {
            $msg = "$configFile does not exist – nothing to do";
            $this->info($msg);
            Log::info(__CLASS__.": $msg");
            exit(0);
        }
        try {
            $msg = "Deleting $configFile";
            $this->info($msg);
            Log::info(__CLASS__.": $msg");
            unlink($configFile);
        } catch (\Throwable $ex) {
            $msg = "Error deleting $configFile: ".$ex->getMessage();
            $this->error($msg);
            Log::error(__CLASS__.": $msg");
            exit(1);
        }
    }

    /**
     * Reload icinga config.
     *
     * @author Patrick Reichel
     */
    protected function reloadIcinga()
    {
        $this->line('Trigger icinga2 reload');
        if (! is_dir(storage_path('systemd'))) {
            mkdir(storage_path('systemd'));
        }
        touch(storage_path('systemd/icinga2-reload'));
    }
}
