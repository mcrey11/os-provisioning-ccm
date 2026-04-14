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

use App\AddressFunctionsTrait;
use App\ImportTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Modules\BillingBase\Entities\BookingAccount;
use Modules\BillingBase\Entities\CostCenter;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\Product;
use Modules\BillingBase\Entities\SepaMandate;
use Modules\PropertyManagement\Entities\Apartment;
use Modules\PropertyManagement\Entities\Node;
use Modules\PropertyManagement\Entities\Realty;
// use Modules\ProvBase\Entities\Address;
use Modules\ProvBase\Entities\Configfile;
use Modules\ProvBase\Entities\Contract;
use Modules\ProvBase\Entities\Modem;
use Modules\ProvVoip\Entities\Mta;
use Modules\ProvVoip\Entities\Phonenumber;
use stdClass;

class ImportPinetCommand extends Command
{
    use AddressFunctionsTrait, ImportTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pinet
        {product-mapping-file : PHP file with Pinet product posting groups to nmsprime product type mappings.}
        {salutation-mapping-file : PHP file with Pinet salutations to nmsprime salutation mappings.}
        {--T|test : Don\'t geocode contract and modem addresses as this takes huge time and costs money.}
    ';

    protected $description = 'Import data from Pinet/Navision mssql database';

    protected $baseConfigFile;

    protected $bookingAccounts;

    protected $cities = [];

    protected $contractDates = [];

    protected $customers = [];

    protected $defaultMtaCf = null;

    protected $genericProducts = [];

    protected $missingCounts = [];

    protected $missingDates = 0;

    protected $newConfigfiles = [];

    protected $newCostCenterIds = [];

    protected $newProducts = [];

    protected $newRealEstates = [];

    protected $nodes = [];

    protected $possibleServices = [];

    protected $prodGroups = [];

    protected $prodTypes = [];

    protected $salutations = [];

    protected $streets = [];

    protected $warnings = [];

    protected $connectionTypes = [
        0 => 'Virtuell/BH',
        1 => 'Kabelanschluss', // Kabel_DS30
        2 => 'Satellitenanschluss', // Kabel/SAT_onlyTV
        3 => 'Kabel-/Satellitenanschluss', // FTTB_GPON
        4 => 'DSL-Anschluss', // DSL-TAL-Anschluss
        5 => 'LWL-Anschluss', // FTTH_P2P
        6 => 'Funkanschluss',
        7 => 'L2BSA', // L2/L3BSA
        8 => 'L3BSA',
    ];

    protected $cycleMappings = [
        '' => 'Monthly',
        'EINM' => 'Once',
        'JAHR' => 'Yearly',
        'M' => 'Monthly',
        'MTL' => 'Monthly',
        'OBJEKT' => 'Once',
        'PSCH' => 'Once',
        'QUARTAL' => 'Quarterly',
        'STK' => 'Once',
        'WE' => 'Monthly',
    ];

    public function handle()
    {
        // $this->check();
        // $this->generateSchema();

        $this->loadNecessaryData();

        $this->importCostCenters();
        $this->createConfigfiles();

        $this->importNodes();
        $this->importRealEstates();

        $this->importApartmentsAndContracts();
        $this->importModems();
        $this->setCustomerData();
        $this->printWarnings();

        $this->importSepaMandates();
        $this->setContractDescription();
        $this->importPhonenumbers();
        $this->printWarnings();

        $this->importBookingAccounts();
        $this->importProducts();
        $this->importItems();
        $this->printWarnings();
    }

    private function loadNecessaryData()
    {
        echo __FUNCTION__."...\n";

        $this->baseConfigFile = Configfile::where('device', 'cm')->whereRaw("lower(name) like 'base%'")->orderBy('id')->first();
        $this->defaultMtaCf = Configfile::where('device', 'mta')->whereRaw("lower(name) like 'base%'")->orderBy('id')->first();

        if (! $this->defaultMtaCf) {
            exit("Missing base MTA configfile as default for dummy MTAs used for phonenumber import. Stop here.\n");
        }

        $this->loadContractDates();
        $this->loadStreets();
        $this->loadCities();
        $this->loadCustomers();
        $this->loadPossibleServices();
        $this->loadProductPostingGroups();
        $this->prodTypes = include $this->argument('product-mapping-file');
        $this->salutations = include $this->argument('salutation-mapping-file');
    }

    private function generateSchema()
    {
        $fn = '/tmp/mssql-db-content.csv';
        $tables = DB::connection('mssql-navdb')->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE'
            and TABLE_NAME like 'Kirst & Schulze BK GmbH$%'
        ");

        $tables = DB::connection('mssql-navdb')->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME like 'Kirst & Schulze BK GmbH$%'");

        file_put_contents($fn, '');
        $writeHeadline = true;

        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        foreach ($tables as $table) {
            $bar->advance();
            $tableName = $table->TABLE_NAME;

            if (\Str::contains($tableName, ['(', ')'])) {
                continue;
            }

            try {
                $entries = DB::connection('mssql-navdb')->table($tableName)->limit(60)->count();
            } catch (\Throwable $th) {
                dd($table, DB::connection('mssql-navdb')->table($tableName)->toSql());
            }

            if ($entries < 50) {
                continue;
            }

            $colDefs = DB::connection('mssql-navdb')->select("SELECT ORDINAL_POSITION, COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE
                FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tableName'");

            // $colDefs = DB::connection('mssql-navdb')->table($tableName)->limit(5)->get();

            $file = fopen($fn, 'a');

            if ($writeHeadline) {
                // dd($colDefs[0], json_encode($colDefs[0]));
                $headline = array_keys(json_decode(json_encode($colDefs[0]), true));
                fputcsv($file, $headline);
                $writeHeadline = false;
            }

            fwrite($file, "\n$tableName\n");

            foreach ($colDefs as $colDef) {
                $colDef = json_decode(json_encode($colDef), true);
                fputcsv($file, $colDef);
            }
        }

        $bar->finish();
        echo "\nNew file $fn\n";
        exit;
    }

    private function loadStreets()
    {
        $table = 'Street';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $this->streets = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->where('Possible Service', '!=', '10')
            // ->limit(100)
            ->select(
                'Code',
                'Description',
                'Post Code',
                'City',
                'Possible Service'
            )
            ->get()
            ->keyBy('Code');

        // d('Streets', $structure, $this->streets);
    }

    private function loadCities()
    {
        $table = 'City';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $this->cities = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->where('Possible Service', '!=', '10')
            // ->limit(10)
            ->select(
                'Code',
                'Description',
            )
            ->get()
            ->keyBy('Code');

        // d('cities', $structure, $this->cities);
    }

    private function loadPossibleServices()
    {
        $table = 'HP Possible Services';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $this->possibleServices = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->where('Possible Service', '!=', '10')
            // ->limit(100)
            ->select(
                'Code',
                'Description',
            )
            ->get()
            ->keyBy('Code');

        // dd('Possible Services', $structure, $this->possibleServices);
    }

    private function import()
    {
        $table = '';
        $col = '';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table);
        // ->where('No_', '>', 1386)
        // ->limit(10);

        if ($col) {
            $objects = $objects->select($col)->groupBy($col)->pluck($col);
        } else {
            $objects = $objects->select(
                // 'No_',
                // '',
            )->get();
        }

        // d('objects', $structure, $objects);

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        foreach ($objects as $obj) {
            Object::create([

            ]);
        }

        $bar->finish();
    }

    private function check()
    {
        $table = 'General Posting Setup';
        // $table = 'Gen_ Product Posting Group';
        $col = '';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            ->where('Sales Account', '!=', '')
            ->whereNotNull('Sales Account');
        // ->limit(10);

        if ($col) {
            $objects = $objects->select($col)->groupBy($col)->pluck($col);
        } else {
            $objects = $objects->select(
                // 'No_',
                // '',
            )->get();
        }

        d($table, $structure, $objects);
    }

    private function loadContractDates()
    {
        $table = 'Billing Contract';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $this->contractDates = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->where('Valid from', '!=', '1753-01-01 00:00:00.000')
            // ->where('Partner No_', '20790')
            // ->where('No_', '304002')
            // ->limit(100)
            ->select(
                'No_', // Relation to Contract ?
                'Valid from',
                'Valid to',
                'Partner No_', // Relation to Customer ?
            )
            ->get()
            ->keyBy('No_');

        // d('contractdates', $structure, $this->contractDates);
    }

    private function loadCustomers()
    {
        $fullCustomerStructure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$Customer')->first();
        $col = '';

        $query = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$Customer');
        // ->where('Tax Liable', '!=', '0')
        // ->where('No_', '=', 1391)
        // ->limit(10)

        if ($col) {
            $this->customers = $query->select($col)->groupBy($col)->pluck($col);
        } else {
            $this->customers = $query->select(
                'No_', // = 'Participant No_' in Contract / Billing Header
                'Salutation Code',
                'Name',
                'Search Name',
                'Address',
                'Address 2',
                'Post Code',
                'City',
                'County',
                'Phone No_',   // Add to contract PN if different
                'Phone No_ 2', // Add to contract PN if different
                'Contact',
                'E-Mail',
                'Birthdate', // Set to null if 1753-01-01
                // 'Tax Liable', // Always '0'
                // 'Budgeted Amount', // Always '.00000000000000000000'
            )->get()
                ->keyBy('No_');
        }

        // d('Customers', $fullCustomerStructure, $this->customers);
    }

    private function loadProductPostingGroups()
    {
        $table = 'General Posting Setup';
        // $table = 'Gen_ Product Posting Group';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $this->prodGroups = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            ->select()
            ->get()
            // ->keyBy('Code');
            ->keyBy('Gen_ Prod_ Posting Group');

        // d($table, $structure, $this->prodGroups);
    }

    private function createConfigfiles()
    {
        $table = 'Billing Modem Data';

        echo __FUNCTION__."...\n";

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            ->groupBy('Contract Name')
            ->pluck('Contract Name');

        // d('Configfiles', $objects);

        foreach ($objects as $cf) {
            $ret = preg_match('/\d{4}/', $cf, $matches);

            if (! $ret) {
                $this->warnings['cfWithout4Digits-'.$cf] = "Configfile $cf doesn't have 4 digits. Skip adding configfile.";

                continue;
            }

            $name = $matches[0];

            $data = [
                'name' => $name,
                'device' => 'cm',
                'public' => 'yes',
                'parent_id' => 1,
                'dashboard' => '/grafana/d/3-42DM6Gk/cablemodem',
            ];

            $this->newConfigfiles[$name] = Configfile::firstOrCreate($data);
        }
    }

    private function importApartmentsAndContracts()
    {
        echo __FUNCTION__."...\n";

        $col = '';
        // $col = 'Contract No_';
        $fullContractStructure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$Billing Header')->first();
        $query = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$Billing Header');
        // ->where('Participant No_', '1004')
        // ->where('Contract No_', '304002')
        // ->where('Connection Type', '1')
        // ->whereIn('No_', ['A13793']) // A26892 = stillgelegt
        // ->whereIn('No_', ['A15834', 'A10315', 'A10771', 'A26351', 'A17581', 'A17601', 'A27409']);
        // ->whereIn('Contract No_', ["306235","306856","305574","306445","305127"]) // A26892 = stillgelegt
        // ->limit(100)

        // Kabel_DS30 => A15834
        // DSL-TAL-Anschluss => A10315
        // Kabel/SAT_onlyTV => A10771
        // FTTB_GPON => A26351
        // FTTH_P2P => A17581
        // L2/L3BSA => A17601
        // Virtuell/BH => A27409

        if ($col) {
            // $contracts = $query->select($col)->groupBy($col)->pluck($col);
            $contracts = $query->selectRaw('COUNT(*), [Contract No_]')->having(DB::raw('COUNT(*)'), '>', 2)->groupBy($col)->pluck($col);
        } else {
            $contracts = $query->select(
                'No_', // = "Adapter No_" in item / Billing Line
                'Contract No_',
                'Activation Date', // Vertragsstart
                'Participant Name',
                'Participant Address',
                'Participant Address 2',
                'Patricipant City',
                'Payer Name',
                'Payer Address 2',
                'Search Name',
                'Internet Part_ Name',
                'Participant No_', // = 'No_' in Customer
                'Internet Participant No_',
                'Owner No_',
                'Payer No_', // Has sth to do with SEPA-Mandate?
                'Object No_', // Relation to Real estate
                'Internet Start Date',
                'Internet End Date',
                'Cancellation Date',
                'Bell',
                'Head Station Code',
                'Street No_',
                'House No_',
                'Street Description',
                'Shortcut Dimension 1 Code',
                'Part_ Phone No_', // Take it from customer or from here?
                'Birthdate',
                'Adapter State',
                'Adapter Mode',
                'Connection Type',
                'Client',
                'Top No_', // = Wohnungsnr
                'Amplifier',
                'No_ Series',
            )
                ->get();
        }

        // d('contracts', $fullContractStructure, $contracts);

        // In case previous function was commented out during this run due to tests, but did run before - load previously imported real estates
        if (! $this->newRealEstates) {
            $this->newRealEstates = Realty::get()->keyBy('number');
        }

        $bar = $this->output->createProgressBar(count($contracts));
        $bar->start();

        foreach ($contracts as $c) {
            $bar->advance();
            $lastname = explode(' ', $c->{'Participant Name'});
            $lastname = end($lastname);
            $street = $this->streets[$c->{'Street No_'}] ?? null;
            $city = $street && isset($this->cities[$street->City]) ? $this->cities[$street->City] : null;

            if (! $street) {
                // $hasMatch = preg_match('/[^\d ]*/', $c->{'Participant Address 2'}, $matches);
                // $address = $hasMatch ? $matches[0] : null;
                $city = $street = (new stdClass);
                $street->Description = $c->{'Street Description'};
                $street->{'Post Code'} = null;
                $city->Description = $c->{'Patricipant City'};
            }

            $apartment = $this->addApartment($c);

            $dates = $this->contractDates[$c->{'Contract No_'}] ?? null;
            if (! $dates) {
                $this->missingDates += 1;
            }

            $ccId = $this->newCostCenterIds[$c->{'Shortcut Dimension 1 Code'}] ?? null;

            if (! $ccId) {
                continue;

                if (! isset($this->missingCounts['costcenters'][$c->{'Shortcut Dimension 1 Code'}])) {
                    $this->missingCounts['costcenters'][$c->{'Shortcut Dimension 1 Code'}] = 0;
                }

                $this->missingCounts['costcenters'][$c->{'Shortcut Dimension 1 Code'}] += 1;
                $this->warnings['missingCc-'.$c->{'Shortcut Dimension 1 Code'}] = 'Costcenter '.$c->{'Shortcut Dimension 1 Code'}.' does not exist. Skip adding '
                    .$this->missingCounts['costcenters'][$c->{'Shortcut Dimension 1 Code'}].' contracts';
                // dd('date', $c, $fullContractStructure);
            }

            // $customer = $this->customers[$c->{'Participant No_'}];

            // Stillgelegt
            if (! $c->{'Contract No_'} || $c->{'Adapter State'} == '2') {
                continue;
            }

            $data = [
                'number' => $c->{'Contract No_'}, // Contract nr
                'number2' => $c->{'No_'}, // Anschlussnr
                'number3' => $c->{'Participant No_'}, // Customer nr
                'firstname' => trim(str_replace($lastname, '', $c->{'Participant Name'})),
                'lastname' => $lastname,
                'street' => $street->Description,
                'house_number' => $c->{'House No_'},
                'zip' => $street->{'Post Code'},
                'city' => $city?->Description,
                'apartment_id' => $apartment?->id,
                'birthday' => $c->{'Birthdate'},
                // 'district' => $c->{''},
                'phone' => $c->{'Part_ Phone No_'},
                'costcenter_id' => $ccId,
                // 'apartment_id' => $c->{''},
                'contract_start' => $this->convertDate($dates?->{'Valid from'}),
                'contract_end' => $this->convertDate($dates?->{'Valid to'}),
            ];

            if ($this->option('test')) {
                $data['lat'] = 50;
                $data['lng'] = 50;
            }

            $newContract = Contract::create($data);
        }

        $bar->finish();
        echo "\n";
    }

    private function addApartment($c)
    {
        $realty = $this->newRealEstates[$c->{'Object No_'}] ?? null;

        if (! $realty) {
            $this->warnings['missingObj-'.$c->{'Object No_'}] = 'RealEstate '.$c->{'Object No_'}.' does not exist. Skip adding Apartment '.$c->{'No_'};

            return;
        }

        return Apartment::firstOrCreate([
            'realty_id' => $realty->id,
            'connection_type' => $this->connectionTypes[$c->{'Adapter Mode'}] ?? null,
            'code' => $c->{'No_'}, // Anschlussnr
            // 'floor' => ,
            'number' => $c->{'Top No_'},
        ]);
    }

    private function mapSalutation($salutation)
    {
        // 26 different ones: "AN DEN","WEG","HERR/FRAU","VEREIN","DR.","HERR","SCHULE","SUPER","DIPL. STOM","GRUNDST","FA.","FÜR DIE","RA","AN",
        // "AN DAS","KÖNIGSBRÜC","","AN DIE","WOHNGEMEIN","PROF. DR.","HÄDICKE","BETREUUNG","DRK","FAM.","FRAU","DR.MED."
        if (! isset($this->salutations[$salutation])) {
            $this->warnings['missingSalutation-'.$salutation] = 'Salutation mapping for '.$salutation.' is missing';

            return;
        }

        return $this->salutations[$salutation];
    }

    private function mapProductType($prodPostGroup)
    {
        if (! isset($this->prodTypes[$prodPostGroup])) {
            $this->warnings['missingProdPostGroup-'.$prodPostGroup] = 'Product type mapping for '.$prodPostGroup.' is missing. Cant set product type.';

            return;
        }

        return $this->prodTypes[$prodPostGroup];
    }

    private function setCustomerData()
    {
        echo __FUNCTION__."...\n";

        $bar = $this->output->createProgressBar(count($this->customers));
        $bar->start();

        foreach ($this->customers as $customer) {
            // dd($customer);
            $bar->advance();

            if (! $customer->{'No_'}) {
                continue;
            }

            $pns = $this->composePns([$customer->{'Phone No_'}, $customer->{'Phone No_ 2'}]);

            $data = [
                'additional' => $customer->{'Address'},
                'salutation' => $this->mapSalutation($customer->{'Salutation Code'}),
                'email' => $customer->{'E-Mail'} ?: null,
                'birthday' => $this->convertDate($customer->Birthdate),
                'phone' => $pns,
            ];

            [$street, $housenr] = self::splitStreetHousenr($customer->{'Address 2'});
            if ($street) {
                $data['street'] = $street;
            }
            if ($housenr) {
                $data['house_number'] = $housenr;
            }
            if ($customer->{'Post Code'}) {
                $data['zip'] = $customer->{'Post Code'};
            }
            if ($customer->{'City'}) {
                $data['city'] = $customer->{'City'};
            }
            if ($customer->{'Name'}) {
                $lastname = explode(' ', $customer->Name);
                $data['lastname'] = end($lastname);
                $data['firstname'] = trim(str_replace($data['lastname'], '', $customer->Name));
            }

            Contract::where('number3', $customer->{'No_'})->update($data);
        }

        $bar->finish();
        echo "\n";
    }

    private function composePns($pns)
    {
        $phonenrs = [];

        foreach ($pns as $pn) {
            if ($pn) {
                $phonenrs[] = $pn;
            }
        }

        return implode(',', $phonenrs);
    }

    private function importBookingAccounts()
    {
        echo __FUNCTION__."...\n";

        $table = 'G_L Account';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->where('No_', '=', '0652')
            // ->where('Name', '=', 'Kabel TV')
            // ->where('Gen_ Prod_ Posting Group', '=', 'Kabel TV')
            // ->join('Kirst & Schulze BK GmbH$G_L Entry as accrec', 'Kirst & Schulze BK GmbH$'.$table.'.No_', 'accrec.G_L Account No_')
            // ->limit(100)
            ->select(
                'No_',
                'Name',
                'Account Type', // 0,1,3,4 ??
                'Blocked',
                'Gen_ Prod_ Posting Group',
            )
            ->get();

        // d('BookingAccounts', $structure, $objects);

        $table = 'G_L Entry';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();
        // $accRecs = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
        //     ->limit(10)
        //     ->get();

        // d('Accounting Records', $structure, $accRecs);

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        foreach ($objects as $ba) {
            $accRecExists = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$G_L Entry')
                ->where('G_L Account No_', $ba->{'No_'})
                ->limit(1)
                ->count();

            if (! $accRecExists) {
                continue;
            }

            $this->bookingAccounts[$ba->{'No_'}] = BookingAccount::create([
                'name' => $ba->Name,
                'number' => $ba->{'No_'},
            ]);
        }

        $bar->finish();
        echo "\n";
    }

    private function importProducts()
    {
        echo __FUNCTION__."...\n";

        $table = 'Item';
        $col = '';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->where('No_', 'not like', 'ALT%')->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->where('No_', 'not like', 'ALT%');
        // ->whereIn('No_', ['T13700', '30007', '10220'])
        // ->limit(10)

        if ($col) {
            $objects = $objects->select($col)->groupBy($col)->pluck($col);
        } else {
            $objects = $objects->select(
                'No_',
                'Description',
                'Base Unit of Measure', // "EINM", "JAHR", "M", "MTL", "OBJEKT", "PSCH", "QUARTAL", "STK", "WE"
                // 'Inventory Posting Group', // LAGER|LEISTUNG
                'Unit Price',
                'Price Includes VAT',
                'Gen_ Prod_ Posting Group', // 41 different ones => Type Produktbuchungsgruppe
                // "0% SIGNAL","0% STROM","BBSA","BC","BEREIT","BEREITORG","BKZ","BMS","BMSORG","BNET","BNETORG","BSA","BSASONST","BSERVICE",
                // "BSV","BTEL", "BTELORG","BTV","DIENSTL","DUO","INFRAORG","INTER$13B","IPTV","KABELTV","MIETEBMS","MIETENET","MIETETEL","MIETETV",
                // "NET","NETSONST","PROVI", "REGIO","SATTV","SERVICE","TEL","TELEFONART","TELSONST","TRIO","WARE19","WAREORG","ZUSATZL",

                'Item Category Code', // 60 different ones
                // '0% SIGNAL', '0% STROM', 'AKTIONIPTV', 'AKTIONNET', 'BBSA', 'BBSABEREIT', 'BC', 'BEREIT_SYM', 'BEREITNET', 'BEREITORG',
                // 'BEREITTEL', 'BEREITTV', 'BKZ', 'BMS', 'BMSEINR', 'BMSORG', 'BNET', 'BNET_SYM', 'BNET-ASYM', 'BNETORG', 'BSA', 'BSASONST',
                // 'BSERVICE', 'BSV', 'BSV_DF', 'BSVBREIT', 'BTEL', 'BTELBEREIT', 'BTELORG', 'CATV', 'CATVZI', 'DIENSTL', 'DUO', 'HW_MIETETV',
                // 'HWNET_BK', 'HWNET_PK', 'HWTEL_BK', 'HWTEL_PK', 'HWTV_PK', 'HWWLANPK', 'INFRAORG', 'IPTV', 'KABELTV', 'MIET_NET_B',
                // 'MIET_NET_P', 'MIET_TEL_B', 'MIETEBMS', 'MIETENET', 'NET', 'NETSONST', 'PRÄMIE', 'REGIO', 'SATTV', 'SERVICE', 'TEL',
                // 'TELSONST', 'TRIO', 'WARE19', 'WAREORG', 'ZUSATZL',
                'Blocked',
                'VAT Prod_ Posting Group', // MWST ja/nein
                'Period',
                'Days of Period',
                // 'From Date', // Always '1753-01-01 00:00:00.000'
                // 'Until Date', // Always '1753-01-01 00:00:00.000'
                // 'Tax Group Code', // Always ''
            )
                ->get();
        }

        // d('Products', $structure, $objects);

        $bar = $this->output->createProgressBar($objects->count());
        $bar->start();

        foreach ($objects as $product) {
            $bar->advance();
            $price = round($product->{'Unit Price'}, 4);
            if ($product->{'Price Includes VAT'} && $product->{'VAT Prod_ Posting Group'} == 'MWST19') {
                $price = round(floatval($price) / 1.19, 4);
            }

            $cycle = $this->cycleMappings[$product->{'Base Unit of Measure'}];
            $bookingAccount = $this->getBookingAccountByProdGroup($product->{'Gen_ Prod_ Posting Group'});

            $newProd = Product::create([
                'name' => $product->{'Description'},
                'billing_cycle' => $cycle,
                'type' => $this->mapProductType($product->{'Gen_ Prod_ Posting Group'}),
                'booking_account_id' => $bookingAccount?->id, // Artikel -> Produktbuchungsgruppe = Gen_ Prod_ Posting Group -> General Posting Setup -> Erlöskonto = G_L Account
                // 'costcenter_id' => $product->{''},
                'price' => $price,
                'tax' => $product->{'VAT Prod_ Posting Group'} == 'MWST19' ? true : false, // MWST19|KEINEMWST
                'proportional' => $cycle != 'Once',
                'deprecated' => $product->{'Blocked'},
                'description' => $product->{'No_'}, // Artikelnr - Referenz zu item
            ]);

            $this->newProducts[$product->{'No_'}] = $newProd;
        }

        $bar->finish();
        echo "\n";
    }

    // Get correct booking account
    private function getBookingAccountByProdGroup($groupName)
    {
        if (! $this->bookingAccounts) {
            $this->bookingAccounts = BookingAccount::get()->keyBy('number');
        }

        if (! isset($this->prodGroups[$groupName])) {
            $this->warnings['missingProdGroup-'.$groupName] = 'Product Group '.$groupName.' is missing. Can\'t set or compare booking account for product or item.';

            return;
        }

        $prodGroup = $this->prodGroups[$groupName];

        if (! isset($this->bookingAccounts[$prodGroup->{'Sales Account'}])) {
            $this->warnings['missingBookingAcc-'.$prodGroup->{'Sales Account'}] = 'Booking Account '.$prodGroup->{'Sales Account'}.' is missing. Can\'t set or compare booking account for product or item.';

            return;
        }

        return $this->bookingAccounts[$prodGroup->{'Sales Account'}];
    }

    private function importItems()
    {
        echo __FUNCTION__."...\n";

        $fullItemStructure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$Billing Line')->first();

        $items = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$Billing Line')
            // ->where('Adapter No_', '=', 'A10016')
            // ->where('Description', 'IPv4-Adresse')
            ->where('Billing', 1)
            ->select(
                'Adapter No_', // = 'No_' in contract
                'Line No_',
                'Billing',
                'Type',
                'No_', // Reference to Product
                'Description',
                'Quantity',
                'Unit Price',
                'Unit Cost (LCY)',
                'VAT %',
                'VAT Prod_ Posting Group',
                'Gen_ Prod_ Posting Group',
                'Line Amount',
                'Line Amount incl_ VAT',
                'Payer No_',
                'Cleared until',
                'Cancellation Date',
                'Closing Date',
                'Billing Date until',
                'Last Billing',
                'Payer Name',
                'Object No_',
                'Contract No_',
                'Street No_',
                'House No_',
                'Contract Name', // Posten Start + ggf. Laufzeiten; wenn leer, dann 1.1.2000
            )
            // ->limit(100)
            // ->groupBy('Base Unit of Measure')
            // ->where('Gen_ Prod_ Posting Group', 'WARE19')
            // ->whereRaw('"Sales Unit of Measure" != "Base Unit of Measure"')
            ->get();

        // d('Items', $fullItemStructure, $items);

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $item) {
            $bar->advance();
            $contracts = Contract::where('number2', $item->{'Adapter No_'})->get();

            if (! $item->{'No_'}) {
                $this->warnings['itemWithoutProdRef-'.$item->{'Description'}] = 'Item '.$item->{'Description'}.' has no product reference (Artikelnr). Skip Adding item.';

                continue;
            }

            $newProduct = $this->newProducts[$item->{'No_'}] ?? null;

            $useGenericProduct = false;
            if (! $newProduct) {
                // $taxed = $item->{'VAT Prod_ Posting Group'} == 'MWST19' ? 1 : 0;
                // $newProduct = $this->genericProducts[$taxed];
                // $useGenericProduct = true;

                if (! isset($this->missingCounts['Products'][$item->{'No_'}])) {
                    $this->missingCounts['Products'][$item->{'No_'}] = 0;
                }

                $this->missingCounts['Products'][$item->{'No_'}] += 1;
                $this->warnings['missingProduct-'.$item->{'No_'}] = 'Product '.$item->{'No_'}.' does not exist. Skip adding '
                    .$this->missingCounts['Products'][$item->{'No_'}].' items';

                continue;
            }

            $bookingAccount = $this->getBookingAccountByProdGroup($item->{'Gen_ Prod_ Posting Group'});
            if ($newProduct->booking_account_id != $bookingAccount?->id) {
                $this->warnings['itemBookingAccMissmatch-'.$item->{'Adapter No_'}.$item->{'Line No_'}] = 'Item '.$item->{'Description'}.' of contract/Anschluss '.$item->{'Adapter No_'}.' has different booking account (Group: '.$item->{'Gen_ Prod_ Posting Group'}.') than its product ('.$newProduct->bookingAccount?->name.')';
            }

            $price = round(floatval($item->{'Unit Price'}), 4);

            foreach ($contracts as $contract) {
                Item::withoutEvents(function () use ($contract, $newProduct, $item, $price, $useGenericProduct) {
                    Item::create([
                        'contract_id' => $contract->id,
                        'product_id' => $newProduct->id,
                        'valid_from' => $contract->contract_start,
                        'valid_from_fixed' => 1,
                        'valid_to' => $this->convertDate($item->{'Cancellation Date'}),
                        'valid_to_fixed' => 1,
                        // 'Cleared until' ('Cancellation Date', 'Closing Date', 'Billing Date until', 'Last Billing')
                        'payed_until_before_sr' => Carbon::createFromTimestamp(strtotime($this->convertDate($item->{'Cleared until'})))->endOfMonth()->toDateString(),
                        'credit_amount' => $price != $newProduct->price ? $price : null,
                        'count' => intval($item->{'Quantity'}),
                        'accounting_text' => $useGenericProduct || ($price != $newProduct->price) ? $item->{'Description'} : null,
                    ]);
                });
            }
        }

        $bar->finish();
        echo "\n";
    }

