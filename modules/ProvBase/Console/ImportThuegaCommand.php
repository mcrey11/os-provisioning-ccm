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
use Modules\BillingBase\Entities\BookingAccount;
use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\Phonenumber;

class ImportThuegaCommand extends Command
{
    use ImportTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:thuega
        {--T|test : Don\'t geocode contract and modem addresses as this takes huge time and costs money.}
        {--P|products= : Import only products from a CSV file.}
        {contracts? : Structured CSV file (-path) with contract/tariff and article data.}
        {customers? : Structured CSV file (-path) with customer/contract data.}
        {devices? : Structured CSV file (-path) with devices/modem data.}
    ';

    protected $fileColCounts = [
        'contracts' => 39,
        'customers' => 44,
        'devices' => 32,
    ];

    protected $description = 'Import customer data from Thüga CSVs';

    protected $warnings = [];

    protected $telephoneTypes = [
        'telBusiness' => 'Telefon geschäftlich',
        'telPrivate' => 'Telefon privat',
        'telMobile' => 'Telefon mobil',
    ];

    protected $faxTypes = [
        'faxBusiness' => 'Fax geschäftlich',
        'faxPrivate' => 'Fax privat',
    ];

    /** @vars currently processed objects */
    protected $line;
    protected $contract;

    /** @var int ID of existing base config file */
    protected $baseConfigfileId;
    /** @var array created configfiles keyed by name */
    protected $configfiles;

    /** @var array imported contracts keyed by contract number */
    protected $contracts;

    /** @var array imported contract numbers keyed by customer number */
    protected $customerNumbers;

    /** @var array Existing CostCenters keyed by number */
    protected $costcenters;

    /** @var array Existing Products keyed by name */
    protected $products;

    /** @var array SEPA mandates keyed by IBAN */
    protected $sepamandates;

    public function handle()
    {
        $this->validateInput();
        $this->loadNecessaryData();

        if ($this->option('products')) {
            $this->importProducts();
            // $this->importAddtlItems();

            return;
        }

        $this->importContracts();
        $this->importCustomerData();
        $this->importModemData();

        \Artisan::call('nms:dhcp');
        exec('systemctl restart dhcpd');

        $this->printWarnings();
    }

    private function validateInput()
    {
        foreach ($this->fileColCounts as $argument => $colCount) {
            if (! $this->argument($argument)) {
                continue;
            }

            if (count(str_getcsv(fgets(fopen($this->argument($argument), 'r')), ';')) != $colCount) {
                dd(str_getcsv(fgets(fopen($this->argument($argument), 'r')), ';'), $colCount, $this->argument($argument));
                exit('File '.$this->argument($argument).' has unexpected column count. Stop here. Please check if structure fits!');
            }
        }
    }

    private function loadNecessaryData()
    {
        $this->costcenters = CostCenter::get()->keyBy('number');
        $this->products = Product::get()->keyBy('name');
    }

    /**
     * Extra function to add SIP data after original import
     */
    private function importSipDataOnly()
    {
        $list = file($this->argument('contracts'));
        unset($list[0]);

        if (strtolower(str_getcsv($list[1], ';')[0]) == 'mandant') {
            unset($list[1]);
        }

        echo "Add SIP data ...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach ($list as $line) {
            $bar->advance();
            $this->line = $line = str_getcsv($line, ';');

            if (! $line[29]) {
                continue;
            }

            $pn = Phonenumber::where('number', $line[29])->first();

            if ($pn) {
                continue;
            }

            $number = trim($line[3]);

            $c = Contract::where('number', $number)->with('modems')->first();

            if (! $c) {
                $this->warnings['missingContract'.$line[3]] = 'Cant add phonenumber '.$line[29].' as contract '.$line[3].' is missing';

                continue;
                // dd($line[3], $number, $line[29]);
            }

            $cm = $c->modems->first();

            if (! $cm) {
                $data = [
                    'contract_id' => $c->id,
                    'mac' => 'ff:ff:ff:ff:ff:fe',
                    'configfile_id' => 1,
                    'salutation' => $c->salutation,
                    'company' => $c->company,
                    'firstname' => $c->firstname,
                    'lastname' => $c->lastname,
                    'street' => $c->street,
                    'house_number' => $c->house_number,
                    'zip' => $c->zip,
                    'city' => $c->city,
                ];

                $cm = Modem::createQuietly($data);
            }

            $mta = $cm->mtas()->with('phonenumbers')->first();

            if (! $mta) {
                $mta = Mta::createQuietly([
                    'modem_id' => $cm->id,
                    'mac' => unifyMac($this->mac2mtaMac($cm->mac)),
                    'configfile_id' => 2,
                    'type' => 'sip',
                ]);
            }

            Phonenumber::createQuietly([
                'active' => false,
                'country_code' => '49',
                'prefix_number' => $line[29],
                'mta_id' => $mta->id,
                'number' => $line[29],
                'password' => $line[31],
                'port' => $mta->phonenumbers->count() + 1,
                // 'sipdomain' => null,
                'username' => $line[30],
            ]);
        }

        $bar->finish();
        echo "\n";
    }

