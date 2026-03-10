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

namespace Modules\ProvVoip\Tests\Lifecycle;

use Tests\BaseLifecycleTest;

/**
 * MTA lifecycle: update must change a real column. Base class skips {@code mac} in auto-mutation (format-sensitive);
 * {@code configfile} is not a DB column (use {@code configfile_id}). Test DB may only have one MTA configfile, so we
 * derive the next legal MAC like {@see \Modules\ProvVoip\Http\Controllers\MtaController::view_form_fields()}.
 */
class MtaLifecycleTest extends BaseLifecycleTest
{
    // modem can only be created from Modem.edit
    protected $create_from_model_context = '\Modules\ProvBase\Entities\Modem';

    // fields to be used in update test (real edit form / table)
    protected $update_fields = [
        'mac',
        'configfile_id',
    ];

    /**
     * {@inheritdoc}
     */
    protected function overrideUpdateMutation(string $id, array &$postData, object $row, array $rowArr): ?array
    {
        $mac = (string) ($rowArr['mac'] ?? '');
        $decMac = hexdec(preg_replace('/[^[:xdigit:]]/', '', $mac));
        $decMac++;
        $postData['mac'] = rtrim(strtoupper(chunk_split(str_pad(dechex($decMac), 12, '0', STR_PAD_LEFT), 2, ':')), ':');

        return ['field' => 'mac', 'before' => $rowArr['mac']];
    }
}
