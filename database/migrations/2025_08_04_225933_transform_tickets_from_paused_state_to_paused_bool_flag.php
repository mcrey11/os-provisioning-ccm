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
use Illuminate\Support\Facades\DB;

return new class extends BaseMigration
{
    public $migrationScope = 'system';

    protected $tableName = 'ticket';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Module::collections()->has('Ticketsystem') == false) {
            return;
        }

        // Look up state IDs by name to avoid hardcoded IDs that may differ per customer
        // Use raw DB queries to avoid autoloader issues during RPM installation
        $newState = DB::table('ticket_type_state')
            ->where('name', 'New')
            ->whereNull('deleted_at')
            ->first();
        $pausedState = DB::table('ticket_type_state')
            ->where('name', 'Paused')
            ->whereNull('deleted_at')
            ->first();

        if (! $newState || ! $pausedState) {
            // If states don't exist, skip migration
            return;
        }

        $newStateID = $newState->id;
        $pausedStateID = $pausedState->id;
        $now = now();

        // Directly update in DB using raw queries to avoid Eloquent model autoloading
        DB::table('ticket')
            ->where('ticket_type_state_id', $pausedStateID)
            ->update(['paused' => true, 'ticket_type_state_id' => $newStateID]);

        // Move Paused state to trash directly bypassing undeletable
        DB::table('ticket_type_state')
            ->where('id', $pausedStateID)
            ->update(['deleted_at' => $now]);

        // Delete transitions involving Paused state
        DB::table('ticket_type_transition')
            ->where(function ($q) use ($pausedStateID) {
                $q->where('from_state_id', $pausedStateID)
                    ->orWhere('to_state_id', $pausedStateID);
            })
            ->update(['deleted_at' => $now]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\Module::collections()->has('Ticketsystem') == false) {
            return;
        }

        // Look up Paused state ID by name to avoid hardcoded IDs that may differ per customer
        // Use raw DB queries to avoid autoloader issues during RPM installation
        $pausedState = DB::table('ticket_type_state')
            ->where('name', 'Paused')
            ->first();

        if (! $pausedState) {
            // If Paused state doesn't exist, skip rollback
            return;
        }

        $pausedStateID = $pausedState->id;

        // Restore Paused state from trash
        DB::table('ticket_type_state')
            ->where('id', $pausedStateID)
            ->update(['deleted_at' => null]);

        // Restore transitions involving Paused state
        DB::table('ticket_type_transition')
            ->where(function ($q) use ($pausedStateID) {
                $q->where('from_state_id', $pausedStateID)
                    ->orWhere('to_state_id', $pausedStateID);
            })
            ->update(['deleted_at' => null]);

        // Restore tickets to Paused state
        DB::table('ticket')
            ->where('paused', true)
            ->update(['ticket_type_state_id' => $pausedStateID]);
    }
};
