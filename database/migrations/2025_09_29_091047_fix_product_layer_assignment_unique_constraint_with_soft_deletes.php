<?php

use Database\Migrations\BaseMigration;
use Illuminate\Support\Facades\DB;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'product_layer_assignment';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the existing unique constraint
        DB::statement("ALTER TABLE {$this->tableName} DROP CONSTRAINT IF EXISTS product_layer_assignment_product_id_product_layer_id_unique");

        // Create a partial unique constraint that excludes soft-deleted records
        DB::statement("CREATE UNIQUE INDEX product_layer_assignment_product_id_product_layer_id_unique_active ON {$this->tableName} (product_id, product_layer_id) WHERE deleted_at IS NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the partial unique constraint
        DB::statement('DROP INDEX IF EXISTS product_layer_assignment_product_id_product_layer_id_unique_active');

        // Recreate the original unique constraint
        DB::statement("ALTER TABLE {$this->tableName} ADD CONSTRAINT product_layer_assignment_product_id_product_layer_id_unique UNIQUE (product_id, product_layer_id)");
    }
};
