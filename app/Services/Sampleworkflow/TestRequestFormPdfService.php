<?php

namespace App\Services\Sampleworkflow;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestRequestFormPdfService
{
    public const ATTACHMENT_TITLE = 'Test Request Form';

    public function __construct(
        private readonly TestRequestFormReportDataBuilder $builder
    ) {}

    public function resolveStoragePath(SubmissionFormInstance $instance): string
    {
        return 'test-request-forms/trf-sfi-'.$instance->id.'.pdf';
    }

    /**
     * Human-readable download / display name (storage path keeps the UUID basename).
     */
    public function resolveDisplayFilename(SubmissionFormInstance $instance): string
    {
        $formNumber = trim((string) ($instance->form_number ?? ''));
        $suffix = $formNumber !== ''
            ? (string) preg_replace('/[\/\\\\]+/', '-', $formNumber)
            : (string) $instance->id;

        return 'Test-Request-Form-'.$suffix.'.pdf';
    }

    public function resolvePublicUrl(SubmissionFormInstance $instance): string
    {
        return '/storage/'.$this->resolveStoragePath($instance);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(SubmissionFormInstance $instance, bool $forPdf = true): array
    {
        return $this->builder->buildFromSubmissionFormInstance($instance, $forPdf);
    }

    public function resolveViewName(SubmissionFormInstance $instance): string
    {
        $data = $this->buildViewData($instance);

        return $this->viewForVariant($data['variant']);
    }

    public function buildHtml(SubmissionFormInstance $instance, bool $forPdf = true): string
    {
        $viewData = $this->buildViewData($instance, $forPdf);

        return view($this->viewForVariant($viewData['variant']), $viewData)->render();
    }

    private function viewForVariant(string $variant): string
    {
        return match ($variant) {
            'food' => 'workflow.forms.test-request.food',
            'waste_water' => 'workflow.forms.test-request.waste-water',
            default => 'workflow.forms.test-request.water',
        };
    }

    private function applyPaperSettings($pdf, string $variant): void
    {
        $orientation = match ($variant) {
            'waste_water' => 'portrait',
            'food', 'water' => 'landscape',
            default => 'portrait',
        };

        $pdf->setPaper('a4', $orientation);
    }

    public function generateAndStore(SubmissionFormInstance $instance): string
    {
        try {
            $viewData = $this->buildViewData($instance, true);
            $viewName = $this->viewForVariant($viewData['variant']);

            $pdf = app('dompdf.wrapper');
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('compress', false);
            $pdf->loadView($viewName, $viewData);
            $this->applyPaperSettings($pdf, $viewData['variant']);

            $storagePath = $this->resolveStoragePath($instance);
            Storage::disk('public')->put($storagePath, $pdf->output());

            return $this->resolvePublicUrl($instance);
        } catch (\Throwable $exception) {
            Log::warning('Test Request Form PDF generation failed.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function stream(SubmissionFormInstance $instance)
    {
        $viewData = $this->buildViewData($instance, true);
        $viewName = $this->viewForVariant($viewData['variant']);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView($viewName, $viewData);
        $this->applyPaperSettings($pdf, $viewData['variant']);

        return $pdf->download($this->resolveDisplayFilename($instance));
    }

    public function download(SubmissionFormInstance $instance)
    {
        return $this->stream($instance);
    }

    /**
     * Build preview from draft field values before instance is saved.
     *
     * @param  array<string, mixed>  $formData
     */
    public function buildHtmlFromDraft(
        array $formData,
        SubmissionForm $form,
        ?SubmissionFormInstance $submission = null,
        bool $forPdf = false,
    ): string {
        $sampleType = $form->sampleTypes()->first();
        $viewData = $this->builder->buildFromDraft($formData, $sampleType, $submission, $forPdf);
        $viewName = $this->viewForVariant($viewData['variant']);

        return view($viewName, $viewData)->render();
    }
}
