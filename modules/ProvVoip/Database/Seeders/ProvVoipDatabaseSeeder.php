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

namespace Modules\ProvVoip\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class ProvVoipDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $seeders = [
            'Modules\ProvVoip\Database\Seeders\ProvVoipConfigTableSeeder',
            'Modules\ProvVoip\Database\Seeders\MtaTableSeeder',
            'Modules\ProvVoip\Database\Seeders\PhoneTariffTableSeeder',
            'Modules\ProvVoip\Database\Seeders\PhonenumberTableSeeder',
            'Modules\ProvVoip\Database\Seeders\PhonenumberManagementTableSeeder',
        ];

        foreach ($seeders as $class) {
            $start = config('app.seed_profile', false) ? hrtime(true) : 0;
            $this->call($class);
            if (config('app.seed_profile', false)) {
                $elapsed = (hrtime(true) - $start) / 1e9;
                fwrite(STDERR, sprintf("    %s: %.2fs\n", class_basename($class), $elapsed));
            }
        }
    }
}
