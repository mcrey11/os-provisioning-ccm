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

    protected $tableName = 'order_portal';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // WebOrder specific config fields
            $table->boolean('enable_business_customers')->default(true)->after('show_header');

            // Shared config fields (WebOrder + CCC)
            // Payment methods - individual boolean fields
            $table->boolean('payment_method_sepa')->default(true)->after('enable_business_customers');
            $table->boolean('payment_method_rechnung')->default(true)->after('payment_method_sepa');
            $table->boolean('payment_method_acs')->default(false)->after('payment_method_rechnung');
            $table->boolean('payment_method_credit_card')->default(false)->after('payment_method_acs');
            $table->unsignedInteger('postal_invoice_product_id')->nullable()->after('payment_method_credit_card');
        });

        // Defaults are already set in column definitions (SEPA and Rechnung enabled)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn([
                'enable_business_customers',
                'payment_method_sepa',
                'payment_method_rechnung',
                'payment_method_acs',
                'payment_method_credit_card',
                'postal_invoice_product_id',
            ]);
        });
    }
};
