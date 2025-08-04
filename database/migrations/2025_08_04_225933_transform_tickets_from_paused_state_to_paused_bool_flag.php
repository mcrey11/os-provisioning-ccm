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

        $newStateID = \Modules\Ticketsystem\Entities\TicketTypeState::STATES['New'];
        $pausedStateID = \Modules\Ticketsystem\Entities\TicketTypeState::STATES['Paused'];

        // Directly update in DB as we aren't concerned with TicketObserver here
        \Modules\Ticketsystem\Entities\Ticket::where('ticket_type_state_id', $pausedStateID)->update(['paused' => true, 'ticket_type_state_id' => $newStateID]);

        // Move Paused state to trash directly bypassing undeletable
        \Modules\Ticketsystem\Entities\TicketTypeState::whereId($pausedStateID)->update(['deleted_at' => now()]);

        // Delete transitions involving Paused state
        \Modules\Ticketsystem\Entities\TicketTypeTransition::where(fn ($q) => $q->where('from_state_id', $pausedStateID)->orWhere('to_state_id', $pausedStateID))->update(['deleted_at' => now()]);
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

        $pausedStateID = \Modules\Ticketsystem\Entities\TicketTypeState::STATES['Paused'];

        \Modules\Ticketsystem\Entities\TicketTypeState::withTrashed()->whereId($pausedStateID)->update(['deleted_at' => null]);

        \Modules\Ticketsystem\Entities\TicketTypeTransition::withTrashed()->where(fn ($q) => $q->where('from_state_id', $pausedStateID)->orWhere('to_state_id', $pausedStateID))->update(['deleted_at' => null]);
        \Modules\Ticketsystem\Entities\Ticket::wherePaused(true)->update(['ticket_type_state_id' => $pausedStateID]);
    }
};