    private function importModems()
    {
        echo __FUNCTION__."...\n";

        $table = 'Billing Modem Data';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
        // ->where('Adapter No_', '=', 'A10003')
        // ->limit(10)
            ->select(
                'Adapter No_', // = 'No_' in Contract
                'Line No_',
                'MAC-Address',
                'Status',
                'Start Date', // ?? Meaning
                'Contract Name',
                'Device Type', // ?? 16 = Modem,
                // 'Active', // always '1'
                // 'End Date', // always '1753-01-01 00:00:00.000'
                // 'Bootfile', // always ''
                // 'Down-_Upload', // always '.00000000000000000000'
                // 'Modem Name', // always ''
                // 'IP-Address',
            )
            ->get();

        // d('modems', $structure, $objects);

        if (! $this->newConfigfiles) {
            $this->newConfigfiles = Configfile::get()->keyBy('name');
        }

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        foreach ($objects as $cm) {
            $bar->advance();
            $contracts = Contract::where('number2', $cm->{'Adapter No_'})->get();
            $contractCount = $contracts->count();

            if ($contractCount > 1) {
                $this->warnings['multipleContractsForCm-'.$cm->{'Adapter No_'}] = 'Modem '.$cm->{'MAC-Address'}.' has multiple contracts with Adapter No_ '.$cm->{'Adapter No_'};

                continue;
            } elseif (! $contractCount) {
                $this->warnings['missingContractForCm-'.$cm->{'MAC-Address'}] = 'Modem '.$cm->{'MAC-Address'}.' has no contract with Adapter No_ '.$cm->{'Adapter No_'};

                continue;
            } else {
                $contract = $contracts->first();
            }

            $data = [
                'contract_id' => $contract->id,
                'configfile_id' => $this->getCmCfId($cm),
                'mac' => $cm->{'MAC-Address'},
                'salutation' => $contract->salutation,
                'company' => $contract->company,
                'department' => $contract->department,
                'firstname' => $contract->firstname,
                'lastname' => $contract->lastname,
                'street' => $contract->street,
                'house_number' => $contract->house_number,
                'zip' => $contract->zip,
                'city' => $contract->city,
                'district' => $contract->district,
                'birthday' => $contract->birthday,
            ];

            $this->option('test') ? Modem::createQuietly($data) : Modem::create($data);
        }

        $bar->finish();
        echo "\n";
    }

