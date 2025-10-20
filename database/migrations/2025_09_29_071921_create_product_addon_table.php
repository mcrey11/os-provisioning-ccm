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

    protected $tableName = 'product_addon';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Base product reference (nullable for type-based addons)
            $table->bigInteger('base_product_id')->nullable()->index();
            $table->foreign('base_product_id')->references('id')->on('product')->onDelete('restrict');

            // Alternative to base_product_id
            $table->string('base_type')->nullable(); // product type for type-based addons

            // Add-on product (always required)
            $table->bigInteger('addon_product_id')->index();
            $table->foreign('addon_product_id')->references('id')->on('product')->onDelete('restrict');

            // Addon options
            $table->boolean('required')->default(false);
            $table->smallInteger('max_qty')->default(1);

            // Indexes for common queries
            $table->index(['base_product_id', 'base_type']);
            $table->index(['base_type', 'required']);

            // Ensure at least one base reference is provided
            $table->index(['base_product_id', 'addon_product_id']);
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
