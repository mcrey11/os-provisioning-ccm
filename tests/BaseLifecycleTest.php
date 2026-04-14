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

namespace Tests;

use App\GuiLogWriter;
use App\User;
use Database\Seeders\BaseSeeder;
use Database\Seeders\NmsFaker;
use DateTime;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Base class to derive lifecycle tests for a model from
 *
 * This will reuse the static get_fake_data method in your model's seeder class (e.g. for create).
 * Assure that your seeder is up do date and running!
 *
 * Uses Laravel HTTP testing (get/post) instead of BrowserKit. Skips when the module is disabled.
 *
 * @author Patrick Reichel
 */
class BaseLifecycleTest extends TestCase
{
    // flag to show debug output from test runs
    protected $debug = false;

    // flag to print currently executed test method to stdout
    protected $echo_running_test = true;

    // flag to delete created entries from database after testing
    protected $clean_database_after_testing = true;

    // list of test method names to be run – can be used in development to work on single tests only
    protected $tests_to_be_run = [
        'test_create_twice_using_the_same_data',
        'test_create_with_fake_data',
        'test_delete_from_index_view',
        'test_empty_create',
        'test_index_view_visible',
        'test_datatable_data_returned',
        'test_update',
    ];

    // this blacklist defines tests that should not be run (overwrites $tests_to_be_run)
    protected $tests_to_be_excluded = [];

    // define how often the create, update and delete tests should be run
    protected $testrun_count = 2;

    // most models are created from another models context – if so set this in your derived class
    protected $create_from_model_context = null;

    // flag to indicate if creating without data should fail
    protected $creating_empty_should_fail = true;

    // flag to indicate if creating the same data entry twice should fail
    protected $creating_twice_should_fail = true;

    // fields to be updated with random data
    protected $update_fields = [];

    /** @var array|null When set, for update these fields are taken from the existing record (e.g. so IPs stay in net range) */
    protected $update_preserve_from_existing = null;

    /** @var bool When true, test_empty_create sends empty string for all non-_id keys from get_fake_data to avoid undefined key warnings. Set to false if that causes validation to pass unexpectedly. */
    protected $send_empty_keys_on_empty_create = true;

    /**
     * Form field keys omitted from the “all empty strings” POST in test_empty_create (relation IDs that must not be '').
     * Subclasses may remove entries so a key is still posted as '' (e.g. Item needs product_id for ItemController::prepare_input).
     *
     * @var list<string>
     */
    protected array $empty_create_excluded_field_keys = [
        'contract_id',
        'configfile_id',
        'mta_id',
        'modem_id',
        'netgw_id',
        'product_id',
        'realty_id',
        'apartment_id',
        'phonenumber_id',
    ];

    // array holding the edit form structure
    protected static $edit_field_structure = [];

    /** @var array<string, list<string|int>> Created ids per concrete lifecycle test class (subclasses must not share one list) */
    protected static array $created_entity_ids_by_class = [];

    protected $module_path = null;

    protected $model_name = null;

    protected $model_path = null;

    protected $controller_path = null;

    protected $database_table = null;

    protected $seeder = null;

    /** @var string */
    protected $class_name;

    /** @var User|null */
    protected $user;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->class_name = static::class;

        if ($this->class_name === self::class) {
            return;
        }

