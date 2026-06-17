<?php

namespace App\Services\Sampleworkflow;

use App\Models\TestRequestFormInstance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestRequestFormPdfService
{
    public const ATTACHMENT_TITLE = 'Test Request Form';

    public function __construct(
        private readonly TestRequestFormReportDataBuilder $builder
    ) {}

    public function resolveStoragePath(TestRequestFormInstance $instance): string
    {
        return 'test-request-forms/trf-' . $instance->id . '.pdf';
    }

    public function resolvePublicUrl(TestRequestFormInstance $instance): string
    {
        return '/storage/' . $this->resolveStoragePath($instance);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(TestRequestFormInstance $instance, bool $forPdf = true): array
    {
        return $this->builder->build($instance, $forPdf);
    }

    public function resolveViewName(TestRequestFormInstance $instance): string
    {
        $data = $this->buildViewData($instance);

        return $this->viewForVariant($data['variant']);
    }

    public function buildHtml(TestRequestFormInstance $instance, bool $forPdf = true): string
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

    public function generateAndStore(TestRequestFormInstance $instance): string
    {
        try {
            $viewData = $this->buildViewData($instance, true);
            $viewName = $this->viewForVariant($viewData['variant']);

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->loadView($viewName, $viewData);
            $this->applyPaperSettings($pdf, $viewData['variant']);

            $storagePath = $this->resolveStoragePath($instance);
            Storage::disk('public')->put($storagePath, $pdf->output());

            return $this->resolvePublicUrl($instance);
        } catch (\Throwable $exception) {
            Log::warning('Test request form PDF generation failed.', [
                'instance_id' => $instance->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function stream(TestRequestFormInstance $instance)
    {
        $viewData = $this->buildViewData($instance, true);
        $viewName = $this->viewForVariant($viewData['variant']);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView($viewName, $viewData);
        $this->applyPaperSettings($pdf, $viewData['variant']);

        $filename = 'test-request-form-' . ($viewData['serialNumber'] ?: $instance->id) . '.pdf';

        return $pdf->stream($filename);
    }

    public function download(TestRequestFormInstance $instance)
    {
        $this->ensureStored($instance);

        $storagePath = $this->resolveStoragePath($instance);
        $viewData = $this->buildViewData($instance, true);
        $filename = 'test-request-form-' . ($viewData['serialNumber'] ?: $instance->id) . '.pdf';

        return Storage::disk('public')->download($storagePath, $filename);
    }

    public function ensureStored(TestRequestFormInstance $instance): string
    {
        $storagePath = $this->resolveStoragePath($instance);

        if (! Storage::disk('public')->exists($storagePath)) {
            return $this->generateAndStore($instance);
        }

        return $this->resolvePublicUrl($instance);
    }
}
