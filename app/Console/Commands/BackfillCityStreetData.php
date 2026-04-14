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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\PropertyManagement\Entities\City;
use Modules\PropertyManagement\Entities\Realty;
use Modules\PropertyManagement\Entities\Street;

class BackfillCityStreetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:backfill-city-street-data {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill city and street data from existing realty records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting city/street data backfill...');

        // Get all realties that have city/street text but no city_id/street_id
        $realties = Realty::whereNotNull('city')
            ->whereNotNull('street')
            ->where(function ($query) {
                $query->whereNull('city_id')
                    ->orWhereNull('street_id');
            })
            ->get();

        $this->info("Found {$realties->count()} realties to process");

        $progressBar = $this->output->createProgressBar($realties->count());
        $progressBar->start();

        $processed = 0;
        $errors = 0;

        foreach ($realties as $realty) {
            try {
                if (! $isDryRun) {
                    // Use the observer logic to normalize city/street
                    $realty->city_id = null;
                    $realty->street_id = null;

                    // Create or find city
                    $city = City::firstOrCreateByName($realty->city);
                    $realty->city_id = $city->id;

                    // Create or find street
                    $street = Street::firstOrCreateByName($city->id, $realty->street);
                    $realty->street_id = $street->id;

                    // Save without triggering observers to avoid infinite loops
                    $realty->saveQuietly();
                }

                $processed++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing realty ID {$realty->id}: ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if ($isDryRun) {
            $this->info("DRY RUN COMPLETE: Would process {$processed} realties");
        } else {
            $this->info("Backfill complete: {$processed} realties processed, {$errors} errors");
        }

        // Show statistics
        $cityCount = City::count();
        $streetCount = Street::count();
        $this->info("Total cities: {$cityCount}");
        $this->info("Total streets: {$streetCount}");

        return $errors > 0 ? 1 : 0;
    }
}
