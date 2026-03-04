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

/**
 * PHPUnit bootstrap: ensure test DBs are ready before tests run.
 * Tries migrate first (fast path); on failure runs full test:setup-databases.
 * The nmsprime.migrations table comes from the schema dump, so if the dump
 * was never loaded, migrate will fail and we run the full setup.
 */

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (config('database.default') !== 'pgsql') {
    return;
}

$runFullSetup = function (): bool {
    return Artisan::call('test:setup-databases') === 0;
};

try {
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    if ($exitCode !== 0) {
        if (! $runFullSetup()) {
            fwrite(STDERR, "Test database setup failed. Run 'php artisan test:setup-databases' manually.\n");
            exit(1);
        }
    }
} catch (\Throwable $e) {
    if (! $runFullSetup()) {
        fwrite(STDERR, 'Test database setup failed: '.$e->getMessage()."\n");
        fwrite(STDERR, "Run 'php artisan test:setup-databases' manually.\n");
        exit(1);
    }
}
