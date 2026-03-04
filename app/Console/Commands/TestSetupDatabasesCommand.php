<?php

/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

/**
 * Create and initialize test databases (test_nmsprime, test_nmsprime_ccc) for PHPUnit.
 * Uses sudo -u postgres and psql path from Install/files/postgres_version (same as Install/after_install.sh).
 * For local/CI only; requires sudo without password for the postgres user on the DB host.
 */
class TestSetupDatabasesCommand extends Command
{
    protected $signature = 'test:setup-databases
                            {--rebuild : Drop test databases first, then create and load from scratch}
                            {--skip-create : Skip creating the databases}
                            {--skip-load : Skip loading schema dumps}
                            {--skip-migrate : Skip running migrations}';

    protected $description = 'Create test_nmsprime and test_nmsprime_ccc, load schema dumps, and run migrations (for local/CI testing)';

    private const TEST_DB_MAIN = 'test_nmsprime';

    private const TEST_DB_CCC = 'test_nmsprime_ccc';

    private bool $didLoadMainDump = false;

    public function handle(): int
    {
        if (config('database.default') !== 'pgsql') {
            $this->error('Test database setup is only supported for PostgreSQL (default connection must be pgsql).');

            return 1;
        }

        if (! is_readable(base_path('Install/files/postgres_version'))) {
            return $this->exitVersionFileMissing();
        }

        if ($this->option('rebuild') && $this->dropDatabases() !== 0) {
            return 1;
        }

        if (! $this->option('skip-create')) {
            if ($this->createDatabases() !== 0) {
                return 1;
            }
        }

        if (! $this->option('skip-load')) {
            if ($this->loadSchemaDumps() !== 0) {
                return 1;
            }
        }

        if (! $this->option('skip-migrate')) {
            if ($this->runMigrations() !== 0) {
                return 1;
            }
        }

        $this->info('Test databases are ready.');

        return 0;
    }

    /**
     * Resolve psql binary path from Install/files/postgres_version (same source as Install scripts).
     * Returns null if the version file cannot be read (e.g. on RPM-installed production); do not create test DBs there.
     * When the file is readable, falls back to glob or "psql" from PATH if the versioned path is not executable.
     */
    private function getPsqlPath(): ?string
    {
        $versionFile = base_path('Install/files/postgres_version');
        if (! is_readable($versionFile)) {
            return null;
        }

        $version = trim((string) file_get_contents($versionFile));
        if ($version !== '') {
            $path = '/usr/pgsql-'.$version.'/bin/psql';
            if (is_executable($path)) {
                return $path;
            }
        }

        $glob = glob('/usr/pgsql-*/bin/psql');
        if ($glob !== false && $glob !== []) {
            rsort($glob);

            return $glob[0];
        }

        return 'psql';
    }

    private function exitVersionFileMissing(): int
    {
        $path = base_path('Install/files/postgres_version');
        $this->error('Install/files/postgres_version could not be read.');
        $this->line('Test databases cannot be created (this file is not present on RPM-installed production systems).');
        $this->line('Run test:setup-databases on a dev or CI environment where the Install files are present.');

        return 1;
    }

    private function dropDatabases(): int
    {
        $this->info('Dropping test databases…');

        $psqlPath = $this->getPsqlPath();
        if ($psqlPath === null) {
            return $this->exitVersionFileMissing();
        }

        foreach ([self::TEST_DB_MAIN, self::TEST_DB_CCC] as $dbName) {
            if (! $this->databaseExists($dbName)) {
                $this->line("  Database does not exist: {$dbName}");

                continue;
            }

            $dbQuoted = '"'.str_replace('"', '""', $dbName).'"';
            $process = new Process(
                [
                    'sudo', '-u', 'postgres',
                    $psqlPath, '-d', 'postgres',
                    '-c', "DROP DATABASE {$dbQuoted} WITH (FORCE)",
                ],
                null,
                null,
                null,
                30
            );
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error("  Failed to drop {$dbName}: ".$process->getErrorOutput());

                return 1;
            }
            $this->line("  Dropped database: {$dbName}");
        }

