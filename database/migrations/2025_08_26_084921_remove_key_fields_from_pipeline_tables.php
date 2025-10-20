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
use Illuminate\Support\Facades\Schema;

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
        // Remove key field from crm_pipeline table
        Schema::table('crm_pipeline', function (Blueprint $table) {
            $table->dropColumn('key');
        });

        // Remove key field from crm_pipeline_stage table
        Schema::table('crm_pipeline_stage', function (Blueprint $table) {
            $table->dropColumn('key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add key field back to crm_pipeline table
        Schema::table('crm_pipeline', function (Blueprint $table) {
            $table->string('key', 32)->unique()->after('id');
        });

        // Add key field back to crm_pipeline_stage table
        Schema::table('crm_pipeline_stage', function (Blueprint $table) {
            $table->string('key', 32)->after('pipeline_id');
            // Recreate the unique constraint for pipeline_id + key
            $table->unique(['pipeline_id', 'key']);
        });
    }
};
