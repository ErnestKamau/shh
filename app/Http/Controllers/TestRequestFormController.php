<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Http\Response;

class TestRequestFormController extends Controller
{
    public function __construct(
        private readonly TestRequestFormPdfService $pdfService
    ) {}

    public function preview(string $instance): Response
    {
        $sfi = SubmissionFormInstance::query()->findOrFail($instance);

        return response($this->pdfService->buildHtml($sfi, false))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function previewDraft(): Response
    {
        $draft = session('test_request_form_preview_draft');
        if (! is_array($draft)) {
            abort(404, 'No test request form preview is available.');
        }

        $form = \App\Models\SubmissionForm::with('sampleTypes')->find($draft['submission_form_id'] ?? null);
        if ($form === null) {
            abort(404, 'No submission form template for preview.');
        }

        $submission = null;
        if (! empty($draft['submission_form_instance_id'])) {
            $submission = SubmissionFormInstance::with('crmCustomer.contacts')
                ->find($draft['submission_form_instance_id']);
        }

        return response($this->pdfService->buildHtmlFromDraft(
            $draft['form_data'] ?? $draft['field_values'] ?? [],
            $form,
            $submission,
            false,
        ))->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function pdf(string $instance)
    {
        $sfi = SubmissionFormInstance::query()->findOrFail($instance);

        return $this->pdfService->stream($sfi);
    }

    public function download(string $instance)
    {
        $sfi = SubmissionFormInstance::query()->findOrFail($instance);

        return $this->pdfService->download($sfi);
    }

    public function regenerate(string $instance)
    {
        $sfi = SubmissionFormInstance::query()->findOrFail($instance);
        $this->pdfService->generateAndStore($sfi);

        return redirect()
            ->back()
            ->with('success', 'Test Request Form PDF regenerated.');
    }
}
