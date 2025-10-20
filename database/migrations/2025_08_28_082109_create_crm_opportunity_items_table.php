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

    protected $tableName = 'crm_opportunity_item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Foreign keys
            $table->bigInteger('opportunity_id')->nullable()->index('by_opportunity_id');
            $table->bigInteger('contract_id')->nullable()->index('by_contract_id');
            $table->bigInteger('product_id')->nullable()->index('by_product_id');
            $table->bigInteger('costcenter_id')->nullable();
            $table->bigInteger('settlementrun_id')->nullable();

            // Item details
            $table->bigInteger('count')->default(1);
            $table->date('valid_from')->nullable();
            $table->boolean('valid_from_fixed')->default(true);
            $table->date('valid_to')->nullable();
            $table->boolean('valid_to_fixed')->default(true);
            $table->decimal('credit_amount', 13, 4)->nullable();
            $table->text('accounting_text')->nullable();
            $table->string('payed_month', 10)->nullable();
            $table->text('smartcardids')->nullable();
            $table->date('payed_until_before_sr')->nullable();
            $table->date('payed_until_after_sr')->nullable();
            $table->json('custom_data')->nullable();
            $table->string('external_status', 191)->nullable();
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
