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

namespace App\Console\Commands\Import;

use App\ImportTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\BillingBase\Entities\Item;
use Modules\BillingBase\Entities\Product;
use Modules\PropertyManagement\Entities\Apartment;
use Modules\PropertyManagement\Entities\Node;
use Modules\PropertyManagement\Entities\Realty;
use Modules\ProvBase\Entities\Contract;
use Str;

class ImportAvpCustomersCommand extends Command
{
    use ImportTrait;

    /** @var string The name and signature of the console command. */
    protected $signature = 'import:avp
        {dataFile : Structured CSV file (-path) with customer data.}
        {ccId : CostCenter ID for all the imported contracts.}
        {format : avp|anmeldung|awg|abo}
        {--P|priceMappings= : File with price to product ID mapping.}
    ';

    /** @var string The console command description. */
    protected $description = 'import Customers from CSV and add TV Tarif';

    protected $collectionContractId = 1448;
    protected $products = [];

    // Currently processed objects
    protected $apartment;
    protected $city;
    protected $contract;
    protected $file;
    protected $line;
    protected $node;
    protected $realEstate;
    protected $zip;

    public function handle()
    {
        $this->warn('Please set collectionContractId in code before you import AWG real estates and customers!');

        $this->file = file($this->argument('dataFile'));

        match ($this->argument('format')) {
            'abo' => $this->importAbo(),
            'anmeldung' => $this->importAnmeldung(),
            'avp' => $this->importAvpCustomerList(),
            'awg' => $this->importAwg(),
            default => exit("Format not supported! Stop.\n"),
        };

        self::printImportantTodos();
    }

    // AVP Kundenliste der Kabelkunden - hier benötige ich:
    // Liegenschaften, Kundenverträge, die Posten sollen zum 01.01.26 beginnen und sind am Betrag zu erkennen (109-112 Kabelgebühr) Beispiel 41.40 € ist Kabelgebühr halbjährlich
    // Achtung: Einige der Kunden haben in NMS schon eine neue Kundenummer, da Sie bereits Tel oder Internet benutzen. Manche zahlen über NMS auch schon die Kabelgebühr, obwohl Sie hier noch drin stehen.
    // (Den Rest, z.B. Bankdaten und Bemerkungen muss ich dann manuell nachtragen.)
    private function importAvpCustomerList()
    {
        if (! $this->option('priceMappings')) {
            $this->error("Path to price mapping file must be specified with -P option for AVP import! Stop.");

            exit;
        }

        $this->availableProducts = Product::where('type', 'TV')->get()->keyBy('id');
        ob_start();
        $this->products = include($this->option('priceMappings'));
        ob_end_clean();

        $source = str_getcsv($this->file[0], ';')[1];
        $parts = explode(' ', $source);
        $this->zip = trim($parts[0]);
        $this->city = trim($parts[1]);

        unset($this->file[0]);

        echo "Import customers\n";
        $num = count($this->file);
        $bar = $this->output->createProgressBar($num);
        $bar->start();

        foreach ($this->file as $line) {
            $bar->advance();
            $this->line = str_getcsv($line, ';');

            if (Str::startsWith($this->line[0], 'Vstpkt')) {
                $this->addNode();

                continue;
            }

            if (Str::startsWith($this->line[0], 'Vorname')) {
                continue;
            }

            $this->addRealEstate();
            $this->addApartment();
            $this->addContract();
            $this->addItem();
        }

        $bar->finish();
        echo "\n";
    }

    private function addNode()
    {
        $name = trim($this->line[1]);

        if ($this->node && $this->node->name == $name) {
            return;
        }

        $this->node = Node::firstOrCreate([
            'name' => $name,
        ]);
    }

    private function addRealEstate()
    {
        $parts = explode(' ', $this->line[4]);
        $housenr = end($parts);
        $street = str_replace($housenr, '', $this->line[4]);

        $this->realEstate = Realty::firstOrCreate([
            'node_id' => $this->node->id,
            'city' => $this->city,
            'house_nr' => trim($housenr),
            'street' => trim($street),
            'zip' => $this->zip,
            'country_code' => 'DE',
        ]);
    }

