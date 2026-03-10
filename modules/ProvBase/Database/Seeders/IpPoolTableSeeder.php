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

use Modules\ProvBase\Entities\IpPool;
use Modules\ProvBase\Entities\NetGw;

class IpPoolTableSeeder extends \BaseSeeder
{
    public function run()
    {
        foreach (range(1, self::$max_seed) as $index) {
            IpPool::create(static::get_fake_data('seed'));
        }
    }

    /**
     * Returns an array with faked IP pool data; used e.g. in seeding and testing
     *
     * @param  $topic  Context the method is used in (seed|test)
     * @param  $netgw  NetGw to create the IP pool at; used in testing
     *
     * @author Patrick Reichel
     */
    public static function get_fake_data($topic, $netgw = null)
    {
        $faker = &\NmsFaker::getInstance();

        // in seeding mode: choose random NetGw to create ippool at
        if ($topic == 'seed') {
            $netgw = NetGw::all()->random();
            $netgw_id = $netgw->id;
        } else {
            if (! is_null($netgw)) {
                $netgw_id = $netgw->id;
            } else {
                $netgw_id = null;
            }
        }

        $m = $faker->numberBetween(0, 255);
        $n = $faker->numberBetween(0, 255);
        $base = '10.'.$m.'.'.$n;

        $ret = [
            'netgw_id' => $netgw_id,
            'type' => (string) rand(0, 3),
            'net' => $base.'.0/24',
            'ip_pool_start' => $base.'.2',
            'ip_pool_end' => $base.'.253',
            'router_ip' => $base.'.1',       // first real IP (gateway)
            'broadcast_ip' => $base.'.255',
            'dns1_ip' => $base.'.1',         // first real IP
            'dns2_ip' => $base.'.2',         // second real IP
            'dns3_ip' => $base.'.3',         // third real IP (within range)
            'description' => $faker->sentence(),
        ];

        return $ret;
    }
}
