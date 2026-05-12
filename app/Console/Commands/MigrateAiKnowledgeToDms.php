<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigrateAiKnowledgeToDms extends Command
{
    protected $signature = 'dms:migrate-kb';
    protected $description = 'Migrate legacy AI manual documents to the Document Management Module';

    public function handle()
    {
        $this->info('Starting migration of AI Knowledge to DMS...');

        // 1. Ensure "Knowledge" document type exists
        $kbType = DocumentType::firstOrCreate(
            ['name' => 'Knowledge'],
            [
                'code' => 'KB',
                'description' => 'AI Knowledge Base entries',
                'is_active' => true,
            ]
        );

        // 2. Check if legacy table exists
        $legacyTable = 'ai.ai_manual_documents';
        if (!Schema::connection('pgsql_ai')->hasTable('ai_manual_documents')) {
            $this->warn("Legacy table {$legacyTable} not found. Nothing to migrate.");
            return 0;
        }

        $legacyDocs = DB::connection('pgsql_ai')->table('ai_manual_documents')->get();
        $this->info("Found " . $legacyDocs->count() . " legacy documents.");

        $bar = $this->output->createProgressBar($legacyDocs->count());
        $bar->start();

        foreach ($legacyDocs as $legacy) {
            // Check if already migrated
            if (Document::where('title', $legacy->title)->where('document_type_id', $kbType->id)->exists()) {
                $bar->advance();
                continue;
            }

            $metadata = json_decode($legacy->metadata, true) ?: [];

            Document::create([
                'id' => Str::uuid(),
                'document_type_id' => $kbType->id,
                'document_number' => 'KB-' . strtoupper(Str::random(8)),
                'title' => $legacy->title,
                'description' => $metadata['description'] ?? 'Migrated from legacy KB',
                'owner_id' => $legacy->created_by ?: auth()->id() ?: 1,
                'created_by' => $legacy->created_by ?: 1,
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_kb_indexed' => true,
                'kb_collection' => $legacy->collection_name ?: 'General Documents',
                'kb_required_permission' => $legacy->required_permission ?: 'general.view',
                'kb_last_indexed_at' => $legacy->last_indexed_at,
                'kb_indexing_status' => 'indexed',
                'kb_chunk_size' => $metadata['chunk_size'] ?? 800,
                'kb_chunk_overlap' => $metadata['chunk_overlap'] ?? 100,
                'kb_content' => $legacy->content,
                'file_path' => $legacy->file_path ?: '',
                'file_name' => $legacy->file_name ?: '',
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migration completed successfully.');
        
        return 0;
    }
}
