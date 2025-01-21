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
use Modules\BillingBase\Entities\Item;
use Modules\ProvBase\Entities\Address;
use Modules\ProvBase\Entities\Contract;
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
    private $validationExceptions = ['email', 'number', 'salutation'];

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

        $this->availableProdIds = \Modules\BillingBase\Entities\Product::pluck('id');

        foreach ($list as $line) {
            $bar->advance();

            $this->currentLine = str_getcsv($line, ';');

            $this->addContract();
            $this->addItems();
        }

        $bar->finish();

        Item::where('id', '!=', 0)->update(['valid_to' => null]);

        $this->printWarnings();
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
        $number = $this->currentLine[10];
        $this->contract = null;
        $description = null;

        if (strpos($number, 'Node') !== false) {
            $this->contract = Contract::find(615);
        } elseif (Contract::where('number', $number)->first()) {
            $description = "belongs to $number";
            $number = '';
        } else {
            $this->addAddress();
        }

        if (! $this->contract) {
            $addr = explode(' ', $this->currentLine[20], 2);
            $housenr = $addr[0];
            $street = $addr[1] ?? null;

            $data = [
                'apartment_nr' => $this->currentLine[3],
                'city' => $this->currentLine[21] ?: $this->currentLine[4],
                'company' => $this->currentLine[11] ?: null,
                'contract_start' => '2000-01-01',
                'costcenter_id' => $this->argument('contract-cc'),
                'country_code' => $this->currentLine[22], // State
                'create_invoice' => true,
                'description' => $description,
                'email' => $this->currentLine[14],
                'lat' => $this->currentLine[8] ? (is_float($this->currentLine[8]) ? $this->currentLine[8] : null) : null,
                'lng' => $this->currentLine[7] ? (is_float($this->currentLine[7]) ? $this->currentLine[7] : null) : null,
                // 'contract_end' => ,
                'firstname' => $this->currentLine[13],
                'house_number' => $housenr ? $housenr : $this->currentLine[1],
                'lastname' => $this->currentLine[12],
                'number' => (string) $number,
                'phone' => $this->currentLine[15],
                // Salutation is required but rules is removed in isDataValid()
                // 'salutation' => self::map_salutation(self::$line[self::C_SALUT]),
                'street' => $street ?: $this->currentLine[2],
                'zip' => $this->currentLine[23] ?: $this->currentLine[6],
            ];

            // if (! $data['street'] && ! $data['firstname'] && ! $data['company']) {
            //     return;
            // }

            if ($data['street'] != $this->currentLine[2] ||
                $data['house_number'] != $this->currentLine[1] ||
                $data['zip'] != $this->currentLine[6] ||
                $data['city'] != $this->currentLine[4]
            ) {
                unset($data['lat']);
                unset($data['lng']);
            }

            // if (! $this->isDataValid('Contract', $data)) {
            //     return;
            // }

            $this->contract = Contract::create($data);
        }

        $this->addModem();
    }

    private function addModem()
    {
        $mac = $this->currentLine[69] ?? null;

        if (! $mac) {
            if (! isset($this->currentLine[69])) {
                $this->line(implode(';', $this->currentLine));
            }

            return;
        }

        $data = [
            'city' => $this->currentLine[4],
            'configfile_id' => $this->getModemConfigfileId(),
            'contract_id' => $this->contract->id,
            'firstname' => $this->contract->firstname,
            'house_number' => $this->currentLine[1],
            'lastname' => $this->contract->lastname,
            'mac' => $mac,
            'street' => $this->currentLine[2],
            'zip' => $this->currentLine[6],
            'lat' => $this->currentLine[8] ? (is_float($this->currentLine[8]) ? $this->currentLine[8] : null) : null,
            'lng' => $this->currentLine[7] ? (is_float($this->currentLine[7]) ? $this->currentLine[7] : null) : null,
        ];

        if (! $this->isDataValid('Modem', $data)) {
            return;
        }

        $this->modem = Modem::create($data);

        $this->addMta();
    }

    private function isDataValid(string $classname, array $data): bool
    {
        $module = [
            'Contract' => 'ProvBase',
            'Modem' => 'ProvBase',
            'Mta' => 'ProvVoip',
            'Phonenumber' => 'ProvVoip',
        ];

        $fqClassName = '\Modules\\'.$module[$classname]."\Entities\\$classname";
        $obj = new $fqClassName;
        $fqControllerName = '\Modules\\'.$module[$classname]."\Http\Controllers\\$classname".'Controller';
        $controller = new $fqControllerName;

        $rules = $controller->prepare_rules($obj->rules(), $data);

        foreach ($this->validationExceptions as $key) {
            if (isset($rules[$key])) {
                unset($rules[$key]);
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            echo "\n";
            $ref = $data['number'] ?? ($data['mac'] ?? '');
            // dd($validator->errors()->);
            $this->line("$classname validation error for $ref: ".implode(', ', array_keys($validator->errors()->messages())));
            // $this->line(implode(';', $this->currentLine));
            echo "\n";
            // $this->warnings[] = "$classname validation error: ".$validator->errors();

            return false;
        }

        return true;
    }

    private function getModemConfigfileId()
    {
        $bridgeProdIds = [4, 7, 9, 11];

        foreach ($bridgeProdIds as $prodId) {
            if (strpos($this->currentLine[60], "$prodId,") !== false) {
                // Bridge no WiFi configfile
                return 6;
            }
        }

        // Base Configfile
        return 1;
    }

    private function addMta()
    {
        if (! $this->currentLine[70]) {
            return;
        }

        $data = [
            'configfile_id' => $this->getMtaConfigfileId(),
            'mac' => $this->currentLine[70],
            'modem_id' => $this->modem->id,
            'type' => 'sip',
        ];

        if (! $this->isDataValid('Mta', $data)) {
            return;
        }

        $this->mta = Mta::create($data);

        $this->addPhonenumber();
    }

    private function getMtaConfigfileId()
    {
        return 9;
    }

    private function addPhonenumber()
    {
        if (! $this->currentLine[71]) {
            return;
        }

        $data = [
            'active' => true,
            'country_code' => '001',
            'prefix_number' => '352',
            'mta_id' => $this->mta->id,
            'number' => str_replace(['352', '-'], '', $this->currentLine[71]),
            'password' => $this->currentLine[72],
            'port' => 1,
            'sipdomain' => 'mwnms1.mitotec.com',
            'username' => str_replace('-', '', $this->currentLine[71]),
        ];

        if (! $this->isDataValid('Phonenumber', $data)) {
            return;
        }

        Phonenumber::create($data);
    }

    private function addItems()
    {
        $productIds = explode(',', $this->currentLine[60]);

        foreach ($productIds as $pId) {
            if (! $pId || ! $this->availableProdIds->contains($pId)) {
                continue;
            }

            $data = [
                'contract_id' => $this->contract->id,
                'payed_until_before_sr' => date('Y-m-d', strtotime('last day of next month')),
                'product_id' => $pId,
                'valid_from' => '2000-01-01',
            ];

            \Modules\BillingBase\Entities\Item::create($data);
        }
    }

    private function printWarnings()
    {
        foreach ($this->warnings as $warning) {
            $this->line($warning);
        }
    }
}
