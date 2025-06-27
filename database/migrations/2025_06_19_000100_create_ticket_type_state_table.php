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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'ticket_type_state';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            $table->string('name');
            $table->string('bs_class');
            $table->string('description')->nullable()->default(null);
        });

        // insert legacy ticket states
        DB::table($this->tableName)->insert([
            ['created_at' => now(), 'updated_at' => now(), 'name' => 'New', 'bs_class' => 'warning'],
            ['created_at' => now(), 'updated_at' => now(), 'name' => 'In Progress', 'bs_class' => 'green'],
            ['created_at' => now(), 'updated_at' => now(), 'name' => 'Paused', 'bs_class' => 'info'],
            ['created_at' => now(), 'updated_at' => now(), 'name' => 'Closed', 'bs_class' => 'success'],
        ]);
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
