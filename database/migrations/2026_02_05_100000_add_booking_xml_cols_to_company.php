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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->bigInteger('cdr_costcenter_id')->nullable();
            $table->bigInteger('cdr_booking_account_id')->nullable();
            $table->string('invoice_notif_template')->nullable();
            $table->string('org_unit_nr')->nullable();
            $table->string('tax_code')->nullable();
            $table->bigInteger('tenant_id')->nullable();
            $table->string('tenant_nr')->nullable();
            $table->string('xml_transfer_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumns([
                'cdr_costcenter_id',
                'cdr_booking_account_id',
                'invoice_notif_template',
                'tax_code',
                'tenant_id',
                'tenant_nr',
                'xml_transfer_reason',
            ]);
        });
    }
};
