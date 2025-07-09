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

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'calixont' value to the existing configfile_device enum type
        DB::statement("ALTER TYPE nmsprime.configfile_device ADD VALUE 'calixont'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Note: PostgreSQL does not support removing enum values directly
        // This would require recreating the enum type, which is complex and potentially dangerous
        // For safety, we'll leave this as a comment explaining the limitation
        //
        // To remove the enum value, you would need to:
        // 1. Create a new enum type without 'calixont'
        // 2. Update all columns using the old enum type to use the new one
        // 3. Drop the old enum type
        // 4. Rename the new enum type to the original name
        //
        // This is not implemented here due to the complexity and potential data loss risks

        $this->warn('Cannot automatically remove enum value "calixont" from configfile_device type.');
        $this->warn('Manual intervention required if rollback is needed.');
    }
};
