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

/**
 * Make several adaptations to make it possible to account for user specified month
 *
 * Add accounting_month, rcd, sepaaccount_id
 * Remove month, path, year
 */
return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'settlementrun';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('accounting_month')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('rcd')->nullable();
            $table->string('sepaaccount_id')->nullable();
        });

        if (\Module::collections()->has('BillingBase')) {
            foreach (Modules\BillingBase\Entities\SettlementRun::get() as $sr) {
                $sr->update(['accounting_month' => $sr->year.'-'.str_pad($sr->month, 2, '0', STR_PAD_LEFT)]);
            }
        }

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['year', 'month', 'path']);
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
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->string('path')->nullable();
        });

        if (\Module::collections()->has('BillingBase')) {
            foreach (Modules\BillingBase\Entities\SettlementRun::get() as $sr) {
                $parts = explode('-', $sr->accounting_month);

                $sr->update([
                    'month' => $parts[1],
                    'year' => $parts[0],
                ]);
            }
        }

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['accounting_month', 'invoice_date', 'rcd', 'sepaaccount_id']);
        });
    }
};