    private function getCmCfId($cm)
    {
        $cfName = trim($cm->{'Contract Name'});

        if (! $cfName) {
            return $this->baseConfigFile->id;
        }

        $mac = $cm->{'MAC-Address'};
        $ret = preg_match('/\d{4}/', $cfName, $matches);

        if (! $ret) {
            $this->warnings['cmCfNameWithout4Digits-'.$cfName] = "configfile name $cfName doesn't have 4 digits. Configfile of Modem $mac will be set to base.";

            return $this->baseConfigFile->id;
        }

        $cfName = $matches[0];

        if (! isset($this->newConfigfiles[$cfName])) {
            $this->warnings['missingCfForCm-'.$mac] = "Configfile $cfName is missing for modem ".$mac;

            return $this->baseConfigFile->id;
        }

        return $this->newConfigfiles[$cfName]->id;
    }

    private function importSepaMandates()
    {
        echo __FUNCTION__."...\n";

        $table = 'Customer Bank Account';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->where('IBAN', '!=', '')->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->where('IBAN', '!=', '')
            // ->where('Customer No_', '=', '4971')
            ->where('Clearing', 1)
            // ->limit(10)
            ->select(
                'Customer No_', // = 'No_' in Customer
                'IBAN',
                'SWIFT Code',
                'Alternative Account Owner',
                'Date of Signature',
                'Mandate Identification',
                'Name',
            )
            ->get();

        // d('SepaMandates', $structure, $objects);

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        foreach ($objects as $sm) {
            $bar->advance();

            if (! $sm->{'IBAN'}) {
                continue;
            }

            $contracts = Contract::where('number3', $sm->{'Customer No_'})->get();

            if ($contracts->isEmpty()) {
                $this->warnings['missingContractForSepa-'.$sm->{'IBAN'}] = 'Contract is missing for SepaMandate '.$sm->{'IBAN'}.' with Customer No '.$sm->{'Customer No_'};

                continue;
            }

            foreach ($contracts as $contract) {
                SepaMandate::create([
                    'contract_id' => $contract->id,
                    'reference' => $sm->{'Mandate Identification'},
                    'signature_date' => $this->convertDate($sm->{'Date of Signature'}),
                    'holder' => $sm->{'Alternative Account Owner'} ?: $contract->firstname.' '.$contract->lastname,
                    'iban' => $sm->{'IBAN'},
                    'bic' => $sm->{'SWIFT Code'},
                    'institute' => $sm->{'Name'},
                    'valid_from' => $this->convertDate($sm->{'Date of Signature'}),
                    // 'valid_to' => $sm->{''},
                    // 'state' => $sm->{''},
                    // 'costcenter_id' => $sm->{''},
                ]);
            }
        }

        $bar->finish();
        echo "\n";
    }

