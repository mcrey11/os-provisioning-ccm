<?php

use Database\Migrations\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'product_addon';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Add new product_layer_id column
            $table->bigInteger('product_layer_id')->nullable()->index()->after('base_product_id');
            $table->foreign('product_layer_id')->references('id')->on('product_layer')->onDelete('restrict');
        });

        // Migrate existing base_type data to product_layer_id where possible
        $this->migrateBaseTypeToProductLayer();

        Schema::table($this->tableName, function (Blueprint $table) {
            // Drop old base_type column and indexes
            $table->dropIndex(['base_product_id', 'base_type']);
            $table->dropIndex(['base_type', 'required']);
            $table->dropColumn('base_type');
            
            // Add new indexes
            $table->index(['product_layer_id', 'required']);
            $table->index(['base_product_id', 'product_layer_id']);
        });

        // Fix unique constraints to respect soft deletes
        DB::statement("ALTER TABLE {$this->tableName} DROP CONSTRAINT IF EXISTS product_addon_base_product_id_addon_product_id_unique");
        DB::statement("CREATE UNIQUE INDEX product_addon_base_product_id_addon_product_id_unique_active ON {$this->tableName} (base_product_id, addon_product_id) WHERE deleted_at IS NULL");

        DB::statement("ALTER TABLE {$this->tableName} DROP CONSTRAINT IF EXISTS product_addon_product_layer_id_addon_product_id_unique");
        DB::statement("CREATE UNIQUE INDEX product_addon_product_layer_id_addon_product_id_unique_active ON {$this->tableName} (product_layer_id, addon_product_id) WHERE deleted_at IS NULL");
    }

    /**
     * Migrate base_type values to corresponding product_layer_id values.
     */
    protected function migrateBaseTypeToProductLayer()
    {
        // Map product types to layers (you may need to adjust these mappings)
        $typeToLayerMapping = [
            'internet' => 1, // Internet layer
            'tv' => 2,       // TV layer  
            'tv_advanced' => 3, // TV Advanced layer
        ];

        foreach ($typeToLayerMapping as $baseType => $layerId) {
            DB::table($this->tableName)
                ->whereNotNull('base_type')
                ->where('base_type', $baseType)
                ->whereNull('deleted_at')
                ->update([
                    'product_layer_id' => $layerId,
                    'base_type' => null,
                ]);
        }

        // Remove any remaining base_type entries that couldn't be mapped
        // You might want to keep these as base_product_id only or handle them differently
        DB::table($this->tableName)
            ->whereNotNull('base_type')
            ->whereNull('deleted_at')
            ->update(['base_type' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore unique constraints
        DB::statement("DROP INDEX IF EXISTS product_addon_product_layer_id_addon_product_id_unique_active");
        DB::statement("DROP INDEX IF EXISTS product_addon_base_product_id_addon_product_id_unique_active");
        
        DB::statement("ALTER TABLE {$this->tableName} ADD CONSTRAINT product_addon_base_product_id_addon_product_id_unique UNIQUE (base_product_id, addon_product_id)");
        DB::statement("ALTER TABLE {$this->tableName} ADD CONSTRAINT product_addon_product_layer_id_addon_product_id_unique UNIQUE (product_layer_id, addon_product_id)");

        Schema::table($this->tableName, function (Blueprint $table) {
            // Add back base_type column
            $table->string('base_type')->nullable()->after('product_layer_id');
            
            // Restore indexes
            $table->index(['base_product_id', 'base_type']);
            $table->index(['base_type', 'required']);
            
            // Remove new indexes
            $table->dropIndex(['product_layer_id', 'required']);
            $table->dropIndex(['base_product_id', 'product_layer_id']);
        });

        // Drop foreign key and column
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropForeign(['product_layer_id']);
            $table->dropColumn('product_layer_id');
        });
    }
};