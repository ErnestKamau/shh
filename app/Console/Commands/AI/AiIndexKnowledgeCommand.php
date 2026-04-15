<?php

namespace App\Console\Commands\AI;

use Illuminate\Console\Command;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\NonConformance;
use App\Models\CRM\Complaint;
use App\Models\Document;
use App\Services\AI\KnowledgeBaseService;
use Illuminate\Support\Facades\DB;

class AiIndexKnowledgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:index-knowledge {--domain=all : Domain to index (all|capa|finding|incident|sop)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform bulk ingestion of existing data into the RAG vector store.';

    protected KnowledgeBaseService $kbService;

    /**
     * Execute the console command.
     */
    public function handle(KnowledgeBaseService $kbService): void
    {
        $this->kbService = $kbService;
        $domain = $this->option('domain');

        $this->info("AI Knowledge Indexing Starting [Domain: $domain]");

        if ($domain === 'all' || $domain === 'capa') {
            $this->indexCorrectiveActions();
        }

        if ($domain === 'all' || $domain === 'finding') {
            $this->indexNonConformances();
        }

        if ($domain === 'all' || $domain === 'incident') {
            $this->indexComplaints();
        }

        if ($domain === 'all' || $domain === 'sop') {
            $this->indexSops();
        }

        $this->info("Indexing completed. Check the RAG traces for details.");
    }

    protected function indexCorrectiveActions(): void
    {
        $this->info("Indexing Corrective Actions (CAPA)...");
        $count = 0;
        CorrectiveAction::chunk(100, function ($cas) use (&$count) {
            foreach ($cas as $ca) {
                $this->kbService->indexCorrectiveAction($ca);
                $count++;
            }
        });
        $this->success("✓ Indexed $count CAPAs.");
    }

    protected function indexNonConformances(): void
    {
        $this->info("Indexing Non-Conformances (Audit Findings)...");
        $count = 0;
        NonConformance::chunk(100, function ($ncs) use (&$count) {
            foreach ($ncs as $nc) {
                $this->kbService->indexNonConformance($nc);
                $count++;
            }
        });
        $this->success("✓ Indexed $count Non-Conformances.");
    }

    protected function indexComplaints(): void
    {
        $this->info("Indexing Complaints (Incidents)...");
        $count = 0;
        Complaint::chunk(100, function ($complaints) use (&$count) {
            foreach ($complaints as $complaint) {
                $this->kbService->indexComplaint($complaint);
                $count++;
            }
        });
        $this->success("✓ Indexed $count Complaints.");
    }

    protected function indexSops(): void
    {
        $this->info("Indexing SOPs (Documents)...");
        if (!class_exists(Document::class)) {
            $this->warn("! App\\Models\\Document class not found. Skipping SOP indexing.");
            return;
        }

        $count = 0;

        $strictQuery = Document::query()
            ->where('is_active', true)
            ->where('is_published', true);

        if ((clone $strictQuery)->count() > 0) {
            $strictQuery->chunk(100, function ($docs) use (&$count) {
                foreach ($docs as $doc) {
                    $this->kbService->indexDocument($doc);
                    $count++;
                }
            });
        } else {
            $this->warn('! No active/published SOPs found. Falling back to all documents for indexing.');

            Document::query()->chunk(100, function ($docs) use (&$count) {
                foreach ($docs as $doc) {
                    $this->kbService->indexDocument($doc);
                    $count++;
                }
            });
        }

        $this->success("✓ Indexed $count SOP Documents.");
    }

    protected function success(string $message): void
    {
        $this->line("<fg=green>$message</>");
    }
}
