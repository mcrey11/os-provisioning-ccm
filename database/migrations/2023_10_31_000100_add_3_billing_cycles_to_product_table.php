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

return new class extends BaseMigration
{
    public $migrationScope = 'database';

    protected $tableName = 'product';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Correct owner of all user defined types if not yet done - This is necessary to be able to adapt the type
        $this->fixOwnerOfTypes();

        DB::statement("ALTER TYPE product_billing_cycle ADD VALUE '3m';");
        DB::statement("ALTER TYPE product_billing_cycle ADD VALUE 'q1';");
        // DB::statement("ALTER TYPE product_billing_cycle ADD VALUE 'y1';");
        DB::statement("ALTER TYPE product_billing_cycle ADD VALUE 'semiannual';");
    }

    public function fixOwnerOfTypes()
    {
        $owner = DB::select("SELECT typowner::regrole FROM pg_type WHERE typname = 'product_billing_cycle';");

        if ($owner[0]->typowner == 'nmsprime') {
            return;
        }

        // Get all user defined types - see https://dba.stackexchange.com/questions/35497/display-user-defined-types-and-their-details
        $types = DB::select("select pg_catalog.format_type ( t.oid, NULL ) AS name
    FROM pg_catalog.pg_type t
    LEFT JOIN pg_catalog.pg_namespace n
        ON n.oid = t.typnamespace
    WHERE ( t.typrelid = 0
            OR ( SELECT c.relkind = 'c'
                    FROM pg_catalog.pg_class c
                    WHERE c.oid = t.typrelid
                )
        )
        AND NOT EXISTS
            ( SELECT 1
                FROM pg_catalog.pg_type el
                WHERE el.oid = t.typelem
                    AND el.typarray = t.oid
            )
        AND n.nspname <> 'pg_catalog'
        AND n.nspname <> 'information_schema'
        AND pg_catalog.pg_type_is_visible ( t.oid );");

        foreach ($types as $stdObj) {
            system("sudo -u postgres /usr/pgsql-13/bin/psql nmsprime -c 'ALTER TYPE nmsprime.$stdObj->name OWNER TO nmsprime;'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TYPE product_billing_cycle RENAME TO product_billing_cycle_old;');
        DB::statement("CREATE TYPE product_billing_cycle AS ENUM('Monthly', 'Once', 'Quarterly', 'Yearly');");
        // NOTE: In case the migration rollback fails - e.g. because products with the new cycles were already added - at
        // this step you need to remove all products having one of the upper cycles or set the cycle to a previously existing one
        DB::statement("ALTER TABLE $this->tableName ALTER COLUMN billing_cycle TYPE product_billing_cycle USING billing_cycle::text::product_billing_cycle;");
        DB::state('DROP TYPE product_billing_cycle_old;');
    }
};
