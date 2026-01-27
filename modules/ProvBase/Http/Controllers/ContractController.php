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

namespace Modules\ProvBase\Http\Controllers;

use App\GlobalConfig;
use Bouncer;
use Module;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Qos;

class ContractController extends \BaseController
{
    // get functions for some address select options
    use \App\AddressFunctionsTrait;

    protected $relation_create_button = 'Add';

    /**
     * SmartOnt OTO uses a very different form set than the rest.
     * This is defined here for flavor GESA.
     *
     * @param  $model  A contract
     * @return array
     *
     * @author Patrick Reichel
     */
    public function viewFormFieldsGesaOto($model)
    {
        $fields = [
            [
                'form_type' => 'text',
                'name' => 'sep_id',
                'description' => 'SEP ID',
                'create' => ['Modem'],
                'space' => '1',
            ],

            [
                'form_type' => 'text',
                'name' => 'company',
                'description' => 'Company',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'department',
                'description' => 'Department',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'firstname',
                'description' => 'Firstname',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'lastname',
                'description' => 'Lastname',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'text',
                'name' => 'street',
                'description' => 'Street',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'house_number',
                'description' => 'House number',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'zip',
                'description' => 'Postcode',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'city',
                'description' => 'City',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'district',
                'description' => 'District',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'text',
                'name' => 'national_building_id',
                'description' => $this->translateNationalBuildingId($model->country_code),
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'country_code',
                'description' => 'Country code',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'text',
                'name' => 'phone',
                'description' => 'Phone',
            ],
            [
                'form_type' => 'text',
                'name' => 'email',
                'description' => 'E-Mail Address',
                'space' => '1',
            ],

            [
                'form_type' => 'text',
                'name' => 'oto_id',
                'description' => 'OTO ID',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'oto_port',
                'description' => 'OTO Port',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'oto_socket_usage',
                'description' => 'OTO Socket Usage',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'oto_status',
                'description' => 'OTO Status',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'flat_id',
                'description' => 'Flat ID',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'alex_status',
                'description' => 'ALEX Status',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'omdf_id',
                'description' => 'OMDF ID',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'boc_label',
                'description' => 'BOC Label',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'bof_label',
                'description' => 'BOF Label',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'select',
                'name' => 'type',
                'description' => 'Type',
                'create' => ['Modem'],
                'value' => $model->getTypesForForm(),
                'space' => '1',
            ],
            [
                'form_type' => 'textarea',
                'name' => 'description',
                'description' => 'Description',
            ],
            [
                'form_type' => 'date',
                'name' => 'contract_start',
                'description' => 'Contract Start',
                'hidden' => 1,
            ],
        ];

        return $fields;
    }

    /**
     * SmartOnt OTO uses a very different form set than the rest.
     * This is defined here for flavor LFO.
     *
     * @param  $model  A contract
     * @return array
     *
     * @author Patrick Reichel
     */
    public function viewFormFieldsLfoOto($model)
    {
        $fields = [
            [
                'form_type' => 'text',
                'name' => 'company',
                'description' => 'Company',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'department',
                'description' => 'Department',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'firstname',
                'description' => 'Firstname',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'lastname',
                'description' => 'Lastname',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'text',
                'name' => 'street',
                'description' => 'Street',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'house_number',
                'description' => 'House number',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'zip',
                'description' => 'Postcode',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'city',
                'description' => 'City',
                'create' => ['Modem'],
            ],
            [
                'form_type' => 'text',
                'name' => 'district',
                'description' => 'District',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'text',
                'name' => 'country_code',
                'description' => 'Country code',
                'create' => ['Modem'],
                'space' => '1',
            ],
            [
                'form_type' => 'text',
                'name' => 'phone',
                'description' => 'Phone',
            ],
            [
                'form_type' => 'text',
                'name' => 'email',
                'description' => 'E-Mail Address',
                'space' => '1',
            ],
            [
                'form_type' => 'textarea',
                'name' => 'description',
                'description' => 'Description',
            ],
            [
                'form_type' => 'date',
                'name' => 'contract_start',
                'description' => 'Contract Start',
                'hidden' => 1,
            ],
        ];

        return $fields;
    }

