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
    public $migrationScope = 'database';

    protected $tableName = 'web_orders';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing check constraint
        DB::statement('ALTER TABLE web_orders DROP CONSTRAINT IF EXISTS web_orders_payment_method_check');

        // Add updated check constraint including all payment methods
        DB::statement("ALTER TABLE web_orders ADD CONSTRAINT web_orders_payment_method_check 
            CHECK (payment_method IS NULL OR payment_method IN ('sepa', 'card', 'acs', 'rechnung'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the updated constraint
        DB::statement('ALTER TABLE web_orders DROP CONSTRAINT IF EXISTS web_orders_payment_method_check');

        // Restore the original constraint
        DB::statement('ALTER TABLE web_orders ADD CONSTRAINT web_orders_payment_method_check 
            CHECK (payment_method IS NULL OR payment_method IN (\'sepa\', \'card\'))');
    }
};
