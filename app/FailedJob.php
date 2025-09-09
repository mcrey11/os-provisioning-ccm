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

namespace App;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    /** @var string The table associated with the model. */
    public $table = 'jobs';

    public function name()
    {
        return json_decode($this->payload)->data->commandName;
    }

    public function relationId()
    {
        // return Attribute::get(function () {
        $cmdPayload = json_decode($this->payload)->data->command;
        preg_match('/"id";i:(\d*);/', $cmdPayload, $matches);

        return $matches[1];
        // });
    }
}