        $this->_set_helper_vars();
    }

    /** @var array<string, true> guilog truncated once per lifecycle test class */
    protected static array $guilogTruncatedForClass = [];

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->class_name === self::class) {
            return;
        }

        if (! isset(self::$guilogTruncatedForClass[$this->class_name])) {
            DB::table('guilog')->truncate();
            self::$guilogTruncatedForClass[$this->class_name] = true;
        }

        $this->resetGuiLogWriterSingletonForTests();

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; PHPUnit LifecycleTest)';
        App::setLocale('en');

        $moduleName = Str::afterLast($this->module_path, '\\');
        if (! Module::collections()->has($moduleName)) {
            $this->markTestSkipped("Module {$moduleName} is not enabled.");
        }
    }

    protected function _set_helper_vars(): void
    {
        $parts = explode('\\Tests\\', $this->class_name);
        if (is_null($this->module_path)) {
            $this->module_path = $parts[0];
        }
        $class = class_basename($this->class_name);

        if (is_null($this->model_name)) {
            $this->model_name = str_replace('LifecycleTest', '', $class);
        }

        if (is_null($this->controller_path)) {
            $this->controller_path = '\\'.$this->module_path.'\\Http\\Controllers\\'.$this->model_name.'Controller';
        }

        if (is_null($this->database_table)) {
            $this->database_table = strtolower($this->model_name);
        }

        if (is_null($this->seeder)) {
            $this->seeder = '\\'.$this->module_path.'\\Database\\Seeders\\'.$this->model_name.'TableSeeder';
        }

        if (is_null($this->model_path)) {
            $this->model_path = '\\'.$this->module_path.'\\Entities\\'.$this->model_name;
        }
    }

    public function createApplication(): Application
    {
        global $app;
        if (is_null($app)) {
            $app = parent::createApplication();
        }
        $this->_get_user();
        $this->_get_form_structure();

        return $app;
    }

    protected function _get_user(): void
    {
        $this->user = User::findOrFail(1);
    }

    protected function _get_form_structure($model = null): void
    {
        if (array_key_exists($this->class_name, self::$edit_field_structure)) {
            return;
        }

        $controller = new $this->controller_path;
        // In test context there is no request, so pass the correct model type so controllers never get null or wrong model
        $model = $model ?? new $this->model_path;
        $form_raw = $controller->view_form_fields($model);
        $structure = [];

        foreach ($form_raw as $form_raw_field) {
            if (@$form_raw_field['hidden']) {
                continue;
            }
            if (! $name = @$form_raw_field['name']) {
                continue;
            }
            if (! $type = @$form_raw_field['form_type']) {
                continue;
            }
            if (! isset($form_raw_field['value'])) {
                $value = null;
            } else {
                if (in_array($type, ['select', 'radio'])) {
                    $val = $form_raw_field['value'];
                    $value = $val instanceof Collection
                        ? $val->keys()->all()
                        : array_keys($val);
                } else {
                    $value = $form_raw_field['value'];
                }
            }
            self::$edit_field_structure[$this->class_name][$name]['type'] = $type;
            self::$edit_field_structure[$this->class_name][$name]['values'] = $value;
        }
    }

    /**
     * Return field names that have a unique rule in the model's validation rules.
     *
     * @param  array|null  $rules  Model rules(); if null, uses $this->model_path rules
     * @return array<int, string>
     */
    protected function _get_unique_fields_from_rules(?array $rules = null): array
    {
        if ($rules === null) {
            $model = new $this->model_path;
            $rules = $model->rules();
        }
        $unique = [];
        foreach ($rules as $field => $rule) {
            $ruleStr = is_array($rule) ? implode('|', $rule) : (string) $rule;
            if (strpos($ruleStr, 'unique') !== false) {
                $unique[] = $field;
            }
        }

        return $unique;
    }

    /**
     * When non-null, used instead of the default update-field mutation (e.g. phonenumber port uniqueness per mta).
     *
     * @param  array<string, mixed>  $postData
     * @param  array<string, mixed>  $rowArr
     * @return array{field: string, before: mixed}|null
     */
    protected function overrideUpdateMutation(string $id, array &$postData, object $row, array $rowArr): ?array
    {
        return null;
    }

    protected function pushCreatedEntityId(string|int $id): void
    {
        if (! isset(self::$created_entity_ids_by_class[$this->class_name])) {
            self::$created_entity_ids_by_class[$this->class_name] = [];
        }
        self::$created_entity_ids_by_class[$this->class_name][] = $id;
    }

    /** @return list<string|int> */
    protected function getCreatedEntityIds(): array
    {
        return self::$created_entity_ids_by_class[$this->class_name] ?? [];
    }

    /**
     * Clear GuiLogWriter's static dedupe buffer so lifecycle tests do not suppress real log rows
     * when multiple test classes run in one PHP process (production GuiLog is unaffected).
     */
    private function resetGuiLogWriterSingletonForTests(): void
    {
        try {
            $ref = new \ReflectionClass(GuiLogWriter::class);
            foreach (['changes_logged', 'instance'] as $name) {
                if (! $ref->hasProperty($name)) {
                    continue;
                }
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                $prop->setValue(null, $name === 'instance' ? null : []);
            }
        } catch (\Throwable) {
            // ignore: structure may differ
        }
    }

    protected function _get_fake_data($related_to, $model_id = -1): array
    {
        if (! class_exists('BaseSeeder', false)) {
            class_alias(BaseSeeder::class, 'BaseSeeder');
        }
        if (! class_exists('NmsFaker', false)) {
            class_alias(NmsFaker::class, 'NmsFaker');
        }

        $modelClass = $this->model_path;
        $model = new $modelClass;
        $rules = $model->rules();
        $unique_fields = $this->_get_unique_fields_from_rules($rules);

        if ($model_id > 0) {
            $existing = $modelClass::find($model_id);
            if ($existing) {
                $data = call_user_func($this->seeder.'::get_fake_data', 'test', $related_to);
                foreach ($unique_fields as $field) {
                    if (array_key_exists($field, $existing->getAttributes())) {
                        $data[$field] = $existing->getAttribute($field);
                    }
                }
                if (is_array($this->update_preserve_from_existing ?? null)) {
                    foreach ($this->update_preserve_from_existing as $field) {
                        if (array_key_exists($field, $existing->getAttributes())) {
                            $data[$field] = $existing->getAttribute($field);
                        }
                    }
                }

                return $data;
            }
        }

        $tries = 0;
        while (true) {
            $tries++;
            if ($tries > 100) {
                throw new \Exception('Unable to create unique data.');
            }
            $data_is_unique = true;
            $data = call_user_func($this->seeder.'::get_fake_data', 'test', $related_to);

            foreach ($unique_fields as $unique_field) {
                if (is_null($data[$unique_field] ?? null)) {
                    continue;
                }
                $exists = DB::table($this->database_table)
                    ->where($unique_field, '=', $data[$unique_field])
                    ->where('id', '!=', $model_id)
                    ->exists();
                if ($exists) {
                    $data_is_unique = false;
                    break;
                }
            }
            if ($data_is_unique) {
                return $data;
            }
        }
    }

    /**
     * Build POST data array from form structure and fake data (for HTTP tests).
     */
    protected function _build_post_data(array $data, string $method): array
    {
        $post = [];
        if (! isset(self::$edit_field_structure[$this->class_name])) {
            return $post;
        }

        foreach (self::$edit_field_structure[$this->class_name] as $field_name => $structure) {
            if ($method === 'update' && ! in_array($field_name, $this->update_fields)) {
                continue;
            }
            if (! array_key_exists($field_name, $data)) {
                continue;
            }
            $faked_data = $data[$field_name];

            if ($faked_data instanceof DateTime) {
                $faked_data = $faked_data->format('Y-m-d');
            }

            switch ($structure['type']) {
                case 'select':
                case 'radio':
                    $values = $structure['values'];
                    if (is_array($values) && ! empty($values) && ! in_array($faked_data, $values)) {
                        $faked_data = $values[array_rand($values)];
                    }
                    $post[$field_name] = $faked_data;
                    break;
                case 'checkbox':
                    $post[$field_name] = $faked_data ? '1' : '0';
                    break;
                default:
                    $post[$field_name] = $faked_data;
                    break;
            }
        }

        if ($method === 'create' || $method === 'update') {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $post)) {
                    continue;
                }
                if ($value instanceof DateTime) {
                    $post[$key] = $value->format('Y-m-d');
                } elseif (is_scalar($value) || $value === null) {
                    $post[$key] = $value;
                }
            }
            // Prefer seeder/fake data for required fields (e.g. select2 with empty structure values)
            foreach ($data as $key => $value) {
                if (is_scalar($value) || $value === null) {
                    $post[$key] = $value instanceof DateTime ? $value->format('Y-m-d') : $value;
                }
            }
        }

        if (Module::collections()->has('BillingBase') &&
            (! isset($post['costcenter_id']) || $post['costcenter_id'] === '' || $post['costcenter_id'] == 0)) {
            $costcenterId = DB::table('costcenter')->value('id');
            if ($costcenterId !== null) {
                $post['costcenter_id'] = $costcenterId;
            }
        }

        return $post;
    }

    protected function _test_shall_be_run(string $test_method): bool
    {
        if ($this->echo_running_test) {
            echo "\n".$this->class_name.'->'.$test_method.'()';
        }
        if (! in_array($test_method, $this->tests_to_be_run)) {
            echo "\n	WARNING: Skipping ".$this->class_name.'->'.$test_method.'() (not found in tests_to_be_run)';

            return false;
        }
        if (in_array($test_method, $this->tests_to_be_excluded)) {
            echo "\n	WARNING: Skipping ".$this->class_name.'->'.$test_method.'() (found in tests_to_be_excluded)';

            return false;
        }

        return true;
    }

    /**
     * Extract created entity ID from store redirect response.
     */
    protected function _getIdFromStoreRedirect($response): ?string
    {
        $location = $response->headers->get('Location');
        if (! $location) {
            return null;
        }
        $path = rtrim(parse_url($location, PHP_URL_PATH), '/');
        $segments = explode('/', $path);
        $id = end($segments);

        return is_numeric($id) && $id != '0' ? $id : null;
    }

    /**
     * Build request URL that works when APP_URL has a path (avoids 404 from wrong path).
     */
    protected function _url(string $path): string
    {
        $path = '/'.ltrim($path, '/');

        return 'http://localhost'.$path;
    }

    protected function _get_create_context(): array
    {
        if (is_null($this->create_from_model_context)) {
            return ['instance' => null, 'params' => []];
        }
        $model = $this->create_from_model_context;
        $all = $model::all();
        if ($all->isEmpty()) {
            $name = class_basename($model);
            $this->markTestSkipped("No {$name} record in test database. Seed the test DB (e.g. php artisan db:seed and module seeders).");
        }
        $instance = $all->random();

        return [
            'instance' => $instance,
            'params' => [$instance->getTable().'_id' => $instance->id],
        ];
    }

    public function test_empty_create(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        $msg_expected = $this->creating_empty_should_fail
            ? 'please correct the following errors'
            : 'Created!';

        $context = $this->_get_create_context();
        $emptyData = [];
        if ($this->send_empty_keys_on_empty_create) {
            try {
                $data = $this->_get_fake_data($context['instance']);
                $emptyData = array_fill_keys(array_keys($data), '');
                // Exclude relation FKs that must not be '' (Model::find('') / overwrite of context params); keep e.g. costcenter_id, qos_id
                $excludeIds = $this->empty_create_excluded_field_keys;
                $emptyData = array_diff_key($emptyData, array_flip(array_intersect(array_keys($emptyData), $excludeIds)));
            } catch (\Throwable $e) {
                // keep $emptyData = [] so POST stays minimal when get_fake_data fails
            }
        }
        $createPath = '/admin/'.$this->model_name.'/create'.(empty($context['params']) ? '' : '?'.http_build_query($context['params']));
        $this->actingAs($this->user)->get($this->_url($createPath));

        $postData = array_merge($context['params'], $emptyData, ['_save' => '1', '_token' => Session::token()]);
        // Use CSRF token from session after GET (session may have been regenerated by the create page)
        $postData['_token'] = Session::token();

        if ($this->creating_empty_should_fail) {
            $response = $this->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name), $postData);
            $response->assertRedirect();
            $response->assertSessionHasErrors();
        } else {
            $response = $this->followingRedirects()->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name), $postData);
            $response->assertSee($msg_expected, false);
        }

        if (! $this->creating_empty_should_fail) {
            $id = $this->_getIdFromStoreRedirect($response);
            if ($id !== null) {
                $this->pushCreatedEntityId($id);
                $this->assertDatabaseHas('guilog', [
                    'method' => 'created',
                    'model' => $this->model_name,
                    'model_id' => $id,
                ]);
            }
        }
    }

    #[Depends('test_create_twice_using_the_same_data')]
    public function test_index_view_visible(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        $model = new $this->model_path;
        $index_header_raw = $model->view_index_label()['index_header'];
        $langPath = lang_path('en/dt_header.php');
        $index_header_translations_en = file_exists($langPath) ? include $langPath : [];
        $index_header = [];
        foreach ($index_header_raw as $raw) {
            $index_header[] = $index_header_translations_en[$raw] ?? $raw;
        }

        $response = $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name));
        $response->assertOk();
        $response->assertSee('NMS Prime', false);
        $response->assertSee('Next Generation NMS', false);
        $response->assertSee($this->model_name, false);
        $response->assertSee('/admin/'.$this->model_name.'/0', false);

        $content = $response->getContent();
        $visibleHeaders = array_filter($index_header, fn ($header) => $header && str_contains($content, $header));
        $this->assertGreaterThan(0, count($visibleHeaders), 'At least one index table header (e.g. '.($index_header[0] ?? '').') should be visible.');
    }

    #[Depends('test_empty_create')]
    public function test_create_with_fake_data(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        for ($i = 0; $i < $this->testrun_count; $i++) {
            $context = $this->_get_create_context();
            $data = $this->_get_fake_data($context['instance']);
            $postData = $this->_build_post_data($data, 'create');
            $postData = array_merge($context['params'], $postData, ['_save' => '1', '_token' => Session::token()]);

            $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name.'/create').(empty($context['params']) ? '' : '?'.http_build_query($context['params'])));
            // Use CSRF token from session after GET (session may have been regenerated by the create page)
            $postData['_token'] = Session::token();
            $response = $this->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name), $postData);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect();
            $response->assertSessionHas('message', 'Created!');

            $id = $this->_getIdFromStoreRedirect($response);
            if ($id !== null) {
                $this->pushCreatedEntityId($id);
                $this->assertDatabaseHas('guilog', [
                    'method' => 'created',
                    'model' => $this->model_name,
                    'model_id' => $id,
                ]);
            }
        }
    }

    #[Depends('test_create_with_fake_data')]
    public function test_create_twice_using_the_same_data(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        $context = $this->_get_create_context();
        $data = $this->_get_fake_data($context['instance']);
        $postData = $this->_build_post_data($data, 'create');
        $postData = array_merge($context['params'], $postData, ['_save' => '1', '_token' => Session::token()]);
        // So both POSTs use the same unique field values (avoid select/radio replacing with random option)
        if ($this->creating_twice_should_fail) {
            foreach ($this->_get_unique_fields_from_rules() as $field) {
                if (array_key_exists($field, $data)) {
                    $postData[$field] = $data[$field] instanceof \DateTimeInterface
                        ? $data[$field]->format('Y-m-d')
                        : $data[$field];
                }
            }
        }

        $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name.'/create').(empty($context['params']) ? '' : '?'.http_build_query($context['params'])));
        // Use CSRF token from session after GET (session may have been regenerated by the create page)
        $postData['_token'] = Session::token();
        $first = $this->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name), $postData);
        $first->assertSessionHasNoErrors();
        $first->assertRedirect();
        $id = $this->_getIdFromStoreRedirect($first);
        if ($id !== null) {
            $this->pushCreatedEntityId($id);
        }

        $createPath = '/admin/'.$this->model_name.'/create'.(empty($context['params']) ? '' : '?'.http_build_query($context['params']));
        $this->actingAs($this->user)->get($this->_url($createPath));
        // Use current CSRF token so the second POST is accepted (session may have been regenerated by the GET)
        $postData['_token'] = Session::token();
        if ($this->creating_twice_should_fail) {
            // Ensure unique fields are in the request so the duplicate triggers validation
            foreach ($this->_get_unique_fields_from_rules() as $field) {
                if (array_key_exists($field, $data)) {
                    $postData[$field] = $data[$field] instanceof \DateTimeInterface
                        ? $data[$field]->format('Y-m-d')
                        : $data[$field];
                }
            }
        }
        // When expecting validation failure, do not follow redirects so session errors are on the redirect response
        $second = $this->creating_twice_should_fail
            ? $this->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name), $postData)
            : $this->followingRedirects()->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name), $postData);

        if ($this->creating_twice_should_fail) {
            // Ensure no duplicate was created (unique validation must reject the second create)
            $uniqueFields = $this->_get_unique_fields_from_rules();
            $value = null;
            foreach ($uniqueFields as $field) {
                if (! array_key_exists($field, $data)) {
                    continue;
                }
                // Use stored value from first created record if available (handles MAC normalization etc.)
                if ($id !== null) {
                    $stored = DB::table($this->database_table)->where('id', $id)->whereNull('deleted_at')->value($field);
                    if ($stored !== null && $stored !== '') {
                        $value = $stored;
                        break;
                    }
                }
                $v = $data[$field] instanceof \DateTimeInterface
                    ? $data[$field]->format('Y-m-d')
                    : $data[$field];
                if ($v !== null && $v !== '') {
                    $value = $v;
                    break;
                }
            }
            if ($value !== null && $value !== '') {
                $count = DB::table($this->database_table)
                    ->where($field, $value)
                    ->whereNull('deleted_at')
                    ->count();
                $this->assertSame(1, $count, "Expected exactly one record with {$field}='".(is_scalar($value) ? $value : json_encode($value))."' after duplicate create attempt, found {$count}. Unique validation may not be applied.");
            }
            $second->assertSessionHasErrors();
        } else {
            $second->assertSee('Created!', false);
        }
        $id2 = $this->_getIdFromStoreRedirect($second);
        if ($id2 !== null) {
            $this->pushCreatedEntityId($id2);
        }
    }

    #[Depends('test_index_view_visible')]
    public function test_update(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        if (empty($this->update_fields)) {
            echo "	WARNING: No entries in update_fields – cannot test!\n";

            return;
        }

        foreach ($this->getCreatedEntityIds() as $id) {
            if ($this->debug) {
                echo "\nUpdating $this->model_name $id";
            }

            $context = $this->_get_create_context();
            $data = $this->_get_fake_data($context['instance'], $id);
            $postData = $this->_build_post_data($data, 'update');
            foreach ($this->update_fields as $field) {
                if (array_key_exists($field, $postData) || ! array_key_exists($field, $data)) {
                    continue;
                }
                $val = $data[$field];
                if ($val instanceof \DateTimeInterface) {
                    $postData[$field] = $val->format('Y-m-d');
                } elseif (is_scalar($val) || $val === null) {
                    $postData[$field] = $val;
                }
            }
            $postData['_save'] = '1';
            $postData['_token'] = Session::token();
            $postData['_method'] = 'PUT';

            // Derive new values from the persisted row so save() is actually dirty (Laravel skips performUpdate when ! isDirty(), so no guilog "updated")
            $suffix = ' (upd '.$id.')';
            $formatSensitive = ['mac'];
            $row = DB::table($this->database_table)->where('id', $id)->whereNull('deleted_at')->first();
            $this->assertNotNull($row, 'Record '.$id.' not found in '.$this->database_table.' for update test.');

            $rowArr = get_object_vars($row);

            $override = $this->overrideUpdateMutation((string) $id, $postData, $row, $rowArr);
            if ($override !== null) {
                $mutatedField = $override['field'];
                $valueBeforeUpdate = $override['before'];
                $didMutate = true;
            } else {
                $didMutate = false;
                $mutatedField = null;
                $valueBeforeUpdate = null;

                foreach ($this->update_fields as $field) {
                    if (! array_key_exists($field, $rowArr)) {
                        continue;
                    }
                    if (str_ends_with((string) $field, '_ip') || in_array($field, $formatSensitive, true)) {
                        continue;
                    }
                    $dbVal = $rowArr[$field];
                    $mutatedField = $field;
                    $valueBeforeUpdate = $dbVal;
                    if (is_bool($dbVal)) {
                        $postData[$field] = $dbVal ? '0' : '1';
                        $didMutate = true;
                        break;
                    }
                    if ($dbVal === null || $dbVal === '') {
                        $postData[$field] = 'chg'.$suffix;
                        $didMutate = true;
                        break;
                    }
                    if (is_numeric($dbVal)) {
                        $postData[$field] = (string) ((int) $dbVal + max(1, (int) $id));
                        $didMutate = true;
                        break;
                    }
                    if (is_string($dbVal)) {
                        $postData[$field] = rtrim($dbVal).$suffix;
                        $didMutate = true;
                        break;
                    }
                }
                $this->assertTrue(
                    $didMutate,
                    'Could not derive a new value for '.$this->model_name.' update from DB row; adjust update_fields.',
                );
            }

            $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name.'/'.$id));
            $response = $this->actingAs($this->user)->put($this->_url('/admin/'.$this->model_name.'/'.$id), $postData);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route($this->model_name.'.edit', $id));
            $valueAfterUpdate = DB::table($this->database_table)->where('id', $id)->value($mutatedField);
            $this->assertNotSame(
                $valueBeforeUpdate === null ? null : (string) $valueBeforeUpdate,
                $valueAfterUpdate === null ? null : (string) $valueAfterUpdate,
                'Expected '.$this->model_name.'.'.$mutatedField.' to change after HTTP update (request must persist a real change before guilog can record updated).',
            );
            $this->assertDatabaseHas('guilog', [
                'method' => 'updated',
                'model' => $this->model_name,
                'model_id' => $id,
            ]);
        }
    }

    #[Depends('test_update')]
    public function test_datatable_data_returned(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        $response = $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name.'/datatables'));
        $response->assertOk();

        $model_count = DB::table($this->database_table)->whereNull('deleted_at')->count();
        $response->assertJson(['recordsTotal' => $model_count]);

        foreach ($this->getCreatedEntityIds() as $id) {
            $route = route($this->model_name.'.edit', $id);
            $search = last(explode(':', str_replace('/', '\/', $route)));
            $response->assertSee($search, false);
        }
    }

    #[Depends('test_datatable_data_returned')]
    public function test_delete_from_index_view(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        $ids_to_delete = [];
        $bucket = $this->getCreatedEntityIds();
        $ids_to_delete[] = [array_pop($bucket)];
        $ids_to_delete[] = $bucket;
        self::$created_entity_ids_by_class[$this->class_name] = [];

        foreach ($ids_to_delete as $ids) {
            if (empty($ids)) {
                continue;
            }

            $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name));

            $post_ids = [];
            foreach ($ids as $id) {
                $post_ids[$id] = '1';
                if ($this->debug) {
                    echo "\nDeleting $this->model_name $id";
                }
            }
            $form_data = [
                '_delete' => '',
                '_token' => Session::token(),
                '_method' => 'DELETE',
                'ids' => $post_ids,
            ];

            $response = $this->followingRedirects()->actingAs($this->user)->post($this->_url('/admin/'.$this->model_name.'/0'), $form_data);

            foreach ($ids as $id) {
                $response->assertSee('Deleted', false);
                $response->assertSee($this->model_name, false);
                $this->assertDatabaseMissing($this->database_table, [
                    'id' => $id,
                    'deleted_at' => null,
                ]);
                $this->assertDatabaseHas('guilog', [
                    'method' => 'deleted',
                    'model' => $this->model_name,
                ]);

                if ($this->clean_database_after_testing) {
                    DB::table($this->database_table)->where('id', '=', $id)->delete();
                }
            }
        }
    }

    protected function tearDown(): void
    {
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (! $prop->isStatic() && strpos($prop->getDeclaringClass()->getName(), 'PHPUnit') !== 0) {
                try {
                    $prop->setValue($this, null);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        parent::tearDown();
    }
}
