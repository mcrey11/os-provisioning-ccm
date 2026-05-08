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
    public $migrationScope = 'database';

    protected $tableName = 'web_order_items';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::statement("ALTER TABLE {$this->tableName} ALTER COLUMN product_id TYPE bigint USING (product_id)::bigint");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE {$this->tableName} ALTER COLUMN product_id TYPE varchar(191) USING (product_id)::varchar(191)");
    }
};
