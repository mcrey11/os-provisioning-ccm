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

use Illuminate\Support\Facades\DB;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\ProvVoip;
use Tests\BaseLifecycleTest;

/**
 * Phonenumber create uses a random MTA from BaseLifecycleTest context; updates must use fake POST data for the same MTA
 * as the edited row so mta_id and port validation in PhonenumberController::prepare_rules() match persisted data.
 *
 * When ProvVoipEnvia is disabled (e.g. nmsprime_testsuite Voip circuits), Phonenumber::rules() requires username and
 * sipdomain; the table seeder fake payload does not include them, so they are filled here for HTTP lifecycle posts.
 */
class PhonenumberLifecycleTest extends BaseLifecycleTest
{
    // created from Mta context
    protected $create_from_model_context = '\Modules\ProvVoip\Entities\Mta';

    protected $update_fields = [
        'port',
    ];

    /**
     * {@inheritdoc}
     *
     * For updates, anchor seeder fake data to the phonenumber’s actual MTA (not a newly picked random MTA).
     *
     * @param  mixed  $related_to
     */
    protected function _get_fake_data($related_to, $model_id = -1): array
    {
        if ($model_id > 0) {
            $modelClass = $this->model_path;
            $existing = $modelClass::find($model_id);
            if ($existing !== null) {
                $mta = $existing->mta;
                if ($mta instanceof Mta) {
                    $related_to = $mta;
                }
            }
        }

        $data = parent::_get_fake_data($related_to, $model_id);

        if (! \Module::collections()->has('ProvVoipEnvia')) {
            $sipFallback = ProvVoip::query()->value('default_sip_registrar') ?? 'sip.lifecycle.test';
            if (! isset($data['username']) || $data['username'] === '' || $data['username'] === null) {
                $data['username'] = 'lc_'.$data['prefix_number'].'_'.$data['number'];
            }
            if (! isset($data['sipdomain']) || $data['sipdomain'] === '' || $data['sipdomain'] === null) {
                $data['sipdomain'] = $sipFallback;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * Port is unique per mta_id; naive +id on number breaks PhonenumberController::prepare_rules() uniqueness.
     */
    protected function overrideUpdateMutation(string $id, array &$postData, object $row, array $rowArr): ?array
    {
        $mtaId = $rowArr['mta_id'];
        $maxPort = (int) DB::table('phonenumber')->where('mta_id', $mtaId)->whereNull('deleted_at')->max('port');
        $postData['port'] = (string) ($maxPort + 1);

        return ['field' => 'port', 'before' => $rowArr['port']];
    }
}
