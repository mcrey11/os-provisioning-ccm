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

use App\Role;
use App\User;
use Database\Migrations\BaseMigration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Silber\Bouncer\BouncerFacade as Bouncer;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create or update CCCAPI role
        $role = Role::firstOrNew(['name' => 'CCCAPI']);

        // Only set ID if role doesn't exist yet (to avoid conflicts)
        if (! $role->exists) {
            // Find next available role ID
            $maxId = Role::max('id') ?? 0;
            $role->id = $maxId + 1;
        }

        $role->title = 'CCC API';
        $role->description = 'API access role for CCC and WebOrderPortal';
        $role->rank = 10; // Low rank for API-only access
        $role->save();

        // Grant API access ability to the role
        Bouncer::allow('CCCAPI')->to('use api');

        // Grant model permissions to the CCCAPI role
        // Contract: view, update
        Bouncer::allow('CCCAPI')->to('view', \Modules\ProvBase\Entities\Contract::class);
        Bouncer::allow('CCCAPI')->to('update', \Modules\ProvBase\Entities\Contract::class);

        // Modem: view, update
        Bouncer::allow('CCCAPI')->to('view', \Modules\ProvBase\Entities\Modem::class);
        Bouncer::allow('CCCAPI')->to('update', \Modules\ProvBase\Entities\Modem::class);

        // SepaMandate: view, create, update
        Bouncer::allow('CCCAPI')->to('view', \Modules\BillingBase\Entities\SepaMandate::class);
        Bouncer::allow('CCCAPI')->to('create', \Modules\BillingBase\Entities\SepaMandate::class);
        Bouncer::allow('CCCAPI')->to('update', \Modules\BillingBase\Entities\SepaMandate::class);

        // Item: view, create, update
        Bouncer::allow('CCCAPI')->to('view', \Modules\BillingBase\Entities\Item::class);
        Bouncer::allow('CCCAPI')->to('create', \Modules\BillingBase\Entities\Item::class);
        Bouncer::allow('CCCAPI')->to('update', \Modules\BillingBase\Entities\Item::class);

        // Ticket: view, create, update
        Bouncer::allow('CCCAPI')->to('view', \Modules\Ticketsystem\Entities\Ticket::class);
        Bouncer::allow('CCCAPI')->to('create', \Modules\Ticketsystem\Entities\Ticket::class);
        Bouncer::allow('CCCAPI')->to('update', \Modules\Ticketsystem\Entities\Ticket::class);

        // Comment: view, create
        Bouncer::allow('CCCAPI')->to('view', \Modules\Ticketsystem\Entities\Comment::class);
        Bouncer::allow('CCCAPI')->to('create', \Modules\Ticketsystem\Entities\Comment::class);

        // Refresh Bouncer cache after granting permissions
        Bouncer::refresh();

        // Create or update the CCC API user
        $user = User::firstOrNew(['email' => 'cccapi@nmsprime.com']);
        $user->first_name = 'CCC';
        $user->last_name = 'API';
        $user->login_name = 'cccapi';
        $user->active = true;

        // Set password if user is new (random password, should be changed via API token)
        if (! $user->exists) {
            $user->password = Hash::make(Str::random(32));
            $user->api_token = Str::random(80);
        }

        $user->save();

        // Assign CCCAPI role to the user
        Bouncer::assign('CCCAPI')->to($user);
        Bouncer::refreshFor($user);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove role assignment from user
        $user = User::where('email', 'cccapi@nmsprime.com')->first();
        if ($user) {
            Bouncer::retract('CCCAPI')->from($user);
        }

        // Remove model permissions from role
        Bouncer::disallow('CCCAPI')->to('view', \Modules\ProvBase\Entities\Contract::class);
        Bouncer::disallow('CCCAPI')->to('update', \Modules\ProvBase\Entities\Contract::class);
        Bouncer::disallow('CCCAPI')->to('view', \Modules\ProvBase\Entities\Modem::class);
        Bouncer::disallow('CCCAPI')->to('update', \Modules\ProvBase\Entities\Modem::class);
        Bouncer::disallow('CCCAPI')->to('view', \Modules\BillingBase\Entities\SepaMandate::class);
        Bouncer::disallow('CCCAPI')->to('create', \Modules\BillingBase\Entities\SepaMandate::class);
        Bouncer::disallow('CCCAPI')->to('update', \Modules\BillingBase\Entities\SepaMandate::class);
        Bouncer::disallow('CCCAPI')->to('view', \Modules\BillingBase\Entities\Item::class);
        Bouncer::disallow('CCCAPI')->to('create', \Modules\BillingBase\Entities\Item::class);
        Bouncer::disallow('CCCAPI')->to('update', \Modules\BillingBase\Entities\Item::class);
        Bouncer::disallow('CCCAPI')->to('view', \Modules\Ticketsystem\Entities\Ticket::class);
        Bouncer::disallow('CCCAPI')->to('create', \Modules\Ticketsystem\Entities\Ticket::class);
        Bouncer::disallow('CCCAPI')->to('update', \Modules\Ticketsystem\Entities\Ticket::class);
        Bouncer::disallow('CCCAPI')->to('view', \Modules\Ticketsystem\Entities\Comment::class);
        Bouncer::disallow('CCCAPI')->to('create', \Modules\Ticketsystem\Entities\Comment::class);

        // Remove API ability from role
        Bouncer::disallow('CCCAPI')->to('use api');

        // Refresh Bouncer cache
        Bouncer::refresh();

        // Note: We don't delete the role or user in down() to avoid data loss
        // If you need to remove them, do it manually
    }
};