    private function importPhonenumbers()
    {
        echo __FUNCTION__."...\n";

        $table = 'PiNet VoIP Customer Phone No_';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->where('Phone No_', '!=', '')->where('Password', '!=', '')->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->where('Phone No_', '!=', '')->where('Password', '!=', '')
            // ->where('Adapter No_', '=', 'A10003')
            // ->limit(10)
            ->select(
                // 'Adapter No_' => is 'No_' in Contract
                // '',
            )
            ->get();

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        $port = 1;
        foreach ($objects as $pn) {
            $bar->advance();
            $contracts = Contract::where('number2', $pn->{'Adapter No_'})->with('modems')->get();

            if ($contracts->count() > 1) {
                $this->warnings['multipleContractsForPn-'.$pn->{'Adapter No_'}] = 'Phonenumber '.$pn->{'Phone No_'}
                    .' has multiple contracts with Adapter No_ '.$pn->{'Adapter No_'};

                continue;
            } else {
                $cm = $contracts->first()?->modems->first();
            }

            if (! $cm) {
                $msg = 'Skip adding phonenumber '.$pn->{'Phone No_'}.'. ';
                $msg .= $contracts->first() ? 'Contract '.$contracts->first()->id.' has no modem.' : 'Contract with number2 '.$pn->{'Adapter No_'}.' does not exist';

                $this->warnings['noModemForPn-'.$pn->{'Adapter No_'}] = $msg;

                continue;
            }

            $mta = Mta::createQuietly([
                'modem_id' => $cm->id,
                'mac' => unifyMac($this->mac2mtaMac($cm->mac)),
                'configfile_id' => $this->defaultMtaCf->id,
                'type' => 'sip',
            ]);

            $port++;

            Phonenumber::createQuietly([
                'active' => false,
                'country_code' => '49',
                'prefix_number' => substr($pn->{'Phone No_'}, 2, 4),
                'mta_id' => $mta->id,
                'number' => substr($pn->{'Phone No_'}, 6),
                'password' => $pn->{'Password'},
                'port' => $port,
                // 'sipdomain' => null,
                // 'username' => null,
            ]);
        }

        $bar->finish();
        echo "\n";

        // d('Phonenumbers', $structure, $objects);
    }

