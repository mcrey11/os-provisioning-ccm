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

    protected $tableName = 'web_order_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Foreign keys / references
            $table->bigInteger('web_order_id')->nullable(false)->index('idx_web_order_items_web_order_id');

            // Business fields
            $table->string('type', 20)->nullable(false); // plan | addon | hardware | service
            $table->string('product_id', 191)->nullable(false)->index('idx_web_order_items_product_id');
            $table->string('name', 191)->nullable(false);
            $table->integer('qty')->default(1);
            $table->integer('sort')->default(0)->nullable();
        });

        // Check constraint for type
        \DB::statement("ALTER TABLE web_order_items ADD CONSTRAINT web_order_items_type_check CHECK (type IN ('plan','addon','hardware','service'))");
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
