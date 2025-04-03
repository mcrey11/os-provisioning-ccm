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

    protected $tableName = 'tax';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            $table->string('name')->nullable();
            $table->decimal('rate', 7, 4, true)->nullable();
            $table->string('type', 50)->nullable();
            $table->boolean('enabled')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
        });

        DB::insert("INSERT INTO {$this->tableName} (name, rate) VALUES ('Mehrwertsteuer', 19)");

        Schema::table('billingbase', function (Blueprint $table) {
            $table->dropColumn('tax');
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

        Schema::table('billingbase', function (Blueprint $table) {
            $table->decimal('tax', 7, 4, true);
        });

        if (\Module::collections()->has('BillingBase')) {
            Modules\BillingBase\Entities\BillingBase::first()->update(['tax' => 19]);
        }
    }
};
