<?php

namespace Modules\ProvBase\Services;

use Cron\CronExpression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ProvBase\Entities\FirmwareUpgrade;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Mta;

class FirmwareUpgradeService
{
    protected $modems;

    protected $mtas;

    public static function getActiveFirmwareUpgrades(): \Illuminate\Support\Collection
    {
        // Get all Firmware upgrades within active period
        $now = now();
        $nowDate = $now->toDateString();
        $nowTime = $now->format('H:i');

        return FirmwareUpgrade::where(function ($query) use ($nowDate, $nowTime) {
            $query->where('start_date', '<', $nowDate)
                ->orWhere(function ($query) use ($nowDate, $nowTime) {
                    $query->where('start_date', $nowDate)
                        ->where('start_time', '<=', $nowTime);
                });
        })
            ->whereNull('finished_date')
            ->get();
    }

    /**
     * Execute firmware upgrades on modems.
     *
     * This method iterates over active firmware upgrades and retrieves
     * the modems that match the conditions for each upgrade. If an upgrade
     * does not require restart only, it updates the modems with a new configfile_id.
     * Modems restart automatically after being updated. The process is then logged.
     *
     * @return void
     */
    public function upgradeFirmware()
    {
        $activeFirmwareUpgrades = self::getActiveFirmwareUpgrades();

        foreach ($activeFirmwareUpgrades as $firmwareUpgrade) {
            $this->setMatchingDevices($firmwareUpgrade);

            if ($this->hasUpgradeFinished($firmwareUpgrade)) {
                continue;
            }

            // Only restart devices
            if ($firmwareUpgrade->restart_only) {
                $this->onlyRestartDevices($firmwareUpgrade);

                continue;
            }

            $this->updateModemConfigfile($firmwareUpgrade->to_configfile_id);
        }
    }

    /**
     * If no devices to update, set finished_date and continue to next upgrade
     */
    private function hasUpgradeFinished($firmwareUpgrade)
    {
        if (! $this->modems->isEmpty() || ! $this->mtas->isEmpty()) {
            return false;
        }

        // Log::info('Firmware upgrade process has finished for upgrade id: '.$firmwareUpgrade->id);
        $firmwareUpgrade->update(['finished_date' => now()]);

        return true;
    }

    private function onlyRestartDevices($firmwareUpgrade)
    {
        foreach ($this->modems as $modem) {
            $modem->restart();
        }

        foreach ($this->mtas as $mta) {
            $mta->restart();
        }

        if (! $firmwareUpgrade->cron_string) {
            $firmwareUpgrade->update(['finished_date' => now()]);
        }
    }

    /**
     * Updates the configfile_id for a collection of Modems.
     *
     * @param  Collection  $modems  The modems to update.
     * @param  int  $configfileId  The ID of the new configfile.
     * @return void
     */
    protected function updateModemConfigfile(int $configfileId)
    {
        DB::beginTransaction();

        foreach ($this->modems as $modem) {
            $modem->configfile_id = $configfileId;
            $modem->save();
        }

        DB::commit();

        // Log the number of updated modems
        Log::info($this->modems->count().' modems have been updated with configfile id: '.$configfileId);
    }

    /**
     * Retrieves a collection of Modem/MTA models that match the criteria specified in the provided firmware upgrade.
     *
     * This method will filter modems based on the `configfile_id`s associated with the firmware upgrade. If the
     * firmware upgrade specifies a `restart_only` flag, the method will also filter modems whose `sw_rev` matches
     * any of the lines in the `firmware_match_string` of the firmware upgrade.
     *
     * If a `batch_size` is specified in the firmware upgrade, the method will limit the number of modems returned to
     * that batch size.
     *
     * @param  FirmwareUpgrade  $firmwareUpgrade  The firmware upgrade which specifies the criteria for matching modems.
     */
    protected function setMatchingDevices($firmwareUpgrade)
    {
        $configfileIds = $firmwareUpgrade->fromConfigfile()->pluck('device', 'configfile_id');

        $ids = [
            'modem' => [],
            'mta' => [],
        ];

        $configfileIds->each(function ($devicetype, $id) use (&$ids) {
            if ($devicetype == 'mta') {
                $ids['mta'][] = $id;

                return;
            }

            // cm, tr069
            $ids['modem'][] = $id;
        });

        $modems = Modem::whereIn('configfile_id', $ids['modem']);
        $mtas = Mta::whereIn('configfile_id', $ids['mta']);

        if ($firmwareUpgrade->firmware_match_string) {
            // Split the firmware_match_string into an array of lines
            $matchStrings = preg_split('/\r\n|\r|\n/', $firmwareUpgrade->firmware_match_string);

            $modems = $modems->where(function ($query) use ($matchStrings) {
                foreach ($matchStrings as $matchString) {
                    $query = $query->orWhere('sw_rev', 'LIKE', "%$matchString%");
                }
            });
        }

        // Limit to batch size if it's specified
        if ($firmwareUpgrade->batch_size && ! $firmwareUpgrade->restart_only) {
            $modems = $modems->limit($firmwareUpgrade->batch_size);
            $mtas = $mtas->limit($firmwareUpgrade->batch_size);
        }

        $this->modems = $modems->get();
        $this->mtas = $mtas->get();
    }

    /**
     * Check if firmware upgrade service must run according to set cron strings, start time and current time
     */
    public static function mustRun()
    {
        $activeFirmwareUpgrades = self::getActiveFirmwareUpgrades();

        foreach ($activeFirmwareUpgrades as $upgrade) {
            // No cron string specified, but since this is an active upgrade, we should run it
            if (! is_string($upgrade->cron_string)) {
                return true;
            }

            // Parse the cron string with the CronExpression library
            $cron = new CronExpression($upgrade->cron_string);

            // Check if the current time matches the cron string
            if ($cron->isDue()) {
                return true;
            }
        }

        // If no firmware upgrades match, prevent the command from running
        return false;
    }
}
