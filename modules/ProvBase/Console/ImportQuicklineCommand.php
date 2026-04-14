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

// use App\ImportTrait;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\Product;
use Modules\ProvBase\Entities\Address;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Endpoint;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\Phonenumber;
use Validator;

class ImportQuicklineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:quickline
        {--C|customers= : Structured CSV file (-path) with customer data.}
        {--P|products= : Import products only from a CSV that only contains a product list}
    ';

    protected $description = 'Import either product or customer data from Quickline as CSV';

    protected $warnings = [];

    /** @var array CSV entries */
    protected $list;

    protected $unitMapping = [
        'Monat' => 'Monthly',
        'Stk' => 'Once',
        'Stk.' => 'Once',
        'einmalig' => 'Once',
        'pro Rata Monat' => 'Monthly',
    ];

    protected $typeMapping = [
        '1' => 'Internet',
        '2' => 'TV',
        '3' => 'Voip',
        '4' => 'Voip',
        '5' => 'Credit',
        '6' => 'Other',
    ];

    protected $cfMapping = [
        // 'default' => 5, // Generic ONT
        'fritz' => 6,
        'zyxel' => 7,
    ];

    protected $qosMapping = [
        'Internetprofil 10 / 10 GBit/s' => 1,
        'Internetprofil 1000 / 1000 MBit/s' => 3,
        'Internetprofil 500 / 500 MBit/s' => 4,
        'Internetprofil 300 / 300 MBit/s' => 5,
        'Internetprofil 200 / 200 MBit/s' => 6,
        'Internetprofil 100 / 100 MBit/s' => 7,
    ];

    public function handle()
    {
        if (! $this->option('customers') && ! $this->option('products')) {
            return $this->error('Please specify one of the options. Stop');
        }

        if ($this->option('products')) {
            $this->list = file($this->option('products'));
            unset($this->list[0]);

            $this->addProducts();
        }

        if ($this->option('customers')) {
            $this->list = file($this->option('customers'));
            unset($this->list[0]);

            $this->addCustomers();
        }

        $this->printWarnings();
    }

    private function addProducts()
    {
        if (! $this->list) {
            return $this->error('Empty product CSV');
        }

        Product::where('created_at', '>', DB::raw('CURRENT_DATE'))->forceDelete();

        echo "Add products...\n";
        $bar = $this->output->createProgressBar(count($this->list));
        $bar->start();

        foreach ($this->list as $line) {
            $bar->advance();
            $line = str_getcsv($line, ';');

            // Ignore
            if (in_array($line[3], ['Min.', 'MB'])) {
                continue;
            }

            $id = $line[0];

            if (! $id) {
                continue;
            }

            // dd(mb_substr($id, 0, 1), $id, $line);

            $type = $this->typeMapping[mb_substr($id, 0, 1)];

            $deprecated = false;
            if ($type == 'Internet' && $id >= 120) {
                $deprecated = true;
            }

            if ($type == 'Voip' && ! in_array(intval($id), [300, 301, 320, 321, 322, 323, 400, 401, 402, 403])) {
                $type = 'Other';
            }

            $qosId = null;
            if ($type == 'Internet') {
                $qosId = $this->qosMapping[$line[2]] ?? null;
            }

            $now = now();

            DB::table('product')->insert([
                'created_at' => $now,
                'updated_at' => $now,
                'id' => intval($id),
                'name' => trim($line[1]),
                'reselling_partner' => $line[2] ? 'iway' : null,
                'billing_cycle' => $this->unitMapping[$line[3]] ?? null,
                'price' => round(intval(str_replace(['CHF', ' '], '', $line[4]) ?: 0) / (1 + 8.1 / 100), 4),
                // 'deprecated' => $deprecated,
                'qos_id' => $qosId,
                'type' => $type,
            ]);
        }

        $bar->finish();
        echo "\n";
    }

    private function addCustomers()
    {
        $num = count($this->list);
        $this->line("Load $num contracts...");
        $bar = $this->output->createProgressBar($num);
        $bar->start();

        $this->availableProducts = Product::get()->keyBy('name');
        // $this->availableConfigfileIds = Configfile::get()->keyBy('name');

        foreach ($this->list as $line) {
            $bar->advance();

            $this->currentLine = str_getcsv($line, ';');

            $this->addContract();
            $this->addItems();
        }

        $bar->finish();
        echo "\n";

        Item::where('id', '!=', 0)->update(['valid_to' => null]);
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

        $housenr = $this->currentLine[2];
        $street = $this->currentLine[1];

        $data = [
            // 'academic_degree' => $this->currentLine[1],
            // 'apartment_nr' => $this->currentLine[11],
            'city' => $this->currentLine[4],
            // 'company' => $this->currentLine[5] ?: null,
            'contract_start' => '2025-11-01',
            'costcenter_id' => 1, // $this->argument('contract-cc'),
            // 'country_code' => $this->currentLine[22], // State
            'create_invoice' => true,
            // 'description' => $description,
            'email' => $this->currentLine[5],
            // 'lat' => $this->currentLine[8] ? (is_float($this->currentLine[8]) ? $this->currentLine[8] : null) : null,
            // 'lng' => $this->currentLine[7] ? (is_float($this->currentLine[7]) ? $this->currentLine[7] : null) : null,
            // 'contract_end' => ,
            'firstname' => $this->currentLine[9],
            'house_number' => $housenr,
            'lastname' => $this->currentLine[8],
            'number' => (string) $number,
            // 'phone' => $this->currentLine[32],
            // Salutation is required but rules is removed in isDataValid()
            'salutation' => $this->currentLine[7],
            'street' => $street,
            'zip' => str_pad($this->currentLine[3], 5, '0', STR_PAD_LEFT),
        ];

        $this->contract = Contract::create($data);

        $this->addModem();
    }

    private function addModem()
    {
        // $mac = $this->currentLine[16];

        $this->modem = null;

        $data = [
            'city' => $this->contract->city,
            'configfile_id' => 0,
            'configfile_id' => $this->getModemConfigfileId(),
            'contract_id' => $this->contract->id,
            'description' => 'WLAN SSID: '.$this->currentLine[24].($this->currentLine[25] ? ' - PSW: '.$this->currentLine[25] : ''),
            'firstname' => $this->contract->firstname,
            'house_number' => $this->contract->house_number,
            'internet_access' => true,
            'lastname' => $this->contract->lastname,
            'name' => $this->currentLine[23],
            // 'mac' => $mac ?: null,
            // 'ppp_password' => $this->currentLine[22],
            // 'ppp_username' => str_replace(' ', '', $this->currentLine[21]),
            'public' => true,
            'street' => $this->contract->street,
            'zip' => $this->contract->zip,
            // 'lat' => $this->currentLine[8] ? (is_float($this->currentLine[8]) ? $this->currentLine[8] : null) : null,
            // 'lng' => $this->currentLine[7] ? (is_float($this->currentLine[7]) ? $this->currentLine[7] : null) : null,
        ];

        // if (! $this->isDataValid(new Modem, $data)) {
        //     $this->modem = Modem::where('mac', $mac)->first();

        //     if (! $this->modem) {
        //         return;
        //     }
        // }

        $this->modem = Modem::createQuietly($data);

        // $this->addEndpoint();
        // $this->addMta();
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
        $name = $this->currentLine[23];
        if (! $name) {
            return 0;
        }

        $cfId = 0;
        foreach ($this->cfMapping as $key => $id) {
            if (strpos(strtolower($name), $key) !== false) {
                $cfId = $id;

                break;
            }
        }

        return $cfId ?: 5;
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
        for ($i = 26; $i <= 181; $i += 2) {
            $prodName = trim($this->currentLine[$i]);

            if (! $prodName) {
                continue;
            }

            if (! isset($this->availableProducts[$prodName])) {
                $this->warnings[$prodName] = "Missing product in DB for $prodName";

                dd($i, $prodName);

                return;
            }

            $pId = $this->availableProducts[$prodName]->id;

            $data = [
                'contract_id' => $this->contract->id,
                // 'payed_until_before_sr' => date('Y-m-d', strtotime('last day of next month')),
                'product_id' => $pId,
                'valid_from' => '2025-11-01',
            ];

            Item::create($data);
        }
    }

    private function printWarnings()
    {
        foreach ($this->warnings as $warning) {
            $this->line($warning);
        }
    }
}
