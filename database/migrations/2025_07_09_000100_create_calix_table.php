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

    protected $tableName = 'calix';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            $table->string('default_org_id')->nullable()->default('Calix');
            $table->string('default_service_type')->nullable();
            $table->string('default_ont_port_id')->nullable();
            $table->string('default_ont_type')->nullable()->default('Residential');
            $table->string('default_ont_profile_id')->nullable();
        });

        // Insert default record
        DB::insert("INSERT INTO {$this->tableName} (created_at,updated_at) VALUES (now(),now())");
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
