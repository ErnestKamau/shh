<?php

namespace App\Jobs\Sampleworkflow;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAcceptanceFormPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $acceptanceFormId
    ) {}

    public function handle(AcceptanceFormPdfService $pdfService): void
    {
        $form = AnalysisAcceptanceForm::find($this->acceptanceFormId);

        if (!$form) {
            Log::warning('GenerateAcceptanceFormPdfJob: Acceptance form not found', [
                'acceptance_form_id' => $this->acceptanceFormId
            ]);
            return;
        }

        try {
            $pdfService->generatePdfAndStoreAttachment($form);
        } catch (\Throwable $e) {
            Log::error('GenerateAcceptanceFormPdfJob failed', [
                'acceptance_form_id' => $this->acceptanceFormId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
