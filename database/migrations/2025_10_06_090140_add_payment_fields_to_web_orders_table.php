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

    protected $tableName = 'web_orders';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->after('state');
            $table->string('payment_status', 20)->default('pending')->after('payment_method');
            $table->string('payment_reference', 255)->nullable()->after('payment_status');
            $table->json('payment_data')->nullable()->after('payment_reference');
        });

        // Add check constraints using raw SQL
        DB::statement('ALTER TABLE web_orders ADD CONSTRAINT web_orders_payment_method_check CHECK (payment_method IS NULL OR payment_method IN (\'sepa\', \'card\'))');
        DB::statement('ALTER TABLE web_orders ADD CONSTRAINT web_orders_payment_status_check CHECK (payment_status IN (\'pending\', \'processing\', \'completed\', \'failed\', \'cancelled\'))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'payment_reference', 'payment_data']);
        });
    }
};
