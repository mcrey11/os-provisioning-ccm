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

namespace Modules\ProvBase\Console;

use App\ImportTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\Product;
use Modules\PropertyManagement\Entities\Apartment;
use Modules\PropertyManagement\Entities\Node;
use Modules\PropertyManagement\Entities\Realty;
use Modules\ProvBase\Entities\Address;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\Phonenumber;
use Validator;

/**
 * This command takes care of the (minimum) data that needs to be imported to make provisioning (and billing) work
 * It contains the base skeleton to prepare the data, validate and import it for every future import
 *
 * TODO: Please adapt, in- and exclude functions to your needs! (/according to the available data)
 */
class ImportCsvCommand extends Command
{
    use ImportTrait;
    use \App\AddressFunctionsTrait;

    /** @var array Keys of data that shall not be validated */
    private $validationExceptions = ['mac', 'number'];

    /** @var \Modules\ProvBase\Entities\Contract Currently processed contract */
    private $contract;

    /** @var array Current line of CSV / excel sheet to be processed */
    private $currentLine;

    /** @var \Modules\ProvBase\Entities\Modem Currently processed modem */
    private $modem;

    /** @var array Storage with warnings to print after command has finished */
    private $warnings = [];

    /** @var Collection Product Ids existing in nmsprime DB */
    private $availableProdIds;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv
        {file : Structured CSV file (-path) with customer data.}
        {contract-cc : CostCenter ID for all the imported contracts.}
    ';

    protected $description = 'Import data from one single CSV - first aligned to data from momentum. Should be adapted and used for all future CSV imports.';

    public function handle()
    {
        $list = file($this->argument('file'));
        unset($list[0]);
        $num = count($list);
        $this->line("Load $num contracts...");
        $bar = $this->output->createProgressBar($num);
        $bar->start();

        $this->availableProdIds = Product::get()->keyBy('name');
        $this->availableConfigfileIds = Configfile::get()->keyBy('name');

        foreach ($list as $line) {
            $bar->advance();

            $this->currentLine = str_getcsv($line, ';');

            $this->addContract();
            $this->addItems();
        }

        $bar->finish();
        echo "\n";

        Item::where('id', '!=', 0)->update(['valid_to' => null]);

        $this->printWarnings();
    }

    private function addRealEstate()
    {
        return $this->realEstate = Realty::firstOrCreate([
            // 'node_id' => $nodeId ?: 0,
            // 'number' => $line[4],
            'street' => $this->currentLine[2],
            'house_nr' => $this->currentLine[1],
            'zip' => str_pad($this->currentLine[6], 5, '0', STR_PAD_LEFT),
            'city' => $this->currentLine[4],
            'country_code' => $this->currentLine[5],
            // 'description' => 'Gestatter-Nr '.$this->currentLine[8],
        ]);
    }

    private function addApartment()
    {
        return Apartment::firstOrCreate([
            'realty_id' => $this->realEstate->id,
            // 'code' => $this->currentLine[3],
            // 'floor' => $this->currentLine[6] ?: null,
            'number' => $this->currentLine[3],
        ]);
    }

    private function addAddress()
    {
        $data = [
            'city' => $this->currentLine[4],
            // 'district' => ,
            'house_number' => $this->currentLine[1],
            'lat' => $this->currentLine[8],
            'lng' => $this->currentLine[7],
            'street' => $this->currentLine[2],
            'source' => 'CSV',
            'zip' => $this->currentLine[6],
        ];

        if (! $data['lat'] || ! $data['lng'] || ! is_float($data['lat']) || ! is_float($data['lng'])) {
            return;
        }

        $existing = Address::where('city', $data['city'])
            ->where('house_number', $data['house_number'])
            ->where('street', $data['street'])
            ->where('zip', $data['zip'])
            ->first();

        if ($existing) {
            $logMsg = 'Address '.$data['house_number'].' '.$data['street'].' '.$data['city'].' '.$data['zip'].' has different lat specifications:';
            if ($existing->lat != $data['lat']) {
                $this->warnings[] = $logMsg." $existing->lat vs ".$data['lat'];
            }

            if ($existing->lng != $data['lng']) {
                $this->warnings[] = $logMsg." $existing->lng vs ".$data['lng'];
            }

            return;
        }

        Address::create($data);
    }

