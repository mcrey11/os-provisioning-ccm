#!/usr/bin/env php
<?php

declare(strict_types=1);

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
 * Dump values and terminate script execution.
 */
function dd(mixed ...$data): never
{
    foreach ($data as $d) {
        echo "\n\n";
        echo '--------------------------------------------------------------------------------';
        echo "\n";
        var_dump($d);
        echo "\n";
        echo '----------------------------------------';
    }
    echo "\n\n";
    exit(1);
}

class TestRunner
{
    /** Absolute project base path. */
    protected string $basepath = '';

    /** PHP executable command. */
    protected string $php = '';

    /** Artisan command prefix. */
    protected string $artisan = '';

    /** PHPUnit command used for lifecycle suite. */
    protected string $phpunit = '';

    /** @var array<int, string> Module names enabled before runner starts. */
    protected array $initiallyEnabledModules = [];

    /** Original phpunit.xml content to restore in destructor. */
    protected string $originalPhpunitXml = '';

    /** PHPUnit XML template content for per-circuit test runs. */
    protected string $phpunitXmlTemplate = '';

    /** @var array<int, string> Missing lifecycle test file paths. */
    protected array $missingTestFiles = [];

    /** @var array<string, array<int, string>> Circuit name => enabled module names. */
    protected array $testCircuits = [
        'Basic' => [
            'HfcReq',
            'ProvBase',
        ],
        'Billing' => [
            'BillingBase',
            'HfcReq',
            'ProvBase',
        ],
        'Voip' => [
            'HfcReq',
            'ProvBase',
            'ProvVoip',
        ],
        'Voip and Billing' => [
            'BillingBase',
            'HfcReq',
            'ProvBase',
            'ProvVoip',
        ],
    ];

    /** @var array<int, string> Additional test files always included per circuit. */
    protected array $additionalTests = [
        './tests/RoutesAuthTest.php',
    ];

    /**
     * Initialize runner commands and default paths.
     */
    public function __construct()
    {
        $this->basepath = '/var/www/nmsprime';
        $this->php = '/usr/bin/env php';
        $this->artisan = $this->php.' artisan';
        $this->phpunit = $this->artisan.' test --stop-on-failure --colors=always --testsuite=Lifecycle';
    }

    /**
     * Prepare runtime state before circuit execution.
     */
    public function prepare(): void
    {
        chdir($this->basepath);
        array_map('unlink', glob('./phpunit/out/*.htm'));
        putenv('TERM=xterm-256color');

        $this->originalPhpunitXml = (string) file_get_contents('phpunit.xml');
        $this->phpunitXmlTemplate = (string) file_get_contents('phpunit/phpunit_lifecycle.tpl.xml');

        $this->initiallyEnabledModules = $this->getEnabledModules();
    }

    /**
     * Read currently enabled modules from `artisan module:list`.
     *
     * @return array<int, string>
     */
    protected function getEnabledModules(): array
    {
        $enabledModules = [];
        $output = (string) shell_exec($this->artisan.' --no-ansi module:list');
        $output = explode("\n", $output);

        while ($output) {
            $line = trim(array_pop($output));
            if (! str_starts_with($line, '[')) {
                continue;
            }
            $_ = explode(' ', $line);

            $status = trim($_[0]);
            if ($status != '[Enabled]') {
                continue;
            }

            $module = trim($_[1]);
            $enabledModules[] = $module;
        }

        asort($enabledModules);

        return $enabledModules;
    }

    /**
     * Execute all configured lifecycle test circuits.
     */
    public function runLifecycleTests(): void
    {
        foreach ($this->testCircuits as $circuit => $enabledModules) {
            echo "\n\n================================================================================";
            echo "\nTesting circuit “".$circuit.'”';
            echo "\n";
            $this->addMissingTestFiles($enabledModules);
            $this->enableDisableModules($enabledModules);
            $this->writePhpunitXml($circuit, $enabledModules);
            echo "\n";
            passthru($this->phpunit, $returnCode);
            if (is_file('phpunit/phpunit_log.htm')) {
                rename('phpunit/phpunit_log.htm', 'phpunit/phpunit_'.str_replace(' ', '', $circuit).'_log.htm');
            }
            if ($returnCode) {
                echo "\n\nError – stop running tests";

                return;
            }
        }
    }

