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

    protected $tableName = 'crm_lead';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Foreign key fields
            $table->unsignedBigInteger('contact_point_id')->nullable();
            $table->unsignedBigInteger('realty_id')->nullable();
            $table->unsignedBigInteger('apartment_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();

            // Lead specific fields
            $table->string('source', 64)->nullable();
            $table->string('legal_basis', 32)->default('unknown');
            $table->string('status', 24)->default('cold');
            $table->string('disqual_reason', 191)->nullable();
            $table->text('notes')->nullable();

            // Add indexes for all foreign keys
            $table->index('contact_point_id');
            $table->index('realty_id');
            $table->index('apartment_id');
            $table->index('owner_id');

            // Add indexes for commonly queried fields
            $table->index('status');
            $table->index('source');
            $table->index('legal_basis');
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
