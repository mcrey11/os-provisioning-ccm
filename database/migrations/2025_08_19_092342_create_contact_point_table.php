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

    protected $tableName = 'contact_point';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Contact type and basic info
            $table->string('type', 32); // individual|organization
            $table->string('salutation', 32)->nullable();
            $table->string('firstname', 191)->nullable();
            $table->string('lastname', 191)->nullable();
            $table->string('company', 191)->nullable();
            $table->string('email', 191)->nullable()->index();
            $table->string('phone', 100)->nullable();
            $table->date('birthday')->nullable();

            // Foreign key relationships
            $table->bigInteger('apartment_id')->nullable();
            $table->bigInteger('address_id')->nullable();
            $table->string('party_id_ext', 64)->nullable(); // external/TMF Party id

            // Additional info
            $table->text('notes')->nullable();

            // Indexes
            $table->index('apartment_id');
            $table->index('address_id');

            // Foreign key constraints
            $table->foreign('apartment_id')->references('id')->on('apartment')->onDelete('restrict');
            $table->foreign('address_id')->references('id')->on('address')->onDelete('restrict');
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
