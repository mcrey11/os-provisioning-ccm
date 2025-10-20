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

    protected $tableName = 'ci_customer_interaction';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $this->upTableGeneric($table);

            // Polymorphic relationship
            $table->string('subject_type', 191);
            $table->unsignedBigInteger('subject_id');

            // Foreign key relationships
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('contact_point_id')->nullable();
            $table->unsignedBigInteger('ci_channel_id');
            $table->unsignedBigInteger('ci_direction_id');
            $table->unsignedBigInteger('ci_category_id');
            $table->unsignedBigInteger('ci_status_id');

            // Content fields
            $table->string('subject', 191)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('requires_ticket')->default(false);

            // Contact information
            $table->string('msisdn', 64)->nullable();
            $table->string('email_address', 191)->nullable();

            // Timestamps
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();

            // User relationships
            $table->unsignedBigInteger('users_created_by_id')->nullable();
            $table->unsignedBigInteger('users_closed_by_id')->nullable();

            // Metadata
            $table->json('meta')->default('{}');

            // Indexes
            $table->index(['subject_type', 'subject_id']);
            $table->index('contract_id');
            $table->index('contact_point_id');
            $table->index('ci_channel_id');
            $table->index('ci_direction_id');
            $table->index('ci_category_id');
            $table->index('ci_status_id');
            $table->index('users_created_by_id');
            $table->index('users_closed_by_id');
            $table->index('opened_at');
            $table->index('closed_at');

            // Foreign key constraints
            $table->foreign('contract_id')->references('id')->on('contract')->onDelete('restrict');
            $table->foreign('contact_point_id')->references('id')->on('contact_point')->onDelete('restrict');
            $table->foreign('ci_channel_id')->references('id')->on('ci_channel')->onDelete('restrict');
            $table->foreign('ci_direction_id')->references('id')->on('ci_direction')->onDelete('restrict');
            $table->foreign('ci_category_id')->references('id')->on('ci_category')->onDelete('restrict');
            $table->foreign('ci_status_id')->references('id')->on('ci_status')->onDelete('restrict');
            $table->foreign('users_created_by_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('users_closed_by_id')->references('id')->on('users')->onDelete('restrict');
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
