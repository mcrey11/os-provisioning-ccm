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
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ci_category_subject_rule', function (Blueprint $table) {
            $this->upTableGeneric($table);
            $table->bigInteger('ci_category_id')->unsigned();
            $table->integer('subject_type_id');
            $table->boolean('active')->default(true);
            
            // Foreign key constraint
            $table->foreign('ci_category_id')->references('id')->on('ci_category')->onDelete('restrict');
            
            // Unique constraint
            $table->unique(['ci_category_id', 'subject_type_id']);
            
            // Index for performance
            $table->index(['subject_type_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ci_category_subject_rule');
    }
};
