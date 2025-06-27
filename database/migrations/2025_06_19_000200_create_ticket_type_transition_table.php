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

use Database\Migrations\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'ticket_type_transition';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            $table->string('name')->nullable()->default(null);
            $table->string('description')->nullable()->default(null);
            $table->bigInteger('ticket_type_id');
            $table->bigInteger('from_state_id');
            $table->bigInteger('to_state_id');
        });

        // insert legacy ticket transitions
        $inserts = [];
        $ticketTypeIds = DB::table('ticket_type')->whereNull('deleted_at')->pluck('id');
        foreach ($ticketTypeIds as $ticketTypeId) {
            $inserts[] = ['created_at' => now(), 'updated_at' => now(), 'ticket_type_id' => $ticketTypeId, 'from_state_id' => 1, 'to_state_id' => 2]; // New -> In Progress
            $inserts[] = ['created_at' => now(), 'updated_at' => now(), 'ticket_type_id' => $ticketTypeId, 'from_state_id' => 2, 'to_state_id' => 3]; // In Progress -> Paused
            $inserts[] = ['created_at' => now(), 'updated_at' => now(), 'ticket_type_id' => $ticketTypeId, 'from_state_id' => 2, 'to_state_id' => 4]; // In Progress -> Closed
            $inserts[] = ['created_at' => now(), 'updated_at' => now(), 'ticket_type_id' => $ticketTypeId, 'from_state_id' => 3, 'to_state_id' => 2]; // Paused -> In Progress
            $inserts[] = ['created_at' => now(), 'updated_at' => now(), 'ticket_type_id' => $ticketTypeId, 'from_state_id' => 4, 'to_state_id' => 2]; // Closed -> In Progress
        }

        DB::table($this->tableName)->insert($inserts);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
};
