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

    protected $tableName = 'ci_category_field_rule';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);
            $table->bigInteger('ci_category_id');
            $table->bigInteger('ci_field_id');
            $table->bigInteger('ci_requirement_level_id');
            $table->boolean('active')->default(true);
            $table->json('conditional_when')->nullable();
            $table->bigInteger('ci_status_id')->nullable();
            
            // Foreign key constraints
            $table->foreign('ci_category_id')->references('id')->on('ci_category')->onDelete('restrict');
            $table->foreign('ci_field_id')->references('id')->on('ci_field')->onDelete('restrict');
            $table->foreign('ci_requirement_level_id')->references('id')->on('ci_requirement_level')->onDelete('restrict');
            $table->foreign('ci_status_id')->references('id')->on('ci_status')->onDelete('restrict');
            
            // Indexes
            $table->index('ci_category_id');
            $table->index('ci_field_id');
            $table->index('ci_requirement_level_id');
            $table->index('ci_status_id');
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
};