    /**
     * defines the formular fields for the edit and create view
     *
     * @return array
     */
    public function view_form_fields($model = null)
    {
        if (! $model) {
            $model = new Contract;
        }

        // Handle POST data when creating Contract from Customer (via relation.blade.php)
        // Most POST data is automatically handled by BaseViewController::prepare_form_fields via $_POST
        // We only need to handle salutation mapping (English → German) and customer_id for select2
        if (! $model->exists && Module::collections()->has('Crm')) {
            // Map salutation_for_contract and academic_degree_for_contract from POST to salutation and academic_degree
            // This handles the mapping from Customer (English) to Contract (German) salutations
            if (request()->has('salutation_for_contract')) {
                request()->merge(['salutation' => request('salutation_for_contract')]);
            }
            if (request()->has('academic_degree_for_contract')) {
                request()->merge(['academic_degree' => request('academic_degree_for_contract')]);
            }

            // Set customer_id on model for select2 pre-selection (from POST or GET)
            $customerId = request('customer_id') ?: request('_id');
            if ($customerId) {
                $model->customer_id = $customerId;
                if ($customer = \Modules\Crm\Entities\Customer::find($customerId)) {
                    $model->setRelation('customer', $customer);
                }
            }
        }

        if (Module::collections()->has('SmartOnt')) {
            if (config('smartont.flavor.active') == 'GESA') {
                return $this->viewFormFieldsGesaOto($model);
            }
            if (config('smartont.flavor.active') == 'LFO') {
                return $this->viewFormFieldsLfoOto($model);
            }

            throw new \Exception(__METHOD__.': Cannot create view form fields for flavor '.config('smartont.flavor.active'));
        }

        // Compose related phonenumbers as readonly info field
        $model->related_phonenrs = $model->relatedPns();

        $a = $c2 = $d = [];

        $selectPropertyMgmt = [];
        if (Module::collections()->has('PropertyManagement')) {
            $selectPropertyMgmt = ['select' => 'noApartment'];
        }

        // label has to be the same like column in sql table
        $a = [];

        // Add customer select field at the top if CRM module is enabled
        if (Module::collections()->has('Crm')) {
            $initialValue = $this->setupSelect2Field($model, 'Customer', 'customer_id');
            $customerOptions = ['' => trans('view.No Customer')];
            foreach ($initialValue as $key => $value) {
                if ($key !== null && $key !== '') {
                    $customerOptions[$key] = $value;
                }
            }

            $a[] = [
                'form_type' => 'select',
                'name' => 'customer_id',
                'description' => trans('view.Menu_Customer'),
                'value' => $customerOptions,
                'options' => [
                    'class' => 'select2-ajax',
                    'data-allow-clear' => 'true',
                    'ajax-route' => route('Customer.select2', ['relation' => 'customers']),
                ],
                'space' => 1,
            ];
        }

        // basic data
        $a[] = ['form_type' => 'text', 'name' => 'number', 'description' => $model->get_column_description('number'), 'help' => trans('helper.contract.number')];
        $a[] = [
            'form_type' => 'collapse', 'name' => 'collapse', 'description' => trans('view.collapseNumbers'), 'space' => 1, 'form_fields' => [
                ['form_type' => 'text', 'name' => 'number2', 'description' => $model->get_column_description('number2')],
                ['form_type' => 'text', 'name' => 'number3', 'description' => $model->get_column_description('number3'), 'help' => 'If left empty contract number will be used as customer number, too.'],
                ['form_type' => 'text', 'name' => 'number4', 'description' => $model->get_column_description('number4')],
            ],
        ];
        $a[] = ['form_type' => 'text', 'name' => 'debtor', 'description' => 'Debtor'];
        // 'create' makes this field a hidden input field in Modem create form - so the company, etc. will be already set from contract when the user wants to create a new modem
        $a[] = ['form_type' => 'text', 'name' => 'company', 'description' => 'Company', 'create' => ['Modem'], 'init_value' => $model->company ?? null];
        $a[] = ['form_type' => 'text', 'name' => 'department', 'description' => 'Department', 'create' => ['Modem'], 'init_value' => $model->department ?? null];
        $a[] = ['form_type' => 'select', 'name' => 'salutation', 'description' => 'Salutation', 'value' => $model->getSalutationOptions(), 'create' => ['Modem'], 'help' => trans('helper.contract.salutation'), 'init_value' => $model->salutation ?? null];
        $a[] = ['form_type' => 'select', 'name' => 'academic_degree', 'description' => 'Academic Degree', 'value' => $model->getAcademicDegreeOptions(), 'init_value' => $model->academic_degree ?? null];
        $a[] = ['form_type' => 'text', 'name' => 'firstname', 'description' => 'Firstname', 'create' => ['Modem'], 'init_value' => $model->firstname ?? null];
        $a[] = ['form_type' => 'text', 'name' => 'lastname', 'description' => 'Lastname', 'create' => ['Modem'], 'space' => '1', 'init_value' => $model->lastname ?? null];

        $a[] = array_merge(['form_type' => 'text', 'name' => 'street', 'description' => 'Street', 'create' => ['Modem'], 'autocomplete' => [], 'init_value' => $model->street ?? null], $selectPropertyMgmt);
        $a[] = array_merge(['form_type' => 'text', 'name' => 'house_number', 'description' => 'House Number', 'create' => ['Modem'], 'init_value' => $model->house_number ?? null], $selectPropertyMgmt);
        $a[] = array_merge(['form_type' => 'text', 'name' => 'zip', 'description' => 'Postcode', 'create' => ['Modem'], 'autocomplete' => [], 'init_value' => $model->zip ?? null], $selectPropertyMgmt);
        $a[] = array_merge(['form_type' => 'text', 'name' => 'city', 'description' => 'City', 'create' => ['Modem'], 'autocomplete' => [], 'init_value' => $model->city ?? null], $selectPropertyMgmt);
        $a[] = array_merge(['form_type' => 'text', 'name' => 'district', 'description' => 'District', 'create' => ['Modem'], 'autocomplete' => [], 'init_value' => $model->district ?? null], $selectPropertyMgmt);
        $a[] = array_merge(['form_type' => 'text', 'name' => 'country_code', 'description' => 'Country code', 'create' => ['Modem'], 'autocomplete' => [], 'init_value' => $model->country_code ?? null], $selectPropertyMgmt);

        if (! Module::collections()->has('Ccc')) {
            // Find the number field and unset its help
            foreach ($a as $key => $field) {
                if (isset($field['name']) && $field['name'] === 'number') {
                    unset($a[$key]['help']);
                    break;
                }
            }
        }

        if (Module::collections()->has('PropertyManagement')) {
            $a[] = ['form_type' => 'select', 'name' => 'apartment_id', 'description' => 'Apartment', 'hidden' => 0,
                'value' => $this->setupSelect2Field($model, 'Apartment'),
                'options' => ['class' => 'select2-ajax', 'data-allow-clear' => 'true',
                    'ajax-route' => route('Apartment.select2', ['relation' => 'apartments']), ],
            ];
        } else {
            $a[] = ['form_type' => 'text', 'name' => 'apartment_nr', 'description' => 'Apartment number'];
        }

        $a[] = ['form_type' => 'text', 'name' => 'additional', 'description' => 'Additional info', 'create' => ['Contract'], 'autocomplete' => [], 'space' => 1, 'init_value' => $model->additional ?? null];

        $b1[] = ['form_type' => 'text', 'name' => 'phone', 'description' => 'Phone', 'init_value' => $model->phone ?? null];

        if (Module::collections()->has('ProvVoip')) {
            $b1[] = ['form_type' => 'text', 'name' => 'related_phonenrs', 'description' => trans('provvoip::view.contractRelatedPns'), 'options' => ['readonly']];
        }

        foreach ($model::GROUNDS_FOR_DISMISSAL as $key => $reason) {
            $reasons[$reason] = trans("view.contract.groundsForDismissal.$reason");
        }

        $b2 = [
            ['form_type' => 'text', 'name' => 'fax', 'description' => 'Fax'],
            ['form_type' => 'text', 'name' => 'email', 'description' => 'E-Mail Address', 'init_value' => $model->email ?? null],
        ];

        if (Module::collections()->has('Ccc') && Module::collections()->has('BillingBase') && $model->cccUser) {
            $model->newsletter = $model->cccUser->newsletter;
            $b2[] = ['form_type' => 'checkbox', 'name' => 'newsletter', 'description' => trans('messages.receiveNewsletters')];
        }

        $b3 = [
            ['form_type' => 'date', 'name' => 'birthday', 'description' => 'Birthday', 'create' => ['Modem'], 'space' => '1', 'init_value' => $model->birthday ?? null],
            ['form_type' => 'date', 'name' => 'contract_start', 'description' => 'Contract Start'],
            ['form_type' => 'date', 'name' => 'contract_end', 'description' => 'Valid to'],
        ];

        if (Module::collections()->has('BillingBase')) {
            $b3[] = ['form_type' => 'date', 'name' => 'last_amendment', 'description' => 'Last contract amendment', 'space' => 1, 'help' => trans('helper.contract.lastAmendment')];

            $days = range(0, 28);
            $days[0] = null;

            $c = [
                // ['form_type' => 'checkbox', 'name' => 'has_telephony', 'description' => 'Has telephony', 'help' => trans('helper.has_telephony'), 'hidden' => 1],
                ['form_type' => 'select', 'name' => 'costcenter_id', 'description' => 'Cost Center', 'value' => selectList('costcenter', 'name', true)],
                ['form_type' => 'checkbox', 'name' => 'create_invoice', 'description' => 'Create Invoice', 'checked' => 1],
            ];

            if (config('app.locale') == 'de') {
                $c[] = ['form_type' => 'checkbox', 'name' => 'paragraph_13', 'description' => 'Tax exempted due to §13'];
            }

            $c[] = ['form_type' => 'checkbox', 'name' => 'sales_tax_org', 'description' => trans('billingbase::view.contract.salesTaxOrg'), 'help' => trans('helper.contract.salesTaxOrg')];
            $c[] = ['form_type' => 'checkbox', 'name' => 'block_funds_payout', 'description' => trans('billingbase::view.contract.blockFundsPayout')];
            $c[] = ['form_type' => 'select', 'name' => 'value_date', 'description' => 'Date of value', 'value' => $days, 'help' => trans('helper.contract.valueDate')];
            // NOTE: qos is required as hidden field to automatically create modem with correct contract qos class
            $c[] = ['form_type' => 'text', 'name' => 'qos_id', 'description' => 'QoS', 'create' => ['Modem'], 'hidden' => 1];
            $c[] = ['form_type' => 'select', 'name' => 'salesman_id', 'description' => 'Salesman', 'value' => selectList('salesman', ['firstname', 'lastname'], true, ' - ')];
        } else {
            $b3[2][] = ['space' => 1];

            $qoss = Qos::all();

            $c = [
                ['form_type' => 'checkbox', 'name' => 'internet_access', 'description' => 'Internet Access', 'value' => '1', 'create' => ['Modem'], 'checked' => 1],
                ['form_type' => 'checkbox', 'name' => 'has_telephony', 'description' => 'Has telephony', 'help' => trans('helper.has_telephony')],
                ['form_type' => 'select', 'name' => 'qos_id', 'description' => 'QoS', 'create' => ['Modem'], 'value' => $model->html_list($qoss, 'name')],
                ['form_type' => 'select', 'name' => 'next_qos_id', 'description' => 'QoS next month', 'value' => $model->html_list($qoss, 'name', true)],
            ];

            if (\Module::collections()->has('ProvVoipEnvia')) {
                $purchase_tariffs = \Modules\ProvVoip\Entities\PhoneTariff::get_purchase_tariffs();
                $sales_tariffs = \Modules\ProvVoip\Entities\PhoneTariff::get_sale_tariffs();

                $c2 = [
                    ['form_type' => 'select', 'name' => 'purchase_tariff', 'description' => 'Purchase tariff', 'value' => $purchase_tariffs],
                    ['form_type' => 'select', 'name' => 'voip_id', 'description' => 'Sale tariff', 'value' => $sales_tariffs],
                    ['form_type' => 'text', 'name' => 'next_purchase_tariff', 'description' => 'Purchase tariff next month', 'value' => $purchase_tariffs],
                    ['form_type' => 'text', 'name' => 'next_voip_id', 'description' => 'Sales tariff next month', 'value' => $sales_tariffs],
                ];

                $c = array_merge($c, $c2);
            }
        }

        if (Module::collections()->has('PropertyManagement')) {
            $c[] = ['form_type' => 'checkbox', 'name' => 'group_contract', 'description' => 'Group Contract', 'space' => 1];
            $c[] = ['form_type' => 'select', 'name' => 'contact_point_id', 'description' => 'Contact',
                'value' => $this->setupSelect2Field($model, 'Contact'),
                'options' => ['class' => 'select2-ajax', 'data-allow-clear' => 'true', 'ajax-route' => route('Contact.select2', ['relation' => 'contacts'])],
            ];
        } else {
            $c[array_key_last($c)]['space'] = 1;
        }

        if (\Module::collections()->has('BillingBase') && cache('billingBase')->show_ags) {
            $c[] = ['form_type' => 'select', 'name' => 'contact', 'description' => 'Contact Persons', 'value' => \Modules\BillingBase\Entities\BillingBase::contactPersons()];
        }

        $d = [
            ['form_type' => 'select', 'name' => 'ground_for_dismissal', 'description' => trans('view.contract.groundForDismissal'),
                'value' => array_merge([null => null], $reasons), ],
            ['form_type' => 'textarea', 'name' => 'description', 'description' => 'Description'],
        ];

        if (Module::collections()->has('Customer1000')) {
            $d[] = ['form_type' => 'select', 'name' => 'correspondence_recipient_id', 'description' => trans('view.Header_CorrespondenceRecipient'),
                'value' => $this->setupSelect2Field($model, 'CorrespondenceRecipient'),
                'options' => [
                    'class' => 'select2-ajax',
                    'data-allow-clear' => 'true',
                    'ajax-route' => route('CorrespondenceRecipient.select2', ['relation' => 'correspondenceRecipient']),
                ],
            ];
        }

        $d[] = ['form_type' => 'checkbox', 'name' => 'lawsuit', 'description' => 'Ongoing lawsuit'];

        $all_fields = array_merge($a, $b1, $b2, $b3, $c, $d);

        // POST data from Customer (via relation.blade.php hidden fields) is automatically handled
        // by BaseViewController::prepare_form_fields which reads from $_POST
        // No need for init_values - POST hidden fields work the same way as Contract -> Modem

        return $all_fields;
    }