        return 0;
    }

    private function databaseExists(string $dbName): bool
    {
        $psqlPath = $this->getPsqlPath();
        if ($psqlPath === null) {
            return false;
        }

        $process = new Process(
            [
                'sudo', '-u', 'postgres',
                $psqlPath, '-d', 'postgres', '-t', '-A',
                '-c', 'SELECT 1 FROM pg_database WHERE datname = '.$this->quoteIdentifier($dbName),
            ],
            null,
            null,
            null,
            10
        );
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) === '1';
    }

    private function quoteIdentifier(string $name): string
    {
        return "'".str_replace("'", "''", $name)."'";
    }

    private function createDatabases(): int
    {
        $this->info('Creating test databases…');

        $psqlPath = $this->getPsqlPath();
        if ($psqlPath === null) {
            return $this->exitVersionFileMissing();
        }
        $mainUser = config('database.connections.pgsql.username');
        $cccUser = config('database.connections.pgsql-ccc.username') ?: $mainUser;

        foreach ([
            [self::TEST_DB_MAIN, $mainUser],
            [self::TEST_DB_CCC, $cccUser],
        ] as [$dbName, $owner]) {
            if ($this->databaseExists($dbName)) {
                $this->line("  Database already exists: {$dbName}");

                continue;
            }

            $dbQuoted = '"'.str_replace('"', '""', $dbName).'"';
            $ownerQuoted = '"'.str_replace('"', '""', $owner).'"';
            $sql = "CREATE DATABASE {$dbQuoted} OWNER {$ownerQuoted}";

            $process = new Process(
                ['sudo', '-u', 'postgres', $psqlPath, '-d', 'postgres', '-c', $sql],
                null,
                null,
                null,
                30
            );
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error("  Failed to create {$dbName}: ".$process->getErrorOutput());

                return 1;
            }
            $this->line("  Created database: {$dbName}");
        }

        return 0;
    }

    private function mainDumpAlreadyLoaded(): bool
    {
        $psqlPath = $this->getPsqlPath();
        if ($psqlPath === null) {
            return false;
        }

        $process = new Process(
            [
                'sudo', '-u', 'postgres',
                $psqlPath, '-d', self::TEST_DB_MAIN, '-t', '-A',
                '-c', "SELECT 1 FROM information_schema.tables WHERE table_schema = 'nmsprime' AND table_name = 'migrations'",
            ],
            null,
            null,
            null,
            10
        );
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) === '1';
    }

    private function cccDumpAlreadyLoaded(): bool
    {
        $psqlPath = $this->getPsqlPath();
        if ($psqlPath === null) {
            return false;
        }

        $process = new Process(
            [
                'sudo', '-u', 'postgres',
                $psqlPath, '-d', self::TEST_DB_CCC, '-t', '-A',
                '-c', "SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'cccauthuser'",
            ],
            null,
            null,
            null,
            10
        );
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) === '1';
    }

    private function loadSchemaDumps(): int
    {
        $this->info('Loading schema dumps into test databases…');

        $psqlPath = $this->getPsqlPath();
        if ($psqlPath === null) {
            return $this->exitVersionFileMissing();
        }

        $basePath = base_path('Install/files');

        $dumps = [
            [self::TEST_DB_MAIN, $basePath.DIRECTORY_SEPARATOR.'nmsprime.pgsql', 'main'],
            [self::TEST_DB_CCC, $basePath.DIRECTORY_SEPARATOR.'nmsprime_ccc.pgsql', 'ccc'],
        ];

        foreach ($dumps as [$database, $dumpPath, $kind]) {
            if ($kind === 'main' && $this->mainDumpAlreadyLoaded()) {
                $this->line("  Schema already present in {$database}, skipping dump");

                continue;
            }
            if ($kind === 'ccc' && $this->cccDumpAlreadyLoaded()) {
                $this->line("  Schema already present in {$database}, skipping dump");

                continue;
            }

            if (! is_readable($dumpPath)) {
                $this->error("  Schema dump not found or not readable: {$dumpPath}");

                return 1;
            }

            $process = new Process(
                [
                    'sudo', '-u', 'postgres',
                    $psqlPath,
                    '-d', $database,
                    '-f', $dumpPath,
                    '-v', 'ON_ERROR_STOP=1',
                ],
                null,
                null,
                null,
                300
            );
            $process->run();

            if (! $process->isSuccessful()) {
                $this->error("  Failed to load dump into {$database}:");
                $this->line($process->getErrorOutput());

                return 1;
            }
            $this->line("  Loaded schema into: {$database}");
            if ($kind === 'main') {
                $this->didLoadMainDump = true;
            }
        }

        return 0;
    }

    private function runMigrations(): int
    {
        $this->info('Running migrations on test databases…');

        Config::set('database.connections.pgsql.database', self::TEST_DB_MAIN);
        Config::set('database.connections.pgsql-ccc.database', self::TEST_DB_CCC);
        DB::purge('pgsql');
        DB::purge('pgsql-ccc');

        $migrationsTable = config('database.migrations.table', 'migrations');
        if ($this->didLoadMainDump) {
            DB::connection('pgsql')->table($migrationsTable)->delete();
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->line(Artisan::output());
            Artisan::call('module:migrate', ['--all' => true, '--force' => true]);
            $this->line(Artisan::output());
        } catch (\Throwable $e) {
            $this->error('Migrations failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
