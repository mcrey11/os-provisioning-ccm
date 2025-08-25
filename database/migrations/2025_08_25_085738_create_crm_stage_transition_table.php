<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the file at:
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
use Illuminate\Support\Facades\DB;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'crm_stage_transition';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);
            
            $table->bigInteger('pipeline_id')->unsigned();
            $table->bigInteger('from_stage_id')->unsigned();
            $table->bigInteger('to_stage_id')->unsigned();
            $table->json('guard_expr')->nullable();
            $table->string('autofail_message', 191)->nullable();
            
            // Add indexes for foreign keys
            $table->index('pipeline_id');
            $table->index('from_stage_id');
            $table->index('to_stage_id');
            
            // Add unique constraint (will be replaced with partial unique index below)
        });
        
        // Add check constraint for from_stage_id <> to_stage_id using raw SQL
        DB::statement('ALTER TABLE crm_stage_transition ADD CONSTRAINT crm_stage_transition_check_stages CHECK (from_stage_id <> to_stage_id)');
        
        // Add partial unique index that excludes soft-deleted records
        DB::statement('CREATE UNIQUE INDEX crm_stage_transition_unique ON crm_stage_transition (pipeline_id, from_stage_id, to_stage_id) WHERE deleted_at IS NULL');
        
        // Add simple foreign key constraints
        DB::statement('ALTER TABLE crm_stage_transition ADD CONSTRAINT crm_stage_transition_pipeline_fk FOREIGN KEY (pipeline_id) REFERENCES crm_pipeline(id) ON DELETE RESTRICT');
        DB::statement('ALTER TABLE crm_stage_transition ADD CONSTRAINT crm_stage_transition_from_stage_fk FOREIGN KEY (from_stage_id) REFERENCES crm_pipeline_stage(id) ON DELETE RESTRICT');
        DB::statement('ALTER TABLE crm_stage_transition ADD CONSTRAINT crm_stage_transition_to_stage_fk FOREIGN KEY (to_stage_id) REFERENCES crm_pipeline_stage(id) ON DELETE RESTRICT');
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