    private function addContract()
    {
        $number = $this->currentLine[0];
        $this->contract = null;
        $description = $this->currentLine[27];

        if (! $this->contract) {
            $housenr = $this->currentLine[10];
            $street = $this->currentLine[9];

            $data = [
                'academic_degree' => $this->currentLine[1],
                'apartment_nr' => $this->currentLine[11],
                'city' => $this->currentLine[7],
                'company' => $this->currentLine[5] ?: null,
                'contract_start' => '2000-01-01',
                'costcenter_id' => $this->argument('contract-cc'),
                // 'country_code' => $this->currentLine[22], // State
                'create_invoice' => true,
                'description' => $description,
                'email' => $this->currentLine[12],
                // 'lat' => $this->currentLine[8] ? (is_float($this->currentLine[8]) ? $this->currentLine[8] : null) : null,
                // 'lng' => $this->currentLine[7] ? (is_float($this->currentLine[7]) ? $this->currentLine[7] : null) : null,
                // 'contract_end' => ,
                'firstname' => $this->currentLine[3],
                'house_number' => $housenr,
                'lastname' => $this->currentLine[4],
                'number' => (string) $number,
                'phone' => $this->currentLine[32],
                // Salutation is required but rules is removed in isDataValid()
                'salutation' => $this->currentLine[2],
                'street' => $street,
                'zip' => str_pad($this->currentLine[8], 5, '0', STR_PAD_LEFT),
            ];

            $this->contract = Contract::create($data);
        }

        $this->addModem();
    }

    private function addModem()
    {
        $mac = $this->currentLine[16];

        // if (! $mac) {
        //     $this->line(implode(';', $this->currentLine));

        //     return;
        // }
        $this->modem = null;

        $data = [
            'city' => $this->contract->city,
            'configfile_id' => $this->getModemConfigfileId(),
            'contract_id' => $this->contract->id,
            'firstname' => $this->contract->firstname,
            'house_number' => $this->contract->house_number,
            'internet_access' => $this->currentLine[14],
            'lastname' => $this->contract->lastname,
            'mac' => $mac ?: null,
            'ppp_password' => $this->currentLine[22],
            'ppp_username' => str_replace(' ', '', $this->currentLine[21]),
            'public' => true,
            'street' => $this->contract->street,
            'zip' => $this->contract->zip,
            // 'lat' => $this->currentLine[8] ? (is_float($this->currentLine[8]) ? $this->currentLine[8] : null) : null,
            // 'lng' => $this->currentLine[7] ? (is_float($this->currentLine[7]) ? $this->currentLine[7] : null) : null,
        ];

        if (! $this->isDataValid(new Modem, $data)) {
            $this->modem = Modem::where('mac', $mac)->first();

            if (! $this->modem) {
                return;
            }
        }

        if (! $this->modem) {
            $this->modem = Modem::create($data);
        }

        $this->addEndpoint();
        $this->addMta();
    }

    private function isDataValid($obj, array $data): bool
    {
        $className = $obj->get_model_name();
        $module = $obj->getModuleName();
        $fqControllerName = '\Modules\\'.$module."\Http\Controllers\\$className".'Controller';
        $controller = new $fqControllerName;

        $rules = $controller->prepare_rules($obj->rules(), $data);

        foreach ($this->validationExceptions as $key) {
            if (isset($rules[$key])) {
                unset($rules[$key]);
            }
        }

        $validator = Validator::make($data, $rules);

        if (! $validator->fails()) {
            return true;
        }

        $ref = $data['number'] ?? ($data['mac'] ?? '');
        $failedKeys = array_keys($validator->errors()->messages());

        foreach ($data as $key => $value) {
            if (! in_array($key, $failedKeys)) {
                unset($data[$key]);
            }
        }

        $this->line("\n$className validation error for $ref: ".implode(', ', Arr::flatten($validator->errors()->messages())).' ['.implode(',', $data)."]\n");

        return false;
    }

