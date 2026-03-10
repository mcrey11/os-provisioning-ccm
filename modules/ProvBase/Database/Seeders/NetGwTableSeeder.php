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

namespace Modules\ProvBase\Database\Seeders;

use Modules\ProvBase\Entities\NetGw;

class NetGwTableSeeder extends \BaseSeeder
{
    public function run()
    {
        foreach (range(1, self::$max_seed_l2) as $index) {
            NetGw::create(static::get_fake_data('seed'));
        }
    }

    /**
     * Returns an array with faked NetGw data; used e.g. in seeding and testing
     *
     * @param  $topic  Context the method is used in (seed|test)
     *
     * @author Patrick Reichel
     */
    public static function get_fake_data($topic)
    {
        $faker = &\NmsFaker::getInstance();

        $conf = config('provbase.netgw');
        $company = array_keys($conf);

        $series = \Illuminate\Support\Arr::flatten($conf);

        $types = NetGw::TYPES;
        $chosenType = $types[array_rand($types)];

        $ret = [
            'hostname' => $faker->unique->name,
            'type' => $chosenType,
            'ip' => $faker->localIpv4(),
            'community_rw' => 'private',
            'community_ro' => 'public',
            'company' => $company[array_rand($company)],
            'series' => $series[array_rand($series)],
        ];

        if ($chosenType === 'bras') {
            $ret['nas_secret'] = $faker->regexify('[A-Za-z0-9]{16}');
        }

        return $ret;
    }
}
