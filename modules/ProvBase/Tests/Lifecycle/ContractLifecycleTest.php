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
 * Lifecycle test for Contract.
 *
 * Known full-suite issues:
 * - test_index_view_visible: 500 on GET index when NetElement loads (kalnoy/nestedset NodeTrait vs Laravel 11
 *   Model::whenBooted). Expect fix after framework/nestedset alignment (e.g. L13). Intentional failure until then.
 * - test_empty_create: may be reported risky (global error/exception handlers) when create flow touches the same stack;
 *   documented; not skipped.
 */
class ContractLifecycleTest extends BaseLifecycleTest
{
    // fields to be used in update test
    protected $update_fields = [
        'company',
        'department',
        'salutation',
        'academic_degree',
        'firstname',
        'lastname',
        'street',
        'house_number',
        'zip',
        'city',
    ];
}