    /**
     * Collect missing lifecycle test files for enabled modules.
     *
     * @param  array<int, string>  $enabledModules
     */
    protected function addMissingTestFiles(array $enabledModules): void
    {
        foreach ($enabledModules as $module) {
            $modelPattern = "./modules/$module/Entities/*.php";
            $testPattern = "./modules/$module/Tests/Lifecycle/{{MODEL}}LifecycleTest.php";
            foreach (glob($modelPattern) as $modelPath) {
                if (str_ends_with($modelPath, "/$module.php")) {
                    // No lifecycle tests for e.g. modules/ProvBase/Entities/ProvBase.php
                    continue;
                }
                $model = str_replace('.php', '', basename($modelPath));
                $test = str_replace('{{MODEL}}', $model, $testPattern);
                if (! is_file($test)) {
                    $this->missingTestFiles[] = $test;
                }
            }
        }
    }

    /**
     * Render and write a temporary phpunit.xml for one circuit.
     *
     * @param  array<int, string>  $enabledModules
     */
    protected function writePhpunitXml(string $circuit, array $enabledModules): void
    {
        $xml = $this->phpunitXmlTemplate;
        $circuit = str_replace(' ', '', $circuit);

        $testPaths = [];
        foreach ($enabledModules as $module) {
            $dir = './modules/'.$module.'/Tests/Lifecycle';
            if (is_dir($dir)) {
                $testPaths[] = '<directory suffix="Test.php">./modules/'.$module.'/Tests/Lifecycle</directory>';
            }
        }
        foreach ($this->additionalTests as $test) {
            $testPaths[] = '<file>'.$test.'</file>';
        }

        $paths = implode("\n            ", $testPaths);
        $xml = str_replace('{{testsuite_directories}}', $paths, $xml);
        $xml = str_replace('{{phpunit_html_log_file}}', 'phpunit/out/lifecycle_'.$circuit.'_log.htm', $xml);
        file_put_contents('phpunit.xml', $xml);
    }

    /**
     * Restore module state and phpunit.xml after test execution.
     */
    public function __destruct()
    {
        echo "\n\n================================================================================";
        echo "\nRestoring initial module enable/disable status";
        echo "\n";
        $this->enableDisableModules($this->initiallyEnabledModules);
        echo "\nRestoring original phpunit.xml file";
        echo "\n";
        file_put_contents('phpunit.xml', $this->originalPhpunitXml);
        if ($this->missingTestFiles) {
            echo "\n\nMissing test files:";
            foreach (array_unique($this->missingTestFiles) as $file) {
                echo "\n    $file";
            }
        }
        echo "\n\nFinished";
        echo "\n\n";
    }

    /**
     * Disable all modules and enable only the requested set.
     *
     * @param  array<int, string>  $modulesToEnable
     */
    protected function enableDisableModules(array $modulesToEnable): void
    {
        echo "\nDisabling all modules";
        exec($this->artisan.' --no-ansi module:disable --all', $output, $returnCode);
        if ($returnCode) {
            echo "\nERROR ($returnCode):";
            var_dump($output);
        }

        $modules = implode(' ', $modulesToEnable);
        echo "\nEnabling module(s) $modules";
        exec($this->artisan.' --no-ansi module:enable '.$modules, $output, $returnCode);
        if ($returnCode) {
            echo "\nERROR ($returnCode):";
            var_dump($output);
        }

        echo "\nRunning artisan optimize command";
        exec($this->artisan.' --no-ansi optimize', $output, $returnCode);
        if ($returnCode) {
            echo "\nERROR ($returnCode):";
            var_dump($output);
        }
    }
}

$tr = new TestRunner;
$tr->prepare();
$tr->runLifecycleTests();