    /**
     * Get all management jobs for envia TEL
     *
     * @author Patrick Reichel
     *
     * @param  $contract  current contract object
     * @return array containing linktexts and URLs to perform actions against REST API
     */
    public static function _get_envia_management_jobs($contract)
    {
        $provvoipenvia = new \Modules\ProvVoipEnvia\Entities\ProvVoipEnvia;

        // check if user has the right to perform actions against envia TEL API
        if (Bouncer::cannot('view', \Modules\ProvVoipEnvia\Entities\ProvVoipEnvia::class)) {
            return;
        }

        return $provvoipenvia->get_jobs_for_view($contract, 'contract');
    }

    /**
     * Set contract Start date - TODO: move to default_input(), when it is executed in BaseController
     */
    public function prepare_input($data)
    {
        if (empty($data['country_code'] ?? null)) {
            $config = cache('GlobalConfig', function () {
                return GlobalConfig::first();
            });
            $data['country_code'] = $config->default_country_code;
        }
        // ISO 3166 country codes are uppercase
        $data['country_code'] = \Str::upper($data['country_code']);

        $defaultContractStart = date('Y-m-d');

        if (Module::collections()->has('SmartOnt')) {
            // contract_start not used in SmartOnt
            // this date indicates that and avoids possible later confusion
            $defaultContractStart = '1900-01-01';
        }

        // set contract_start to today if none is given
        $data['contract_start'] = $data['contract_start'] ?: $defaultContractStart;

        if (! Module::collections()->has('SmartOnt')) {
            // generate contract number
            if (! $data['number'] && Module::collections()->has('BillingBase')) {
                // generate contract number
                $num = \Modules\BillingBase\Entities\NumberRange::getNextContractNr($data['costcenter_id']);

                if ($num) {
                    $data['number'] = $num;
                }
            }
        }

        $data['house_number'] = str_replace(' ', '', strtolower($data['house_number']));

        $data = parent::prepare_input($data);

        // set this to null if no value is given
        $nullable_fields = [
            'contract_end',
            'voip_contract_start',
            'voip_contract_end',
            'birthday',
            'value_date',
        ];
        $data = $this->_nullify_fields($data, $nullable_fields);

        return $data;
    }

