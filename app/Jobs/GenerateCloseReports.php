<?php

namespace App\Jobs;

use App\Models\CRM\Complaint;
use App\Services\CRM\ComplaintInvestigationReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCloseReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $complaintId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ComplaintInvestigationReportService $reportService): void
    {
        $complaint = Complaint::find($this->complaintId);
        if (! $complaint) {
            Log::warning("GenerateCloseReports: Complaint {$this->complaintId} not found.");
            return;
        }

        try {
            $reportService->generateAndAttachCloseReports($complaint);
        } catch (\Throwable $e) {
            Log::error("GenerateCloseReports: Failed to generate close reports for complaint {$this->complaintId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateCloseReports job failed for complaint {$this->complaintId}: " . $exception->getMessage());
    }
}
