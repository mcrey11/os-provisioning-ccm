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

namespace Modules\ProvBase\Tests\Lifecycle;

use Tests\BaseLifecycleTest;

class IpPoolLifecycleTest extends BaseLifecycleTest
{
    // modem can only be created from NetGw.edit
    protected $create_from_model_context = '\Modules\ProvBase\Entities\NetGw';

    // empty POST only sends context params (netgw_id), not form init values – validation fails
    protected $creating_empty_should_fail = true;

    // fields to be used in update test
    protected $update_fields = [
        'dns2_ip',
        'dns3_ip',
        'description',
    ];

    // keep net and IPs from existing record so validation (ip_in_range) passes
    protected $update_preserve_from_existing = [
        'net', 'ip_pool_start', 'ip_pool_end', 'router_ip', 'broadcast_ip',
        'dns1_ip', 'dns2_ip', 'dns3_ip',
    ];
}
