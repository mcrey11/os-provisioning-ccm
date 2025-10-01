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
        Schema::table($this->tableName, function (Blueprint $table) {
            // Make product_id nullable since it's now conditional
            $table->bigInteger('product_id')->nullable()->change();

            // Add type field to distinguish between product and product_layer
            $table->string('type')->default('product')->after('product_id');

            // Add product_layer_id field (nullable since it's conditional)
            $table->bigInteger('product_layer_id')->nullable()->index()->after('type');
            $table->foreign('product_layer_id')->references('id')->on('product_layer')->onDelete('restrict');

            // Add index for type field
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropForeign(['product_layer_id']);
            $table->dropIndex(['product_layer_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'product_layer_id']);
        });
    }
};