    public function prepare_rules($rules, $data)
    {
        foreach ($rules as $name => $rule) {
            $rules[$name] = str_replace('placeholder_salutations_person', implode(',', $this->getSalutationOptionsPerson()), $rules[$name]);
            $rules[$name] = str_replace('placeholder_salutations_institution', implode(',', $this->getSalutationOptionsInstitution()), $rules[$name]);
        }

        return parent::prepare_rules($rules, $data);
    }

    /**
     * Overwrite BaseController method => not required dates should be set to null if not set
     * Otherwise we get entries like 0000-00-00, which cause crashes on validation rules in case of update
     *
     * @author Patrick Reichel
     */
    protected function default_input($data)
    {
        $data = parent::default_input($data);

        $nullable_fields = [
            'contract_end',
            'voip_contract_start',
            'voip_contract_end',
        ];

        foreach ($this->view_form_fields(static::get_model_obj()) as $field) {
            if (array_key_exists($field['name'], $data)) {
                if (array_search($field['name'], $nullable_fields) !== false) {
                    if ($data[$field['name']] == '') {
                        $data[$field['name']] = null;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Show tabs in Contract edit page.
     *
     * @author Roy Schneider
     *
     * @param Modules\ProvBase\Entities\Contract
     * @return array
     */
    protected function editTabs($contract)
    {
        $defaultTabs = parent::editTabs($contract);
        unset($defaultTabs[0]);

        return $defaultTabs;
    }

    /**
     * Convert WebOrderItems to Items for a contract
     * Handles cancellation of existing items by setting valid_to
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function convertWebOrderItems($id)
    {
        $contract = Contract::findOrFail($id);

        // Get all confirmed web order items for this contract
        $webOrderItems = \Modules\OrderPortal\Entities\WebOrderItem::where('contract_id', $contract->id)->
            where('confirmed', true)->
            with('product')->
            get();

        if ($webOrderItems->isEmpty()) {
            $contract->addAboveMessage(trans('messages.no_weborder_items_to_convert'), 'error', 'form');

            return redirect()->back();
        }

        \DB::beginTransaction();

        try {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $today = date('Y-m-d');

            // Use withoutEvents to prevent observers from triggering daily_conversion during item creation
            $controller = $this;
            \Modules\BillingBase\Entities\Item::withoutEvents(function () use ($contract, $webOrderItems, $today, $yesterday, $controller) {
                foreach ($webOrderItems as $webOrderItem) {
                    $product = $webOrderItem->product;

                    if (! $product) {
                        continue;
                    }

                    // Get the qualified model class (Item or WaipuTVItem)
                    $itemClass = $controller->getQualifiedModelClassForProduct($product);
                    $item = new $itemClass;

                    // Set basic item fields
                    $item->contract_id = $contract->id;
                    $item->product_id = $webOrderItem->product_id;
                    $item->count = $webOrderItem->qty ?: 1;
                    $item->valid_from = $today;
                    $item->valid_from_fixed = true;
                    $item->valid_to = null;
                    $item->valid_to_fixed = false;
                    $item->costcenter_id = $contract->costcenter_id;
                    $item->accounting_text = $webOrderItem->name;
                    // Set qualified_model_class manually since withoutEvents prevents the creating hook
                    $item->qualified_model_class = $itemClass;

                    // Handle fixed cycles if needed
                    $controller->handleFixedCyclesForItem($item, $product);

                    // For Internet, Voip, and TV types: cancel existing active items
                    if (in_array($product->type, ['Internet', 'Voip', 'TV'])) {
                        $existingTariff = $contract->get_valid_tariff($product->type);

                        if ($existingTariff) {
                            $existingTariff->valid_to = $yesterday;
                            $existingTariff->valid_to_fixed = true;
                            $existingTariff->observer_enabled = false; // Prevent observer recursion
                            $existingTariff->save();
                        }
                    }

                    $item->save();
                }
            });

            // Run daily_conversion once at the end for all changes
            // Wrap in try-catch to handle missing DHCP config file and geocoding errors gracefully
            try {
                $contract->daily_conversion();
            } catch (\Exception $dailyConversionException) {
                // Check if this is a non-critical error (DHCP file, geocoding, config files, etc.)
                $errorMessage = $dailyConversionException->getMessage();
                $isNonCriticalError = (
                    strpos($errorMessage, 'ignore-cpe.conf') !== false ||
                    strpos($errorMessage, 'Geocoding API') !== false ||
                    strpos($errorMessage, 'geo coordinates') !== false ||
                    strpos($errorMessage, '/tftpboot/cm/') !== false ||
                    strpos($errorMessage, 'file_put_contents') !== false
                );

                if ($isNonCriticalError) {
                    // Log the error but don't fail the conversion
                    // These are system configuration issues, not data issues
                    \Log::warning('Daily conversion encountered non-critical error during web order items conversion: '.$errorMessage, [
                        'contract_id' => $contract->id,
                    ]);
                } else {
                    // For other errors, re-throw to be caught by outer catch
                    throw $dailyConversionException;
                }
            }

            // Delete the converted web order items after successful conversion
            foreach ($webOrderItems as $webOrderItem) {
                $webOrderItem->delete();
            }

            \DB::commit();

            $contract->addAboveMessage(trans('messages.weborder_items_converted'), 'success', 'form');

            return redirect()->back();
        } catch (\Exception $e) {
            \DB::rollBack();

            $contract->addAboveMessage(trans('messages.weborder_items_conversion_failed', ['error' => $e->getMessage()]), 'error', 'form');

            return redirect()->back();
        }
    }

    /**
     * Get qualified model class for product (similar to ItemObserver logic)
     *
     * @param  \Modules\BillingBase\Entities\Product  $product
     * @return string
     */
    protected function getQualifiedModelClassForProduct($product)
    {
        if (Module::collections()->has('WaipuTV')) {
            $partner = $product->reselling_partner;

            if ($partner == 'waipu.tv') {
                return 'Modules\WaipuTV\Entities\WaipuTVItem';
            }
        }

        return 'Modules\BillingBase\Entities\Item';
    }

    /**
     * Handle fixed cycles for item (similar to ItemObserver logic)
     *
     * @param  \Modules\BillingBase\Entities\Item  $item
     * @param  \Modules\BillingBase\Entities\Product  $product
     * @return void
     */
    protected function handleFixedCyclesForItem($item, $product)
    {
        if (! $product->cycle_count) {
            return;
        }

        $cnt = $product->cycle_count;
        if ($product->billing_cycle == 'Quarterly') {
            $cnt *= 3;
        }
        if ($product->billing_cycle == 'Yearly') {
            $cnt *= 12;
        }

        if (! $item->valid_from) {
            $item->valid_from = date('Y-m-d');
        }

        $item->valid_to = date('Y-m-d', strtotime('last day of this month', strtotime("+$cnt month", strtotime($item->valid_from))));
    }
}
