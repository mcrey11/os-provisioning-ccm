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

/**
 * Known full-suite issue (lifecycle update + guilog): mutated description may not persist on HTTP update
 * (DB unchanged). Fix Modem edit pipeline or test mutation; intentional assertion failure until then.
 */
class ModemLifecycleTest extends BaseLifecycleTest
{
    // modem can only be created from Contract.edit
    protected $create_from_model_context = '\Modules\ProvBase\Entities\Contract';

    // fields to be used in update test (include description for a full address/contact payload; seeder now provides company, department, salutation)
    protected $update_fields = [
        'description',
        'mac',
        'company',
        'department',
        'salutation',
        'firstname',
        'lastname',
        'street',
        'house_number',
        'zip',
        'city',
        'configfile_id',
    ];
}
