<?php

namespace App\Services\Sampleworkflow;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TestRequestFormPdfService
{
    public const ATTACHMENT_TITLE = 'Test Request Form';

    public const ORIENTATION_LANDSCAPE = 'landscape';

    public const ORIENTATION_PORTRAIT = 'portrait';

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
        return route('test-request-form.pdf', $instance->id);
    }

    public function defaultOrientationForVariant(string $variant): string
    {
        return match ($variant) {
            'waste_water' => self::ORIENTATION_PORTRAIT,
            'food', 'water' => self::ORIENTATION_LANDSCAPE,
            default => self::ORIENTATION_LANDSCAPE,
        };
    }

    public function normalizeOrientation(?string $orientation): ?string
    {
        if ($orientation === null || $orientation === '') {
            return null;
        }

        $normalized = strtolower(trim($orientation));
        if (! in_array($normalized, [self::ORIENTATION_LANDSCAPE, self::ORIENTATION_PORTRAIT], true)) {
            throw ValidationException::withMessages([
                'trfPdfOrientation' => 'Choose landscape or portrait for the Test Request Form PDF.',
            ]);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(
        SubmissionFormInstance $instance,
        bool $forPdf = true,
        ?string $orientation = null,
    ): array {
        $viewData = $this->builder->buildFromSubmissionFormInstance($instance, $forPdf);
        $viewData['orientation'] = $this->resolveOrientation(
            $orientation,
            (string) $viewData['variant'],
            $instance,
        );

        return $viewData;
    }

    public function resolveViewName(SubmissionFormInstance $instance): string
    {
        $data = $this->buildViewData($instance);

        return $this->viewForVariant($data['variant']);
    }

    public function buildHtml(
        SubmissionFormInstance $instance,
        bool $forPdf = true,
        ?string $orientation = null,
    ): string {
        $viewData = $this->buildViewData($instance, $forPdf, $orientation);

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

    public function resolveOrientation(
        ?string $override,
        string $variant,
        ?SubmissionFormInstance $instance = null,
    ): string {
        $normalized = $this->normalizeOrientation($override);
        if ($normalized !== null) {
            return $normalized;
        }

        if ($instance !== null
            && Schema::hasColumn($instance->getTable(), 'trf_pdf_orientation')
            && filled($instance->trf_pdf_orientation)
        ) {
            $stored = $this->normalizeOrientation((string) $instance->trf_pdf_orientation);
            if ($stored !== null) {
                return $stored;
            }
        }

        return $this->defaultOrientationForVariant($variant);
    }

    private function applyPaperSettings($pdf, string $orientation): void
    {
        $pdf->setPaper('a4', $orientation);
    }

    private function persistOrientation(SubmissionFormInstance $instance, string $orientation): void
    {
        if (! Schema::hasColumn($instance->getTable(), 'trf_pdf_orientation')) {
            return;
        }

        $instance->trf_pdf_orientation = $orientation;
        $instance->save();
    }

    public function generateAndStore(
        SubmissionFormInstance $instance,
        ?string $orientation = null,
    ): string {
        try {
            $viewData = $this->buildViewData($instance, true, $orientation);
            $resolvedOrientation = (string) $viewData['orientation'];
            $viewName = $this->viewForVariant($viewData['variant']);

            $this->persistOrientation($instance, $resolvedOrientation);

            $pdf = app('dompdf.wrapper');
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('compress', false);
            $pdf->loadView($viewName, $viewData);
            $this->applyPaperSettings($pdf, $resolvedOrientation);

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

    public function stream(SubmissionFormInstance $instance, ?string $orientation = null)
    {
        $viewData = $this->buildViewData($instance, true, $orientation);
        $viewName = $this->viewForVariant($viewData['variant']);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView($viewName, $viewData);
        $this->applyPaperSettings($pdf, (string) $viewData['orientation']);

        return $pdf->stream($this->resolveDisplayFilename($instance));
    }

    public function download(SubmissionFormInstance $instance, ?string $orientation = null)
    {
        $viewData = $this->buildViewData($instance, true, $orientation);
        $viewName = $this->viewForVariant($viewData['variant']);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView($viewName, $viewData);
        $this->applyPaperSettings($pdf, (string) $viewData['orientation']);

        return $pdf->download($this->resolveDisplayFilename($instance));
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
        ?string $orientation = null,
    ): string {
        $sampleType = $form->sampleTypes()->first();
        $viewData = $this->builder->buildFromDraft($formData, $sampleType, $submission, $forPdf);
        $viewData['orientation'] = $this->resolveOrientation(
            $orientation,
            (string) $viewData['variant'],
            $submission,
        );
        $viewName = $this->viewForVariant($viewData['variant']);

        return view($viewName, $viewData)->render();
    }
}
