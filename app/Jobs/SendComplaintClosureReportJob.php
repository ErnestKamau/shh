<?php

namespace App\Jobs;

use App\Mail\ComplaintClosureMail;
use App\Models\CRM\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendComplaintClosureReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param  array<int, string>  $emails
     */
    public function __construct(
        public int $complaintId,
        public array $emails,
        public string $relativePath,
    ) {}

    public function handle(): void
    {
        $complaint = Complaint::find($this->complaintId);
        if (! $complaint) {
            return;
        }

        if (! Storage::disk('public')->exists($this->relativePath)) {
            Log::warning("SendComplaintClosureReportJob: closure report file missing for complaint {$this->complaintId}");

            return;
        }

        try {
            $pdfContent = Storage::disk('public')->get($this->relativePath);
            Mail::to($this->emails)->send(new ComplaintClosureMail($complaint, $pdfContent));
        } catch (\Throwable $e) {
            Log::error("SendComplaintClosureReportJob: failed for complaint {$this->complaintId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendComplaintClosureReportJob failed for complaint {$this->complaintId}: " . $exception->getMessage());
    }
}
