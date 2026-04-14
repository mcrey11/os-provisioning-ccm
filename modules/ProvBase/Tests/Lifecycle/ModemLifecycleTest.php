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

namespace Modules\ProvBase\Tests\Lifecycle;

use PHPUnit\Framework\Attributes\Depends;
use Tests\BaseLifecycleTest;

class ModemLifecycleTest extends BaseLifecycleTest
{
    // modem can only be created from Contract.edit
    protected $create_from_model_context = '\Modules\ProvBase\Entities\Contract';

    protected array $empty_create_excluded_field_keys = [
        'contract_id',
        'mta_id',
        'modem_id',
        'netgw_id',
        'product_id',
        'realty_id',
        'apartment_id',
        'phonenumber_id',
    ];

    // fields to be used in update test (include description for a full address/contact payload; seeder now provides company, department, salutation)
    protected $update_fields = [
        'name',
        'description',
        'mac',
        'company',
        'department',
        'salutation',
        'firstname',
        'lastname',
        'street',
        'house_number',
        'zip',
        'city',
        'configfile_id',
    ];

    protected function overrideUpdateMutation(string $id, array &$postData, object $row, array $rowArr): ?array
    {
        if (array_key_exists('name', $rowArr)) {
            $postData['name'] = rtrim((string) ($rowArr['name'] ?? '')).' (upd '.$id.')';
        }

        return null;
    }

    #[Depends('test_index_view_visible')]
    public function test_update(): void
    {
        if (! $this->_test_shall_be_run(__FUNCTION__)) {
            return;
        }

        if (empty($this->update_fields)) {
            return;
        }

        foreach ($this->getCreatedEntityIds() as $id) {
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
            $postData['_token'] = \Session::token();
            $postData['_method'] = 'PUT';

            $row = \DB::table($this->database_table)->where('id', $id)->whereNull('deleted_at')->first();
            $this->assertNotNull($row, 'Record '.$id.' not found in '.$this->database_table.' for update test.');
            $rowArr = get_object_vars($row);
            if (array_key_exists('name', $rowArr)) {
                $postData['name'] = rtrim((string) ($rowArr['name'] ?? '')).' (upd '.$id.')';
            }
            $contractCanceled = (bool) \DB::table('contract')
                ->where('id', $rowArr['contract_id'] ?? 0)
                ->whereNotNull('contract_end')
                ->where('contract_end', '<=', date('Y-m-d'))
                ->exists();
            $postData['internet_access'] = $contractCanceled ? '0' : (($rowArr['internet_access'] ?? 0) ? '1' : '0');

            $this->actingAs($this->user)->get($this->_url('/admin/'.$this->model_name.'/'.$id));
            $response = $this->actingAs($this->user)->put($this->_url('/admin/'.$this->model_name.'/'.$id), $postData);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route($this->model_name.'.edit', $id));
        }
    }
}
