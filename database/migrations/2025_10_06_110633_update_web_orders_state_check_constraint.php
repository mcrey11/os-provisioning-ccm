<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing check constraint first
        DB::statement('ALTER TABLE web_orders DROP CONSTRAINT IF EXISTS web_orders_state_check');

        // Now update any existing invalid state values
        DB::statement("UPDATE web_orders SET state = 'products_selected' WHERE state = 'product_selected'");

        // Add the new check constraint with all the states
        DB::statement("ALTER TABLE web_orders ADD CONSTRAINT web_orders_state_check 
            CHECK (state IN (
                'draft',
                'availability_check',
                'customer_type_selected', 
                'products_selected',
                'order_summary',
                'payment',
                'confirmation',
                'submitted',
                'pending_checks',
                'manual_review',
                'rejected',
                'ready_for_sales',
                'converted'
            ))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new check constraint
        DB::statement('ALTER TABLE web_orders DROP CONSTRAINT IF EXISTS web_orders_state_check');

        // Restore the original check constraint (if needed)
        DB::statement("ALTER TABLE web_orders ADD CONSTRAINT web_orders_state_check 
            CHECK (state IN (
                'draft',
                'submitted',
                'pending_checks',
                'manual_review',
                'rejected',
                'ready_for_sales',
                'converted'
            ))");
    }
};
