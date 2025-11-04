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

    protected $tableName = 'web_orders';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // JSON field for porting data (using jsonb for PostgreSQL)
            $table->jsonb('porting_data')->nullable()->after('payment_data');
        });

        // Add GIN index for JSON field with proper operator class
        \DB::statement('CREATE INDEX idx_web_orders_porting_data_gin ON web_orders USING gin (porting_data jsonb_path_ops)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Drop index first
            \DB::statement('DROP INDEX IF EXISTS idx_web_orders_porting_data_gin');

            $table->dropColumn('porting_data');
        });
    }
};
