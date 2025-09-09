<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\CustomerInteraction\Entities\CiCustomerInteraction;
use Modules\CustomerInteraction\Entities\CiCategory;
use Modules\CustomerInteraction\Handlers\HandlerRegistry;
use Modules\CustomerInteraction\Handlers\HandlerDispatcher;

class TestCustomerInteractionHandlers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ci-handlers {--category=} {--contract-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Customer Interaction handlers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Customer Interaction Handlers...');
        
        // Get handler registry
        $registry = app(HandlerRegistry::class);
        $dispatcher = app(HandlerDispatcher::class);
        
        // List all registered handlers
        $this->info("\nRegistered Handlers:");
        foreach ($registry->getAllHandlers() as $handler) {
            $this->line("- {$handler->getName()}: {$handler->getDescription()}");
        }
        
        // Test with specific category if provided
        $categoryName = $this->option('category');
        $contractId = $this->option('contract-id');
        
        if ($categoryName) {
            $category = CiCategory::where('label', $categoryName)->first();
            if (!$category) {
                $this->error("Category '{$categoryName}' not found");
                return 1;
            }
            
            $this->info("\nTesting category: {$category->label}");
            
            // Check if category has a handler
            if ($registry->hasHandlerForCategory($category)) {
                $handler = $registry->getHandlerForCategory($category);
                $this->info("✓ Handler found: {$handler->getName()}");
                
                // Create test interaction
                $testData = $this->getTestDataForCategory($category->label);
                $interaction = $this->createTestInteraction($category, $contractId, $testData);
                
                if ($interaction) {
                    $this->info("✓ Test interaction created (ID: {$interaction->id})");
                    
                    // Test validation
                    if ($handler->validate($interaction)) {
                        $this->info("✓ Handler validation passed");
                    } else {
                        $this->warn("⚠ Handler validation failed");
                    }
                }
            } else {
                $this->warn("⚠ No handler found for category: {$category->label}");
            }
        }
        
        $this->info("\nHandler test completed!");
        return 0;
    }
    
    /**
     * Get test data for specific category
     */
    protected function getTestDataForCategory(string $categoryLabel): array
    {
        switch ($categoryLabel) {
            case 'Plan Change':
            case 'Tarifwechsel':
                return [
                    'Neuer Tarif' => 'Test Internet 100',
                    'Wirksamkeitsdatum' => now()->addDays(7)->format('Y-m-d'),
                    'Grund' => 'Test tariff change'
                ];
                
            case 'Cancellation':
            case 'Kündigung':
                return [
                    'Kündigungsdatum' => now()->format('Y-m-d'),
                    'Kündigungsgrund' => 'Test cancellation',
                    'Kündigungsfrist' => '30'
                ];
                
            default:
                return [];
        }
    }
    
    /**
     * Create test interaction
     */
    protected function createTestInteraction(CiCategory $category, ?int $contractId, array $customData): ?CiCustomerInteraction
    {
        try {
            $interaction = CiCustomerInteraction::create([
                'subject_type' => 'Modules\\ProvBase\\Entities\\Contract',
                'subject_id' => $contractId ?? 1,
                'contract_id' => $contractId ?? 1,
                'ci_category_id' => $category->id,
                'ci_channel_id' => 1,
                'ci_direction_id' => 1,
                'ci_status_id' => 1,
                'subject' => 'Test Interaction',
                'notes' => 'Test interaction for handler testing',
                'custom_data' => $customData,
                'opened_at' => now(),
                'users_created_by_id' => 1,
            ]);
            
            return $interaction;
        } catch (\Exception $e) {
            $this->error("Failed to create test interaction: " . $e->getMessage());
            return null;
        }
    }
}