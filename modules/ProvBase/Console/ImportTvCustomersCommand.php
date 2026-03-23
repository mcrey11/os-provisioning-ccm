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
use Illuminate\Support\Facades\Log;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\Ccc\Entities\CccUser;
use Modules\ProvBase\Entities\Contract;

class ImportTvCustomersCommand extends Command
{
    use \App\AddressFunctionsTrait;
    use ImportTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tv
        {file : Structured CSV file (-path) with customer data.}
        {ccContract : CostCenter ID for all the imported contracts.}
        {--charge=* : Array of charge in Euro for every TV product.}
        {--productId=* : Array of ID\'s of TV products corresponding to charge.}
        {--ccSepa= : CostCenter ID for all the sepa mandates.}
        {--ag=0 : Antenna community ID for all the imported contracts.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import Customers from CSV and add TV Tarif';

    /**
     * Column Number and Description for easy Adaption
     */
    protected const C_NR = 0;
    protected const C_NAME = 1;
    protected const C_STRASSE = 2;
    protected const C_ZIP = 3;
    protected const C_CITY = 4;
    protected const C_TEL = 5;
    protected const C_FAX = 6;
    protected const C_MAIL = 7;
    protected const C_SALUT = 13;   // Anrede (Bemerkung)
    protected const C_DESC2 = 14;   // Zusatz
    protected const C_DESC1 = 15;   // Watt
    protected const C_DESC3 = 16;   // Sonstiges
    protected const C_START = 20;   // Eintritt
    protected const C_END = 21;     // Austritt

    // Sepa Data
    protected const S_REF = 0;
    protected const S_HOLDER = 8;
    protected const S_INST = 9;
    protected const S_BIC = 10;
    protected const S_IBAN = 11;
    protected const S_VALID = 12;   // Zahlungsziel (invalid when = "14 Tage")
    protected const S_SIGNATURE = 24;

    // Item Data
    protected const TARIFF = 17;    // Umlage
    protected const CREDIT = 19;    // Verstärkergeld

    /*
     * TODO: Change product IDs according to Database and yearly Charges according to CostCenter
     */
    // mapping of Watt amount to credit
    // Watt amount => product_id
    protected const CREDITS_WATT = [
        '4,5' 	=> 51,
        5 		=> 64,
        7 		=> 62, 	// & 63
        8 		=> 53,
        '8,5' 	=> 55,
        11 		=> 65,
        14 		=> 54,
        15 		=> 61,
        16 		=> 58,
        '16,5'  => 52, // & 57
    ];

    // helper variables
    protected static $newCustomer = false;
    protected static $contract = null;
    protected static $line = [];

    protected static $option = [];
    protected static $argument = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command - Import new Contracts with TV Tariff from a CSV-File (Separator: ";")
     *
     * See order and description of Columns ahead defined by constants
     *
     * @return mixed
     */
    public function handle()
    {
        $this->validateUserInput();

        $file_arr = file($this->argument('file'));
        // skip headline
        unset($file_arr[0]);

        $num = count($file_arr);
        $bar = $this->output->createProgressBar($num);
        echo "Import TV customers\n";
        Log::info("Import potentially $num TV customers");
        $bar->start();

        // to use with static method from tinker aswell
        self::$argument = $this->argument();
        self::$option = $this->option();
        self::setCharge($this->option('charge'));

        foreach ($file_arr as $line) {
            $bar->advance();

            self::$line = str_getcsv($line, ';');
            // self::$line = str_getcsv(self::$line, "\t");

            self::$contract = $this->findOrAddContract($this->option('ag'), $this->argument('ccContract'));

            if (! self::$contract) {
                continue;
            }

            self::addTariff();
            self::addCredit();
            $this->addSepaMandate();
        }

        $bar->finish();

        self::printImportantTodos();
    }

    private static function getNumber()
    {
        return self::$line[self::C_NR];
    }

    private static function getFirstAndLastName()
    {
        $name = explode(',', self::$line[self::C_NAME]);

        return [
            'firstName' => isset($name[1]) ? trim($name[1]) : trim($name[0]),
            'lastName' => isset($name[1]) ? trim($name[0]) : '',
        ];
    }

    private static function getStreetAndHouseNumber()
    {
        $ret = self::splitStreetHousenr(self::$line[self::C_STRASSE]);

        return [
            'street' => $ret[0],
            'houseNr' => $ret[1],
        ];
    }

    private static function getCityAndDistrict()
    {
        $arr = explode(' OT ', self::$line[self::C_CITY]);

        return [
            'city' => $arr[0],
            'district' => $arr[1] ?? '',
        ];
    }

    private static function getSignatureDate()
    {
        return date('Y-m-d', strtotime(self::$line[self::S_SIGNATURE]));
    }

    private static function setCharge($charge)
    {
        self::$option['charge'] = array_map(fn ($charge) => number_format($charge, 2), $charge);
    }

    private static function getYmdDate($date)
    {
        return $date ? date('Y-m-d', strtotime($date)) : null;
    }

    /**
     * Create new Contract or return existing one
     *
     * @return object New created contract or if found the already existing one
     */
    private function findOrAddContract($contact, $ccContract)
    {
        $number = self::getNumber();
        ['firstName' => $firstName, 'lastName' => $lastName] = self::getFirstAndLastName();
        ['street' => $street, 'houseNr' => $houseNr] = self::getStreetAndHouseNumber();
        ['city' => $city, 'district' => $district] = self::getCityAndDistrict();

        $contract = self::contractExists($number, $firstName, $lastName, $street, $city, $houseNr);

        // if existing contract was found update the contact and return it
        if ($contract) {
            self::$newCustomer = false;
            if ($contact) {
                Contract::where('id', $contract->id)->update(['contact' => $contact]);
            }

            $this->setNewsletterFlag($contract);

            return $contract;
        }

        self::$newCustomer = true;

        $contract = [
            'contract_start' =>  self::getYmdDate(self::$line[self::C_START]) ?? '2000-01-01',
            'contract_end' => self::getYmdDate(self::$line[self::C_END]),
            'number' => $number,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'street' => $street,
            'house_number' => $houseNr,
            'zip' => str_pad(self::$line[self::C_ZIP], 5, '0', STR_PAD_LEFT),
            'city' => $city,
            'district' => $district,
            //'academic_degree' => self::map_academic_degree(self::$line[self::C_ACAD_DGR]),
            'salutation' => self::map_salutation(self::$line[self::C_SALUT]),
            'phone' => str_replace(['/', '-', ' '], '', self::$line[self::C_TEL]),
            'description' => self::$line[self::C_DESC1]."\n".self::$line[self::C_DESC2]."\n".self::$line[self::C_DESC3],
            'costcenter_id' => $ccContract,
            'contact' => $contact ?? null,
            'create_invoice' => true,
            'fax' => self::$line[self::C_FAX],
            'email' => self::$line[self::C_MAIL],
        ];

        // Discard contracts that ended last year
        if ($contract['contract_end'] && $contract['contract_end'] < date('Y-01-01')) {
            Log::info("Contract $number is out of date ({$contract['contract_start']} - {$contract['contract_end']})");
        }

        if (self::validationFailed(Contract::class, $contract, ['Number' => $number])) {
            return;
        }

        Log::info("Add Contract {$number}: {$firstName}, {$lastName}");

        $contract = Contract::create($contract);

        $this->setNewsletterFlag($contract);

        return $contract;
    }

    public static function map_academic_degree($string)
    {
        if (strpos($string, 'Prof') !== false) {
            return 'Prof. Dr.';
        }

        if (strpos($string, 'Dr.') !== false) {
            return 'Dr.';
        }

        return '';
    }

    public static function map_salutation($string)
    {
        if (strpos($string, 'Damen und Herren') !== false) {
            return 'Firma';
        }

        if (strpos($string, 'Herr') !== false) {
            return 'Herr';
        }

        return 'Frau';
    }

    private function setNewsletterFlag($contract)
    {
        if (self::$line[25] != 'ja') {
            return;
        }

        if (! $contract->cccUser) {
            $cccUser = (new CccUser)->setRelation('contract', $contract);
            $cccUser->contract_id = $contract->id;
            $cccUser->store();
            $contract->load('cccUser');
        } elseif ($contract->cccUser->newsletter) {
            return;
        }

        $contract->cccUser->newsletter = true;
        $contract->cccUser->save();
    }

    private static function addTariff()
    {
        $tariff = self::$line[self::TARIFF];
        $contract = self::$contract;

        if (! $tariff) {
            Log::debug("'Umlage' is zero or empty - don't add tariff");

            return;
        }

        $existing = false;
        if ($contract->items()->count()) {
            $existing = $contract->items->contains(function ($item, $value) {
                return in_array($item->product_id, self::$option['productId']);
            });
        }

        if ($existing) {
            Log::debug("Contract $contract->number already has TV Tariff assigned");

            return;
        }

        $amount = str_replace('EUR', '', $tariff);
        $amount = str_replace(',', '.', $tariff);
        $amount = number_format((float) trim($amount), 2);

        if ($amount == 0) {
            return;
        }

        $key = array_search($amount, self::$option['charge'], true);
        if ($key === false) {
            $msg = "Contract $contract->number is charged with $amount EUR. Please add Tariff manually!";
            self::$importantTodos[] = $msg;
            Log::warning($msg);

            return;
        }

        $productId = self::$option['productId'][$key];

        Item::create([
            'contract_id' 		=> $contract->id,
            'product_id' 		=> $productId,
            'valid_from' 		=> self::$line[self::C_START] ?: '2000-01-01',
            'valid_from_fixed' 	=> 1,
            'valid_to' 			=> $contract->contract_end,
            'valid_to_fixed' 	=> 1,
        ]);

        Log::info("Add TV Tariff $productId for Contract $contract->number");
    }

    private static function addCredit()
    {
        $credit = self::$line[self::CREDIT];
        $watt_amount = self::$line[self::C_DESC1];
        $contract = self::$contract;

        if (! $credit) {
            return;
        }

        $product_id = 0;
        foreach (self::CREDITS_WATT as $watt => $prod_id) {
            if ($watt_amount == $watt) {
                self::addTodo("Please check if contract $contract->number has correct credit assigned! (multiple possible)");

                $product_id = $prod_id;
                break;
            }
        }

        if (! $product_id) {
            self::addTodo("Contract $contract->number [Old Contract Nr ".self::$line[self::C_NR]."] has credit of $credit € [Watt: $watt_amount]. Please add credit manually!");

            return;
        }

        $existing = false;
        if ($contract->items) {
            $existing = $contract->items->contains('product_id', $product_id);
        }

        if ($existing) {
            Log::debug("Contract $contract->number already has Credit ".$product_id.' assigned');

            return;
        }

        // $creditAmount = str_replace('EUR', '', $credit);
        // $creditAmount = str_replace(',', '.', $credit);
        // $creditAmount = trim($creditAmount);

        if (date('Y') == date('Y', strtotime($contract->contract_start)) || date('Y') == date('Y', strtotime($contract->contract_end))) {
            self::addTodo("Please check Amplifier credit for Contract $contract->number as it's calculated partly for the year");
        }

        Item::create([
            'contract_id' 		=> $contract->id,
            'product_id' 		=> $product_id,
            'valid_from' 		=> $contract->contract_start,
            'valid_from_fixed' 	=> 1,
            'valid_to' 			=> $contract->contract_end,
            'valid_to_fixed' 	=> 1,
            // 'credit_amount' 	=> $creditAmount,
            'costcenter_id' 	=> self::$argument['ccContract'],
        ]);

        Log::info("Add Credit [Product ID $product_id] for Amplifier to Contract $contract->number");
    }

    private function addSepaMandate()
    {
        $valid = trim(self::$line[self::S_VALID]) == 'einzug';
        $contract = self::$contract;

        if (! $valid) {
            Log::debug("Contract $contract->number has no valid SepaMandate");

            // Set CostCenter for current SepaMandate in case customer pays TV charge in cash
            SepaMandate::where('contract_id', '=', $contract->id)
                ->where(function ($query) {
                    $query->whereNull('costcenter_id')->orWhere('costcenter_id', '=', 0);
                })
                ->update(['costcenter_id' => $contract->costcenter_id]);

            return;
        }

        // Check and return if SepaMandate with this IBAN currently exists and is valid
        if ($contract->sepamandates && $contract->sepamandates->contains('iban', self::$line[self::S_IBAN])) {
            $mandates = $contract->sepamandates->where('iban', self::$line[self::S_IBAN]);

            foreach ($mandates as $sm) {
                if (! $sm->valid_to || ($sm->valid_to > date('Y-m-d')) || ($sm->signature_date > self::getSignatureDate())) {
                    Log::notice("Contract $contract->number already has SEPA-mandate with IBAN ".self::$line[self::S_IBAN]);

                    return;
                }
            }
        }

        self::createSepaMandate($this->option('ccSepa'));
    }

    private static function createSepaMandate($costcenterId = null)
    {
        $contract = self::$contract;
        $signatureDate = self::getSignatureDate();

        $sepa = [
            'contract_id' => $contract->id,
            'reference' => self::$line[self::C_NR],
            'signature_date' => $signatureDate,
            'holder' => self::$line[self::S_HOLDER],
            'iban' => self::$line[self::S_IBAN],
            'bic' => self::$line[self::S_BIC],
            'institute' => self::$line[self::S_INST],
            'valid_from' => $signatureDate,
            'state' => 'RCUR',
            'costcenter_id' => $costcenterId ?? null,
        ];

        $iban = self::$line[self::S_IBAN];

        if (self::validationFailed(SepaMandate::class, $sepa, ['IBAN' => $iban])) {
            return;
        }

        SepaMandate::create($sepa);

        $costcenterId ??= 'NULL';

        Log::info("Add SepaMandate [IBAN: {$iban}] for contract {$contract->number} with Costcenter ID $costcenterId");
    }

    private function validateUserInput()
    {
        if (! $this->option('charge') || ! $this->option('productId')) {
            $this->error('Charge and/or productId options missing! Please correct this issue!');

            exit;
        }

        if (count($this->option('charge')) != count($this->option('productId'))) {
            $this->error('Number of options for charge and productId must be equal. Please correct this issue!');

            exit;
        }
    }

    /**
     * Customer specific static methods
     */

    /**
     * Add SepaMandate without costcenter_id for Internet/new customers or
     * add SepaMandate with costcenter_id to already existing customer.
     *
     * @param  string  $file
     * @param  int  $ccSepa
     * @param  int  $ag
     * @param  int  $ccContract
     * @param  array  $productId
     * @param  array  $charge  with 2 decimal places like [0 => 60.00, 1 => 0.00]
     *
     * @author Roy Schneider
     *
     * @return void
     */
    public static function manuallyAddSepaMandate($file, $ccSepa, $ag, $ccContract, $productId, $charge)
    {
        self::$option['productId'] = $productId;
        self::setCharge($charge);
        self::$argument['ccContract'] = $ccContract;
        $file = file($file);

        // remove table headers
        array_shift($file);

        $count = count($file);
        Log::info("Import potentially $count TV customers");

        foreach ($file as $line) {
            self::$line = str_getcsv($line, ';');
            self::$contract = $contract = $this->findOrAddContract($ag, $ccContract);

            self::addTariff();
            self::addCredit();

            $valid = trim(self::$line[self::S_VALID]) == 'einzug';

            if (! $valid) {
                Log::debug("Contract $contract->number has no valid SepaMandate");

                // Set CostCenter for current SepaMandate in case customer pays TV charge in cash
                SepaMandate::where('contract_id', $contract->id)
                    ->where(function ($query) {
                        $query->whereNull('costcenter_id')->orWhere('costcenter_id', 0);
                    })
                    ->update(['costcenter_id' => $contract->costcenter_id]);

                continue;
            }

            // Check and return if SepaMandate with this IBAN currently exists and is valid
            if ($contract->sepamandates) {
                $iban = self::$line[self::S_IBAN];
                if ($contract->sepamandates->contains('iban', $iban)) {
                    $mandates = $contract->sepamandates->where('iban', $iban);

                    // if is a sepa that is used for internet
                    if ($contract->where('type', 'Internet')) {
                        self::createSepaMandate();

                        continue;
                    }

                    foreach ($mandates as $sm) {
                        if (! $sm->valid_to || ($sm->valid_to > date('Y-m-d')) || ($sm->signature_date > self::getSignatureDate())) {
                            Log::notice("Contract $contract->number already has SEPA-mandate with IBAN {$iban}");

                            continue;
                        }
                    }
                }
            }

            // new customer
            if (self::$newCustomer) {
                self::createSepaMandate();

                continue;
            }

            // already existing customer
            self::createSepaMandate($ccSepa);
        }
    }
}