    private function getModemConfigfileId()
    {
        if ($this->currentLine[13] != 'CABLEMODEM') {
            return 29;
        }

        $name = $this->currentLine[18];

        if (! isset($this->availableConfigfileIds[$name])) {
            $this->line("\n Configfile $name does not exist. Use Base Configfile");

            return 1;
        }

        return $this->availableConfigfileIds[$name]->id;
    }

    private function addEndpoint()
    {
        $ip = $this->currentLine[25];

        if (! $ip) {
            return;
        }

        $data = [
            'fixed_ip' => true,
            'hostname' => 'cpe-'.$this->modem->id,
            'ip' => $ip,
            'mac' => strpos($this->currentLine[26], '--') === false ? $this->currentLine[26] : null,
            'modem_id' => $this->modem->id,
            'version' => '4',
        ];

        if (! $this->isDataValid(new Endpoint, $data)) {
            if (Endpoint::where('hostname', $data['hostname'])->first()) {
                $data['hostname'] .= '-2';
            } else {
                return;
            }
        }

        Endpoint::create($data);
    }

    private function addMta()
    {
        $mac = $this->currentLine[33];
        $this->mta = null;

        if (! $mac) {
            if ($this->currentLine[28] && $this->currentLine[13] != 'CABLEMODEM') {
                $this->line("Modem {$this->modem->mac} has phonenumber but no MTA\n");
            }

            return;
        }

        $data = [
            'configfile_id' => $this->getMtaConfigfileId(),
            'mac' => $mac,
            'modem_id' => $this->modem->id,
            'type' => 'sip',
        ];

        if (! $this->isDataValid(new Mta, $data)) {
            $this->mta = Mta::where('mac', $mac)->first();

            if (! $this->mta) {
                return;
            }
        }

        if (! $this->mta) {
            $this->mta = Mta::create($data);
        }

        $this->addPhonenumber();
    }

    private function getMtaConfigfileId()
    {
        $name = $this->currentLine[19];
        $cf = Configfile::where('name', 'ilike', $name.'%')->first();

        if (! $cf) {
            $this->line("\n Configfile $name does not exist for MTA");

            return;
        }

        return $cf->id;
    }

    private function addPhonenumber()
    {
        foreach ([28, 30] as $key) {
            if (! $this->currentLine[$key]) {
                return;
            }

            $data = [
                'active' => true,
                'country_code' => '0043',
                'prefix_number' => '0535',
                'mta_id' => $this->mta->id,
                'number' => str_replace(['0535', '-'], '', $this->currentLine[$key]),
                'password' => $this->currentLine[$key + 1],
                'port' => $key == 28 ? 1 : 2,
                'sipdomain' => '',
                'username' => str_replace('-', '', $this->currentLine[$key]),
            ];

            if (! $this->isDataValid(new Phonenumber, $data)) {
                return;
            }

            Phonenumber::create($data);
        }
    }

    private function addItems()
    {
        $prodName = $this->currentLine[15];

        if (! isset($this->availableProdIds[$prodName])) {
            $this->line("\nMissing product in DB for $prodName");

            return;
        }

        $pId = $this->availableProdIds[$prodName]->id;

        $data = [
            'contract_id' => $this->contract->id,
            'payed_until_before_sr' => date('Y-m-d', strtotime('last day of next month')),
            'product_id' => $pId,
            'valid_from' => '2000-01-01',
        ];

        Item::create($data);
    }

    private function printWarnings()
    {
        foreach ($this->warnings as $warning) {
            $this->line($warning);
        }
    }
}