    /**
     * Extra function to set proper modem address after original import
     */
    private function setModemAddress()
    {
        $list = file($this->argument('devices'));
        unset($list[0]);

        if (strtolower(str_getcsv($list[1], ';')[0]) == 'mandant') {
            unset($list[1]);
        }

        echo "Import modems ...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();
        $now = now();

        foreach ($list as $line) {
            $bar->advance();

            $this->line = $line = str_getcsv($line, ';');
            $mac = unifyMac(str_pad($line[29], 12, '0', STR_PAD_LEFT));

            $cm = Modem::where('mac', $mac)->first();

            if (! $cm) {
                $this->warnings[] = "Modem with mac $mac not found. Can't update address.";

                continue;
            }

            Modem::where('mac', $mac)->update([
                'updated_at' => $now,
                'salutation' => $line[20] == 'Herr u Frau' ? 'Herr' : $line[20],
                'firstname' => $line[14],
                'lastname' => $line[13],
                'street' => $line[15],
                'house_number' => $line[16].$line[17],
                'zip' => $line[18],
                'city' => $line[19],
            ]);
        }

        $bar->finish();
        echo "\n";
    }

    private function importContracts()
    {
        $list = file($this->argument('contracts'));
        unset($list[0]);

        if (strtolower(str_getcsv($list[1], ';')[0]) == 'mandant') {
            unset($list[1]);
        }

        echo "Add contract data ...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach ($list as $line) {
            $bar->advance();
            $this->line = $line = str_getcsv($line, ';');
            $number = trim($line[3]);
            $contract = $this->contracts[$number] ?? null;

            if (! $contract) {
                $data = [
                    'additional' => $line[12],
                    // 'academic_degree' => $line[1],
                    // 'apartment_nr' => $line[11],
                    'city' => trim($line[14]),
                    'company' => trim($line[6]) == 'Firma' ? $line[7] : null,
                    'contract_start' => date('Y-m-d', intval(trim(strtotime($line[5])))),
                    'costcenter_id' => $this->getCostCenter(),
                    // 'country_code' => $line[22], // State
                    'create_invoice' => true,
                    // 'description' => $description,
                    'email' => trim($line[5]),
                    // 'lat' => $line[8] ? (is_float($line[8]) ? $line[8] : null) : null,
                    // 'lng' => $line[7] ? (is_float($line[7]) ? $line[7] : null) : null,
                    // 'contract_end' => ,
                    'firstname' => trim($line[6]) == 'Firma' ? null : $line[8],
                    'house_number' => trim($line[10]).trim($line[11]),
                    'lastname' => trim($line[6]) == 'Firma' ? null : $line[7],
                    'number' => (string) $number,
                    'number2' => trim($line[2]), // customer number
                    // 'phone' => $line[32],
                    // Salutation is required but rules is removed in isDataValid()
                    'salutation' => trim($line[6]) == 'Herr u Frau' ? 'Herr' : trim($line[6]),
                    'street' => trim($line[9]),
                    'zip' => trim($line[13]),
                ];

                if ($this->option('test')) {
                    $data['lat'] = 50;
                    $data['lng'] = 50;
                }

                $this->contracts[$number] = $contract = Contract::create($data);
                $this->customerNumbers[trim($line[2])][] = $number;
            }

            $this->contract = $contract;
            $this->addItem();
        }

