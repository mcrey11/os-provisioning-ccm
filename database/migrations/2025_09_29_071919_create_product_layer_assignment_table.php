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

    protected $tableName = 'product_layer_assignment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);
            
            // Foreign keys
            $table->bigInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('product')->onDelete('restrict');
            
            $table->bigInteger('product_layer_id')->index();
            $table->foreign('product_layer_id')->references('id')->on('product_layer')->onDelete('restrict');
            
            $table->integer('sort')->default(0);
            
            // Ensure unique combination
            $table->unique(['product_id', 'product_layer_id']);
            
            // Indexes for common queries
            $table->index(['product_layer_id', 'sort']);
            $table->index(['product_id', 'sort']);
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