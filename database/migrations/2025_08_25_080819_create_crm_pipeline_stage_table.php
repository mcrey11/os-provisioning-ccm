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

class CreateCrmPipelineStageTable extends BaseMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->tableName = 'crm_pipeline_stage';

        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Foreign key to crm_pipeline
            $table->bigInteger('pipeline_id')->unsigned();

            // Stage identifier unique per pipeline
            $table->string('key', 32);

            // Display name
            $table->string('name', 64);

            // Order within pipeline
            $table->smallInteger('order_index');

            // Probability percentage (0-100)
            $table->smallInteger('default_probability_pct')->nullable();

            // Color for UI display
            $table->string('color', 16)->nullable();

            // Stage type flags
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);

            // Unique constraints
            $table->unique(['pipeline_id', 'key']);
            $table->unique(['pipeline_id', 'order_index']);

            // Indexes
            $table->index('pipeline_id');
        });
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

    /**
     * Set the migration scope
     */
    public $migrationScope = 'database';
}
