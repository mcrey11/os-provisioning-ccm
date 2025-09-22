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

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\ContactBase\Entities\ContactPoint;
use Modules\Sales\Entities\CrmOpportunity;
use Modules\Sales\Entities\CrmOpportunityItem;
use Modules\Sales\Entities\CrmPipeline;
use Modules\Sales\Entities\CrmPipelineStage;
use Modules\PropertyManagement\Entities\Realty;
use Modules\Ticketsystem\Entities\Ticket;
use Modules\Ticketsystem\Entities\Comment;
use Modules\BillingBase\Entities\Product;

class ImportCrmOpportunitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:import-opportunities 
                            {file : Path to the CSV file}
                            {--limit=0 : Limit the number of entries to import (0 = no limit)}
                            {--extended-logging : Enable extended logging}
                            {--dry-run : Run without actually importing data}
                            {--skip-tickets : Skip creating tickets and comments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import CRM opportunities from CSV file with tickets and opportunity items';

    /**
     * CSV column mapping
     *
     * @var array
     */
    protected $columnMapping = [
        'lfd_nr' => 0,
        'yash_nr_vertrag' => 1,
        'anrede' => 2,
        'name' => 3,
        'vorname' => 4,
        'strasse' => 5,
        'hnr' => 6,
        'hnr_z' => 7,
        'plz' => 8,
        'ort' => 9,
        'ortsteil' => 10,
        'email' => 11,
        'telefon' => 12,
        'geb_dat' => 13,
        'koax_kunde' => 14,
        'koax_nr_vertrag' => 15,
        'glasfaser_fitnesstest' => 16,
        'vertrag_scharf' => 17,
        'vorvertrag_zur_info' => 18,
        'vorvertrag_mit_unterschrift' => 19,
        'bestellanfrage_yash' => 20,
        'bestellung_nr_yash' => 21,
        'wechselvertrag_zur_info' => 22,
        'wechselvertrag_mit_unterschrift' => 23,
        'tarif_mbit' => 24,
        'newsletter' => 25,
        'reseller' => 26,
        'rabatt_1_monat' => 27,
        'lieferschein_nr' => 28,
        'lieferschein_an_maria' => 29,
        'f2115' => 30,
        'fb_5590' => 31,
        'g2120b' => 32,
        'g6426' => 33,
        'gpon_sn' => 34,
        'adress_id' => 35,
        'home_id' => 36,
        'sepa_angelegt' => 37,
        'sepa_an_anna' => 38,
        'portierung' => 39,
        'portierung_am' => 40,
        'angelegt_nms' => 41,
        'aktiv_geschaltet' => 42,
        'bemerkung' => 43,
    ];

    /**
     * Custom field mappings for CrmOpportunityItem
     * Maps CSV column names to product IDs
     * TODO: Update these product IDs to match your actual product IDs in the database
     *
     * @var array
     */
    protected $customFieldMappings = [
        'f2115' => 5,    // Set to actual product ID for F2115 device
        'fb_5590' => 6,  // Set to actual product ID for FB 5590 device
        'g2120b' => null,   // Set to actual product ID for G2120B device
        'g6426' => null,    // Set to actual product ID for G6426 device
        'gpon_sn' => null,  // Set to actual product ID for GPON serial number
    ];

    /**
     * Tariff speed mappings for CrmOpportunityItem
     * Maps "Tarif Mbit/s" values to product IDs
     * TODO: Update these product IDs to match your actual tariff product IDs in the database
     *
     * @var array
     */
    protected $tariffMappings = [
        '75' => 2,      // Set to actual product ID for 75 Mbit/s tariff
        '300' => 4,     // Set to actual product ID for 300 Mbit/s tariff
        '1.000' => 7,   // Set to actual product ID for 1.000 Mbit/s tariff
        '1000' => 7,    // Alternative format for 1000 Mbit/s tariff
        // Add more speed values as needed
    ];

    /**
     * Average runtime in months for deal size calculation
     *
     * @var int
     */
    protected $avgRuntime = 36;

    /**
     * Statistics tracking
     *
     * @var array
     */
    protected $stats = [
        'total_rows' => 0,
        'processed_rows' => 0,
        'created_opportunities' => 0,
        'created_items' => 0,
        'created_tickets' => 0,
        'created_comments' => 0,
        'errors' => 0,
        'skipped_rows' => 0,
    ];

    /**
     * Default pipeline and stage for opportunities
     */
    protected $defaultPipeline;
    protected $defaultStage;
    protected $defaultUser;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $limit = (int) $this->option('limit');
        $verbose = $this->option('extended-logging');
        $dryRun = $this->option('dry-run');
        $skipTickets = $this->option('skip-tickets');

        // Validate file
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Initialize defaults
        if (!$this->initializeDefaults()) {
            return 1;
        }

        $this->info("Starting CRM Opportunities import from: {$filePath}");
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No data will be imported");
        }

        try {
            DB::beginTransaction();

            // Disable notifications during import to avoid observer issues
            if (!$dryRun) {
                Notification::fake();
            }

            $this->processCSV($filePath, $limit, $verbose, $dryRun, $skipTickets);

            if ($dryRun) {
                DB::rollBack();
                $this->info("DRY RUN completed - no changes made to database");
            } else {
                DB::commit();
                $this->info("Import completed successfully");
            }

            $this->displayStats();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            Log::error("CRM Opportunities import failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Initialize default values
     */
    protected function initializeDefaults(): bool
    {
        // Get or create default pipeline
        $this->defaultPipeline = CrmPipeline::first();
        if (!$this->defaultPipeline) {
            $this->error("No CRM pipeline found. Please create at least one pipeline first.");
            return false;
        }

        // Get first stage of the pipeline
        $this->defaultStage = $this->defaultPipeline->stages()->first();
        if (!$this->defaultStage) {
            $this->error("No stages found for pipeline: {$this->defaultPipeline->name}");
            return false;
        }

        // Get default user (use root user with ID 1)
        $this->defaultUser = User::find(1);

        if (!$this->defaultUser) {
            // Fallback to first admin user
            $this->defaultUser = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->first();
        }

        if (!$this->defaultUser) {
            // Final fallback to any user
            $this->defaultUser = User::first();
        }

        if (!$this->defaultUser) {
            $this->error("No users found in the system");
            return false;
        }

        $this->info("Using pipeline: {$this->defaultPipeline->name}");
        $this->info("Using stage: {$this->defaultStage->name}");
        $userName = trim(($this->defaultUser->first_name ?? '') . ' ' . ($this->defaultUser->last_name ?? ''));
        if (empty($userName)) {
            $userName = $this->defaultUser->login_name ?? 'Unknown User';
        }
        $this->info("Using default user: {$userName}");

        return true;
    }

    /**
     * Process the CSV file
     */
    protected function processCSV(string $filePath, int $limit, bool $verbose, bool $dryRun, bool $skipTickets): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Could not open CSV file");
        }

        $rowNumber = 0;
        $processedCount = 0;

        // Skip header rows (first 17 rows based on the CSV structure)
        for ($i = 0; $i < 17; $i++) {
            fgetcsv($handle);
            $rowNumber++;
        }

        while (($row = fgetcsv($handle)) !== false && ($limit === 0 || $processedCount < $limit)) {
            $rowNumber++;
            $this->stats['total_rows']++;

            try {
                if ($this->shouldSkipRow($row)) {
                    $this->stats['skipped_rows']++;
                    if ($verbose) {
                        $this->line("Skipping empty row {$rowNumber}");
                    }
                    continue;
                }

                $this->processRow($row, $rowNumber, $verbose, $dryRun, $skipTickets);
                $processedCount++;
                $this->stats['processed_rows']++;

                if ($processedCount % 10 === 0) {
                    $this->info("Processed {$processedCount} rows...");
                }

            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->error("Error processing row {$rowNumber}: " . $e->getMessage());
                if ($verbose) {
                    Log::error("Row processing error", [
                        'row' => $rowNumber,
                        'data' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        fclose($handle);
    }

    /**
     * Check if row should be skipped
     */
    protected function shouldSkipRow(array $row): bool
    {
        // Skip if essential fields are empty
        return empty(trim($row[$this->columnMapping['name']] ?? '')) && 
               empty(trim($row[$this->columnMapping['vorname']] ?? ''));
    }

    /**
     * Process a single CSV row
     */
    protected function processRow(array $row, int $rowNumber, bool $verbose, bool $dryRun, bool $skipTickets): void
    {
        if ($verbose) {
            $this->line("Processing row {$rowNumber}...");
        }

        // Extract data from CSV row
        $data = $this->extractRowData($row);

        if (!$dryRun) {
            // Create contact point first
            $contactPoint = $this->createContactPoint($data);

            // Find matching realty
            $realty = $this->findMatchingRealty($data);

            // Create CRM opportunity
            $opportunity = $this->createCrmOpportunity($data, $contactPoint, $realty);
            $this->stats['created_opportunities']++;

            // Create opportunity items for custom fields
            $this->createOpportunityItems($opportunity, $data);

            // Create ticket and comments if not skipped
            if (!$skipTickets && !empty($data['bemerkung'])) {
                $this->createTicketWithComments($opportunity, $data['bemerkung']);
            }
        }

        if ($verbose) {
            $this->line("Completed row {$rowNumber}: {$data['firstname']} {$data['lastname']}");
        }
    }

    /**
     * Extract data from CSV row
     */
    protected function extractRowData(array $row): array
    {
        return [
            'lfd_nr' => trim($row[$this->columnMapping['lfd_nr']] ?? ''),
            'yash_nr_vertrag' => trim($row[$this->columnMapping['yash_nr_vertrag']] ?? ''),
            'salutation' => $this->mapSalutation(trim($row[$this->columnMapping['anrede']] ?? '')),
            'lastname' => trim($row[$this->columnMapping['name']] ?? ''),
            'firstname' => trim($row[$this->columnMapping['vorname']] ?? ''),
            'street' => trim($row[$this->columnMapping['strasse']] ?? ''),
            'house_number' => trim($row[$this->columnMapping['hnr']] ?? '') . trim($row[$this->columnMapping['hnr_z']] ?? ''),
            'zip' => trim($row[$this->columnMapping['plz']] ?? ''),
            'city' => trim($row[$this->columnMapping['ort']] ?? ''),
            'district' => trim($row[$this->columnMapping['ortsteil']] ?? ''),
            'email' => trim($row[$this->columnMapping['email']] ?? ''),
            'phone' => trim($row[$this->columnMapping['telefon']] ?? ''),
            'birthday' => $this->parseDate(trim($row[$this->columnMapping['geb_dat']] ?? '')),
            'tarif_mbit' => trim($row[$this->columnMapping['tarif_mbit']] ?? ''),
            'adress_id' => trim($row[$this->columnMapping['adress_id']] ?? ''),
            'home_id' => trim($row[$this->columnMapping['home_id']] ?? ''),
            'bemerkung' => trim($row[$this->columnMapping['bemerkung']] ?? ''),
            'external_order_no' => trim($row[$this->columnMapping['yash_nr_vertrag']] ?? ''),
            // Custom fields
            'f2115' => trim($row[$this->columnMapping['f2115']] ?? ''),
            'fb_5590' => trim($row[$this->columnMapping['fb_5590']] ?? ''),
            'g2120b' => trim($row[$this->columnMapping['g2120b']] ?? ''),
            'g6426' => trim($row[$this->columnMapping['g6426']] ?? ''),
            'gpon_sn' => trim($row[$this->columnMapping['gpon_sn']] ?? ''),
        ];
    }

    /**
     * Map German salutation to system format
     */
    protected function mapSalutation(string $salutation): ?string
    {
        $mapping = [
            'Herr' => 'Mr.',
            'Frau' => 'Ms.',
            'Dr.' => 'Dr.',
        ];

        return $mapping[$salutation] ?? null;
    }

    /**
     * Parse date from German format
     */
    protected function parseDate(string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            $date = \DateTime::createFromFormat('d.m.Y', $dateString);
            if ($date) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Try other formats if needed
        }

        return null;
    }

    /**
     * Create contact point using HasContactPoint trait
     */
    protected function createContactPoint(array $data): ContactPoint
    {
        $contactPoint = new ContactPoint();
        
        // Determine contact type
        $contactPoint->type = 'individual';
        
        // Set contact fields
        $contactPoint->salutation = $data['salutation'];
        $contactPoint->firstname = $data['firstname'];
        $contactPoint->lastname = $data['lastname'];
        $contactPoint->email = $data['email'];
        $contactPoint->phone = $data['phone'];
        $contactPoint->birthday = $data['birthday'];
        $contactPoint->street = $data['street'];
        $contactPoint->house_number = $data['house_number'];
        $contactPoint->zip = $data['zip'];
        $contactPoint->city = $data['city'];
        $contactPoint->district = $data['district'];
        
        $contactPoint->save();
        
        return $contactPoint;
    }

    /**
     * Find matching realty based on address or home ID
     */
    protected function findMatchingRealty(array $data): ?Realty
    {
        if (!class_exists(Realty::class)) {
            return null;
        }

        // Try to match by home_id first
        if (!empty($data['home_id'])) {
            $realty = Realty::where('number', $data['home_id'])->first();
            if ($realty) {
                return $realty;
            }
        }

        // Try to match by address
        if (!empty($data['street']) && !empty($data['house_number']) && !empty($data['zip']) && !empty($data['city'])) {
            $realty = Realty::where('street', 'ILIKE', '%' . $data['street'] . '%')
                ->where('house_nr', $data['house_number'])
                ->where('zip', $data['zip'])
                ->where('city', 'ILIKE', '%' . $data['city'] . '%')
                ->first();
            
            if ($realty) {
                return $realty;
            }
        }

        return null;
    }

    /**
     * Create CRM opportunity
     */
    protected function createCrmOpportunity(array $data, ContactPoint $contactPoint, ?Realty $realty): CrmOpportunity
    {
        $opportunity = new CrmOpportunity();
        
        // Set contact point
        $opportunity->contact_point_id = $contactPoint->id;
        
        // Set basic fields
        $opportunity->pipeline_id = $this->defaultPipeline->id;
        $opportunity->stage_id = $this->defaultStage->id;
        $opportunity->owner_id = $this->defaultUser->id;
        
        // Set realty if found
        if ($realty) {
            $opportunity->realty_id = $realty->id;
        }
        
        // Set external order number
        if (!empty($data['external_order_no'])) {
            $opportunity->external_order_no = $data['external_order_no'];
        }
        
        // Set deal size based on tariff
        if (!empty($data['tarif_mbit'])) {
            $opportunity->deal_size = $this->calculateDealSize($data['tarif_mbit']);
        }
        
        $opportunity->probability_pct = 50; // Default probability
        $opportunity->is_preorder = true; // Since these are pre-orders
        
        $opportunity->save();
        
        return $opportunity;
    }

    /**
     * Calculate deal size based on tariff product price multiplied by average runtime
     */
    protected function calculateDealSize(string $tariff): int
    {
        // Find the product ID for this tariff
        $tariffValue = trim($tariff);
        $productId = $this->tariffMappings[$tariffValue] ?? null;
        
        if (!$productId) {
            // Log warning if product mapping not found
            Log::warning("No product mapping found for tariff: {$tariff}");
            return 0;
        }
        
        // Get the actual product price
        $product = Product::find($productId);
        if (!$product) {
            Log::warning("Product not found for ID: {$productId} (tariff: {$tariff})");
            return 0;
        }
        
        if (!$product->price) {
            Log::warning("Product {$productId} has no price set (tariff: {$tariff})");
            return 0;
        }
        
        // Calculate: product price * average runtime (in months)
        return (int) round($product->price * $this->avgRuntime);
    }

    /**
     * Create opportunity items for custom fields and tariffs
     */
    protected function createOpportunityItems(CrmOpportunity $opportunity, array $data): void
    {
        // Handle custom field mappings (devices, etc.)
        foreach ($this->customFieldMappings as $csvColumn => $productId) {
            if (!empty($data[$csvColumn]) && !empty($productId)) {
                $this->createOpportunityItem($opportunity, $csvColumn, $productId, $data[$csvColumn]);
            }
        }

        // Handle tariff mapping
        if (!empty($data['tarif_mbit'])) {
            $tariffValue = trim($data['tarif_mbit']);
            if (isset($this->tariffMappings[$tariffValue]) && !empty($this->tariffMappings[$tariffValue])) {
                $this->createOpportunityItem($opportunity, 'tarif_mbit', $this->tariffMappings[$tariffValue], $tariffValue);
            }
        }
    }

    /**
     * Create a single opportunity item
     */
    protected function createOpportunityItem(CrmOpportunity $opportunity, string $fieldName, int $productId, string $value): void
    {
        // Verify the product exists
        if (!Product::find($productId)) {
            $this->warn("Product ID {$productId} not found for custom field: {$fieldName}");
            return;
        }

        $item = new CrmOpportunityItem();
        $item->opportunity_id = $opportunity->id;
        $item->product_id = $productId;
        $item->accounting_text = "{$fieldName}: {$value}";
        $item->count = 1;
        
        // Store the CSV value in custom_data for reference
        $item->custom_data = [
            'csv_field' => $fieldName,
            'csv_value' => $value,
            'import_source' => 'yash_csv_import'
        ];
        
        $item->save();
        
        $this->stats['created_items']++;
    }

    /**
     * Create ticket with comments
     */
    protected function createTicketWithComments(CrmOpportunity $opportunity, string $bemerkung): void
    {
        if (!class_exists(Ticket::class)) {
            return;
        }

        // Create the ticket
        $ticket = new Ticket();
        $ticket->name = 'CRM Excel Import Ticket - ' . ($opportunity->contactPoint ? $opportunity->contactPoint->label() : 'Unknown Contact');
        $ticket->ticketable_type = CrmOpportunity::class;
        $ticket->ticketable_id = $opportunity->id;
        $ticket->user_id = $this->defaultUser->id;
        $ticket->priority = 'Minor';
        $ticket->duedate = now()->addDays(7);
        
        // Set ticket type state to avoid observer issues
        $newState = \Modules\Ticketsystem\Entities\TicketTypeState::where('name', 'New')->first();
        if ($newState) {
            $ticket->ticket_type_state_id = $newState->id;
        }
        
        $ticket->save();
        
        $this->stats['created_tickets']++;

        // Add user to ticket
        if ($this->defaultUser) {
            $ticket->users()->attach($this->defaultUser->id);
        }

        // Parse bemerkung and create comments for each line
        $lines = explode("\n", $bemerkung);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $this->createComment($ticket, $line);
            }
        }
    }

    /**
     * Create a comment for a ticket
     */
    protected function createComment(Ticket $ticket, string $commentText): void
    {
        if (!class_exists(Comment::class)) {
            return;
        }

        $comment = new Comment();
        $comment->ticket_id = $ticket->id;
        $comment->user_id = $this->defaultUser->id;
        $comment->comment = $commentText;
        $comment->save();
        
        $this->stats['created_comments']++;
    }

    /**
     * Display import statistics
     */
    protected function displayStats(): void
    {
        $this->info("\n=== Import Statistics ===");
        $this->info("Total rows in CSV: {$this->stats['total_rows']}");
        $this->info("Processed rows: {$this->stats['processed_rows']}");
        $this->info("Skipped rows: {$this->stats['skipped_rows']}");
        $this->info("Created opportunities: {$this->stats['created_opportunities']}");
        $this->info("Created opportunity items: {$this->stats['created_items']}");
        $this->info("Created tickets: {$this->stats['created_tickets']}");
        $this->info("Created comments: {$this->stats['created_comments']}");
        $this->info("Errors: {$this->stats['errors']}");
        
        if ($this->stats['errors'] > 0) {
            $this->warn("There were {$this->stats['errors']} errors during import. Check the logs for details.");
        }
    }
}
