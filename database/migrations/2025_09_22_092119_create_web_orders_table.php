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

    protected $tableName = 'web_orders';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);
            
            // Foreign key relationships
            $table->bigInteger('contact_point_id')->nullable();
            $table->bigInteger('service_address_id')->nullable();
            $table->bigInteger('billing_address_id')->nullable();
            $table->bigInteger('crm_opportunity_id')->nullable();
            
            // Business fields
            $table->string('customer_type', 20)->nullable(false);
            $table->boolean('is_switcher')->default(false);
            $table->string('state', 40)->default('draft');
            $table->text('notes')->nullable();
            
            // JSON fields for flexible data storage (using jsonb for PostgreSQL)
            $table->jsonb('availability_snapshot')->nullable();
            $table->jsonb('utm_json')->nullable();
            $table->jsonb('consent_json')->nullable();
            
            // Indexes for foreign keys
            $table->index('contact_point_id', 'idx_web_orders_cp');
            $table->index('service_address_id', 'idx_web_orders_service_addr');
            $table->index('billing_address_id', 'idx_web_orders_billing_addr');
            $table->index('crm_opportunity_id', 'idx_web_orders_crm_opp');
            
            // Business indexes
            $table->index('state', 'idx_web_orders_state');
            $table->index(['is_switcher', 'state'], 'idx_web_orders_switcher');
            
            // Note: GIN indexes for JSON fields will be added using raw SQL below
            
            // Foreign key constraints
            $table->foreign('contact_point_id')->references('id')->on('contact_point')->onDelete('restrict');
            $table->foreign('service_address_id')->references('id')->on('address')->onDelete('restrict');
            $table->foreign('billing_address_id')->references('id')->on('address')->onDelete('restrict');
            $table->foreign('crm_opportunity_id')->references('id')->on('crm_opportunity')->onDelete('restrict');
        });
        
        // Add check constraints using raw SQL
        \DB::statement('ALTER TABLE web_orders ADD CONSTRAINT web_orders_customer_type_check CHECK (customer_type IN (\'residential\', \'business\'))');
        \DB::statement('ALTER TABLE web_orders ADD CONSTRAINT web_orders_state_check CHECK (state IN (\'draft\', \'submitted\', \'pending_checks\', \'manual_review\', \'rejected\', \'ready_for_sales\', \'converted\'))');
        
        // Add GIN indexes for JSON fields with proper operator class
        \DB::statement('CREATE INDEX idx_web_orders_availability_gin ON web_orders USING gin (availability_snapshot jsonb_path_ops)');
        \DB::statement('CREATE INDEX idx_web_orders_utm_gin ON web_orders USING gin (utm_json jsonb_path_ops)');
        \DB::statement('CREATE INDEX idx_web_orders_consent_gin ON web_orders USING gin (consent_json jsonb_path_ops)');
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
