<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CertificateTemplate;
use App\CertificateTemplateSection;
use App\CertificateTemplateElement;
use App\Models\CertificateTemplateElementHolder;
use Illuminate\Support\Facades\DB;

class MigrateElementsToHolders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'templates:migrate-elements-to-holders
                            {--template= : Migrate elements for a specific template ID}
                            {--dry-run : Preview changes without applying them}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate existing certificate template elements to element holders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting element migration to holders...');
        
        $templateId = $this->option('template');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }
        
        // Get templates to process
        $templates = $templateId 
            ? CertificateTemplate::where('id', $templateId)->get()
            : CertificateTemplate::all();
        
        if ($templates->isEmpty()) {
            $this->error('No templates found.');
            return 1;
        }
        
        $this->info("Found {$templates->count()} template(s) to process");
        
        DB::beginTransaction();
        
        try {
            foreach ($templates as $template) {
                $this->processTemplate($template, $dryRun);
            }
            
            if ($dryRun) {
                DB::rollBack();
                $this->info('DRY RUN completed - No changes were saved');
            } else {
                DB::commit();
                $this->info('Migration completed successfully!');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration failed: {$e->getMessage()}");
            return 1;
        }
    }
    
    /**
     * Process a single template.
     */
    private function processTemplate(CertificateTemplate $template, bool $dryRun): void
    {
        $this->line("Processing template: {$template->name} (ID: {$template->id})");
        
        $sections = $template->sections;
        
        foreach ($sections as $section) {
            $this->processSectionElements($section, $dryRun);
        }
    }
    
    /**
     * Process elements in a section.
     */
    private function processSectionElements(CertificateTemplateSection $section, bool $dryRun): void
    {
        // Get elements without holders
        $elementsWithoutHolders = $section->elements()
            ->whereNull('certificate_template_element_holder_id')
            ->get();
        
        if ($elementsWithoutHolders->isEmpty()) {
            $this->comment("  Section '{$section->title}': No elements to migrate");
            return;
        }
        
        $this->comment("  Section '{$section->title}': {$elementsWithoutHolders->count()} element(s) to migrate");
        
        // Create a default holder for this section
        $holder = $this->createDefaultHolder($section, $dryRun);
        
        if (!$dryRun) {
            // Assign all elements to this holder
            foreach ($elementsWithoutHolders as $element) {
                $element->certificate_template_element_holder_id = $holder->id;
                
                // Set default positions if not set
                if (is_null($element->position_x)) {
                    $element->position_x = 10;
                }
                if (is_null($element->position_y)) {
                    // Stack elements vertically
                    $element->position_y = 10 + ($element->sort_order * 120);
                }
                if (is_null($element->width)) {
                    $element->width = 600;
                }
                if (is_null($element->height)) {
                    $element->height = 100;
                }
                
                $element->save();
            }
            
            $this->info("    ✓ Created holder and migrated {$elementsWithoutHolders->count()} element(s)");
        } else {
            $this->line("    [DRY RUN] Would create holder and migrate {$elementsWithoutHolders->count()} element(s)");
        }
    }
    
    /**
     * Create a default holder for a section.
     */
    private function createDefaultHolder(CertificateTemplateSection $section, bool $dryRun): ?CertificateTemplateElementHolder
    {
        if ($dryRun) {
            return new CertificateTemplateElementHolder();
        }
        
        return CertificateTemplateElementHolder::create([
            'certificate_template_section_id' => $section->id,
            'holder_type' => 'field',
            'max_elements' => 50,
            'sort_order' => 1,
            'position_x' => 50,
            'position_y' => 50,
            'width' => 700,
            'height' => 900,
            'position_x_percent' => 6.3,
            'position_y_percent' => 4.5,
            'width_percent' => 88,
            'height_percent' => 80,
        ]);
    }
}
