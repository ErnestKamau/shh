<?php

use App\Http\Controllers\Api\Portal\Submissions\PortalSubmissionOptionsController;
use App\Http\Controllers\Api\Portal\Submissions\SubmissionFormController;
use App\Http\Controllers\Api\Portal\Submissions\SubmissionFormInstanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Gateway — Submission Forms (internal API)
|--------------------------------------------------------------------------
|
| Authenticated via PORTAL_GATEWAY_API_KEY (Bearer token or X-Portal-Gateway-Key).
| Customer scoping via X-CRM-Customer-Id (required for instance list/show/submit/delete).
|
*/

Route::prefix('v1/portal/submissions')
    ->middleware(['portal.gateway'])
    ->group(function (): void {
        Route::get('/forms', [SubmissionFormController::class, 'index'])->name('api.portal.submissions.forms.index');
        Route::get('/forms/customer-request', [SubmissionFormController::class, 'customerRequest'])
            ->name('api.portal.submissions.forms.customer-request');
        Route::get('/options', [PortalSubmissionOptionsController::class, 'index'])
            ->name('api.portal.submissions.options');

        Route::get('/instances/stats', [SubmissionFormInstanceController::class, 'stats'])
            ->name('api.portal.submissions.instances.stats');
        Route::get('/instances', [SubmissionFormInstanceController::class, 'index'])
            ->name('api.portal.submissions.instances.index');

        Route::get('/forms/{submissionForm}/attachment-forms', [SubmissionFormController::class, 'attachmentForms'])
            ->name('api.portal.submissions.forms.attachment-forms');
        Route::get('/forms/{submissionForm}/instances', [SubmissionFormInstanceController::class, 'formInstances'])
            ->name('api.portal.submissions.forms.instances');
        Route::get('/forms/{submissionForm}', [SubmissionFormController::class, 'show'])->name('api.portal.submissions.forms.show');
        Route::get('/forms/{submissionForm}/schema', [SubmissionFormController::class, 'schema'])->name('api.portal.submissions.forms.schema');

        Route::post('/forms/{submissionForm}/instances', [SubmissionFormInstanceController::class, 'store'])
            ->name('api.portal.submissions.instances.store');

        Route::get('/instances/{instance}/depended-field-value', [SubmissionFormInstanceController::class, 'dependedFieldValue'])
            ->name('api.portal.submissions.instances.depended-field-value');
        Route::get('/instances/{instance}', [SubmissionFormInstanceController::class, 'show'])
            ->name('api.portal.submissions.instances.show');

        Route::get('/instances/{instance}/test-request-form/pdf', [SubmissionFormInstanceController::class, 'testRequestFormPdf'])
            ->name('api.portal.submissions.instances.test-request-form.pdf');

        Route::put('/instances/{instance}', [SubmissionFormInstanceController::class, 'submit'])
            ->name('api.portal.submissions.instances.submit');

        Route::delete('/instances/{instance}', [SubmissionFormInstanceController::class, 'destroy'])
            ->name('api.portal.submissions.instances.destroy');

        Route::get('/acceptance-forms', [\App\Http\Controllers\Api\Portal\AcceptanceFormController::class, 'index'])
            ->name('api.portal.acceptance-forms.index');
        Route::get('/acceptance-forms/{acceptanceForm}', [\App\Http\Controllers\Api\Portal\AcceptanceFormController::class, 'show'])
            ->name('api.portal.acceptance-forms.show');
        Route::post('/acceptance-forms/{acceptanceForm}/sign', [\App\Http\Controllers\Api\Portal\AcceptanceFormController::class, 'sign'])
            ->name('api.portal.acceptance-forms.sign');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Api\Portal\AcceptanceFormController::class, 'markNotificationRead'])
            ->name('api.portal.notifications.read');
    });