    private function addApartment()
    {
        return $this->apartment = Apartment::firstOrCreate([
            'realty_id' => $this->realEstate->id,
            'number' => $this->line[3],
        ]);
    }

    private function AddContract()
    {
        $contract = self::contractExists($this->line[2], $this->line[0], $this->line[1], $this->realEstate->street, $this->realEstate->city, $this->realEstate->house_nr);

        $this->contract = $contract ?: Contract::firstOrCreate([
            'number' => $this->line[2],
            'firstname' => $this->line[0],
            'lastname' => $this->line[1],
            'apartment_id' => $this->apartment->id,
            'costcenter_id' => $this->argument('ccId'),
        ]);
    }

    private function addItem()
    {
        $price = str_replace([' ', '€'], '', $this->line[5]);
        $productId = $this->products[$price] ?? 0;

        if (! $productId || $this->contract->items()->where('product_id', $productId)->count()) {
            return;
        }

        $count = 1;
        $price = floatval($price);

        if ($price != $this->availableProducts[$productId]->price) {
            $count = intval($price / $this->availableProducts[$productId]->price);
        }

        Item::create([
            'contract_id' => $this->contract->id,
            'product_id' => $productId,
            'valid_from' => '2026-01-01',
            'valid_from_fixed' => 1,
            'count' => $count,
        ]);
    }

    // Anmeldung Wünschendorf, hier benötige ich die Liegenschaften. Die Kunden und Kundennummern sollten ja nach 1) alle bereits vorhanden sein.
    private function importAnmeldung()
    {
        unset($this->file[0]);

        echo "Import customers\n";
        $bar = $this->output->createProgressBar(count($this->file));
        $bar->start();

        foreach ($this->file as $line) {
            $bar->advance();
            $this->line = str_getcsv($line, ';');

            if (! $this->line[3]) {
                continue;
            }

            // Add Real Estate
            $this->realEstate = Realty::firstOrCreate([
                'city' => $this->line[6],
                'house_nr' => trim($this->line[4]),
                'street' => trim($this->line[3]),
                'zip' => str_pad($this->line[5], 5, '0', STR_PAD_LEFT),
                'country_code' => 'DE',
            ]);

            // Add Apartment
            $this->apartment = Apartment::firstOrCreate([
                'realty_id' => $this->realEstate->id,
                'number' => $this->line[7],
            ]);

            // Match Contract to Apartment
            $c = self::contractExists($this->line[0], $this->line[2], $this->line[1], $this->realEstate->street, $this->realEstate->city, $this->realEstate->house_nr);

            if ($c && ! $c->apartment_id) {
                $c->apartment_id = $this->apartment->id;
                $c->save();
            }
        }

        $bar->finish();
        echo "\n";
    }

