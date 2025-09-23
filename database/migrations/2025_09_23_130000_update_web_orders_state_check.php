<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 */

use Database\Migrations\BaseMigration;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'web_orders';

    public function up()
    {
        // Replace state check constraint with more granular portal states
        // Drop existing constraint if present
        try {
            \DB::statement("ALTER TABLE {$this->tableName} DROP CONSTRAINT IF EXISTS web_orders_state_check");
        } catch (\Throwable $e) {
            // ignore
        }

        // Add new constraint including progress states
        \DB::statement(
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT web_orders_state_check CHECK (state IN (".
            "'draft',".
            "'availability_checked',".
            "'customer_type_selected',".
            "'product_selected',".
            "'order_details',".
            "'confirmation',".
            // existing business states
            "'submitted',".
            "'pending_checks',".
            "'manual_review',".
            "'rejected',".
            "'ready_for_sales',".
            "'converted'".
            "))"
        );
    }

    public function down()
    {
        // Revert to the previous constraint
        try {
            \DB::statement("ALTER TABLE {$this->tableName} DROP CONSTRAINT IF EXISTS web_orders_state_check");
        } catch (\Throwable $e) {
        }

        \DB::statement(
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT web_orders_state_check CHECK (state IN (".
            "'draft','submitted','pending_checks','manual_review','rejected','ready_for_sales','converted'".
            "))"
        );
    }
};


