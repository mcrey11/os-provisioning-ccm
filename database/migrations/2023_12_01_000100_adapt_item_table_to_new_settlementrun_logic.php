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
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\SettlementRun;
use Modules\BillingBase\Utilities\BillingCycles\Quarterly;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->bigInteger('settlementrun_id')->nullable();
            $table->string('payed_month', 10)->nullable()->change();
            $table->date('payed_until_before_sr')->nullable(); // Before settlement run
            $table->date('payed_until_after_sr')->nullable(); // After settlement run
        });

        if (! Module::collections()->has('BillingBase')) {
            return;
        }

        $lastSr = SettlementRun::latest('id')->first();

        if (! $lastSr) {
            return;
        }

        $itemQuery = Item::withTrashed()->join('product as p', 'p.id', 'item.product_id');

        // Set payed_until date of YEARLY charged items that were payed already for this year
        $date = (clone $lastSr->accounting_month)->endOfYear();
        $dateInSql = $this->selectItemValidTo($date);

        (clone $itemQuery)->where('billing_cycle', 'Yearly')
            ->where('payed_month', '!=', 0)
            ->whereNotNull('payed_month')
            ->update(['payed_until_before_sr' => $dateInSql]);

        $date = (clone $lastSr->accounting_month)->subYear()->endOfYear();
        $dateInSql = $this->selectItemValidTo($date);

        // Set payed_until date of YEARLY charged items that are not yet payed for this year
        (clone $itemQuery)->where('billing_cycle', 'Yearly')
            ->where('item.created_at', '<', now()->startOfYear()->toDateString())
            ->where('item.valid_from', '<', now()->startOfYear()->toDateString())
            ->where(function ($query) {
                $query
                    ->where('payed_month', 0)
                    ->orWhereNull('payed_month');
            })
            ->update(['payed_until_before_sr' => $date]);

        // Set payed_until date of MONTHLY charged items
        $dateBefore = (clone $lastSr->accounting_month)->subMonth()->endOfMonth()->toDateString();
        $dateAfter = (clone $lastSr->accounting_month)->endOfMonth()->toDateString();

        (clone $itemQuery)->where('billing_cycle', 'Monthly')->update([
            'payed_until_before_sr' => $this->selectItemValidTo($dateBefore),
            'payed_until_after_sr' => $this->selectItemValidTo($dateAfter),
        ]);

        // Set payed_until date of QUARTERLY charged items
        $date = (new Quarterly($lastSr->accounting_month))->getNextBillingMonth()->subMonthsNoOverflow(2)->endOfMonth()->toDateString();
        $dateInSql = $this->selectItemValidTo($date);

        (clone $itemQuery)->where('billing_cycle', 'Quarterly')->update(['payed_until_before_sr' => $date]);
    }

    private function selectItemValidTo($date)
    {
        return DB::raw("CASE WHEN item.valid_to is NULL or item.valid_to > '$date' THEN '$date' ELSE item.valid_to END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['settlementrun_id', 'payed_until_before_sr', 'payed_until_after_sr']);
        });

        DB::statement('ALTER TABLE item ALTER payed_month TYPE INT USING payed_month::integer');
    }
};