    // Liegenschaften zusätzlich mit den Wohnungsnummern (Spalte A und B = Objekt und Wohnung)
    // Teilweise sind die Kunden schon in NMS erfasst – siehe Kundennummer in Spalte N.
    // Die TV Verträge unter Spalte L (eigentlich: 134 Kabel-TV-Anschluss AWG, monatlich) werden von der Genossenschaft bei uns abgerechnet! Dürfen in NMS nicht bei der Kundenrechnung berechnet werden.
    // Die TV Verträge unter Spalte M müssten schon in NMS drin sein. (nach und nach nehmen wir die Kunden aus der Abrechnung bei der Genossenschaft raus!)
    // Hier benötige ich dann zusätzlich die Möglichkeit, monatlich eine Liste aller TV-Verträge in genau diesen Liegenschaften zu erstellen (wegen den Abrechnungen bei der Genossenschaft, dazu pflege ich bisher die Exel-Liste.)
    private function importAwg()
    {
        unset($this->file[0]);

        echo "Import real estates\n";
        $bar = $this->output->createProgressBar(count($this->file));
        $bar->start();
        $bar->advance();

        foreach ($this->file as $line) {
            $bar->advance();
            $this->line = str_getcsv($line, ';');

            if (! $this->line[0] || ! intval($this->line[0])) {
                continue;
            }

            // Add Real Estate
            $address = explode(' ', $this->line[6]);
            $housenr = end($address);
            $street = str_replace($housenr, '', $this->line[6]);

            $this->realEstate = Realty::firstOrCreate([
                'city' => 'Weida',
                'house_nr' => trim($housenr),
                'street' => trim($street),
                'zip' => '07570',
                'country_code' => 'DE',
                'contract_id' => $this->line[11] ? $this->collectionContractId : null,
            ]);

            $floor = explode(';', $this->line[7]);
            $floor = count($floor) > 1 ? $floor[1] : $floor[0];
            preg_match('/\d/', $floor, $matches);
            $floor = $matches ? $matches[0] : 0;

            // Add Apartment
            $this->apartment = Apartment::create([
                'number' => $this->line[0].'-'.$this->line[1],
                'realty_id' => $this->realEstate->id,
                'code' => $this->line[7],
                'floor' => $floor,
            ]);

            $number = $this->line[13];

            // Match Contract to Apartment
            if (Contract::where('number', $number)->count() > 1) {
                self::addTodo("Mehrere Verträge mit Vertragsnummer $number");
            }

            $c = self::contractExists($number, $this->line[5], $this->line[4], $this->realEstate->street, $this->realEstate->city, $this->realEstate->house_nr);

            if ($c) {
                if (! $c->apartment_id) {
                    $c->apartment_id = $this->apartment->id;
                    $c->save();
                }

                continue;
            }

            // Add new contract
            Contract::firstOrCreate([
                'number' => $number ?: null,
                'firstname' => $this->line[5],
                'lastname' => $this->line[4],
                'apartment_id' => $this->apartment->id,
                'costcenter_id' => $this->argument('ccId'),
            ]);
        }

        $bar->finish();
        echo "\n";
    }

    // Aboliste HD+, hier benötige ich nur die Kartennummern und die Modulnummern aus den Spalten N und O in die Zeile Smartcard IDs übertragen. Die Kunden und Kundennummern müssten ja nun alle bereits vorhanden sein!
    private function importAbo()
    {
        unset($this->file[0]);

        echo "Import smart cards\n";
        $bar = $this->output->createProgressBar(count($this->file));
        $bar->start();
        $bar->advance();

        $today = now()->toDateString();

        foreach ($this->file as $line) {
            $bar->advance();
            $this->line = str_getcsv($line, ';');

            if (! $this->line[0]) {
                continue;
            }

            $c = Contract::where('number', $this->line[0])->first();

            if (! $c) {
                $c = Contract::where('firstname', $this->line[1])
                    ->where('lastname', $this->line[2])
                    ->where('street', $this->line[3])
                    ->where('house_number', $this->line[4])
                    ->where('city', $this->line[6])
                    ->first();

                if (! $c) {
                    self::addTodo("Vertrag mit Nummer {$this->line[0]} konnte nicht gefunden werden.");

                    continue;
                }
            }

            $item = Item::where('contract_id', $c->id)
                ->join('contract', 'contract.id', 'item.contract_id')
                ->join('product', 'item.product_id', 'product.id')
                ->select('item.*')
                ->where('product.type', 'TV')
                ->where(function($query) use ($today) {
                    $query->where('valid_to', '>=', $today)
                        ->orWhereNull('valid_to');
                })
                ->orderBy('contract.created_at', 'desc')
                ->first();

            if (! $item) {
                self::addTodo("Vertrag mit Nummer {$this->line[0]} hat keinen TV-Posten. Smart card ID kann nicht gesetzt werden.");

                continue;
            }

            $item->smartcardids = 'HD+ Karte: '.$this->line[13].' - SN Modul: '.$this->line[14];
            $item->saveQuietly();
        }

        $bar->finish();
        echo "\n";
    }
}