        $bar->finish();
        echo "\n";
    }

    private function addItem()
    {
        $line = $this->line;
        $prodName = trim($line[18]);
        $prodId = isset($this->products[$prodName]) ? $this->products[$prodName]->id : null;

        if (! $prodId) {
            $this->warnings['missingProd-'.$prodName] = "Product '$prodName' does not exist. Skip adding items for that product";

            return;
        }

        Item::create([
            'contract_id' => $this->contract->id,
            'product_id' => $prodId,
            'valid_from' => date('Y-m-d', intval(strtotime(trim($line[16])))),
            'valid_from_fixed' => 1,
            'valid_to' => in_array(trim($line[17]), ['31.12.2099', '12/31/2099', '2099-12-31']) ? null : date('Y-m-d', intval(strtotime(trim($line[17])))),
            'valid_to_fixed' => 1,
            // TODO: Set for Bereitstellungsgebühr
            // 'payed_until_before_sr' => $nextPayment->subDay(),
        ]);
    }

    private function importCustomerData()
    {
        // return;
        $list = file($this->argument('customers'));
        unset($list[0]);

        if (strtolower(str_getcsv($list[1], ';')[0]) == 'mandant') {
            unset($list[1]);
        }

        echo "Import customers/contracts ...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach ($list as $line) {
            $bar->advance();

            $this->line = $line = str_getcsv($line, ';');
            $number = $line[2];

            if (isset($this->customerNumbers[$number])) {
                $contractNumbers = $this->customerNumbers[$number];
            } else {
                $contractNr = $this->addContractByCustomer();
                $contractNumbers = [$contractNr];
            }

            foreach ($contractNumbers as $contractNr) {
                $this->contract = $this->contracts[$contractNr];

                $this->checkDifferentNameAndAddress();

                $this->contract->birthday = date('Y-m-d', intval(trim(strtotime($line[6]))));
                $mails = [];
                if ($this->contract->email) {
                    $mails[] = $this->contract->email;
                }
                if ($line[40] && $line[40] != $this->contract->email) {
                    $mails[] = trim($line[40]);
                }
                $this->contract->email = implode(', ', $mails);
                $this->contract->number3 = $line[41]; // XDEBINUMMER

                $telBusiness = $line[25].$line[26].$line[27];
                $telPrivate = $line[28].$line[29].$line[30];
                $telMobile = $line[37].$line[38].$line[39];

                $telephones = [];
                foreach ($this->telephoneTypes as $pn => $phoneType) {
                    $$pn = str_replace(' ', '', $$pn);
                    if ($$pn) {
                        $telephones[] = $$pn;
                        $this->contract->description = $phoneType.': '.$$pn;
                    }
                }

                $faxBusiness = $line[31].$line[32].$line[33];
                $faxPrivate = $line[34].$line[35].$line[36];

                $faxes = [];
                foreach ($this->faxTypes as $fax => $faxType) {
                    $$fax = str_replace(' ', '', $$fax);
                    if ($$fax) {
                        $telephones[] = $$fax;
                        $this->contract->description = $faxType.': '.$$fax;
                    }
                }

                if ($telephones) {
                    $this->contract->phone = implode(', ', $telephones);
                }
                if ($faxes) {
                    $this->contract->fax = implode(', ', $faxes);
                }

                $this->contract->save();
                $this->addSepaMandate();
            }
        }

        $bar->finish();
        echo "\n";
    }

    private function addContractByCustomer()
    {
        $number = trim($this->line[2]);

        $data = [
            'city' => trim($this->line[12]),
            'company' => trim($this->line[3]) == 'Firma' ? trim($this->line[4]) : null,
            'contract_start' => date('Y-m-d', intval(trim(strtotime(trim($this->line[5]))))),
            'costcenter_id' => $this->getCostCenter(),
            'create_invoice' => true,
            'email' => trim($this->line[40]),
            'firstname' => trim($this->line[3]) == 'Firma' ? null : trim($this->line[5]),
            'house_number' => trim($this->line[8]).trim($this->line[9]),
            'lastname' => trim($this->line[3]) == 'Firma' ? null : trim($this->line[4]),
            'number' => $number,
            'number2' => $number, // customer number
            'salutation' => trim($this->line[6]) == 'Herr u Frau' ? 'Herr' : trim($this->line[3]),
            'street' => trim($this->line[7]),
            'zip' => trim($this->line[11]),
        ];

        if ($this->option('test')) {
            $data['lat'] = 50;
            $data['lng'] = 50;
        }

        $this->contracts[$number] = Contract::create($data);

        return $number;
    }

    private function checkDifferentNameAndAddress()
    {
        if ($this->contract->firstname != $this->line[5] || $this->contract->lastname != $this->line[4]) {
            $this->warnings['diffName-'.$this->contract->id] = 'Contract name differs in customer and contract file for customer number '.$this->line[2];
        }

        if ($this->contract->street != $this->line[7] || $this->contract->zip != $this->line[11] || $this->contract->house_number != $this->line[8].$this->line[9]) {
            $this->warnings['diffAddr-'.$this->contract->id] = 'Contract address differs in customer and contract file for customer number '.$this->line[2];
        }
    }

    private function addSepaMandate()
    {
        $iban = trim($this->line[21]);
        if (! $iban) {
            return;
        }

        // SEPA mandate already added
        if (isset($this->sepamandates[$iban])) {
            return;
        }

        $signatureDate = date('Y-m-d', intval(trim(strtotime($this->line[19]))));

        $this->sepamandates[$iban] = SepaMandate::create([
            'contract_id' => $this->contract->id,
            'reference' => trim($this->line[24]),
            'signature_date' => $signatureDate,
            'holder' => $this->line[13] ?: trim($this->contract->company ?: $this->contract->firstname.' '.$this->contract->lastname),
            'iban' => $iban,
            'bic' => trim($this->line[23]),
            'institute' => trim($this->line[22]),
            'valid_from' => $signatureDate,
            'valid_to' => in_array(trim($this->line[20]), ['31.12.2099', '12/31/2099', '2099-12-31']) ? null : date('Y-m-d', intval(trim(strtotime($this->line[20])))),
            'state' => 'RCUR',
            // 'costcenter_id' => $costcenterId ?? null,
        ]);
    }

    private function getCostCenter()
    {
        if (isset($this->costcenters[$this->line[0]])) {
            return $this->costcenters[$this->line[0]]->id;
        }

        $this->warnings['ccId-'.$this->line[0]] = 'CostCenter missing for tenant '.$this->line[0];

        return null;
    }

    private function importModemData()
    {
        $this->baseConfigfileId = Configfile::where('name', 'Base')->where('device', 'cm')->first()->id;
        $list = file($this->argument('devices'));
        unset($list[0]);

        if (strtolower(str_getcsv($list[1], ';')[0]) == 'mandant') {
            unset($list[1]);
        }

        echo "Import modems ...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        $defaultContract = Contract::where('number', 1)->first();
        if (! $this->contracts) {
            $this->contracts = Contract::get()->keyBy('number');
        }

        foreach ($list as $line) {
            $bar->advance();

            $this->line = $line = str_getcsv($line, ';');
            $number = $line[3];
            $mac = str_pad($line[29], 12, '0', STR_PAD_LEFT);
            $contract = $this->contracts[$number] ?? $defaultContract;
            $configfileName = $line[31];

            if (isset($this->configfiles[$configfileName])) {
                $configfile = $this->configfiles[$configfileName];
            } else {
                $configfile = $this->addConfigfile();
            }

            $data = [
                'contract_id' => $contract->id,
                'mac' => unifyMac($mac),
                'configfile_id' => $configfile->id,
                'serial_num' => $line[30],
                'salutation' => $line[20] == 'Herr u Frau' ? 'Herr' : $line[20],
                'company' => $line[5] == 'Firma' ? $line[6] : null,
                'firstname' => $line[14],
                'lastname' => $line[13],
                'street' => $line[15],
                'house_number' => $line[16].$line[17],
                'zip' => $line[18],
                'city' => $line[19],
            ];

            Modem::createQuietly($data);
        }

        $bar->finish();
        echo "\n";
    }

    private function addConfigfile()
    {
        $name = $this->line[31];

        return $this->configfiles[$name] = Configfile::create([
            'name' => $name,
            'device' => 'cm',
            'public' => 'yes',
            'dashboard' => '/grafana/d/3-42DM6Gk/cablemodem',
            'parent_id' => $this->baseConfigfileId,
        ]);
    }

    private function importProducts()
    {
        $list = file($this->option('products'));

        // Skip first line (title "Tarifübersicht aktive Tarife Thüga")
        unset($list[0]);

        echo "Import products...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach ($list as $line) {
            $bar->advance();
            $this->line = $line = str_getcsv($line, ';');

            if (! trim($line[0] ?? '') || strtolower(trim($line[0])) == 'tarifname') {
                continue;
            }

            $prodName = trim($line[0]);
            $existingProduct = Product::where('name', $prodName)->first();

            if (! $existingProduct) {
                $this->warnings['existingProd-'.$prodName] = "Product '$prodName' not found. Skipping.";

                continue;
            }

            // Get or create cost center
            $cc = $this->costcenters[trim($line[3])] ?? null;
            if (! $cc) {
                $cc = $this->addNewCostCenter();
            }

            // Get or create booking account
            $ba = $this->bookingAccounts[trim($line[4])] ?? null;
            if (! $ba) {
                $ba = $this->addNewBookingAccount();
            }

            $data = [
                'costcenter_id' => $cc->id,
                'booking_account_id' => $ba->id,
            ];

            Product::where('name', $prodName)->update($data);
        }

        $bar->finish();
        echo "\n";
        $this->printWarnings();
    }

    private function addNewCostcenter()
    {
        $ccNumber = trim($this->line[3]);

        $cc = CostCenter::create([
            'number' => $ccNumber,
            'sepaaccount_id' => intval($ccNumber) < 100000 ? 2 : 3,
        ]);

        $this->costcenters[$ccNumber] = $cc;

        return $cc;
    }


    private function addNewBookingAccount()
    {
        $baNumber = trim($this->line[4]);

        $ba = BookingAccount::create([
            'number' => $baNumber,
        ]);

        $this->bookingAccounts[$baNumber] = $ba;

        return $ba;
    }

    private function printWarnings()
    {
        foreach ($this->warnings as $warning) {
            $this->line($warning);
        }
    }

    private function importAddtlItems()
    {
        $list = file($this->option('products'));
        unset($list[0]);

        echo "Import items...\n";
        $bar = $this->output->createProgressBar(count($list));
        $bar->start();

        foreach ($list as $line) {
            $bar->advance();
            $this->line = $line = str_getcsv($line, ';');

            $c = Contract::where('number', $line[3])->first();

            Item::create([
                'contract_id' => $c->id,
                'product_id' => 266,
                'valid_from' => date('Y-m-d', intval(strtotime(trim($line[16])))),
                'valid_from_fixed' => 1,
                'valid_to' => in_array(trim($line[17]), ['31.12.2099', '12/31/2099', '2099-12-31']) ? null : date('Y-m-d', intval(strtotime(trim($line[17])))),
                'valid_to_fixed' => 1,
                'payed_until_before_sr' => '2025-11-30',
            ]);
        }

        $bar->finish();
        echo "\n";
        $this->printWarnings();
    }
}
