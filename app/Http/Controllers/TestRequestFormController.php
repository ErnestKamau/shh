<?php

namespace App\Http\Controllers;

use App\Models\TestRequestFormInstance;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use Illuminate\Http\Response;

class TestRequestFormController extends Controller
{
    public function __construct(
        private readonly TestRequestFormPdfService $pdfService
    ) {}

    public function preview(string $instance): Response
    {
        $trfi = TestRequestFormInstance::query()->findOrFail($instance);

        return response($this->pdfService->buildHtml($trfi, false))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function previewDraft(): Response
    {
        $draft = session('test_request_form_preview_draft');
        if (! is_array($draft)) {
            abort(404, 'No test request form preview is available.');
        }

        $sampleType = \App\SampleType::find($draft['sample_type_id'] ?? null);
        $submission = null;
        if (! empty($draft['submission_form_instance_id'])) {
            $submission = \App\Models\SubmissionFormInstance::with('crmCustomer.contacts')
                ->find($draft['submission_form_instance_id']);
        }

        $viewData = app(\App\Services\Sampleworkflow\TestRequestFormReportDataBuilder::class)
            ->buildFromDraft($draft['form_data'] ?? [], $sampleType, $submission, false);

        $viewName = $viewData['variant'] === 'food'
            ? 'workflow.forms.test-request.food'
            : 'workflow.forms.test-request.water';

        return response(view($viewName, $viewData)->render())
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function pdf(string $instance)
    {
        $trfi = TestRequestFormInstance::query()->findOrFail($instance);

        return $this->pdfService->stream($trfi);
    }

    public function download(string $instance)
    {
        $trfi = TestRequestFormInstance::query()->findOrFail($instance);

        return $this->pdfService->download($trfi);
    }

    public function regenerate(string $instance)
    {
        $trfi = TestRequestFormInstance::query()->findOrFail($instance);
        $this->pdfService->generateAndStore($trfi);

        return redirect()
            ->back()
            ->with('success', 'Test Request Form PDF regenerated.');
    }
}
