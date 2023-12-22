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
use Modules\ProvBase\Entities\Contract;

class ImportTvCustomersCommand extends Command
{
    use ImportTrait;
    use \App\AddressFunctionsTrait;

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

    protected $newCustomer = false;

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
        $num = count($file_arr);
        $bar = $this->output->createProgressBar($num);

        // skip headline
        // unset($file_arr[0]);

        echo "Import TV customers\n";
        Log::info("Import potentially $num TV customers");
        $bar->start();

        foreach ($file_arr as $line) {
            $bar->advance();

            $line = str_getcsv($line, ';');
            // $line = str_getcsv($line, "\t");
            $c = $this->_add_contract($line);

            if (! $c) {
                continue;
            }

            $this->_add_tarif($c, $line);
            $this->_add_Credit($c, $line);
            $this->_add_sepa_mandate($c, $line);
        }

        $this->printImportantTodos();
    }

    /**
     * Create new Contract or return existing one
     *
     * @return object New created contract or if found the already existing one
     */
    private function _add_contract($line)
    {
        $number = $line[self::C_NR];
        $name = explode(',', $line[self::C_NAME]);
        $firstname = isset($name[1]) ? trim($name[1]) : trim($name[0]);
        $lastname = isset($name[1]) ? trim($name[0]) : '';
        $ret = self::splitStreetHousenr($line[self::C_STRASSE]);
        $street = $ret[0];
        $housenr = $ret[1];
        $arr = explode(' OT ', $line[self::C_CITY]);
        $city = $arr[0];
        $district = isset($arr[1]) ? $arr[1] : '';

        $contract = $this->contractExists($number, $firstname, $lastname, $street, $city, $housenr);
        // if existing contract was found update the contact and return it
        if ($contract) {
            $this->newCustomer = false;
            if ($this->option('ag')) {
                Contract::where('id', $contract->id)->update(['contact' => $this->option('ag')]);
            }

            return $contract;
        }

        $this->newCustomer = true;

        // Add new contract
        $contract = new Contract;

        $contract->contract_start = $line[self::C_START] ? date('Y-m-d', strtotime($line[self::C_START])) : '2000-01-01';
        $contract->contract_end = $line[self::C_END] ? date('Y-m-d', strtotime($line[self::C_END])) : null;

        // Discard contracts that ended last year
        if ($contract->contract_end && ($contract->contract_end < date('Y-01-01'))) {
            Log::info("Contract $number is out of date ($contract->contract_start - $contract->contract_end)");

            return;
        }

        $contract->number = $number;
        $contract->firstname = $firstname;
        $contract->lastname = $lastname;
        $contract->street = $street;
        $contract->house_number = $housenr;
        $contract->zip = str_pad($line[self::C_ZIP], 5, '0', STR_PAD_LEFT);
        $contract->city = $city;
        $contract->district = $district;

        // $contract->academic_degree = self::map_academic_degree($line[self::C_ACAD_DGR]);
        $contract->salutation = self::map_salutation($line[self::C_SALUT]);
        $contract->phone = str_replace(['/', '-', ' '], '', $line[self::C_TEL]);
        $contract->description = $line[self::C_DESC1]."\n".$line[self::C_DESC2]."\n".$line[self::C_DESC3];
        $contract->costcenter_id = $this->argument('ccContract'); 		// Dittersdorf=1
        if ($this->option('ag')) {
            $contract->contact = $this->option('ag');
        }
        $contract->create_invoice = true;

        $contract->fax = $line[self::C_FAX];
        $contract->email = $line[self::C_MAIL];
        // $contract->birthday 	= $contract->geburtsdatum;

        // Set null-fields to '' to fix SQL import problem with null fields
        $relations = $contract->relationsToArray();
        $nullable = ['contract_end'];
        foreach ($contract->toArray() as $key => $value) {
            if (array_key_exists($key, $relations) || in_array($key, $nullable)) {
                continue;
            }

            if ($contract->{$key} == null) {
                $contract->{$key} = '';
            }
        }

        $contract->deleted_at = null;
        // Update or Create Entry
        $contract->save();

        // Log
        Log::info("Add Contract $contract->number: $contract->firstname, $contract->lastname");

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

    private function _add_tarif($contract, $line)
    {
        $tariff = $line[self::TARIFF];

        if (! $tariff) {
            Log::debug("'Umlage' is zero or empty - don't add tariff");

            return;
        }

        $existing = false;
        if ($contract->items()->count()) {
            $existing = $contract->items->contains(function ($item, $value) {
                return in_array($item->product_id, $this->option('productId'));
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

        $key = array_search($amount, $this->option('charge'), true);
        if ($key === false) {
            $msg = "Contract $contract->number is charged with $amount EUR. Please add Tariff manually!";
            $this->importantTodos[] = $msg;
            Log::warning($msg);

            return;
        }

        $productId = $this->option('productId')[$key];

        Item::create([
            'contract_id' 		=> $contract->id,
            'product_id' 		=> $productId,
            'valid_from' 		=> $line[self::C_START] ?: '2000-01-01',
            'valid_from_fixed' 	=> 1,
            'valid_to' 			=> $contract->contract_end,
            'valid_to_fixed' 	=> 1,
        ]);

        Log::info("Add TV Tariff $productId for Contract $contract->number");
    }

    private function _add_Credit($contract, $line)
    {
        $credit = $line[self::CREDIT];
        $watt_amount = $line[self::C_DESC1];

        if (! $credit) {
            return;
        }

        $product_id = 0;
        foreach (self::CREDITS_WATT as $watt => $prod_id) {
            if ($watt_amount == $watt) {
                $this->importantTodos[] = "Please check if contract $contract->number has correct credit assigned! (multiple possible)";

                $product_id = $prod_id;
                break;
            }
        }

        if (! $product_id) {
            $this->importantTodos[] = "Contract $contract->number [Old Contract Nr ".$line[self::C_NR]."] has credit of $credit € [Watt: $watt_amount]. Please add credit manually!";

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
            $this->importantTodos[] = "Please check Amplifier credit for Contract $contract->number as it's calculated partly for the year";
        }

        Item::create([
            'contract_id' 		=> $contract->id,
            'product_id' 		=> $product_id,
            'valid_from' 		=> $contract->contract_start,
            'valid_from_fixed' 	=> 1,
            'valid_to' 			=> $contract->contract_end,
            'valid_to_fixed' 	=> 1,
            // 'credit_amount' 	=> $creditAmount,
            'costcenter_id' 	=> $this->option('ccContract'),
        ]);

        Log::info("Add Credit [Product ID $product_id] for Amplifier to Contract $contract->number");
    }

    private function _add_sepa_mandate($contract, $line)
    {
        $valid = trim($line[self::S_VALID]) == 'einzug';

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

        $signature_date = date('Y-m-d', strtotime($line[self::S_SIGNATURE]));

        // Check and return if SepaMandate with this IBAN currently exists and is valid
        if ($contract->sepamandates && $contract->sepamandates->contains('iban', $line[self::S_IBAN])) {
            $mandates = $contract->sepamandates->where('iban', $line[self::S_IBAN]);

            foreach ($mandates as $sm) {
                if (! $sm->valid_to || ($sm->valid_to > date('Y-m-d')) || ($sm->signature_date > $signature_date)) {
                    Log::notice("Contract $contract->number already has SEPA-mandate with IBAN ".$line[self::S_IBAN]);

                    return;
                }
            }
        }

        $data = [
            'contract_id'     => $contract->id,
            'reference'     => $line[self::C_NR],
            'signature_date' => $signature_date,
            'holder'        => $line[self::S_HOLDER],
            'iban'            => $line[self::S_IBAN],
            'bic'             => $line[self::S_BIC],
            'institute'     => $line[self::S_INST],
            'valid_from'     => date('Y-m-d', strtotime($line[self::S_SIGNATURE])),
            'state'         => 'RCUR',
            'costcenter_id' => $this->option('ccSepa'),
            // 'valid_to' 	=> NULL,
        ];

        $validator = \Validator::make($data, (new SepaMandate)->rules());

        if ($validator->fails()) {
            $this->importantTodos[] = "Cannot add SepaMandate with IBAN {$line[self::S_IBAN]} because of invalid data: ".implode(', ', $validator->errors()->all());

            return;
        }

        SepaMandate::create($data);

        Log::info('Add SepaMandate [IBAN: '.$line[self::S_IBAN]."] for contract $contract->number");
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
}
