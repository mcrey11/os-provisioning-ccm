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

    protected $tableName = 'crm_opportunity';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Foreign key relationships
            $table->bigInteger('contact_point_id');
            $table->bigInteger('created_from_lead_id')->nullable()->unique();
            $table->bigInteger('realty_id')->nullable();
            $table->bigInteger('apartment_id')->nullable();
            $table->bigInteger('pipeline_id');
            $table->bigInteger('stage_id')->nullable();

            // Business fields
            $table->bigInteger('amount_cents')->nullable();
            $table->smallInteger('probability_pct')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->boolean('is_preorder')->default(false);
            $table->boolean('is_switcher')->default(false);
            $table->string('external_order_no', 64)->nullable();

            // JSON fields for flexible data storage
            $table->json('precheck_result')->nullable();
            $table->json('deal_terms_json')->nullable();

            // Porting related fields
            $table->timestampTz('porting_requested_at')->nullable();
            $table->date('porting_date')->nullable();

            // Indexes for foreign keys
            $table->index('contact_point_id');
            $table->index('realty_id');
            $table->index('apartment_id');
            $table->index('pipeline_id');
            $table->index('stage_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the trigger first
        DB::unprepared('DROP TRIGGER IF EXISTS trigger_check_pipeline_stage_consistency ON crm_opportunity;');

        // Drop the function
        DB::unprepared('DROP FUNCTION IF EXISTS check_pipeline_stage_consistency();');

        Schema::dropIfExists($this->tableName);
    }
};