    private function importCostCenters()
    {
        echo __FUNCTION__."...\n";

        $table = 'Dimension Value';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->where('Dimension Code', '!=', 'KOSTENSTELLE')
            // ->where('Dimension Value Type', '!=', '0')
            ->where('Blocked', 0)
            // ->limit(10)
            ->select(
                'Name',
                'Code',
                'Dimension Code',
                'Blocked',
                // 'Totaling', // Always ''
                // 'Dimension Value Type', // Always '0'
            )
            ->get();

        foreach ($objects as $cc) {
            $newCc = CostCenter::firstOrCreate([
                'name' => $cc->Name,
                'number' => $cc->Code,
            ]);

            $this->newCostCenterIds[$cc->Code] = $newCc->id;
        }

        // d($this->newCostCenterIds);
        // d('CostCenters', $structure, $objects);
    }

    private function importNodes()
    {
        echo __FUNCTION__."...\n";

        // Could be retrieved from 'Head Station Code' in Billing Object = RealEstate or Head Station Code in Billing Header = Contract

        $table = 'Billing Object';
        $col = '';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table);
        // ->where('No_', '>', 1386)
        // ->limit(10)

        if ($col) {
            $objects = $objects->select($col)->groupBy($col)->pluck($col);
        } else {
            $objects = $objects
                ->select(
                    // 'No_',
                    'Head Station Code',
                    'Community Code',
                    // 'Street No_',
                    // 'House No_',
                )
                ->groupBy('Head Station Code', 'Community Code')
                ->get();
        }

