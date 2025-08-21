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
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'address';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // First, update any NULL values to 0 to avoid constraint violations
            \DB::statement('UPDATE address SET lat = 0 WHERE lat IS NULL');
            \DB::statement('UPDATE address SET lng = 0 WHERE lng IS NULL');
            
            // Remove NOT NULL constraints from lat and lng fields
            $table->decimal('lat', 10, 8)->nullable()->change();
            $table->decimal('lng', 11, 8)->nullable()->change();
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
            // Restore NOT NULL constraints on lat and lng fields
            $table->decimal('lat', 10, 8)->nullable(false)->change();
            $table->decimal('lng', 11, 8)->nullable(false)->change();
        });
    }
};
