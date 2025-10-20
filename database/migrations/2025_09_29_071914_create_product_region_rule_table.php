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

    protected $tableName = 'product_region_rule';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Product reference
            $table->bigInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('product')->onDelete('restrict');

            // Rule definition
            $table->string('rule'); // allow or deny
            $table->string('scope_type'); // dma|city|street|realty|apartment|segment|managed_network
            $table->bigInteger('scope_ref_id'); // ID in chosen scope (polymorphic)

            // Additional fields
            $table->string('requires_right_code')->nullable();
            $table->boolean('managed_only')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->smallInteger('priority')->default(0);

            // Indexes for common queries
            $table->index(['product_id', 'rule']);
            $table->index(['scope_type', 'scope_ref_id']);
            $table->index(['effective_from', 'effective_to']);
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