        // d('Nodes', $structure, $objects);

        foreach ($objects as $node) {
            if (! $node->{'Head Station Code'}) {
                continue;
            }

            // $street = $this->streets[$node->{'Street No_'}] ?? null;
            // if (! $street) {
            //     dd($node);
            // }
            // $city = $this->cities[$street->City];

            $this->nodes[$node->{'Community Code'}] = Node::createQuietly([
                // 'city' => $city->Description,
                'name' => $node->{'Head Station Code'},
                // 'street' => $street->Description,
                // 'house_nr' => $node->{'House No_'},
                'description' => $node->{'Community Code'},
            ]);
        }
    }

    private function importRealEstates()
    {
        echo __FUNCTION__."...\n";

        $table = 'Billing Object';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            // ->join('Kirst & Schulze BK GmbH$Street', 'Kirst & Schulze BK GmbH$'.$table.'.Street No_', 'Kirst & Schulze BK GmbH$Street.No_')
            // ->where('Possible Service', '!=', '')
            // ->limit(10)
            ->select(
                // 'Community Code',
                // 'No_',
                // 'Head Station Code',
                // 'Street No_',
                // 'House No_',
                // 'Management',
                // 'Billing Comment',
                // 'No_ Series',
                // 'Possible Service', // Always ''
            )
            ->get();

        // d('RealEstates', $structure, $objects);

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        foreach ($objects as $re) {
            $bar->advance();

            if (! $re->{'Street No_'}) {
                continue;
            }

            $street = $this->streets[$re->{'Street No_'}];
            $city = $this->cities[$street->City] ?? null;

            if (! $city) {
                continue;
            }

            $nodeId = isset($this->nodes[$re->{'Head Station Code'}]) ? $this->nodes[$re->{'Head Station Code'}]->id : null;
            $descriptions[0] = 'Community Code: '.$re->{'Community Code'};
            if ($re->{'Comment 1'}) {
                $descriptions[] = 'Bemerkung 1: '.$re->{'Comment 1'};
            }
            if ($re->{'Comment 2'}) {
                $descriptions[] = 'Bemerkung 2: '.$re->{'Comment 2'};
            }
            if ($re->{'Besitzer'}) {
                $descriptions[] = 'Besitzer: '.$re->{'Besitzer'};
            }

            $data = [
                'number' => $re->{'No_'},
                'city' => $city->Description,
                'street' => $street->Description,
                'house_nr' => $re->{'House No_'},
                'node_id' => $nodeId,
                'zip' => $street->{'Post Code'},
                'country_code' => 'DE',
                'description' => implode("\r", $descriptions),
                'services' => isset($this->possibleServices[$street->{'Possible Service'}]) ? $this->possibleServices[$street->{'Possible Service'}]->Description : null,
            ];

            if ($this->option('test')) {
                $data['lat'] = 50;
                $data['lng'] = 50;
            }

            $this->newRealEstates[$re->{'No_'}] = Realty::firstOrCreate($data);
        }

        $bar->finish();
        echo "\n";
    }

    private function setContractDescription()
    {
        echo __FUNCTION__."...\n";

        $table = 'Comment line';
        $structure = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)->first();

        $objects = DB::connection('mssql-navdb')->table('Kirst & Schulze BK GmbH$'.$table)
            ->where('Comment', '!=', '')
            // ->where('No_', 1391)
            // ->limit(10)
            ->select(
                'No_', // = 'No_' in Customer
                'Line No_',
                'Date',
                'Comment',
                'Code',
            )
            ->get();

        // d('ContractDescriptions', $structure, $objects);

        $commentsPerCustomer = [];
        foreach ($objects as $comment) {
            // if (! isset($commentsPerCustomer[$comment->{'No'}])) {
            //     $commentsPerCustomer[$comment->{'No'}] = [];
            // }

            $commentsPerCustomer[$comment->{'No_'}][$comment->{'Line No_'}] = $this->convertDate($comment->{'Date'}).' '.str_replace("'", "''", $comment->Comment);
        }

        $bar = $this->output->createProgressBar(count($objects));
        $bar->start();

        foreach ($commentsPerCustomer as $customerNr => $comments) {
            $bar->advance();
            try {
                Contract::where('number3', $customerNr)->update(['description' => DB::raw("concat(description, '\r', '".implode("\r", $comments)."')")]);
            } catch (\Throwable $th) {
                dd(DB::raw("concat(description, '\r', '".implode("\r", $comments)."')"), $th->getMessage());
            }
        }

        $bar->finish();
    }

    private function convertDate($date, $format = 'Y-m-d')
    {
        if (! $date || $date == '1753-01-01 00:00:00.000') {
            return;
        }

        return date($format, strtotime($date));
    }

    private function printWarnings()
    {
        foreach ($this->warnings as $warning) {
            $this->line($warning);
        }

        if ($this->missingDates) {
            $this->line($this->missingDates.' missing dates for contracts');
        }

        $this->warnings = [];
    }
}
