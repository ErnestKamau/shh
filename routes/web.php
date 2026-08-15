
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\LivewireControllers\LabAppController;
use App\Http\Controllers\LivewireControllers\StandardsController;
use App\Http\Controllers\LivewireControllers\CRMAppController;
use App\Http\Controllers\LivewireControllers\EquipmentAppController;
use App\Http\Controllers\System\PushSubscriptionController;
use App\Http\Controllers\System\SystemBackupController;
use App\Http\Controllers\System\SystemDatabaseExportController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Lab\PersonalDashboardController;
use App\Http\Controllers\Lab\SampleAssignmentController;

Route::get('/', function () {
    return redirect()->route('home');
});





Auth::routes();

// Force-change password (90-day expiry policy)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'showForm'])->name('password.force-change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.force-change.update');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
    Route::get('/portal-media-proxy', 'Portal\\PortalMediaProxyController@show')->name('portal-media.proxy');
});

Route::get('set-locale/{locale}', 'LocaleController@setLocale')->name('set-locale');

// Certificate Template Routes
Route::middleware(['auth'])->group(function () {
    // Certificate Template Management
    Route::resource('certificate-templates', 'CertificateTemplateController');

    // Additional template actions
    Route::post('certificate-templates/{certificateTemplate}/toggle-published', 'CertificateTemplateController@togglePublished')->name('certificate-templates.toggle-published');
    Route::post('certificate-templates/{certificateTemplate}/toggle-active', 'CertificateTemplateController@toggleActive')->name('certificate-templates.toggle-active');
    Route::post('certificate-templates/{certificateTemplate}/duplicate', 'CertificateTemplateController@duplicate')->name('certificate-templates.duplicate');
    Route::get('certificate-templates/{certificateTemplate}/preview', 'CertificateTemplateController@preview')->name('certificate-templates.preview');
    Route::get('certificate-templates/{certificateTemplate}/pdf-preview', 'CertificateTemplateController@generatePdfPreview')->name('certificate-templates.pdf-preview');
    Route::get('certificate-templates/{certificateTemplate}/data', 'CertificateTemplateController@getTemplateData')->name('certificate-templates.data');
    Route::get('certificate-templates/submission-form-instances', 'CertificateTemplateController@getSubmissionFormInstances')->name('certificate-templates.submission-form-instances');
    Route::post('certificate-templates/{certificateTemplate}/generate-report', 'CertificateTemplateController@generateReport')->name('certificate-templates.generate-report');

    // Template Builder Routes
    Route::get('certificate-templates/{certificateTemplate}/builder', 'TemplateBuilderController@builder')->name('certificate-templates.builder');

    // Modern Template Builder Routes
    Route::get('certificate-templates/{certificateTemplate}/modern-builder', 'ModernTemplateBuilderController@builder')->name('certificate-templates.modern-builder');
    Route::post('certificate-templates/{certificateTemplate}/save-layout', 'ModernTemplateBuilderController@saveLayout')->name('certificate-templates.save-layout');
    Route::get('certificate-templates/{certificateTemplate}/load-layout', 'ModernTemplateBuilderController@loadLayout')->name('certificate-templates.load-layout');
    Route::post('certificate-templates/{certificateTemplate}/preview', 'ModernTemplateBuilderController@preview')->name('certificate-templates.preview');
    Route::post('certificate-templates/{certificateTemplate}/export', 'ModernTemplateBuilderController@export')->name('certificate-templates.export');

    // Modern Section Management
    Route::post('certificate-templates/{certificateTemplate}/modern-sections', 'ModernSectionController@store')->name('certificate-templates.modern-sections.store');
    Route::get('certificate-templates/modern-sections/{section}', 'ModernSectionController@show')->name('certificate-templates.modern-sections.show');
    Route::put('certificate-templates/modern-sections/{section}', 'ModernSectionController@update')->name('certificate-templates.modern-sections.update');
    Route::delete('certificate-templates/modern-sections/{section}', 'ModernSectionController@destroy')->name('certificate-templates.modern-sections.destroy');
    Route::post('certificate-templates/{certificateTemplate}/modern-sections/reorder', 'ModernSectionController@reorder')->name('certificate-templates.modern-sections.reorder');
    Route::post('certificate-templates/modern-sections/{section}/add-row', 'ModernSectionController@addRow')->name('certificate-templates.modern-sections.add-row');
    Route::post('certificate-templates/modern-sections/{section}/add-column', 'ModernSectionController@addColumn')->name('certificate-templates.modern-sections.add-column');
    Route::post('certificate-templates/modern-sections/{section}/add-cell', 'ModernSectionController@addCell')->name('certificate-templates.modern-sections.add-cell');
    Route::post('certificate-templates/modern-sections/{section}/add-sub-section', 'ModernSectionController@addSubSection')->name('certificate-templates.modern-sections.add-sub-section');

    // Modern Element Management
    Route::post('certificate-templates/modern-sections/{section}/elements', 'ModernElementController@store')->name('certificate-templates.modern-elements.store');
    Route::get('certificate-templates/modern-elements/{element}', 'ModernElementController@show')->name('certificate-templates.modern-elements.show');
    Route::put('certificate-templates/modern-elements/{element}', 'ModernElementController@update')->name('certificate-templates.modern-elements.update');
    Route::delete('certificate-templates/modern-elements/{element}', 'ModernElementController@destroy')->name('certificate-templates.modern-elements.destroy');
    Route::put('certificate-templates/modern-elements/{element}/position', 'ModernElementController@updatePosition')->name('certificate-templates.modern-elements.position');
    Route::put('certificate-templates/modern-elements/{element}/css-config', 'ModernElementController@updateCssConfig')->name('certificate-templates.modern-elements.css-config');
    Route::put('certificate-templates/modern-elements/{element}/data-config', 'ModernElementController@updateDataConfig')->name('certificate-templates.modern-elements.data-config');

    // Query Builder Routes
    Route::get('certificate-templates/query-builder/tables', 'QueryBuilderController@getTables')->name('certificate-templates.query-builder.tables');
    Route::get('certificate-templates/query-builder/columns/{table}', 'QueryBuilderController@getColumns')->name('certificate-templates.query-builder.columns');
    Route::post('certificate-templates/query-builder/preview', 'QueryBuilderController@preview')->name('certificate-templates.query-builder.preview');

    // Section Management
    Route::post('certificate-templates/{template}/sections', 'CertificateTemplateSectionController@store')->name('certificate-templates.sections.store');
    Route::get('certificate-template-sections/{section}', 'CertificateTemplateSectionController@show')->name('certificate-template-sections.show');
    Route::put('certificate-template-sections/{section}', 'CertificateTemplateSectionController@update')->name('certificate-template-sections.update');
    Route::delete('certificate-template-sections/{section}', 'CertificateTemplateSectionController@destroy')->name('certificate-template-sections.destroy');
    Route::post('certificate-template-sections/{section}/resize', 'CertificateTemplateSectionController@resize')->name('certificate-template-sections.resize');
    Route::post('certificate-templates/{template}/sections/reorder', 'CertificateTemplateSectionController@reorder')->name('certificate-templates.sections.reorder');

    // Element Holder Management
    Route::post('certificate-template-sections/{section}/holders', 'CertificateTemplateElementHolderController@store')->name('certificate-template-sections.holders.store');
    // Specific routes must come before dynamic {holder} routes
    Route::get('certificate-template-holders/data-source/fields', 'CertificateTemplateElementHolderController@getFieldsForDataSource')->name('certificate-template-holders.data-source-fields');
    Route::get('certificate-template-holders/{holder}', 'CertificateTemplateElementHolderController@show')->name('certificate-template-holders.show');
    Route::put('certificate-template-holders/{holder}', 'CertificateTemplateElementHolderController@update')->name('certificate-template-holders.update');
    Route::delete('certificate-template-holders/{holder}', 'CertificateTemplateElementHolderController@destroy')->name('certificate-template-holders.destroy');
    Route::post('certificate-template-sections/{section}/holders/reorder', 'CertificateTemplateElementHolderController@reorder')->name('certificate-template-sections.holders.reorder');
    Route::post('certificate-template-holders/{holder}/clone', 'CertificateTemplateElementHolderController@clone')->name('certificate-template-holders.clone');
    Route::put('certificate-template-holders/{holder}/position', 'CertificateTemplateElementHolderController@updatePosition')->name('certificate-template-holders.position');
    Route::post('certificate-template-holders/{holder}/toggle-direction', 'CertificateTemplateElementHolderController@toggleDirection')->name('certificate-template-holders.toggle-direction');
    Route::post('certificate-template-holders/{parentHolder}/nested-holders', 'CertificateTemplateElementHolderController@storeNestedHolder')->name('certificate-template-holders.nested-holders.store');

    // Element Management
    Route::get('certificate-template-elements/{element}', 'CertificateTemplateElementController@show')->name('certificate-template-elements.show');
    Route::post('certificate-template-holders/{holder}/elements', 'CertificateTemplateElementController@store')->name('certificate-template-holders.elements.store');
    Route::put('certificate-template-elements/{element}', 'CertificateTemplateElementController@update')->name('certificate-template-elements.update');
    Route::delete('certificate-template-elements/{element}', 'CertificateTemplateElementController@destroy')->name('certificate-template-elements.destroy');
    Route::put('certificate-template-elements/{element}/position', 'CertificateTemplateElementController@updatePosition')->name('certificate-template-elements.position');

    // Utility Routes
    Route::get('template-builder/data-fields', 'TemplateBuilderController@getDataFields')->name('template-builder.data-fields');
    Route::post('certificate-templates/upload-image', 'CertificateTemplateController@uploadImage')->name('certificate-templates.upload-image');
});

Route::get('/mark-Accreditted-Samples', 'SampleWorkFlowController@markAccredittedSamples')->name('markAccredittedSamples')->middleware('can:laboratory.components.all samples.edit');

Route::get('/resolveTest', 'SampleWorkFlowController@resolveTest')->name('resolveTest')->middleware('can:laboratory.components.all samples.edit');
Route::get('/fillCapturedresultOperator', 'SampleWorkFlowController@fillCapturedresultOperator')->name('fillCapturedresultOperator')->middleware('can:laboratory.components.all samples.edit');
Route::get('add/suppliers-user', 'SupplierController@make_suppliers_users')->name('add-crm-to-users')->middleware('can:inventory.components.suppliers.edit');

// Route::get('/send/event-notifications-cron', 'API\APIController@send_event_notifications')->name('send_event_notifications');

Route::post('/logout/app/', 'Auth\TwoFactor@mylogout')->name('mylogout');
Route::get('/verify/user', 'Auth\TwoFactor@index')->name('verify-user');
Route::post('/verify-code/store', 'Auth\TwoFactor@storeVerifyCode')->name('verify-store');
Route::get('/verify/totp', [\App\Http\Controllers\Auth\TotpTwoFactorController::class, 'showVerify'])->name('verify-totp');
Route::post('/verify/totp', [\App\Http\Controllers\Auth\TotpTwoFactorController::class, 'storeVerify'])->name('verify-totp-store');
Route::post('/verify-code/store/ext', 'Auth\TwoFactor@storeVerifyCodeExt')->name('verify-store-ext');
Route::get('/verify-code/resend', 'Auth\TwoFactor@resendVerifyCode')->name('verify-resend');

Route::prefix('account/2fa')->name('account.2fa.')->middleware(['auth', 'twofactor'])->group(function () {
    Route::get('/setup', [\App\Http\Controllers\Auth\TotpTwoFactorController::class, 'setup'])->name('setup');
    Route::post('/confirm', [\App\Http\Controllers\Auth\TotpTwoFactorController::class, 'confirm'])->name('confirm');
    Route::post('/disable', [\App\Http\Controllers\Auth\TotpTwoFactorController::class, 'disable'])->name('disable');
    Route::post('/recovery-codes', [\App\Http\Controllers\Auth\TotpTwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes');
});

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/search-sample-code', 'HomeController@searchsample')->name('search-sample-code');
Route::post('/set-default-company', 'HomeController@default_company')->name('set-default-company');

//#############CONFIGURATIONS###############################################################################
Route::get('/system-settings', 'ConfigurationController@index')->name('system-settings')->middleware('can:settings.module.access');
Route::get('/system-settings/backups', [SystemBackupController::class, 'index'])
    ->name('system-settings.backups')
    ->middleware(['auth', 'can:settings.module.access']);
Route::post('/system-settings/backups/export', [SystemBackupController::class, 'export'])
    ->name('system-settings.backups.export')
    ->middleware(['auth', 'can:settings.module.access', 'can:system.dashboard.export']);
Route::get('/system-settings/database-export/{format}', SystemDatabaseExportController::class)
    ->name('system-settings.database-export')
    ->middleware(['auth', 'can:settings.module.access', 'can:system.dashboard.export']);
Route::get('/system-settings/module-visibility', 'ConfigurationController@moduleVisibility')->name('system-settings.module-visibility')->middleware(['can:settings.module.access', 'can:system.module-switching.view']);
Route::get('/system-settings/translations', 'ConfigurationController@translations')->name('system-settings.translations')->middleware(['can:settings.module.access', 'can:system.translations.view']);
Route::get('/system-settings/preferences', 'ConfigurationController@preferences')->name('system-settings.preferences')->middleware('can:settings.module.access');
Route::post('/system-settings/preferences', 'ConfigurationController@updatePreferences')->name('system-settings.preferences.update')->middleware('can:settings.module.access');
Route::get('/lab/whatsapp-configuration', function () {
    return view('layouts.lab.whatsapp-configuration');
})->name('lab.whatsapp-configuration')->middleware('can:settings.module.access');

Route::get('/lab/amendment-report-configuration', function () {
    return view('layouts.lab.amendment-report-configuration');
})->name('lab.amendment-report-configuration')->middleware('can:laboratory.components.sample-types.view');

// Bulk Data Import
Route::get('/bulk-import', 'ConfigurationController@bulkImport')->name('bulk-import')->middleware('can:settings.module.access');

Route::post('/import-my-users', 'PersonnelController@importUser')->name('importUser');
/* COMPANIES */
Route::get('/companies', 'CompanyController@index')->name('companies')->middleware(['can:settings.module.access', 'can:system.companies.view']);
Route::post('/companies', 'CompanyController@add')->name('add-companies')->middleware(['can:settings.module.access', 'can:system.companies.add']);
Route::post('/company/{id}', 'CompanyController@edit')->name('edit-company')->middleware(['can:settings.module.access', 'can:system.companies.edit']);

Route::post('/company-activate', 'CompanyController@activate_company')->name('activate-company');

//######################################SYSTEM###########################################
Route::get('/system-planner/dashboard', 'Event\EventController@dashboard')->name('system-planner.dashboard')->middleware('can:calendar.module.access');
Route::get('/full-calendar/view/{date?}', 'Event\EventController@index')->name('full-calendar')->middleware('can:calendar.module.access');
Route::get('/system-planner/tasks', 'Event\EventController@tasks')->name('system-planner.tasks')->middleware('can:calendar.module.access');
Route::get('/system-planner/schedule-sampling', 'Event\EventController@scheduleSamplingIndex')->name('system-planner.schedule-sampling')->middleware('can:calendar.module.access');
Route::get('/system-planner/schedule-sampling/{schedule}/sample-collection-label', 'Event\EventController@samplingScheduleCollectionLabel')->name('system-planner.schedule-sampling.sample-collection-label')->middleware('can:calendar.module.access');
Route::get('/system-planner/schedule-sampling/{schedule}', 'Event\EventController@scheduleSamplingShow')->name('system-planner.schedule-sampling.show')->middleware('can:calendar.module.access');
Route::get('/system-planner/fill-sampling-forms', 'Event\EventController@fillSamplingFormsIndex')->name('system-planner.fill-sampling-forms')->middleware('can:calendar.module.access');
Route::get('/system-planner/fill-sampling-forms/fill/{sampleType}', 'Event\EventController@fillSamplingFormsFill')->name('system-planner.fill-sampling-forms.fill')->middleware('can:calendar.module.access');
Route::get('/system-planner/actual-collections', 'Event\EventController@actualCollectionsIndex')->name('system-planner.actual-collections')->middleware('can:calendar.module.access');
Route::get('/system-planner/kpi-reports', 'Event\EventController@kpiReportsIndex')->name('system-planner.kpi-reports')->middleware('can:calendar.module.access');
Route::post('/full-calendar/add', 'Event\EventController@created')->name('full-calendar-create')->middleware('can:sampling-planner.components.all events.add');

Route::post('/fullcalendareventmaster/create', 'Event\EventController@create')->middleware('can:sampling-planner.components.all events.add');
Route::post('/full-calendar/update', 'Event\EventController@update')->name('editRoutineEvent')->middleware('can:sampling-planner.components.all events.edit');
Route::post('/fullcalendareventmaster/delete', 'Event\EventController@destroy')->middleware('can:sampling-planner.components.all events.delete');
Route::post('/fullcalendar/user-task', 'Event\EventController@getEventByUser')->middleware('can:sampling-planner.components.all events.view');
Route::get('/fullcalendar/print-user-task', 'Event\EventController@printUserEvents')->name('printUserEvents')->middleware('can:sampling-planner.components.all events.view');
Route::post('/full-callendar/edit', 'Event\EventController@editEvent')->name('editEvent')->middleware('can:sampling-planner.components.all events.edit');
Route::post('/delete-events', 'Event\EventController@delete_event')->name('delete-events')->middleware('can:sampling-planner.components.all events.delete');
Route::get('/get/event/id/{id}', 'Event\EventController@getEvent')->name('getEventByID')->middleware('can:sampling-planner.components.all events.view');
Route::post('/event/occurrence/update-status', 'Event\EventController@updateOccurrenceStatus')->name('updateOccurrenceStatus')->middleware('can:sampling-planner.components.all events.edit');

//#############CONFIGURATIONS END###############################################################################
//######################################dashboard Ajax###############################################
Route::get('/getSamplesByCustomer/{year?}', 'Lab\LabDashboardController@getSamplesByCustomer')->name('getSamplesByCustomer')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getSamplesByGps/{year?}', 'Lab\LabDashboardController@getSamplesByGps')->name('getSamplesByGps')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getSamplesByMonth/{year?}', 'Lab\LabDashboardController@getSamplesByMonth')->name('getSamplesByMonth')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getsamplesBySampletype/{year?}', 'Lab\LabDashboardController@getsamplesBySampletype')->name('getsamplesBySampletype')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getSamplesByLabSection', 'Lab\LabDashboardController@getSamplesByLabSection')->name('getSamplesByLabSection')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getSamplesByStatus', 'Lab\LabDashboardController@getSamplesByStatus')->name('getSamplesByStatus')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getTestingMatrix', 'Lab\LabDashboardController@getTestingMatrix')->name('getTestingMatrix')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getActiveMethods', 'Lab\LabDashboardController@getActiveMethods')->name('getActiveMethods')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getSmartGridTasks', 'Lab\LabDashboardController@getSmartGridTasks')->name('getSmartGridTasks')->middleware('can:laboratory.components.dashboard.view');
Route::get('/getCustomerSampleTypes', 'Lab\LabDashboardController@getCustomerSampleTypes')->name('getCustomerSampleTypes')->middleware('can:laboratory.components.dashboard.view');

//######################################dashboard Ajax###############################################

//#####################LABS######################################################################################
Route::get('/lab-home', 'LabController@index')->name('lab-home')->middleware('can:laboratory.components.dashboard.view');
Route::get('/lab-dashboard', 'Lab\LabDashboardController@index')->name('dashboard-lab')->middleware('can:laboratory.module.access');
Route::get('/lab-dashboard/personal', [PersonalDashboardController::class, 'index'])
    ->name('dashboard-lab-personal')
    ->middleware(['can:laboratory.module.access', 'can:laboratory.components.all samples.view']);

Route::get('/lab/equipment-requests', function () {
    return view('layouts.lab.equipment-requests.index');
})->name('lab.equipment-requests.index')->middleware('can:laboratory.components.equipment-requests.view');

Route::get('/labs', 'LabController@index')->name('labs')->middleware('can:laboratory.components.labs.view');
Route::get('/lab/{labid?}/analysis-types', 'AnalysisTypeController@index')->name('show-lab-analysis-types')->middleware('can:laboratory.components.analysis types.view');
Route::post('/labs', 'LabController@add')->name('add-labs')->middleware('can:laboratory.components.labs.add');
Route::post('/lab/{id}', 'LabController@edit')->name('edit-lab')->middleware('can:laboratory.components.labs.edit');

Route::get('/analytes', [\App\Http\Controllers\LivewireControllers\LabAppController::class, 'analytes'])->name('analytes')->middleware('can:laboratory.components.analytes.view');

Route::get('/shelf-life/studies', [LabAppController::class, 'shelfLifeStudies'])
    ->name('shelf-life.studies.index')
    ->middleware('can:laboratory.components.shelf-life.view');

Route::get('/shelf-life/studies/{study}', [LabAppController::class, 'shelfLifeStudyShow'])
    ->name('shelf-life.studies.show')
    ->middleware('can:laboratory.components.shelf-life.view');

Route::get('/sample-types', 'SampleTypeController@index')->name('sample-types')->middleware('can:laboratory.components.sample-types.view');
Route::get('/sample-type/{id}', 'SampleTypeController@show')->name('sample-type')->middleware('can:laboratory.components.sample-types.view');
Route::post('/sample-type-delete', 'SampleTypeController@delete_sample_type')->name('delete-sample-type')->middleware('can:laboratory.components.sample-types.delete');
Route::post('/sample-types', 'SampleTypeController@add')->name('add-sample-types')->middleware('can:laboratory.components.sample-types.add');
Route::post('/sample-conditions', 'SampleConditionController@add')->name('add-sample-conditions')->middleware('can:laboratory.components.sample-types.add');
Route::post('/sample-condition/Edit', 'SampleConditionController@edit')->name('edit-sample-condition')->middleware('can:laboratory.components.sample-types.edit');
Route::post('/sample-type/{id}', 'SampleTypeController@edit')->name('edit-sample-type')->middleware('can:laboratory.components.sample-types.edit');
Route::post('/add/sample-type-qualification/{id}', 'Lab\Samples\SampleQualificationsController@add')->name('add-sample-type-qualification')->middleware('can:laboratory.components.sample-types.add');
Route::post('/edit/sample-type-qualification/{id}', 'Lab\Samples\SampleQualificationsController@edit')->name('edit-sample-type-qualification')->middleware('can:laboratory.components.sample-types.edit');
Route::post('/delete/sample-type-qualification/{id}', 'Lab\Samples\SampleQualificationsController@delete')->name('delete-sample-type-qualification')->middleware('can:laboratory.components.sample-types.delete');

// Livewire Sample Types Management
Route::get('/livewire/sample-types', [LabAppController::class, 'sampleTypes'])
    ->name('livewire.sample-types')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Monitoring Management
Route::get('/livewire/monitoring', [LabAppController::class, 'monitoring'])
    ->name('livewire.monitoring')
    ->middleware('can:laboratory.components.labs.view');

Route::get('/livewire/monitoring/template/create', [LabAppController::class, 'createMonitoringTemplate'])
    ->name('monitoring.template.create')
    ->middleware('can:laboratory.components.labs.view');

Route::get('/livewire/monitoring/template/{template}/edit', [LabAppController::class, 'editMonitoringTemplate'])
    ->name('monitoring.template.edit')
    ->middleware('can:laboratory.components.labs.view');

Route::get('/livewire/monitoring/export-lws-011', [\App\Http\Controllers\Monitoring\MonitoringExportController::class, 'exportLws011'])
    ->name('monitoring.export-lws-011')
    ->middleware('can:laboratory.components.labs.view');

// Remedies Management Routes
Route::get('/remedies', [LabAppController::class, 'remedies'])
    ->name('remedies.index')
    ->middleware('can:laboratory.components.sample-types.view');

Route::get('/remedies/{remedyHeaderId}/details', [LabAppController::class, 'remedyDetails'])
    ->name('remedies.details')
    ->middleware('can:laboratory.components.sample-types.view');

// Rating Hub Management Routes
Route::get('/rating-hub', [LabAppController::class, 'ratingHub'])
    ->name('ratings.index')
    ->middleware('can:laboratory.components.sample-types.view');

Route::get('/rating-hub/{ratingHeaderId}/details', [LabAppController::class, 'ratingDetails'])
    ->name('ratings.details')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Analysis Types Management
Route::get('/livewire/analysis-types/{sampleTypeId}', [LabAppController::class, 'analysisTypes'])
    ->name('livewire.analysis-types')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Elements Management
Route::get('/livewire/elements/{analysisTypeId}', [LabAppController::class, 'elements'])
    ->name('livewire.elements')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Standards Management
Route::get('/livewire/standards', [StandardsController::class, 'index'])
    ->name('livewire.standards')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Standard Analytes Management
Route::get('/livewire/standard-analytes/{standardId}', [StandardsController::class, 'standardAnalytes'])
    ->name('livewire.standard-analytes')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Report Formats Management
Route::get('/livewire/report-formats', [LabAppController::class, 'reportFormats'])
    ->name('livewire.report-formats')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Workflow Approval Configuration
Route::get('/livewire/workflow-approvals', [LabAppController::class, 'workflowApprovals'])
    ->name('livewire.workflow-approvals')
    ->middleware('can:laboratory.components.checklist-approvals.view');

// Livewire Dedicated Report Format Builder
Route::get('/livewire/report-formats/builder/{id}', [LabAppController::class, 'reportFormatBuilder'])
    ->name('livewire.report-formats.builder')
    ->middleware('can:laboratory.components.sample-types.view');

// Livewire Standard Manager
Route::get('/livewire/standard-manager', [LabAppController::class, 'standardManager'])
    ->name('livewire.standard-manager')
    ->middleware('can:laboratory.components.sample-types.view');

Route::redirect('/livewire/labs', '/labs')->name('livewire.labs');
Route::get('/livewire/labs/{lab}', function () {
    return redirect()->route('labs');
})->name('livewire.labs.show')->middleware('can:laboratory.components.labs.view');

// Livewire Test Page (legacy TRF debug route removed)
Route::get('/livewire-test', function () {
    return response('Legacy TestRequestForm debug route removed. Use Submission Form TRF templates.', 410)
        ->header('Content-Type', 'text/plain');
})->name('livewire-test');



// Livewire CRM Management Routes
Route::get('/crm/dashboard', [CRMAppController::class, 'dashboard'])
    ->name('crm.dashboard')
    ->middleware('can:crm.dashboard.view');

Route::get('/livewire/customers', [CRMAppController::class, 'customers'])
    ->name('livewire.customers')
    ->middleware('can:crm.customers.view');

Route::get('/livewire/customers/{customerId}/profile', [CRMAppController::class, 'customerProfile'])
    ->name('livewire.customer-profile')
    ->middleware('can:crm.customers.view');

Route::get('/crm/complaints-manager/{stage?}', [CRMAppController::class, 'complaintsManager'])
    ->name('crm.complaints-manager')
    ->middleware('can:crm.complaints.view');

// Livewire Billing Management Routes
Route::get('/billing/invoicable-items', function () {
    return view('layouts.billing.invoicable-items-index');
})->name('billing.invoicable-items')->middleware('can:laboratory.components.proforma invoices.view');

Route::get('/billing/dynamics-customers', function () {
    return view('layouts.billing.dynamics-customers-index');
})->name('billing.dynamics-customers')->middleware('can:laboratory.components.proforma invoices.view');

Route::get('/billing/currencies', function () {
    return view('layouts.billing.currencies-index');
})->name('billing.currencies')->middleware('can:laboratory.components.proforma invoices.view');

Route::get('/billing/invoices', function () {
    return view('layouts.billing.invoices-index');
})->name('billing.invoices')->middleware('can:laboratory.components.proforma invoices.view');

Route::get('/billing/invoices/{id}', function (string $id) {
    return view('layouts.billing.invoice-show', ['invoiceId' => $id]);
})->name('billing.invoices.show')->middleware('can:laboratory.components.proforma invoices.view');

Route::get('/billing/quotations', function () {
    $customers = \App\Models\CRM\CRMCustomer::query()
        ->where('active', 1)
        ->orderBy('name')
        ->get();

    return view('layouts.billing.quotations-index', compact('customers'));
})->name('billing.quotations')->middleware('can:laboratory.components.quotation.view');

Route::get('/billing/sales-order/create', function () {
    $batchCodes = request()->get('batches', []);
    return view('layouts.billing.sales-order-create', ['batchCodes' => $batchCodes]);
})->name('billing.sales-order.create')->middleware('can:laboratory.components.draft-invoices.add');

Route::get('/analysis-types', 'AnalysisTypeController@index')->name('analysis-types')->middleware('can:laboratory.components.analysis types.view');
Route::post('/analysis-types', 'AnalysisTypeController@add')->name('add-analysis-types')->middleware('can:laboratory.components.analysis types.add');
Route::post('/analysis-type/{id}', 'AnalysisTypeController@edit')->name('edit-analysis-type')->middleware('can:laboratory.components.analysis types.edit');
Route::get('/analysis-type/{id}', 'AnalysisTypeController@show')->name('analysis-type')->middleware('can:laboratory.components.analysis types.view');
Route::get('/get/Analyte/{id}/Methods', 'AnalysisElementsController@getAnalyteMethods')->name('getAnalyteMethods')->middleware('can:laboratory.components.analysis types.view');
Route::get('/change/Labsection-By-Captured-Results', 'AnalysisElementsController@changeLabsectionByCapturedResults')->name('changeLabsectionByCapturedResults')->middleware('can:laboratory.components.analysis types.view');

Route::post('/add-analyte-guide', 'AnalysisMethodElementsController@update_guide')->name('add-analyte-guide')->middleware('can:laboratory.components.methods.edit');
Route::post('/clone-analyte-guide', 'AnalysisMethodElementsController@clone_analysis_guide')->name('clone_analysis_guide')->middleware('can:laboratory.components.methods.edit');
Route::post('/delete-analyte-guide', 'AnalysisMethodElementsController@delete_analysis_guide')->name('delete_analysis_guide')->middleware('can:laboratory.components.methods.delete');

Route::post('/analysis-elements', 'AnalysisElementsController@add')->name('add-analysis-elements')->middleware('can:laboratory.components.analysis types.add');
Route::post('/analysis-element/{id}', 'AnalysisElementsController@edit')->name('edit-analysis-element')->middleware('can:laboratory.components.analysis types.edit');
Route::get('/move-analysis-analyte/{direction}/{analysis}/{element}', 'AnalysisElementsController@move_analysis_analyte')->name('move-analysis-analyte')->middleware('can:laboratory.components.analysis types.edit');
Route::post('/delete-Analysis-Element', 'AnalysisElementsController@deleteAnalysisElement')->name('deleteAnalysisElement')->middleware('can:laboratory.components.analysis types.delete');

Route::get('/analysis-methods', [LabAppController::class, 'methods'])
    ->name('analysis-methods')
    ->middleware('can:laboratory.components.methods.view');
Route::post('/analysis-methods', 'AnalysisMethodController@add')->name('add-analysis-methods')->middleware('can:laboratory.components.methods.add');
Route::post('/analysis-method/edit', 'AnalysisMethodController@edit')->name('edit-analysis-method')->middleware('can:laboratory.components.methods.edit');
Route::get('/analysis-method/{id}', [LabAppController::class, 'methodDetail'])
    ->name('analysis-method')
    ->middleware('can:laboratory.components.methods.view');
Route::post('/send-for-validation', 'AnalysisMethodController@sendForValidation')->name('send-for-validation')->middleware('can:laboratory.components.methods.edit');

// Method Validation Routes
Route::prefix('analysis-methods/method-validation')->group(function () {
    Route::get('/method-registration', 'Lab\\MethodValidation\\MethodRegistrationController@index')
        ->name('method-validation.registration')
        ->middleware('can:laboratory.components.method-validation.registration.view');
    Route::get('/method-registration/{id}', 'Lab\\MethodValidation\\MethodRegistrationController@show')
        ->name('method-validation.registration.show')
        ->middleware('can:laboratory.components.method-validation.registration.view');
    Route::get('/data-review-analysis', 'Lab\\MethodValidation\\DataReviewAnalysisController@index')
        ->name('method-validation.data-review')
        ->middleware('can:laboratory.components.method-validation.data-review.view');
    Route::get('/method-comparison/{methodId}', 'MethodValidationController@methodComparison')
        ->name('method-validation.comparison')
        ->middleware('can:laboratory.components.method-validation.data-review.view');
    Route::post('/process-action', 'MethodValidationController@processAction')
        ->name('method-validation.process-action')
        ->middleware('can:laboratory.components.method-validation.data-review.view');
});

// Uncertainty Budget Routes
Route::prefix('lab-uncertainty')->name('uncertainty-budgets.')->group(function () {
    Route::get('/', 'UncertaintyBudgetController@index')->name('index')->middleware('can:laboratory.components.uncertainty-budget.view');
    Route::get('/create', 'UncertaintyBudgetController@create')->name('create')->middleware('can:laboratory.components.uncertainty-budget.add');
    Route::post('/', 'UncertaintyBudgetController@store')->name('store')->middleware('can:laboratory.components.uncertainty-budget.add');
    Route::get('/{id}', 'UncertaintyBudgetController@show')->name('show')->middleware('can:laboratory.components.uncertainty-budget.view');
    Route::get('/{id}/edit', 'UncertaintyBudgetController@edit')->name('edit')->middleware('can:laboratory.components.uncertainty-budget.edit');
    Route::put('/{id}', 'UncertaintyBudgetController@update')->name('update')->middleware('can:laboratory.components.uncertainty-budget.edit');
    Route::delete('/{id}', 'UncertaintyBudgetController@destroy')->name('destroy')->middleware('can:laboratory.components.uncertainty-budget.delete');

    // Uncertainty Sources Routes
    Route::post('/{budgetId}/sources', 'UncertaintyBudgetController@addSource')->name('sources.store')->middleware('can:laboratory.components.uncertainty-budget.edit');
    Route::put('/sources/{sourceId}', 'UncertaintyBudgetController@updateSource')->name('sources.update')->middleware('can:laboratory.components.uncertainty-budget.edit');
    Route::delete('/sources/{sourceId}', 'UncertaintyBudgetController@deleteSource')->name('sources.destroy')->middleware('can:laboratory.components.uncertainty-budget.edit');

    // API Routes
    Route::get('/api/analytes', 'UncertaintyBudgetController@getAnalytes')->name('api.analytes');
    Route::get('/api/methods/{analyteId}', 'UncertaintyBudgetController@getMethods')->name('api.methods');
    Route::get('/api/sources/{sourceId}', 'UncertaintyBudgetController@getSource')->name('api.sources');
    Route::get('/api/existing-budgets/{analyteId}', 'UncertaintyBudgetController@getExistingBudgets')->name('api.existing-budgets');

    // Recalculate Route
    Route::post('/{budgetId}/recalculate', 'UncertaintyBudgetController@recalculate')->name('recalculate');
});

Route::post('/check_rft_no', 'SampleWorkFlowController@check_rft_no')->name('check_rft_no')->middleware('can:laboratory.components.rft form.view');
Route::post('/reject-approval-request', 'SampleWorkFlowController@return_batch_reception')->name('return_batch_reception')->middleware('can:laboratory.components.approve for analysis.edit');

Route::post('/analysis-method-elements', 'AnalysisMethodElementsController@add')->name('add-analysis-method-elements')->middleware('can:laboratory.components.methods.add');
Route::post('/analysis-method-element/{id}', 'AnalysisMethodElementsController@edit')->name('edit-analysis-method-element')->middleware('can:laboratory.components.methods.edit');
Route::post('/analysis-method-element/{id}/delete', 'AnalysisMethodElementsController@delete')->name('delete-analysis-method-element')->middleware('can:laboratory.components.methods.delete');
Route::post('/update-method-reagents/{method_id}', 'MethodReagentController@modify')->name('update-method-reagents')->middleware('can:laboratory.components.methods.edit');

Route::get('/reporting-units', 'ReportingUnitController@index')->name('reporting-units')->middleware('can:laboratory.components.reporting-units.view');
Route::get('/reporting-units/{module?}', 'ReportingUnitController@index')->name('inventory-reporting-units')->middleware('can:laboratory.components.reporting-units.view');
Route::post('/reporting-units', 'ReportingUnitController@add')->name('add-reporting-unit')->middleware('can:laboratory.components.reporting-units.add');
Route::post('/reporting-unit/{id}', 'ReportingUnitController@update')->name('edit-reporting-unit')->middleware('can:laboratory.components.reporting-units.edit');
Route::get('/reporting-unit/addAjax', 'ReportingUnitController@addAjax')->name('reporting-addAjax')->middleware('can:laboratory.components.reporting-units.add');

Route::get('/sample-analysis-stages', 'SampleAnalysisStageController@index')->name('sample-analysis-stages')->middleware('can:laboratory.components.sample-tracking-stages.view');
Route::post('/sample-analysis-stages', 'SampleAnalysisStageController@add')->name('add-sample-analysis-stage')->middleware('can:laboratory.components.sample-tracking-stages.add');
Route::post('/sample-analysis-stage/update', 'SampleAnalysisStageController@update')->name('update-sample_analysis_stage')->middleware('can:laboratory.components.sample-tracking-stages.edit');
Route::post('/sample-stages/delete', 'SampleAnalysisStageController@deleteStage')->name('delete-stage')->middleware('can:laboratory.components.sample-tracking-stages.delete');

Route::post('/sample-analysis-stages-to-sample-type/{sample_type_id}', 'SampleToSampleAnalysisStageController@add')->name('add-sample-analysis-stage-to-sample-type')->middleware('can:laboratory.components.sample-tracking-stages.add');
Route::post('/sample-analysis-stages-to-sample-type/{id}/inactivate', 'SampleToSampleAnalysisStageController@update')->name('update-sample-analysis-stage-to-sample-type')->middleware('can:laboratory.components.sample-tracking-stages.edit');

Route::get('/move-sample-type/{direction}/{analysis}/{element}', 'SampleTypeController@move_sample_types')->name('move-sample-type')->middleware('can:laboratory.components.sample-types.edit');

//#############################################Buffer###################################################
Route::get('/stock-monitoring/categories', 'Lab\BufferManagementController@categories_index')->name('stock-monitoring-categories')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/stock-management/sub-categories', 'Lab\BufferManagementController@stock_management_livewire_index')->name('stock_management_index')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/stock-monitoring/sub-categories-delete/{id}', 'Lab\BufferManagementController@delete_category')->name('delete_category')->middleware('can:laboratory.components.stock-monitoring.delete');
Route::post('/stock-monitoring/categories-add', 'Lab\BufferManagementController@add_lab_inventory_categories')->name('add_lab_inventory_categories')->middleware('can:laboratory.components.stock-monitoring.add');
Route::post('/stock-monitoring/filter', 'Lab\BufferManagementController@filter_data')->name('filter_data_category')->middleware('can:laboratory.components.stock-monitoring.view');
Route::post('/stock-monitoring/sub-categories-add', 'Lab\BufferManagementController@add_lab_sub_category')->name('add_lab_sub_inventory_categories')->middleware('can:laboratory.components.stock-monitoring.add');
Route::get('/stock-monitoring/sub-categories/show/{id}', 'Lab\BufferManagementController@stock_management_livewire_show')->name('show_lab_sub_category')->middleware('can:laboratory.components.stock-monitoring.view');
Route::post('/stock-monitoring/lab-category-item', 'Lab\BufferManagementController@add_lab_category_item')->name('add_lab_category_item')->middleware('can:laboratory.components.stock-monitoring.add');
Route::post('/stock-monitoring/lab-category-item/delete', 'Lab\BufferManagementController@delete_show_lab_category_item')->name('delete_show_lab_category_item')->middleware('can:laboratory.components.stock-monitoring.delete');
Route::post('/stock-monitoring/lab-category-item/edit', 'Lab\BufferManagementController@edit_lab_category_item')->name('edit_lab_category_item')->middleware('can:laboratory.components.stock-monitoring.edit');
Route::post('/stock-monitoring/lab-sub-category/edit', 'Lab\BufferManagementController@edit_lab_sub_category')->name('edit_lab_sub_category')->middleware('can:laboratory.components.stock-monitoring.edit');
Route::post('/stock-monitoring/lab-sub-category/delete', 'Lab\BufferManagementController@delete_sub_category')->name('delete_sub_category')->middleware('can:laboratory.components.stock-monitoring.delete');
Route::post('/stock-monitoring/lab-sub-category/clone', 'Lab\BufferManagementController@clone_sub_category')->name('clone_sub_category')->middleware('can:laboratory.components.stock-monitoring.add');

Route::get('/solutions-movement', 'Lab\BufferStockMovementController@livewire_index')->name('solution-movement-index')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/solutions-movement/show/{id}', 'Lab\BufferStockMovementController@livewire_show')->name('solution-movement-show')->middleware('can:laboratory.components.stock-monitoring.view');
Route::post('/solutions-movement/add', 'Lab\BufferStockMovementController@add')->name('solution-movement-add')->middleware('can:laboratory.components.stock-monitoring.add');

Route::get('/solutions-preparation', 'Lab\SolutionPreparationController@index')->name('solutions-preparation-index')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/solutions-preparation/create', 'Lab\SolutionPreparationController@create')->name('solutions-preparation-create')->middleware('can:laboratory.components.stock-monitoring.add');
Route::get('/solutions-preparation/show/{id}', 'Lab\SolutionPreparationController@show')->name('solutions-preparation-show')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/solutions-preparation/analysis-types/{sampleTypeId}', 'Lab\SolutionPreparationAjaxController@analysisTypes')->name('solutions-preparation-analysis-types')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/solutions-preparation/analytes/{analysisTypeId}', 'Lab\SolutionPreparationAjaxController@analytes')->name('solutions-preparation-analytes')->middleware('can:laboratory.components.stock-monitoring.view');
Route::get('/solutions-preparation/standard-limits/{analyteId}/{standardId}', 'Lab\SolutionPreparationAjaxController@standardLimits')->name('solutions-preparation-standard-limits')->middleware('can:laboratory.components.stock-monitoring.view');

Route::post('/stock-taking-counter/{id}/add', 'StockTakingCounterController@add')->name('add-stock-taking-counter')->middleware('can:inventory.components.stock-taking.edit');
Route::post('/stock-taking-counter/{id}/remove', 'StockTakingCounterController@remove')->name('remove-stock-taking-counter')->middleware('can:inventory.components.stock-taking.edit');

//#############################################Sample Workflow###################################################
Route::get('/get-Tat/Delayed/Sample', 'SampleWorkFlowController@getTatDelayedSample')->name('getTatDelayedSample')->middleware('can:laboratory.components.all samples.view');
Route::get('/awaiting/Approval/Samples/{status}', 'SampleWorkFlowController@awaitingApprovalSamples')->name('awaitingApprovalSamples')->middleware('can:laboratory.components.all samples.view');
Route::get('/updateTatCaptured', 'SampleWorkFlowController@updateTatCaptured')->name('updateTatCaptured')->middleware('can:laboratory.components.all samples.edit');
Route::get('/get/Tat/Batch/ApprovalCounter/Ajax/{status}', 'SampleWorkFlowController@getTatBatchApprovalCounterAjax')->name('getTatBatchApprovalCounterAjax')->middleware('can:laboratory.components.all samples.view');

Route::post('/process-raw-results/lab', 'SampleWorkFlowController@processRawResultsLab')->name('process-raw-results-lab')->middleware('can:laboratory.components.all samples.edit');
Route::get('/sample-workflow/kpis', 'Lab\Reports\SamplesReportsController@index')->name('sample-workflow.kpis')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/sample-workflow/request-for-testing', 'SampleWorkFlowController@requestForTesting')
    ->name('sample-workflow.request-for-testing')
    ->middleware('can:laboratory.components.rft form.view');
Route::get('/sample-workflow/request-for-testing/fill/{sampleType}', 'SampleWorkFlowController@requestForTestingFill')
    ->name('sample-workflow.request-for-testing.fill')
    ->middleware('can:laboratory.components.rft form.view');
Route::get('/sample-workflow/request-for-testing/form/{submissionForm}', 'SampleWorkFlowController@requestForTestingFillForm')
    ->name('sample-workflow.request-for-testing.fill-form')
    ->middleware('can:laboratory.components.rft form.view');
Route::get('/sample-workflow/{status?}', 'SampleWorkFlowController@index')->name('sample-workflow')->middleware('can:laboratory.components.all samples.view');
Route::post('/sample-workflow/assign-user', [SampleAssignmentController::class, 'store'])
    ->name('sample-workflow.assign-user')
    ->middleware('can:laboratory.components.sample-workflow.assign-user');
Route::get('/sample-workflow/assign-user/users', [SampleAssignmentController::class, 'users'])
    ->name('sample-workflow.assign-user.users')
    ->middleware('can:laboratory.components.sample-workflow.assign-user');
Route::get('/sample-submission-requests', 'SampleWorkFlowController@submissionRequestsIndex')
    ->name('sample-submission-requests.index')
    ->middleware('can:laboratory.components.all samples.view');

Route::get('/sample-submission-requests/create', 'SampleWorkFlowController@createSampleSubmissionRequest')
    ->name('sample-submission-requests.create')
    ->middleware('can:laboratory.components.all samples.add');

Route::get('/sample-submission-requests/{request}', 'SampleWorkFlowController@showSampleSubmissionRequest')
    ->name('sample-submission-requests.show')
    ->middleware('can:laboratory.components.all samples.view');

Route::get('/sample-submission-requests/{request}/supporting-documents/{instance}', 'SampleWorkFlowController@editSampleSubmissionSupportingDocument')
    ->name('sample-submission-requests.supporting-documents.edit')
    ->middleware('can:laboratory.components.all samples.view');

Route::put('/sample-submission-requests/{request}/supporting-documents/{instance}', 'SampleWorkFlowController@updateSampleSubmissionSupportingDocument')
    ->name('sample-submission-requests.supporting-documents.update')
    ->middleware('can:laboratory.components.all samples.edit');

Route::post('/sample-submission-requests', 'SampleWorkFlowController@storeSampleSubmissionRequest')
    ->name('sample-submission-requests.store')
    ->middleware('can:laboratory.components.all samples.add');

Route::post('/sample-submission-requests/{request}/booking-date/approve', 'SampleWorkFlowController@approveSampleSubmissionBookingDate')
    ->name('sample-submission-requests.booking-date.approve')
    ->middleware('can:laboratory.components.all samples.edit');

Route::post('/sample-submission-requests/{request}/booking-date/reschedule', 'SampleWorkFlowController@rescheduleSampleSubmissionBookingDate')
    ->name('sample-submission-requests.booking-date.reschedule')
    ->middleware('can:laboratory.components.all samples.edit');

Route::get('/sample-submission-requests/customer/{customer}/contacts', 'SampleWorkFlowController@getSubmissionRequestCustomerContacts')
    ->name('sample-submission-requests.customer-contacts')
    ->middleware('can:laboratory.components.all samples.add');
//   Route::get('/sample-workflow/{status?}/stage', 'SampleWorkFlowController@index')->name('sample-workflow')->middleware('can:laboratory.components.status.view');
Route::get('/sample-workflow/{status?}/stage', 'SampleWorkFlowController@index')->name('sample-workflow-stage')->middleware('can:laboratory.components.all samples.view');
Route::get('/sample-workflow/batch/{batch}/details/{client?}/{portal?}/{status?}', 'SampleWorkFlowController@show')->name('view-batch-details')->middleware('can:laboratory.components.all samples.view');
Route::get('/sample-workflow/batch/{batch}/request-test-worksheet-pdf', [\App\Http\Controllers\Lab\RequestTestExportController::class, 'downloadPdf'])
    ->name('batch.request-test-worksheet-pdf')
    ->middleware('can:laboratory.components.all samples.view');
Route::get('/sample-workflow/batch/{batch}/request-test-worksheet-print', [\App\Http\Controllers\Lab\RequestTestExportController::class, 'printPdf'])
    ->name('batch.request-test-worksheet-print')
    ->middleware('can:laboratory.components.all samples.view');
Route::get('/sample-workflow/batch/{batch}/request-test-results-excel', [\App\Http\Controllers\Lab\RequestTestExportController::class, 'downloadExcel'])
    ->name('batch.request-test-results-excel')
    ->middleware('can:laboratory.components.all samples.view');
Route::post('/sample-workflow/batch/{batch}/request-test-results-import', [\App\Http\Controllers\Lab\RequestTestExportController::class, 'importExcel'])
    ->name('batch.request-test-results-import')
    ->middleware('can:laboratory.components.all samples.edit');
Route::get('/sample-workflow/batch/{sample}/approval-checklist', [\App\Http\Controllers\Lab\SampleApprovalChecklistController::class, 'show'])
    ->name('sample-approval-checklist.show')
    ->middleware('can:laboratory.components.sample-approval-checklist.view');
Route::get('/sample-workflow/batch/{batch}/worksheets', 'WorksheetsController@index')
    ->name('batch-worksheets')
    ->middleware('can:laboratory.components.all samples.view');
Route::get('/sample-workflow/batch/{batch}/method-sequence', 'SampleWorkFlowController@getMethodSequence')->name('batch-method-sequence')->middleware('auth');
Route::get('/sample-workflow/batch/{batch}/method-sequences', 'SampleWorkFlowController@methodSequences')->name('batch.method-sequences')->middleware('auth');
Route::get('/sample-workflow/batch/{batch}/method-sequences-jquery', 'SampleWorkFlowController@methodSequences')->name('batch.method-sequences.jquery')->middleware('auth');
Route::get('/sample-workflow/batch/{batch}/method-sequences/{stageHeader}/samples', 'SampleWorkFlowController@getMethodSequenceSamples')->name('method-sequences.samples')->middleware('auth');
Route::post('/sample-workflow/batch/{batch}/method-sequences/auto-create-runs', 'SampleWorkFlowController@autoCreateFirstRunForBatch')->name('method-sequences.auto-create')->middleware('auth');
Route::get('/method-sequences/{stageHeader}/runs', 'SampleWorkFlowController@getMethodSequenceRuns')->name('method-sequences.runs')->middleware('auth');
Route::post('/method-sequences/runs', 'SampleWorkFlowController@createMethodSequenceRun')->name('method-sequences.create-run')->middleware('auth');
Route::delete('/method-sequences/runs/{run}', 'SampleWorkFlowController@deleteMethodSequenceRun')->name('method-sequences.delete-run')->middleware('auth');
Route::post('/method-sequences/tracks/{track}/start', 'SampleWorkFlowController@startMethodSequenceStage')->name('method-sequences.start-stage')->middleware('auth');
Route::post('/method-sequences/tracks/{track}/end', 'SampleWorkFlowController@endMethodSequenceStage')->name('method-sequences.end-stage')->middleware('auth');
Route::post('/method-sequences/tracks/{track}/update', 'SampleWorkFlowController@updateMethodSequenceStageData')->name('method-sequences.update-stage')->middleware('auth');
Route::post('/method-sequences/tracks/{track}/save-results', 'SampleWorkFlowController@saveMethodSequenceResults')->name('method-sequences.save-results')->middleware('auth');
Route::post('/method-sequences/tracks/{track}/result', 'SampleWorkFlowController@updateMethodSequenceStageResult')->name('method-sequences.update-result')->middleware('auth');
Route::get('/method-sequences/batch/{batch}/tracking-results', 'SampleWorkFlowController@getMethodSequenceTrackingResults')->name('method-sequences.tracking-results')->middleware('auth');
Route::post('/method-sequences/import-post-results-sheet', 'SampleWorkFlowController@importMethodSequencePostResultsSheet')->name('method-sequences.import-post-results-sheet')->middleware('auth');
Route::get('/method-sequences/batch/{batch}/track-sample-results', 'SampleWorkFlowController@getTrackSampleResults')->name('method-sequences.track-sample-results')->middleware('auth');
Route::post('/method-sequences/post-results', 'SampleWorkFlowController@postMethodSequenceResults')->name('method-sequences.post-results')->middleware('auth');
Route::get('/method-sequences/tracks/{track}/edit-data', 'SampleWorkFlowController@getEditStageData')->name('method-sequences.edit-data')->middleware('auth');
Route::get('/method-sequence-runs/tracks/{track}/solution-results', 'SampleWorkFlowController@getSolutionResultsForStep6')->name('method-sequence-runs.solution-results')->middleware('auth');
Route::get('/method-sequence-runs/tracks/{track}/sample-results', 'SampleWorkFlowController@getSampleResultsForStep6')->name('method-sequence-runs.sample-results')->middleware('auth');
Route::post('/method-sequence-runs/tracks/{track}/step6-remark', 'SampleWorkFlowController@calculateStep6SampleRemark')->name('method-sequence-runs.step6-remark')->middleware('auth');
Route::post('/method-sequence-runs/tracks/{track}/step6-standard-limit', 'SampleWorkFlowController@updateStep6TrackStandardLimit')->name('method-sequence-runs.step6-standard-limit')->middleware('auth');
// Temporary design route for procedure worksheet PDF template preview.
Route::get(
    '/sample-workflow/batch/{batch}/worksheets/{worksheet}/procedure-preview',
    'WorksheetsController@previewProcedureWorksheetPdf'
)->name('batch-worksheets.procedure-preview')->middleware('can:laboratory.components.all samples.view');
Route::get(
    '/sample-workflow/batch/{batch}/worksheets/print',
    'WorksheetsController@printWorksheet'
)->name('batch-worksheets.print')->middleware('can:laboratory.components.all samples.view');
Route::get(
    '/sample-workflow/batch/{batch}/worksheets/amspec/lws-056-salmonella',
    'WorksheetsController@printAmSpecLws056Salmonella'
)->name('batch-worksheets.amspec-lws056')->middleware('can:laboratory.components.all samples.view');
Route::post('/add-batch-info/{batch}', 'SampleWorkFlowController@add_batch_info')->name('add-batch-info')->middleware('can:laboratory.components.all samples.edit');
Route::post('/add-batch-samples/{batch}', 'SampleWorkFlowController@add_batch_samples')->name('add-batch-samples')->middleware('can:laboratory.components.all samples.edit');
Route::post('/add-new-samples', 'SampleWorkFlowController@add_batch_samples')->name('add-new-samples')->middleware('can:laboratory.components.all samples.edit');
Route::post('/sample-submission-requests/{request}/reception', 'SampleWorkFlowController@updateSampleSubmissionReception')->name('sample-submission-request-reception')->middleware('can:laboratory.components.all samples.edit');
Route::post('/delete-sample/{id}', 'SampleDetailsController@delete')->name('delete-sample')->middleware('can:laboratory.components.all samples.delete');
Route::post('/bulk-update-sample-data', 'SampleWorkFlowController@bulkUpdateSampleData')->name('bulk-update-sample-data')->middleware('can:laboratory.components.all samples.edit');

// Submission Form Integration Routes
Route::get('/sample-workflow-forms/submission-forms', 'SampleWorkFlowController@getAvailableSubmissionForms')->name('sample-workflow.submission-forms')->middleware('can:laboratory.components.rft form.view');
Route::post('/sample-workflow-forms/submission-forms/create-instance', 'SampleWorkFlowController@createSubmissionFormInstance')->name('sample-workflow.create-form-instance')->middleware('can:laboratory.components.rft form.add');
Route::get('/sample-submission-forms/forms', 'FormInstanceController@index')->name('sample-workflow.saved-forms')->middleware('can:laboratory.components.rft form.view');

// Sample Submissions Management Page (Livewire)
Route::get('/sample-submissions', function () {
    return view('layouts.lab.sample-workflow.sign-customer-focus-index');
})->name('sample-submissions')->middleware('can:laboratory.components.rft form.view');

Route::post('/print-labels', 'SampleWorkFlowController@print_labels')->name('print-labels')->middleware('can:laboratory.components.all samples.edit');
Route::post('/send-out-email-reports', 'SampleWorkFlowController@send_report_email')->name('send-out-email-reports')->middleware('can:laboratory.components.all samples.edit');
Route::get('/lab/batch/approve/{id}', 'SampleWorkFlowController@approve_batch')->name('approve-batch-analysis')->middleware('can:laboratory.components.approve for analysis.edit');

Route::post('/change-batch-workflow', 'SampleWorkFlowController@change_workflow_status')->name('change-batch-workflow')->middleware('can:laboratory.components.all samples.edit');
Route::post('/batch-approve-payment', 'SampleWorkFlowController@generate_batch_invoice')->name('generate_batch_invoice')->middleware('can:laboratory.components.generate invoice.view');
Route::post('/batch-payment-reminders', 'SampleWorkFlowController@send_payment_notification')->name('send_payment_notification')->middleware('can:laboratory.components.proforma invoices.edit');
Route::post('/return-back-verification', 'SampleWorkFlowController@return_back_verification')->name('return_back_verification')->middleware('can:laboratory.components.approve for analysis.edit');

Route::post('/update-invoice', 'SampleWorkFlowController@updateInvoiceDetails')->name('updateinvoicedetail')->middleware('can:laboratory.components.proforma invoices.edit');
Route::get('/get-invoice/itemData/{invoice_id}/{item_id}', 'SampleWorkFlowController@getInvoiceItemData')->name('getInvoiceItemData')->middleware('can:laboratory.components.proforma invoices.view');
// -----------------------------------SALES ORDERS----------------------
Route::post('/generate/batch-invoice/ajax', 'SampleWorkFlowController@generate_batch_invoice_ajax')->name('generate_batch_invoice_ajax')->middleware('can:laboratory.components.draft-invoices.add');
Route::get('/send/Sales-Order/{id}', 'SampleWorkFlowController@sendSalesOrder')->name('sendSalesOrder')->middleware('can:laboratory.components.draft-invoices.add');
Route::get('/delete/sales-order/{id}', 'SampleWorkFlowController@deleteSalesOrder')->name('deleteSalesOrder')->middleware('can:laboratory.components.draft-invoices.delete');
Route::post('/send/sales/order-ajax', 'SampleWorkFlowController@moveToLabAjax')->name('move-to-lab-ajax')->middleware('can:laboratory.components.draft-invoices.add');

Route::get('/zoho-item/analysis-types', 'SampleTypeController@zohotoAnalysisTypes')->name('zoho-item-analysis')->middleware('can:laboratory.components.draft-invoices.view');
Route::post('zoho/item/analysis-store', 'SampleTypeController@zohoAnalysisStore')->name('zoho-item-analysis-store')->middleware('can:laboratory.components.draft-invoices.add');
// -----------------------------------SALES ORDERS----------------------


Route::get('/send_notification_reminders', 'Event\EventController@send_notification_reminders')->name('send_notification_reminders');

Route::get('/regerateCustomerInvoice/{id}', 'SampleWorkFlowController@regerateCustomerInvoice')->name('regerateCustomerInvoice')->middleware('can:laboratory.components.proforma invoices.edit');
Route::get('/split-contact', 'SampleWorkFlowController@splitSchoolContacts')->name('/split-contact')->middleware('can:laboratory.components.all samples.view');
//############################################################################################################################
Route::get('/billing-quotation/{stage?}', 'Invoice\QuotationController@index')->name('quotation-index')->middleware('can:laboratory.components.quotation.view');
Route::get('/billing/quotations/kpi-export', 'Invoice\QuotationController@exportQuotationKpi')->name('quotation.kpi.export')->middleware('can:laboratory.components.quotation.view');
Route::get('/billing/analysis-options/{sampleTypeId}', 'Invoice\QuotationController@getAnalysisOptionsBySampleType')->name('billing.analysis-options')->middleware('can:laboratory.components.quotation.view');
Route::post('/billing/quotations/create-enquiry', 'Invoice\QuotationController@createEnquiryFromQuotation')->name('quotation.create-enquiry')->middleware('can:laboratory.components.quotation.add');
Route::get('/billing/change-quotation-workflow/{id}/{stage}', 'Invoice\QuotationController@change_quotation_workflow')->name('change_quotation_workflow')->middleware('can:laboratory.components.quotation.edit');
Route::get('/billing-add-quote-detail-index/{id}/{stage?}', 'Invoice\QuotationController@view_quote_header_detail')->name('add-qoute-details-view')->middleware('can:laboratory.components.quotation.view');
Route::post('/billing-add-quote-header', 'Invoice\QuotationController@add_quotation_header')->name('add-quotation-header')->middleware('can:laboratory.components.quotation.add');
Route::post('/api/get-currency-by-code', 'Invoice\QuotationController@getCurrencyByCode');
Route::post('/billing/quotations/{id}/suggest-line-pricing', 'Invoice\QuotationController@suggestManualLinePricing')->name('quotation.suggest_line_pricing')->middleware('can:laboratory.components.quotation.view');
Route::post('/billing/quotations/{id}/package-defaults', 'Invoice\QuotationController@packageDefaults')->name('quotation.package_defaults')->middleware('can:laboratory.components.quotation.view');
Route::post('/billing/quotations/elements/loq', 'Invoice\QuotationController@updateElementLoq')->name('quotation.update_element_loq')->middleware('can:laboratory.components.quotation.edit');
Route::post('/billing/add-quotation-detail/{id}', 'Invoice\QuotationController@add_quotation_detail')->name('add_quotation_detail')->middleware('can:laboratory.components.quotation.add');
Route::get('/billing-quotation-view-final/{id}/{stage?}', 'Invoice\QuotationController@view_quotation_final')->name('view_quotation_final')->middleware('can:laboratory.components.quotation.view');
Route::post('/billing/edit_quotation_detail', 'Invoice\QuotationController@edit_quotation_detail')->name('edit_quotation_detail')->middleware('can:laboratory.components.quotation.edit');
Route::get('/billing/delete_quotation_detail/{id}', 'Invoice\QuotationController@delete_quotation_detail')->name('delete_quotation_detail')->middleware('can:laboratory.components.quotation.delete');
Route::post('/billing/save_draft/{id}', 'Invoice\QuotationController@save_draft')->name('save_draft')->middleware('can:laboratory.components.quotation.edit');
Route::get('/billing/redirect_from_docs/{id}/{stage?}', 'Invoice\QuotationController@redirect_from_docs')->name('redirect_from_docs')->middleware('can:laboratory.components.quotation.view');
Route::get('/billing/clone_quotation/{id}', 'Invoice\QuotationController@clone_quotation')->name('clone_quotation')->middleware('can:laboratory.components.quotation.add');
Route::post('/billing/quotation/{id}/revision', 'Invoice\QuotationController@create_quotation_revision')->name('create_quotation_revision')->middleware('can:laboratory.components.quotation.add');
Route::post('/billing/save-quotation-final/{id}', 'Invoice\QuotationController@save_quotation_final')->name('save_quotation_final')->middleware('can:laboratory.components.quotation.edit');
Route::post('/billing/delete_quotation/{id}', 'Invoice\QuotationController@delete_quotation')->name('delete_quotation')->middleware('can:laboratory.components.quotation.delete');
Route::get('/billing/quotations/{id}/report/{token}', 'Invoice\QuotationController@publicReportView')->name('quotation.public.report');
Route::get('/billing/quotations/{id}/preview', 'Invoice\QuotationController@previewQuotation')->name('quotation.preview')->middleware('can:laboratory.components.quotation.view');
Route::get('/billing/quotations/{id}/preview.pdf', 'Invoice\QuotationController@streamQuotationPdf')->name('quotation.preview.pdf')->middleware('can:laboratory.components.quotation.view');
Route::get('/billing/print_quotation/{id}', 'Invoice\QuotationController@print_quotation')->name('print_quotation')->middleware('can:laboratory.components.quotation.view');
Route::post('/billing/upload_quotation/{id}', 'Invoice\QuotationController@upload_quotation')->name('upload_quotation')->middleware('can:laboratory.components.quotation.edit');
Route::post('/approve-workflow', 'Invoice\QuotationController@approve_workflow')->name('approve-workflow')->middleware('can:laboratory.components.quotation.edit');
Route::post('/convert-quote/batch', 'Invoice\QuotationController@convertQuoteToBatch')->name('convert-quote-batch')->middleware('can:laboratory.components.quotation.edit');

Route::post('/billing/payment-detail-add', 'InvoicePaymentDetailController@add')->name('payment-detail-add');
Route::post('/billing/payment-detail-edit', 'InvoicePaymentDetailController@edit')->name('payment-detail-edit');
Route::post('/billing/payment-detail-delete', 'InvoicePaymentDetailController@delete')->name('payment-detail-delete');

Route::post('/approve/ready-proccess', 'SampleWorkFlowController@approve_batch_begin_process')->name('approve_batch_begin_process')->middleware('can:laboratory.components.approve for analysis.edit');

Route::get('/fetch-sample-type/{id}', 'SampleWorkFlowController@fetch_sample_type')->name('fetch_sample_type')->middleware('can:laboratory.components.all samples.view');
Route::get('/fetch-sample-analytes/{id}/{analysis}/{detail?}', 'SampleWorkFlowController@fetch_sample_analyte')->name('fetch_sample_analytes')->middleware('can:laboratory.components.all samples.view');
Route::get('/fetch-detail-data/{id}', 'Invoice\QuotationController@get_quotation_detail')->name('get_quotation_detail');
Route::get('/addBatchSamplesDynamically', 'SampleWorkFlowController@addBatchSamplesDynamically')->name('addBatchSamplesDynamically')->middleware('can:laboratory.components.all samples.edit');

Route::post('/sample-workflow/staging/update/{id}', 'SampleWorkFlowController@updateStagingDetail')->name('update-staging-detail')->middleware('can:laboratory.components.all samples.edit');
Route::delete('/sample-workflow/staging/delete/{id}', 'SampleWorkFlowController@deleteStagingDetail')->name('delete-staging-detail')->middleware('can:laboratory.components.all samples.delete');

Route::post('filter-Quotations', 'Invoice\QuotationController@filterQuotations')->name('filterQuotations');
Route::get('populate/Quotation-Detail/Split', 'Invoice\QuotationController@populateQuotationDetailSplit')->name('populateQuotationDetailSplit');
Route::get('get/Labs-By-Analysis/Type-Id-Ajax', 'SampleWorkFlowController@getLabsByAnalysisTypeIdAjax')->name('getLabsByAnalysisTypeIdAjax')->middleware('can:laboratory.components.all samples.view');
Route::post('/add/Batch-Invoice', 'SampleWorkFlowController@addBatchInvoice')->name('addBatchInvoice')->middleware('can:laboratory.components.proforma invoices.add');


//#####################################################################################################################################

Route::post('/move-to-stage/{stage}/{batch_id}', 'SampleWorkFlowController@move_to_stage')->name('move-to-stage')->middleware('can:laboratory.components.all samples.edit');
Route::post('/move-to-workflow/{status}/{batch_id}', 'SampleWorkFlowController@move_to_workflow')->name('move-to-workflow')->middleware('can:laboratory.components.all samples.edit');
Route::post('/add-analytes-to-sample-analysis', 'SampleWorkFlowController@add_analyte_to_sample_analysis')->name('add-analytes-to-sample-analysis')->middleware('can:laboratory.components.all samples.edit');
Route::post('/capture-raw-results', 'SampleWorkFlowController@capture_raw_results')->name('capture-raw-results')->middleware('can:laboratory.components.all samples.edit');

// Captured Results Modal AJAX Routes
Route::post('/captured-results/update-parameter-settings', 'SampleWorkFlowController@updateParameterSettings')->name('update-parameter-settings')->middleware('can:laboratory.components.all samples.edit');
Route::post('/captured-results/update-standard-limit', 'SampleWorkFlowController@updateStandardLimit')->name('update-standard-limit')->middleware('can:laboratory.components.all samples.edit');
Route::post('/captured-results/update-result', 'SampleWorkFlowController@updateResult')->name('update-result')->middleware('can:laboratory.components.all samples.edit');
Route::get('/captured-results/get-parameter-settings/{resultId}', 'SampleWorkFlowController@getParameterSettings')->name('get-parameter-settings')->middleware('can:laboratory.components.all samples.view');
Route::get('/captured-results/get-standard-settings/{resultId}', 'SampleWorkFlowController@getStandardSettings')->name('get-standard-settings')->middleware('can:laboratory.components.all samples.view');

Route::post('/process-results/{batch_id}', 'SampleWorkFlowController@process_results')->name('process-results')->middleware('can:laboratory.components.all samples.edit');
Route::match(['get', 'post'], '/process-raw-results/{batch_id}', 'SampleWorkFlowController@process_results')->name('process-raw-results')->middleware('can:laboratory.components.all samples.view');
Route::post('/report-interpretations/{batch_id}', 'ReportHeaderDetailController@report_interpretations')->name('report-interpretations')->middleware('can:laboratory.components.lab-reports.edit');
Route::get('/process-pdf-report/{batch_id}/{report_format}', 'ReportHeaderDetailController@process_pdf_report')->name('process-pdf-report')->middleware('can:laboratory.components.lab-reports.view');
Route::get('colorQrCode/', 'ReportHeaderDetailController@colorQrCode')->name('colorQrCode')->middleware('can:laboratory.components.lab-reports.view');

Route::get('/fetch-unit-stuff/{name}/{client}', 'SampleWorkFlowController@fetch_unit_stuff')->name('fetch-unit-stuff')->middleware('can:laboratory.components.all samples.view');
Route::get('/mail-report', 'MailController@html_email')->name('mail-report');

Route::post('/lab/delete/batch', 'SampleWorkFlowController@delete_batch')->name('delete-batch')->middleware('can:laboratory.components.all samples.delete');
Route::post('/sample-interpretations/{sample_id}', 'ReportHeaderDetailController@sample_interpretations')->name('sample-interpretations')->middleware('can:laboratory.components.lab-reports.edit');

Route::post('/add_batch_attachment', 'SampleWorkFlowController@add_batch_attachment')->name('add_batch_attachment')->middleware('can:laboratory.components.all samples.edit');
Route::post('/sample-workflow/batch/{batch}/regenerate-submission-form', 'SampleWorkFlowController@regenerateSubmissionForm')->name('regenerate-submission-form')->middleware('can:laboratory.components.rft form.edit');
Route::post('/store-attachment-type', 'SampleWorkFlowController@store_attachment_type')->name('store-attachment-type')->middleware('can:laboratory.components.all samples.edit');
Route::post('/delete_batch_attachmment', 'SampleWorkFlowController@delete_batch_attachmment')->name('delete_batch_attachmment')->middleware('can:laboratory.components.all samples.delete');
Route::post('/merge-attachments', 'SampleWorkFlowController@merge_attachments')->name('merge-attachments')->middleware('can:laboratory.components.all samples.edit');
Route::get('/batch/attachments/{id}/download', 'SampleWorkFlowController@downloadBatchAttachment')->name('download-attachment')->middleware('can:laboratory.components.all samples.view');

// PDF Annotation routes
Route::get('/test-request-form/preview-draft', [\App\Http\Controllers\TestRequestFormController::class, 'previewDraft'])->name('test-request-form.preview-draft');
Route::get('/test-request-form/{instance}/preview', [\App\Http\Controllers\TestRequestFormController::class, 'preview'])->name('test-request-form.preview');
Route::get('/test-request-form/{instance}/pdf', [\App\Http\Controllers\TestRequestFormController::class, 'pdf'])->name('test-request-form.pdf');
Route::get('/test-request-form/{instance}/download', [\App\Http\Controllers\TestRequestFormController::class, 'download'])->name('test-request-form.download');
Route::post('/test-request-form/{instance}/regenerate', [\App\Http\Controllers\TestRequestFormController::class, 'regenerate'])->name('test-request-form.regenerate');

Route::get('/batch/acceptance-pdf/{id}', 'SampleWorkFlowController@viewAcceptancePdf')->name('view-acceptance-pdf');
Route::get('/batch/receipt-notification-pdf/{id}', 'SampleWorkFlowController@viewReceiptNotificationPdf')->name('view-receipt-notification-pdf');
Route::get('/sample-workflow/batch/{batch}/workflow-documents/quotation.pdf', 'SampleWorkFlowController@viewBatchQuotationPdf')
    ->name('batch.workflow-quotation.pdf')
    ->middleware('can:laboratory.components.all samples.view');
Route::get('/batch/case-file-pdf/{id}', 'SampleWorkFlowController@viewCaseFilePdf')->name('view-case-file-pdf');

Route::get('/batch/attachments/{id}/annotate', 'SampleWorkFlowController@showAnnotationPage')->name('show-pdf-annotation-page')->middleware('can:laboratory.components.all samples.edit');
Route::post('/batch/attachments/annotate/save', 'SampleWorkFlowController@saveAnnotatedPdf')->name('save-annotated-pdf')->middleware('can:laboratory.components.all samples.edit');
Route::post('/batch/attachments/annotate/upload-image', 'SampleWorkFlowController@uploadAnnotationImage')->name('upload-annotation-image')->middleware('can:laboratory.components.all samples.edit');
Route::get('/batch/attachments/{id}/annotations', 'SampleWorkFlowController@getAnnotations')->name('get-pdf-annotations')->middleware('can:laboratory.components.all samples.view');
Route::post('/batch/attachments/{id}/annotations/delete', 'SampleWorkFlowController@deleteAnnotations')->name('delete-pdf-annotations')->middleware('can:laboratory.components.all samples.delete');

Route::post('/lab/batch/ammendment', 'BatchAmmendmentController@add')->name('add-batch-ammendment')->middleware('can:laboratory.components.all samples.edit');

//#####################LABS######################################################################################

//############################################INVENTORY##########################################################

Route::get('/inventory-home', 'HomeController@inventory')->name('inventory-home')->middleware('can:inventory.module.access');
Route::get('/inventory-activity', 'InventoryItemController@index')->name('inventory-activity')->middleware('can:inventory.components.inventory-movement.view');
Route::get('/inventory-activity/server-side', 'InventoryItemController@activity_serverside')->name('get-stock-movement')->middleware('can:inventory.components.inventory-movement.view');

//#REPORTS##
Route::get('/inventory-reports', 'ReportGeneratorController@inventory_reports')->name('inventory-reports')->middleware('can:inventory.components.inventory-movement.view');
Route::post('report/configuration/update/{id}', 'ReportGeneratorController@save_report')->name('update_report')->middleware('can:inventory.components.inventory-movement.view');
Route::get('/reports/fields/{table}', 'ReportGeneratorController@fields')->name('fields')->middleware('can:inventory.components.inventory-movement.view');
Route::post('/reports/save', 'ReportGeneratorController@store')->name('store_report')->middleware('can:inventory.components.inventory-movement.view');
Route::post('/reports/fetch', 'ReportGeneratorController@fetch')->name('fetch_report')->middleware('can:inventory.components.inventory-movement.view');
Route::post('/reports/delete', 'ReportGeneratorController@delete')->name('delete_report')->middleware('can:inventory.components.inventory-movement.view');
Route::post('/reports/print', 'ReportGeneratorController@print')->name('report_print')->middleware('can:inventory.components.inventory-movement.view');
Route::post('/reports/csv', 'ReportGeneratorController@exportCsv')->name('report_csv')->middleware('can:inventory.components.inventory-movement.view');

Route::get('/reports/consumption-reports', 'ReportGeneratorController@consumption')->name('consumption-reports')->middleware('can:inventory.components.inventory-movement.view');


Route::get('/inventory-categories', 'InventoryCategoriesController@index')->name('inventory-categories')->middleware('can:inventory.components.categories.view');
Route::post('/inventory-categories', 'InventoryCategoriesController@add')->name('add-inventory-category')->middleware('can:inventory.components.categories.add');
Route::post('/inventory-category/{id}', 'InventoryCategoriesController@edit')->name('edit-inventory-category')->middleware('can:inventory.components.categories.edit');
Route::post('/inventory-category/{id}/delete', 'InventoryCategoriesController@destroy')->name('delete-inventory-category')->middleware('can:inventory.components.categories.delete');
Route::get('/inventory-category/{id}', 'InventoryCategoriesController@show')->name('show-inventory-category')->middleware('can:inventory.components.categories.view');
Route::post('/inventory-category/set-default-store/{id}', 'InventoryCategoriesController@set_default_store')->name('set-default-store')->middleware('can:inventory.components.categories.edit');

Route::post('/change-brand-details/{id}', 'ItemBrandController@edit')->name('change-brand-image')->middleware('can:inventory.components.categories.edit');
Route::post('/add-item-brand/{subcategory}', 'ItemBrandController@add')->name('add-item-brand')->middleware('can:inventory.components.categories.edit');
Route::post('/delete-item-brand/{id}', 'ItemBrandController@delete')->name('delete-item-brand')->middleware('can:inventory.components.categories.delete');

Route::get('/inventory-stores', 'InventoryStoreController@index')->name('inventory-stores')->middleware('can:inventory.components.store.view');
Route::post('/inventory-stores', 'InventoryStoreController@add')->name('add-inventory-store')->middleware('can:inventory.components.store.add');
Route::post('/inventory-store/{id}', 'InventoryStoreController@edit')->name('edit-inventory-store')->middleware('can:inventory.components.store.edit');
Route::post('/add-store-cost-center/{id}', 'InventoryStoreController@add_cost_center')->name('add-store-cost-center')->middleware('can:inventory.components.store.edit');
Route::post('/remove-store-cost-center/{id}', 'InventoryStoreController@remove_cost_center')->name('remove-store-cost-center')->middleware('can:inventory.components.store.edit');
Route::post('/inventory-store/{id}/delete', 'InventoryStoreController@delete')->name('delete-inventory-store')->middleware('can:inventory.components.store.delete');

Route::get('/inventory-store-slots/{store}', 'InventoryStoreSlotController@index')->name('inventory-store-slots')->middleware('can:inventory.components.store.view');
Route::post('/inventory-store-slots/{store}', 'InventoryStoreSlotController@add')->name('add-inventory-store-slot')->middleware('can:inventory.components.store.add');
Route::post('/inventory-store-slots/{id}/slot', 'InventoryStoreSlotController@edit')->name('edit-inventory-store-slot')->middleware('can:inventory.components.store.edit');
Route::post('/inventory-store-slots/{id}/delete', 'InventoryStoreSlotController@delete')->name('delete-inventory-store-slot')->middleware('can:inventory.components.store.delete');

Route::get('/inventory-slot-contents/{slot}/{store}', 'InventoryStoreSlotContentController@index')->name('inventory-slot-contents')->middleware('can:inventory.components.store.view');
Route::post('/inventory-slot-contents/{slot}/{store}', 'InventoryStoreSlotContentController@add')->name('add-inventory-slot-content')->middleware('can:inventory.components.store.add');

//############################################SUBMISSION FORMS##########################################################
Route::prefix('supporting-documents')->name('supporting-documents.')->middleware('auth')->group(function () {
    Route::get('/templates', function () {
        return view('livewire.supporting-documents.template-manager-page');
    })->name('templates.index')->middleware('can:laboratory.components.rft form.view');

    Route::get('/templates/{template}', function (int $template) {
        return view('livewire.supporting-documents.template-editor-page', ['templateId' => $template]);
    })->name('templates.edit')->middleware('can:laboratory.components.rft form.edit');
});

Route::prefix('submission-forms')->name('submission-forms.')->middleware('auth')->group(function () {
    Route::get('/', 'SubmissionFormController@index')->name('index')->middleware('can:laboratory.components.rft form.view');
    Route::get('/create', 'SubmissionFormController@create')->name('create')->middleware('can:laboratory.components.rft form.add');
    Route::post('/', 'SubmissionFormController@store')->name('store')->middleware('can:laboratory.components.rft form.add');

    // Page layout metadata (slots + buttons) for selected target routes
    Route::get('/page-layout', 'SubmissionFormController@getPageLayout')->name('page-layout');

    // Template form type quick-create for dropdown usage
    Route::post('/template-form-types', 'SubmissionFormController@storeTemplateFormType')->name('template-form-types.store')->middleware('can:laboratory.components.rft form.add');

    // Dynamic Options for Custom Elements (must be before /{submissionForm} route)
    Route::get('/dynamic-options', 'SubmissionFormController@getDynamicOptions')->name('dynamic-options')->middleware('can:submission-forms.access');
    Route::get('/user-signature', 'SubmissionFormController@getUserSignature')->name('user-signature')->middleware('can:submission-forms.access');
    Route::get('/contact-signature', 'SubmissionFormController@getContactSignature')->name('contact-signature')->middleware('can:submission-forms.access');
    Route::post('/contact-signature', 'SubmissionFormController@saveContactSignature')->name('save-contact-signature')->middleware('can:submission-forms.access');

    // Quick Store Routes for Modal Forms (must be before /{submissionForm} route)
    Route::post('/quick-store/client', 'SubmissionFormController@quickStoreClient')->name('quick-store.client')->middleware('can:submission-forms.access');
    Route::post('/quick-store/client-unit', 'SubmissionFormController@quickStoreClientUnit')->name('quick-store.client-unit')->middleware('can:submission-forms.access');
    Route::post('/quick-store/client-contact', 'SubmissionFormController@quickStoreClientContact')->name('quick-store.client-contact')->middleware('can:submission-forms.access');
    Route::post('/quick-store/sample-condition', 'SubmissionFormController@quickStoreSampleCondition')->name('quick-store.sample-condition')->middleware('can:submission-forms.access');
    Route::post('/quick-store/sample-point', 'SubmissionFormController@quickStoreSamplePoint')->name('quick-store.sample-point')->middleware('can:submission-forms.access');

    // Mapping Fields (must be before /{submissionForm} route)
    Route::get('/mapping-fields', 'FormBuilderController@getMappingFields')->name('mapping-fields')->middleware('can:laboratory.components.rft form.edit');
    Route::get('/preview/sampling-request-template', 'FormInstanceController@previewSamplingRequestTemplate')->name('preview-sampling-request-template')->middleware('can:submission-forms.access');

    Route::get('/{submissionForm}', 'SubmissionFormController@show')->name('show')->middleware('can:laboratory.components.rft form.view');
    Route::get('/{submissionForm}/edit', 'SubmissionFormController@edit')->name('edit')->middleware('can:laboratory.components.rft form.edit');
    Route::put('/{submissionForm}', 'SubmissionFormController@update')->name('update')->middleware('can:laboratory.components.rft form.edit');
    Route::delete('/{submissionForm}', 'SubmissionFormController@destroy')->name('destroy')->middleware('can:laboratory.components.rft form.delete');
    Route::get('/{submissionForm}/preview', 'SubmissionFormController@preview')->name('preview')->middleware('can:laboratory.components.rft form.view');
    Route::post('/{submissionForm}/toggle-published', 'SubmissionFormController@togglePublished')->name('toggle-published')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/{submissionForm}/clone', 'SubmissionFormController@clone')->name('clone')->middleware('can:laboratory.components.rft form.add');
    Route::get('/{submissionForm}/export', 'SubmissionFormController@export')->name('export')->middleware('can:laboratory.components.rft form.view');

    // Form Builder Routes
    Route::get('/{submissionForm}/builder', 'FormBuilderController@index')->name('builder')->middleware('can:laboratory.components.rft form.edit');
    Route::get('/{submissionForm}/structure', 'FormBuilderController@getFormStructure')->name('structure')->middleware('can:laboratory.components.rft form.view');
    Route::get('/{submissionForm}/validate-element-name', 'FormBuilderController@validateElementName')->name('validate-element-name')->middleware('can:laboratory.components.rft form.edit');

    // Section Management
    Route::post('/{submissionForm}/sections', 'FormBuilderController@addSection')->name('sections.store')->middleware('can:laboratory.components.rft form.edit');
    Route::put('/sections/{section}', 'FormBuilderController@updateSection')->name('sections.update')->middleware('can:laboratory.components.rft form.edit');
    Route::delete('/sections/{section}', 'FormBuilderController@deleteSection')->name('sections.destroy')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/sections/{section}/toggle-hidden', 'FormBuilderController@toggleSectionHidden')->name('sections.toggle-hidden')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/{submissionForm}/sections/reorder', 'FormBuilderController@reorderSections')->name('sections.reorder')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/sections/{section}/clone', 'FormBuilderController@cloneSection')->name('sections.clone')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/sections/{section}/move', 'FormBuilderController@moveSectionToPosition')->name('sections.move')->middleware('can:laboratory.components.rft form.edit');

    // Element Holder Management
    Route::get('/holders/{holder}', 'FormBuilderController@getElementHolder')->name('holders.show')->middleware('can:laboratory.components.rft form.view');
    Route::post('/sections/{section}/holders', 'FormBuilderController@addElementHolder')->name('holders.store')->middleware('can:laboratory.components.rft form.edit');
    Route::put('/holders/{holder}', 'FormBuilderController@updateElementHolder')->name('holders.update')->middleware('can:laboratory.components.rft form.edit');
    Route::delete('/holders/{holder}', 'FormBuilderController@deleteElementHolder')->name('holders.destroy')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/sections/{section}/holders/reorder', 'FormBuilderController@reorderElementHolders')->name('holders.reorder')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/holders/{holder}/clone', 'FormBuilderController@cloneElementHolder')->name('holders.clone')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/holders/{holder}/move', 'FormBuilderController@moveHolderToSection')->name('holders.move')->middleware('can:laboratory.components.rft form.edit');

    // Element Management
    Route::post('/holders/{holder}/elements', 'FormBuilderController@addElement')->name('elements.store')->middleware('can:laboratory.components.rft form.edit');
    Route::put('/elements/{element}', 'FormBuilderController@updateElement')->name('elements.update')->middleware('can:laboratory.components.rft form.edit');
    Route::delete('/elements/{element}', 'FormBuilderController@deleteElement')->name('elements.destroy')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/elements/{element}/toggle-hidden', 'FormBuilderController@toggleElementHidden')->name('elements.toggle-hidden')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/holders/{holder}/elements/reorder', 'FormBuilderController@reorderElements')->name('elements.reorder')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/elements/{element}/clone', 'FormBuilderController@cloneElement')->name('elements.clone')->middleware('can:laboratory.components.rft form.edit');
    Route::post('/elements/{element}/move', 'FormBuilderController@moveElementToHolder')->name('elements.move')->middleware('can:laboratory.components.rft form.edit');

    // Form Instance Routes
    Route::prefix('instances')->name('instances.')->group(function () {
        Route::get('/', 'FormInstanceController@index')->name('index')->middleware('can:submission-forms.access');

        // Batch View - Display form instance with all linked batches and samples (safe route pattern)
        Route::get('/batch/{instance}/view', function () {
            abort(404);
        })->name('batch-view')->where('instance', '[0-9]+');

        // Batch View Print - Print version of batch view
        Route::get('/batch/{instance}/print', 'FormInstanceController@batchViewPrint')->name('batch-view-print')->where('instance', '[0-9]+')->middleware('can:submission-forms.access');

        // Dynamic options route
        Route::get('/dynamic-options', 'FormInstanceController@getDynamicOptions')->name('dynamic-options')->middleware('can:submission-forms.access');
        Route::get('/depended-field-value', 'FormInstanceController@getDependedFieldValue')->name('depended-field-value')->middleware('can:submission-forms.access');

        // Form creation routes
        Route::get('/{submissionForm}/create', 'FormInstanceController@create')->name('create')->middleware('can:submission-forms.submit');
        Route::post('/{submissionForm}', 'FormInstanceController@store')->name('store')->middleware('can:submission-forms.submit');
        Route::post('/{submissionForm}/launch-inline', 'FormInstanceController@launchInline')->name('launch-inline')->middleware('can:submission-forms.submit');
        Route::post('/{submissionForm}', 'FormInstanceController@store')->name('store')->middleware('can:submission-forms.submit');

        // Sample creation / sync (numeric {instance} only; register before generic /{submissionForm}/{instance} routes)
        Route::post('/{instance}/apply-to-batches', 'FormInstanceController@applyToBatches')->name('apply-to-batches')->where('instance', '[0-9]+')->middleware('can:submission-forms.process');
        Route::post('/{instance}/create-samples', 'SampleCreationController@createFromForm')->name('create-samples')->middleware('can:laboratory.components.all samples.add');
        Route::get('/{instance}/sample-status', 'SampleCreationController@getStatus')->name('sample-status')->middleware('can:submission-forms.access');
        Route::get('/{instance}/sample-collection-label', 'FormInstanceController@sampleCollectionLabel')->name('sample-collection-label')->middleware('can:submission-forms.access');
        Route::get('/{submissionForm}/{instance}/sample-integrity-check', 'FormInstanceController@sampleIntegrityCheck')
            ->name('sample-integrity-check')
            ->middleware('can:submission-forms.access');
        Route::get('/{instance}/integrity-worksheet-pdf', [\App\Http\Controllers\Lab\RequestTestExportController::class, 'viewIntegrityPdf'])
            ->name('integrity-worksheet-pdf')
            ->middleware('can:submission-forms.access');
        Route::post('/{instance}/intake-case/confirm', 'LabIntakeCaseController@confirm')->name('intake-case.confirm')->where('instance', '[0-9]+')->middleware('can:submission-forms.process');
        Route::post('/{instance}/intake-case/accept', 'LabIntakeCaseController@accept')->name('intake-case.accept')->where('instance', '[0-9]+')->middleware('can:submission-forms.process');
        Route::post('/{instance}/intake-case/reject', 'LabIntakeCaseController@reject')->name('intake-case.reject')->where('instance', '[0-9]+')->middleware('can:submission-forms.process');
        Route::post('/bulk-create-samples', 'SampleCreationController@bulkCreate')->name('bulk-create-samples')->middleware('can:laboratory.components.all samples.add');

        // Instance-specific routes
        Route::get('/{submissionForm}/{instance}/fill', 'FormInstanceController@fill')->name('fill')->middleware('can:submission-forms.submit');
        Route::get('/{submissionForm}/{instance}/fill-sample', 'FormInstanceController@fillSample')->name('fill-sample')->middleware('can:submission-forms.submit');
        Route::put('/{submissionForm}/{instance}', 'FormInstanceController@update')->name('update')->middleware('can:submission-forms.process');
        Route::get('/{submissionForm}/{instance}', 'FormInstanceController@show')->name('show')->middleware('can:submission-forms.access');
        Route::get('/{submissionForm}/{instance}/edit', 'FormInstanceController@edit')->name('edit')->middleware('can:submission-forms.process');
        Route::get('/{submissionForm}/{instance}/print', 'FormInstanceController@print')->name('print')->middleware('can:submission-forms.access');
        Route::get('/{submissionForm}/{instance}/trf-pdf', 'FormInstanceController@downloadTrfPdf')->name('trf-pdf')->middleware('can:submission-forms.access');
        Route::delete('/{submissionForm}/{instance}', 'FormInstanceController@destroy')->name('destroy')->middleware('can:laboratory.components.rft form.delete');
    });
});

// Sample Staging Routes (outside submission-forms group)
Route::middleware(['auth'])->group(function () {
    Route::get('/lab/samples/staging/{staging}/load-assignment-data', 'SampleCreationController@loadAssignmentData')->name('staging.load-assignment-data')->middleware('can:laboratory.components.all samples.view');
    Route::post('/lab/samples/assign-samples', 'SampleCreationController@assignSamples')->name('samples.assign')->middleware('can:laboratory.components.all samples.edit');
    Route::get('/lab/samples/staging/{staging}/available-areas-points', 'SampleCreationController@getAvailableAreasAndPoints')->name('staging.available-areas-points')->middleware('can:laboratory.components.all samples.view');
    Route::post('/lab/samples/add-customer-sample-point', 'SampleCreationController@addCustomerSamplePoint')->name('samples.add-customer-point')->middleware('can:laboratory.components.all samples.add');
});

// Public Form Submission Routes (no auth required)
Route::get('/forms/{submissionForm:slug}', 'FormInstanceController@create')->name('forms.show');
Route::post('/forms/{submissionForm:slug}', 'FormInstanceController@store')->name('forms.submit');
Route::get('/forms/dynamic-options', 'FormInstanceController@getDynamicOptions')->name('forms.dynamic-options');

// Debug route for testing analysis elements
Route::get('/debug/analysis-elements/{analysisTypeId}', function ($analysisTypeId) {
    $elements = \App\AnalysisElements::where('analysis_type_id', $analysisTypeId)
        ->with('analyte')
        ->get();

    $options = $elements->map(function ($element) {
        $parametername = 'Unknown Parameter';
        if ($element->analyte) {
            $parametername = $element->analyte->name ?? 'Unknown Parameter';
        } elseif ($element->analyte_id) {
            $analyte = \App\Analyte::find($element->analyte_id);
            $parametername = $analyte ? $analyte->name : 'Unknown Parameter';
        }

        $method = $element->method ?? 'No Method';
        return [
            'id' => $element->id,
            'text' => $parametername . ' (' . $method . ')'
        ];
    })->toArray();

    return response()->json([
        'success' => true,
        'options' => $options,
        'count' => count($options),
        'analysis_type_id' => $analysisTypeId
    ]);
});

Route::post('/inventory-slot-contents/{id}/delete', 'InventoryStoreSlotContentController@delete')->name('delete-inventory-slot-content')->middleware('can:inventory.components.store.delete');

Route::get('/show-inventory-items/{category}/{id}', 'InventorySubCategoriesController@index')->name('show-inventory-items')->middleware('can:inventory.components.categories.view');
Route::post('/inventory-sub-categories', 'InventorySubCategoriesController@add')->name('add-inventory-sub-category')->middleware('can:inventory.components.categories.add');
Route::post('/inventory-sub-category/{id}', 'InventorySubCategoriesController@edit')->name('edit-inventory-sub-category')->middleware('can:inventory.components.categories.edit');
Route::post('/inventory-sub-category/{id}/delete', 'InventorySubCategoriesController@destroy')->name('delete-inventory-sub-category')->middleware('can:inventory.components.categories.delete');

Route::post('/add-subcategory-conversion/{id}', 'UnitOfMeasureConversionController@update')->name('add-subcategory-conversion')->middleware('can:inventory.components.categories.edit');
Route::post('/delete-item-conversion', 'UnitOfMeasureConversionController@delete')->name('delete-item-conversion')->middleware('can:inventory.components.categories.delete');

Route::post('/add-subcategory-item-state/{id}', 'ItemStateController@update')->name('add-subcategory-item-state')->middleware('can:inventory.components.categories.edit');
Route::post('/delete-item-state', 'ItemStateController@delete')->name('delete-item-state')->middleware('can:inventory.components.categories.delete');

Route::post('/add-store-contacts/{id}', 'InventoryStoreContactController@add')->name('add-store-contacts')->middleware('can:inventory.components.store.edit');
Route::post('/delete-store-contacts', 'InventoryStoreContactController@delete')->name('delete-store-contacts')->middleware('can:inventory.components.store.delete');

Route::get('/inventory-departments', 'InventoryDepartmentController@index')->name('show-inventory-departments')->middleware('can:inventory.components.departments.view');
Route::post('/inventory-departments/{module?}', 'InventoryDepartmentController@add')->name('add-inventory-department')->middleware('can:inventory.components.departments.add');
Route::post('/inventory-department/{id}', 'InventoryDepartmentController@edit')->name('edit-inventory-department')->middleware('can:inventory.components.departments.edit');
Route::get('/inventory-department/{id}', 'InventoryDepartmentController@show')->name('show-inventory-department')->middleware('can:inventory.components.departments.view');
Route::post('/inventory-sub-category/{id}/delete', 'InventoryDepartmentController@destroy')->name('delete-inventory-department')->middleware('can:inventory.components.departments.delete');

Route::post('/inventory-items', 'InventoryItemController@add')->name('add-inventory-items')->middleware('can:inventory.components.inventory-movement.add');
Route::post('/inventory-item-transfer', 'InventoryItemController@transfer')->name('transfer-inventory-items')->middleware('can:inventory.components.inventory-movement.edit');
Route::post('/stock-keeping', 'InventoryItemController@stock_keeping')->name('stock-keeping')->middleware('can:inventory.components.inventory-movement.edit');
Route::post('/item-disposal', 'InventoryItemController@item_disposal')->name('item-disposal')->middleware('can:inventory.components.inventory-movement.delete');
Route::post('/return-item-to-store', 'InventoryItemController@return_2_store')->name('return-item-to-store')->middleware('can:inventory.components.inventory-movement.edit');

Route::get('/stock-taking-list', 'StockTakingController@index')->name('stock-taking-list')->middleware('can:inventory.components.stock-taking.view');
Route::get('/stock-taking-update/{id}/{print?}', 'StockTaking\Main@show')->name('stock-taking-sheet')->middleware('can:inventory.components.stock-taking.view');
Route::post('/stock-taking-update/{id?}', 'StockTakingController@update')->name('stock-taking-update')->middleware('can:inventory.components.stock-taking.edit');
Route::post('/stock-taking-freeze-stores/{id?}', 'StockTakingController@freeze_stores')->name('stock-taking-freeze-stores')->middleware('can:inventory.components.stock-taking.edit');
Route::post('/stock-taking-save-capture/{id?}', 'StockTakingController@save_capture')->name('stock-taking-save-capture')->middleware('can:inventory.components.stock-taking.edit');
Route::post('/adjust-stock-keeping/{catid}/{subid}', 'StockTakingController@adjust_stock')->name('adjust-stock-keeping')->middleware('can:inventory.components.stock-taking.edit');

Route::get('/stock-transfer-list', 'StockTransferController@index')->name('stock-transfer-list')->middleware('can:inventory.components.stock-transfer.view');
Route::get('/stock-transfer-update/{id}', 'StockTransferController@show')->name('stock-transfer-sheet')->middleware('can:inventory.components.stock-transfer.view');
Route::post('/stock-transfer-update/{id?}', 'StockTransferController@update')->name('stock-transfer-update')->middleware('can:inventory.components.stock-transfer.edit');
Route::post('/stock-transfer-items-update/{id?}', 'StockTransferController@update_items')->name('stock-transfer-items-update')->middleware('can:inventory.components.stock-transfer.edit');
Route::post('/stock-transfer-item-delete', 'StockTransferController@delete_item')->name('stock-transfer-item-delete')->middleware('can:inventory.components.stock-transfer.delete');

Route::post('/edit/supplier-quote/{id}', 'SupplierQuoteController@edit')->name('edit-supplier-item-quote')->middleware('can:inventory.components.suppliers.edit');
Route::post('/remove/supplier-quote/{id}/{itemID?}', 'SupplierQuoteController@remove')->name('remove-supplier-item-quote')->middleware('can:inventory.components.suppliers.delete');

Route::post('/undo-supplier-award/{id}', 'SupplierQuoteController@undo_supplier_award')->name('undo-supplier-award')->middleware('can:inventory.components.suppliers.edit');

Route::post('/remove-this-supplier/{id}/{itemID}', 'SupplierController@remove_supplier_from_inventory')->name('remove-this-supplier')->middleware('can:inventory.components.suppliers.delete');

//############################################INVENTORY##########################################################

//############################################GENERAL REQUISITION#############################################

Route::prefix('general-requisition')->group(function () {
    Route::get('/list', 'GeneralRequisition\GeneralRequisitionController@index')->name('general-requisition-list')->middleware('can:inventory.components.request for quotation.view');
    Route::get('/show/{id?}', 'GeneralRequisition\GeneralRequisitionController@show')->name('general-requisition-view')->middleware('can:inventory.components.request for quotation.view');
    Route::get('/show/{id}/document', 'GeneralRequisition\GeneralRequisitionController@view_document')->name('general-requisition-document')->middleware('can:inventory.components.request for quotation.view');
    Route::get('/get-supplier-list/{search?}', 'GeneralRequisition\GeneralRequisitionController@suppliers')->name('get-supplier-list')->middleware('can:inventory.components.request for quotation.view');
    Route::get('/get-gr-items-list/{search?}', 'GeneralRequisition\GeneralRequisitionController@inventory_items')->name('get-gr-items-list')->middleware('can:inventory.components.request for quotation.view');
    Route::post('/remove-general-requisition-rows/{id}', 'GeneralRequisition\GeneralRequisitionController@remove_items')->name('remove-general-requisition-rows')->middleware('can:inventory.components.request for quotation.edit');
    Route::post('/update/{id?}', 'GeneralRequisition\GeneralRequisitionController@update')->name('general-requisition-update')->middleware('can:inventory.components.request for quotation.edit');
    Route::post('/add-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@add')->name('add-gr-quote')->middleware('can:inventory.components.request for quotation.add');
    Route::post('/update-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@update')->name('update-gr-quotes')->middleware('can:inventory.components.request for quotation.edit');
    Route::post('/delete-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@remove')->name('delete-gr-quotes')->middleware('can:inventory.components.request for quotation.delete');
    Route::post('/change-status/{id}/{type}', 'GeneralRequisition\GeneralRequisitionController@change_status')->name('change-status')->middleware('can:inventory.components.request for quotation.edit');

    Route::post('/jump-request-to-status/{id}', 'RequisitionController@jump_request_to_status')->name('jump-request-to-status')->middleware('can:inventory.components.request for quotation.edit');

    Route::post('/send-notification/{id}/{type}', 'GeneralRequisition\GeneralRequisitionController@sendApprovalNotifications')->name('send-notification-gr')->middleware('can:inventory.components.request for quotation.edit');

    Route::post('/change-approver-this-gr/{id?}/{field?}', 'GeneralRequisition\GeneralRequisitionController@change_approver')->name('change-approver-this-gr')->middleware('can:inventory.components.request for quotation.edit');
    Route::post('/approve-this-gr/{id?}/{type?}', 'GeneralRequisition\GeneralRequisitionController@approve_this')->name('approve-this-gr')->middleware('can:inventory.components.request for quotation.edit');
});

//############################################GENERAL REQUISITION#############################################

//############################################LOCATIONS##########################################################
Route::get('/organizational-locations', 'InventoryLocationController@index')->name('inventory-locations')->middleware('can:inventory.components.departments.view');
Route::post('/organizational-locations', 'InventoryLocationController@add')->name('add-inventory-location')->middleware('can:inventory.components.departments.add');
Route::get('/organizational-locations/{id}', 'InventoryLocationController@show')->name('show-inventory-locations')->middleware('can:inventory.components.departments.view');
Route::post('/organizational-locations/{id}', 'InventoryLocationController@edit')->name('edit-inventory-location')->middleware('can:inventory.components.departments.edit');
Route::post('/organizational-locations/{id}/delete', 'InventoryLocationController@destroy')->name('delete-inventory-location')->middleware('can:inventory.components.departments.delete');

Route::get('/set-user-location/{id}', 'InventoryLocationController@set_user_location')->name('set-user-location')->middleware('can:inventory.components.departments.edit');

Route::post('/add-user-access/{id}', 'InventoryLocationController@add_user')->name('add-user-access')->middleware('can:inventory.components.departments.edit');
Route::post('/remove-user-access/{id}/{user}', 'InventoryLocationController@remove_user_access')->name('remove-user-access')->middleware('can:inventory.components.departments.edit');
//############################################LOCATIONS##########################################################

// Asset Management Routes
Route::get('/equipment/asset-types', [EquipmentAppController::class, 'assetTypeManager'])->name('equipment.asset-types.index')->middleware(['auth', 'can:equipment.components.asset-type.view']);
Route::get('/equipment/asset-locations', [EquipmentAppController::class, 'assetLocationManager'])->name('equipment.asset-locations.index')->middleware(['auth', 'can:equipment.components.asset-location.view']);

Route::get('/equipment/depreciation', [EquipmentAppController::class, 'depreciationList'])->name('equipment.depreciation.index')->middleware(['auth', 'can:equipment.components.depreciation.view']);
Route::get('/equipment/depreciation/methods', [EquipmentAppController::class, 'depreciationMethods'])->name('equipment.depreciation.methods.index')->middleware(['auth', 'can:equipment.components.depreciation.methods.view']);
Route::get('/equipment/depreciation/reports', [EquipmentAppController::class, 'depreciationReports'])->name('equipment.depreciation.reports.index')->middleware(['auth', 'can:equipment.components.depreciation.view']);

//############################################EQUIPMENT##########################################################
// Equipment Monitoring Routes
Route::get('/equipment/monitoring', [EquipmentAppController::class, 'monitoring'])
    ->name('equipment.monitoring')
    ->middleware('can:equipment.permission');

Route::get('/equipment/maintenance', [EquipmentAppController::class, 'equipmentMaintenance'])
    ->name('equipment.maintenance')
    ->middleware('can:equipment.permission');

Route::get('/equipment/monitoring/template/create', [EquipmentAppController::class, 'createMonitoringTemplate'])
    ->name('equipment.monitoring.template.create')
    ->middleware('can:equipment.permission');

Route::get('/equipment/monitoring/template/{template}/edit', [EquipmentAppController::class, 'editMonitoringTemplate'])
    ->name('equipment.monitoring.template.edit')
    ->middleware('can:equipment.permission');

Route::get('/equipment/monitoring/export-lws-011', [\App\Http\Controllers\Monitoring\MonitoringExportController::class, 'exportLws011'])
    ->name('equipment.monitoring.export-lws-011')
    ->middleware('can:equipment.permission');

Route::get('/equipment-home', [EquipmentAppController::class, 'equipmentManager'])->name('equipment-home')->middleware('can:equipment.permission');
Route::get('/equipment-dashboard', [EquipmentAppController::class, 'equipmentDashboard'])->name('equipment-dashboard')->middleware('can:equipment.module.access');
// Route::get('/equipment-checks', [EquipmentAppController::class, 'checksIndex'])->name('equipment-checks')->middleware('can:equipment.permission');
Route::get('/equipment-daily-log', [EquipmentAppController::class, 'dailyLogIndex'])->name('equipment-daily-log')->middleware('can:equipment.module.access');
Route::post('/equipment', 'Equipment\EquipmentController@add')->name('add-equipment')->middleware('can:equipment.components.equipment-list.add');
Route::get('/equipment/{equipmentId}', [EquipmentAppController::class, 'equipmentDetail'])->name('view-equipment')->middleware('can:equipment.components.equipment-list.view');
Route::post('/equipment/{id}', 'Equipment\EquipmentController@edit')->name('edit-equipment')->middleware('can:equipment.components.equipment-list.edit');
Route::post('/schedule-maintainance/{id}', 'Equipment\MaintainanceCalibrationLogController@add')->name('new-maintainance')->middleware('can:equipment.components.maintainance-log.add');
Route::post('/edit-maintainance', 'Equipment\MaintainanceCalibrationLogController@edit')->name('edit-maintainance')->middleware('can:equipment.components.maintainance-log.edit');
Route::post('/usage-log/{id}/{equipment}', 'Equipment\EquipmentUsageController@update')->name('usage-log')->middleware('can:equipment.components.maintainance-log.edit');
Route::post('/add-operators/{equipment}', 'Equipment\EquipmentOperatorController@add')->name('add-operators')->middleware('can:equipment.components.operator-log.add');
Route::post('/remove-operator/{id}', 'Equipment\EquipmentOperatorController@destroy')->name('remove-operator')->middleware('can:equipment.components.operator-log.delete');

Route::post('/add-equipment-attachment', 'Equipment\MaintainanceCalibrationLogController@add_equipment_attachment')->name('add_equipment_attachment')->middleware('can:equipment.components.maintainance-log.edit');

Route::post('/verification-log/{id}', 'Equipment\VerificationLogController@add')->name('add-verification')->middleware('can:equipment.components.verification-log.add');
Route::post('/edit/verification-log/{id}', 'Equipment\VerificationLogController@edit')->name('edit-verification')->middleware('can:equipment.components.verification-log.edit');
Route::get('/delete/verification-log/{id}', 'Equipment\VerificationLogController@delete')->name('delete-verification')->middleware('can:equipment.components.verification-log.delete');
Route::post('/dispose/equipment/{id}', 'Equipment\EquipmentController@dispose')->name('dispose-equipment')->middleware('can:equipment.components.equipment-list.delete');
Route::get('/revert/equipment/{id}', 'Equipment\EquipmentController@revert')->name('revert-equipment')->middleware('can:equipment.components.equipment-list.delete');
Route::post('/delete/part-repaired', 'Equipment\MaintainanceCalibrationLogController@delete')->name('delete-part-repaired')->middleware('can:equipment.components.repair-log.delete');
Route::post('/delete/log', 'Equipment\MaintainanceCalibrationLogController@delete_logs')->name('delete-logs')->middleware('can:equipment.components.repair-log.delete');

Route::post('/add/equipment/frequency', 'Equipment\EquipmentController@addEquipmentNotification')->name('add-equipment-frequency')->middleware('can:equipment.components.equipment-list.edit');
Route::post('/delete/equipment/notification', 'Equipment\EquipmentController@deleteEquipmentNotification')->name('delete-equipment-frequency')->middleware('can:equipment.components.equipment-list.edit');


// Equipment Disposal Workflow Routes
Route::get('/equipment-disposal/workflows', [EquipmentAppController::class, 'workflowManager'])->name('equipment.disposal.workflow.index')->middleware(['auth', 'can:equipment.components.equipment-disposal.view']);
Route::get('/equipment-disposal/workflows/create', [EquipmentAppController::class, 'workflowForm'])->name('equipment.disposal.workflow.create')->middleware(['auth', 'can:equipment.components.equipment-disposal.view']);
Route::get('/equipment-disposal/workflows/{id}/edit', [EquipmentAppController::class, 'workflowForm'])->name('equipment.disposal.workflow.edit')->middleware(['auth', 'can:equipment.components.equipment-disposal.view']);

// Equipment Disposal Routes
Route::get('/equipment-disposal', [EquipmentAppController::class, 'disposalManager'])->name('equipment-disposal-home')->middleware(['auth', 'can:equipment.components.equipment-disposal.view']);
Route::get('/equipment-disposal/{disposalId}', [EquipmentAppController::class, 'disposalDetail'])->name('equipment-disposal-detail')->middleware(['auth', 'can:equipment.components.equipment-disposal.view']);
Route::get('/equipment-disposal/{disposalId}/download-report', 'Equipment\DisposalController@downloadReport')->name('equipment-disposal-download-report')->middleware(['auth', 'can:equipment.components.equipment-disposal.view']);


//############################################EQUIPMENT##########################################################

//############################################SUPPLIER###########################################################
Route::get('/inventory-suppliers', 'SupplierController@index')->name('inventory-suppliers')->middleware('can:inventory.components.suppliers.view');
Route::post('/inventory-suppliers', 'SupplierController@add')->name('add-inventory-supplier')->middleware('can:inventory.components.suppliers.add');
Route::get('/inventory-supplier/{id}', 'SupplierController@show')->name('show-inventory-supplier')->middleware('can:inventory.components.suppliers.view');
Route::post('/inventory-supplier/{id}', 'SupplierController@edit')->name('edit-inventory-supplier')->middleware('can:inventory.components.suppliers.edit');
Route::post('/inventory-supplier/{id}/delete', 'SupplierController@delete_supplier')->name('delete-inventory-supplier')->middleware('can:inventory.components.suppliers.edit');

Route::post('/add-supplier-category/{supplier}', 'SupplierCategoryController@add')->name('add-supplier-category')->middleware('can:inventory.components.suppliers.add');
Route::post('/add-supplier-main-category/{supplier_id}', 'SupplierByCategoryController@add')->name('add-supplier-main-category')->middleware('can:inventory.components.suppliers.add');
Route::post('/add-supplier-to-inventory/{itemID}', 'SupplierController@add_supplier_to_inventory')->name('add-supplier-to-inventory')->middleware('can:inventory.components.suppliers.add');
Route::post('/delete-supplier-category/{id}', 'SupplierCategoryController@destroy')->name('delete-supplier-category')->middleware('can:inventory.components.suppliers.edit');
Route::post('/delete-supplier-main-category/{id}', 'SupplierByCategoryController@remove')->name('delete-supplier-main-category')->middleware('can:inventory.components.suppliers.edit');
Route::post('/change-category-image/{id}', 'SupplierCategoryController@change_image')->name('change-category-image')->middleware('can:inventory.components.suppliers.edit');
Route::post('/supplier-rating', 'InventorySupplierRatingController@add')->name('supplier-rating')->middleware('can:inventory.components.suppliers.edit');

Route::post('/update-supplier-contact/{supplier_id}/{contact_id?}', 'SupplierContactController@update')->name('update-supplier-contact')->middleware('can:inventory.components.suppliers.edit');
Route::post('/remove-supplier-contact/{contact_id}', 'SupplierContactController@destroy')->name('remove-supplier-contact')->middleware('can:inventory.components.suppliers.edit');
//############################################SUPPLIER##########################################################

//############################################SUPPLIER CONTRACTS##########################################################
Route::post('/create-supplier-contract/{supplier}', 'SupplierContractController@modify')->name('create-supplier-contract')->middleware('can:inventory.components.suppliers.edit');
Route::post('/edit-supplier-contract/{supplier}/{id}', 'SupplierContractController@modify')->name('edit-supplier-contract')->middleware('can:inventory.components.suppliers.edit');
//############################################SUPPLIER CONTRACTS##########################################################

//############################################ORDERS############################################################
Route::post('/create-order/{supplier?}', 'InventoryOrderController@add')->name('create-order')->middleware('can:inventory.components.purchase orders.add');
Route::post('/edit-order/{supplier}/{order_id}', 'InventoryOrderController@edit')->name('edit-order')->middleware('can:inventory.components.purchase orders.edit');
Route::post('/edit-order/{supplier}/{order_id}', 'InventoryOrderController@edit')->name('edit-order')->middleware('can:inventory.components.purchase orders.edit');
Route::get('/server-side/{field}/{fieldID}', 'InventoryOrderController@server_side')->name('server-side-orders')->middleware('can:inventory.components.purchase orders.view');
Route::get('/server-side/{field}/{fieldID}/{type?}/requisition', 'InventoryOrderController@server_side_po')->name('server-side-purchase-orders')->middleware('can:inventory.components.purchase orders.view');
Route::post('/delete-order-items/{order_item}', 'InventoryOrderItemController@delete')->name('delete-order-item')->middleware('can:inventory.components.purchase orders.delete');
Route::get('/get-order-items/{field}/{fieldID}', 'InventoryOrderItemController@getItems')->name('get-order-items')->middleware('can:inventory.components.purchase orders.view');
Route::post('/accept-order-items/{order_id}', 'InventoryOrderItemToInventoryItemController@acceptItems')->name('accept-order-items')->middleware('can:inventory.components.inventory-movement.edit');
//############################################ORDERS############################################################

//############################################CUSTOMERS##########################################################
Route::prefix('crm/v2')->middleware(['auth', 'can:crm.customers.view'])->name('crm.v2.')->group(function () {
    Route::get('/', function () {
        return view('layouts.crm.v2-home');
    })->name('home');
    Route::get('/customers', fn () => view('layouts.crm.v2-customers'))->name('customers');
});

Route::get('/crm/customer/{id}', function ($id) {
    $customer = \App\Models\CRM\CRMCustomer::find($id);

    if (!$customer) {
        abort(404);
    }

    return view('layouts.crm.v2-customer-show', [
        'customerId' => $id,
        'customer' => $customer,
    ]);
})->middleware(['auth', 'can:crm.customers.view'])->name('crm.customer.show');

Route::get('/crm-dashboard', '\\' . \App\Livewire\CRM\CrmDashboard::class)
    ->name('crm-dashboard')
    ->middleware('auth')
    ->middleware('can:crm.dashboard.view');

Route::get('/crm-home', [CRMAppController::class, 'customers'])->name('customers-list')->middleware('can:crm.customers.view');
Route::post('/fetch-client-quotes', 'CRM\CRMCustomerController@fetch_client_quote')->name('fetch-client-qoutes')->middleware('can:crm.customers.view');
Route::get('/crm-home-config', 'CRM\CRMCustomerController@checkConfig')->name('add-config-customer')->middleware('can:crm.customers.view');
Route::post('/customers', 'CRM\CRMCustomerController@add')->name('add-customers')->middleware('can:crm.customers.add');
Route::get('/customer/{id}', 'CRM\CRMCustomerController@show')->name('show-customer')->middleware('can:crm.customers.view');
Route::post('/customer/{id}', 'CRM\CRMCustomerController@edit')->name('edit-customer')->middleware('can:crm.customers.edit');
Route::post('/customer/{id}/label', 'CRM\CRMCustomerController@edit_label')->name('change-client-label-name')->middleware('can:crm.customers.edit');
Route::post('/delete-customer', 'CRM\CRMCustomerController@delete_customer')->name('delete_customer')->middleware('can:crm.customers.delete');

Route::get('/crm/customer/{customer}/attachment/{attachment}/download', 'CRM\CustomerAttachmentController@download')->name('crm.customer.attachment.download')->middleware('can:crm.customers.view');
Route::get('/crm/customer/{customer}/contract/{contract}/download', 'CRM\CustomerAttachmentController@downloadContract')->name('crm.customer.contract.download')->middleware('can:crm.customers.view');
Route::get('/crm/customer/{customer}/contract/{contract}/view', 'CRM\CustomerAttachmentController@viewContract')->name('crm.customer.contract.view')->middleware('can:crm.customers.view');

Route::post('/add/customer-certification/{id}', 'CRM\CustomerCertificationController@add')->name('add-customer-certification')->middleware('can:crm.certifications.add');
Route::post('/edit/customer-certification/{id}', 'CRM\CustomerCertificationController@edit')->name('edit-customer-certification')->middleware('can:crm.certifications.edit');
Route::post('/delete/customer-certification/{id}', 'CRM\CustomerCertificationController@delete')->name('delete-customer-certification')->middleware('can:crm.certifications.delete');

Route::get('/complaint/{stage}', '\\' . \App\Livewire\Crm\Complaint\ComplaintList::class)
    ->name('complaint-workflow')
    ->middleware('can:crm.complaints.view');
Route::get('/complaints/create', '\\' . \App\Livewire\Crm\Complaint\ComplaintForm::class)
    ->name('complaint-create')
    ->middleware('can:crm.complaints.add');

Route::get('/complaint-type/home', 'CRM\Complaint\ComplaintTypeController@index')->name('complaint-type-home')->middleware('can:crm.complaint-types.view');
Route::post('/edit/complaint-type/{id}', 'CRM\Complaint\ComplaintTypeController@edit')->name('edit-complaint-type')->middleware('can:crm.complaint-types.edit');
Route::post('/add/complaint-type', 'CRM\Complaint\ComplaintTypeController@add')->name('add-complaint-type')->middleware('can:crm.complaint-types.add');

Route::post('/add/open-complaint', 'CRM\Complaint\ComplaintController@add')->name('add-complaint')->middleware('can:crm.complaints.add');
Route::post('/add-open-complaint/customer', 'CRM\Complaint\ComplaintController@customer_add')->name('customer-add-complaint')->middleware('can:crm.complaints.add');
Route::post('/edit-complaint/{id}', 'CRM\Complaint\ComplaintController@edit')->name('edit-complaint')->middleware('can:crm.complaints.edit');
Route::get('/show-complaint/{id}', 'CRM\Complaint\ComplaintController@show')->name('show-complaint')->middleware('can:crm.complaints.view');
Route::post('/add/complaint-notes/{id}', 'CRM\Complaint\ComplaintNotesController@add')->name('add-notes')->middleware('can:crm.complaints.edit');
Route::post('/edit/complaint-notes/{id}', 'CRM\Complaint\ComplaintNotesController@edit')->name('edit-notes')->middleware('can:crm.complaints.edit');
Route::post('/add/complaint-attachment/{id}', 'CRM\Complaint\ComplaintAttachmentController@add')->name('add-attachment')->middleware('can:crm.complaints.edit');
Route::post('/edit/complaint-attachment/{id}', 'CRM\Complaint\ComplaintAttachmentController@edit')->name('edit-attachment')->middleware('can:crm.complaints.edit');
Route::get('/crm/complaint/{complaint}/attachment/{attachment}/download', 'CRM\Complaint\ComplaintAttachmentController@download')
    ->name('crm.complaint.attachment.download')
    ->middleware('can:crm.complaints.view');
Route::post('/approve-complaint/{id}', 'CRM\Complaint\ComplaintWorkflowController@approve_next')->name('approve-complaint')->middleware('can:crm.complaints-approval.edit');
Route::post('/reverse-complaint/{id}', 'CRM\Complaint\ComplaintWorkflowController@reverse_approval')->name('reverse-complaint')->middleware('can:crm.complaints-approval.delete');
Route::post('/reject-complaint/{id}', 'CRM\Complaint\ComplaintWorkflowController@reject_complaint')->name('reject-complaint')->middleware('can:crm.complaints-approval.delete');
Route::post('/add/complaint-resolution/{id}', 'CRM\Complaint\ComplaintResolutionController@add')->name('add-resolution')->middleware('can:crm.complaints-resolution.add');
Route::post('/edit/complaint-resolution/{id}', 'CRM\Complaint\ComplaintResolutionController@edit')->name('edit-resolution')->middleware('can:crm.complaints-resolution.edit');
Route::get('/show/complaint/{id}', '\\' . \App\Livewire\Crm\Complaint\ComplaintShow::class)
    ->name('complaint-show')
    ->middleware('can:crm.complaints.view');

Route::get('/customer-feedback/home', [CRMAppController::class, 'feedbacks'])->name('feedback-home')->middleware('can:crm.feedback.view');
Route::get('/show/feedback/{id}', '\\' . \App\Livewire\Crm\Feedback\FeedbackShow::class)
    ->name('feedback-show')
    ->middleware('can:crm.feedback.view');
Route::get('/customer-feedback/configuration', '\\' . \App\Livewire\Crm\Feedback\EvaluationMetricManager::class)
    ->name('feedback-config')
    ->middleware('auth')
    ->middleware('can:crm.feedback.view');
Route::post('/add/customer-feedback', 'CRM\CustomerFeedbackController@add')->name('add-feedback')->middleware('can:crm.feedback.add');
Route::post('/add-feedback/customer', 'CRM\CustomerFeedbackController@customer_add')->name('customer-add-feedback')->middleware('can:crm.feedback.add');
Route::post('/edit/customer-feedback/{id}', 'CRM\CustomerFeedbackController@edit')->name('edit-feedback')->middleware('can:crm.feedback.edit');

Route::get('/customer-feedback/submit/{contact_id}', '\\' . \App\Livewire\Crm\Feedback\FeedbackSubmitForm::class)
    ->name('feedback.form')
    ->middleware('signed');



Route::post('/request-resolution-approval/{id}', 'CRM\Complaint\ComplaintWorkflowController@request_resolution_approve')->name('request-resolution')->middleware('can:crm.resolution-approval.add');
Route::post('/reverse-resolution/{id}', 'CRM\Complaint\ComplaintWorkflowController@reverse_resolution')->name('reverse-resolution')->middleware('can:crm.resolution-approval.edit');
Route::post('/reject-resolution/{id}', 'CRM\Complaint\ComplaintWorkflowController@reject_resolution')->name('reject-resolution')->middleware('can:crm.resolution-approval.delete');
Route::post('/approve-resolution/{id}', 'CRM\Complaint\ComplaintWorkflowController@approve_resolution')->name('approve-resolution')->middleware('can:crm.resolution-approval.edit');

Route::post('/company-units/{cust_id}', 'CRM\CRMCompanyUnitController@add')->name('add-company-units')->middleware('can:crm.company-units.add');
Route::post('/company-unit/{id}/{cust_id}', 'CRM\CRMCompanyUnitController@edit')->name('edit-company-unit')->middleware('can:crm.company-units.edit');

Route::post('/customer-product', 'CRM\CompanyProductController@add')->name('add-customer-product')->middleware('can:crm.products.add');
Route::post('/customer-product/edit/{id?}', 'CRM\CompanyProductController@edit')->name('edit-customer-product')->middleware('can:crm.products.edit');

Route::post('/company-contacts/{cust_id}', 'CRM\CustomerContactController@add')->name('add-company-contacts')->middleware('can:crm.contacts.add');
Route::post('/company-contact/{id}/{cust_id}', 'CRM\CustomerContactController@edit')->name('edit-company-contact')->middleware('can:crm.contacts.edit');
Route::post('/customer-contact/add', 'CRM\CustomerContactController@addAjax')->name('customer-contact-add-ajax')->middleware('can:crm.contacts.add');
Route::get('/get/customer/ajax/{id}', 'CRM\CustomerContactController@getCustomerUnits')->name('getCustomerUnits')->middleware('can:crm.contacts.view');

Route::get('/fetch-customer-contacts/{id}', 'CRM\CustomerContactController@get_customer_client')->name('get_customer_client')->middleware('can:crm.contacts.view');
Route::get('/validate-Crm-Customer/Name/{name}/Ajax', 'CRM\CRMCustomerController@validateCrmCustomerNameAjax')->name('validateCrmCustomerNameAjax')->middleware('can:crm.customers.view');
Route::get('/crm-batch-reports', 'CRM\CRMCustomerController@batch_reports')->name('crm-batch-reports')->middleware('can:crm.results.view');
Route::post('/crm-batch-report/data', 'CRM\CRMCustomerController@batch_report_data')->name('crm.batch-report.data')->middleware('can:crm.results.view');
Route::post('/crm-batch-report/export', 'CRM\CRMCustomerController@batch_report_export')->name('crm.batch-report.export')->middleware('can:crm.results.view');
//############################################SUPPLIER##########################################################

//############################################## QUALIFICATIONS #############################################################
Route::get('/qualification-home', 'Lab\QualificationsController@index')->name('qualification-home')->middleware('can:laboratory.components.qc sample.view');
Route::post('/edit/qualification/{id}', 'Lab\QualificationsController@edit')->name('edit-qualification')->middleware('can:laboratory.components.qc sample.edit');
Route::post('/add/qualification', 'Lab\QualificationsController@add')->name('add-qualification')->middleware('can:laboratory.components.qc sample.add');
//############################################## END UALIFICATIONS #############################################################

//###################################AJAX LINKS#######################################
Route::get('/analysis-types/{id}', 'AnalysisTypeController@by_sample_id')->name('api-analysis-types-by-sample')->middleware('can:laboratory.components.analysis types.view');
Route::get('/missing_analysis_parameters_by_sample_code', 'SampleWorkFlowController@missing_analysis_parameters_by_sample_code')->name('missing_analysis_parameters_by_sample_code')->middleware('can:laboratory.components.all samples.view');
Route::post('/remove-analyte-from-captured-result', 'SampleWorkFlowController@remove_analyte_from_captured_result')->name('remove-analyte-from-captured-result')->middleware('can:laboratory.components.all samples.edit');
Route::post('/fetch/results-remark', 'SampleWorkFlowController@fetch_results_remark')->name('fetch_results_remark')->middleware('can:laboratory.components.all samples.view');
Route::get('/get-available-methods', 'SampleWorkFlowController@getAvailableMethods')->name('get-available-methods')->middleware('can:laboratory.components.all samples.view');
Route::post('/capture-results-save', 'SampleWorkFlowController@saveCaptureResults')->name('capture-results-save')->middleware('can:laboratory.components.all samples.edit');
Route::get('/get-customer-contacts/{type}/{customer_id}', 'CRM\CustomerContactController@get_contacts')->name('get-customer-contacts')->middleware('can:crm.contacts.view');
Route::get('/stock-transfer-json', 'StockTransferController@getJson')->name('stock-transfer-json');
Route::get('/get-material-type-states', 'StockTransferController@getMaterialTypeStates')->name('get-material-type-states');
Route::get('/get-store-slots-by-item/{item}', 'InventoryStoreController@store_slots_by_item')->name('get-store-slots-by-item');
//###################################AJAX LINKS#######################################

//###################################AUDIT TRAIL#######################################
Route::get('/audit-logs', 'AuditController@index')->name('get-audit-logs')->middleware('can:personnel.audit trail.view');
Route::get('/server-side-audit_logs/{user_id?}', 'AuditController@server_side')->name('server-side-audit_logs')->middleware('can:personnel.audit trail.view');
Route::get('/server-side-audit_log/{id}/details', 'AuditController@server_side_details')->name('server-side-audit_logs-details')->middleware('can:personnel.audit trail.view');
//###################################AUDIT TRAIL#######################################

//###################################RISK MANAGEMENT#######################################
Route::prefix('risk')->name('risk.')->middleware(['auth', 'can:risk.module.access'])->group(function () {
  Route::get('/', 'RiskManagement\RiskDashboardController@index')->name('dashboard');

  Route::prefix('config')->name('config.')->middleware('can:risk-management.components.risks.view')->group(function () {
    Route::get('/risk-categories', 'RiskManagement\RiskConfigController@riskCategories')->name('risk-categories');
    Route::get('/risk-sources', 'RiskManagement\RiskConfigController@riskSources')->name('risk-sources');
    Route::get('/risk-statuses', 'RiskManagement\RiskConfigController@riskStatuses')->name('risk-statuses');
    Route::get('/treatment-types', 'RiskManagement\RiskConfigController@treatmentTypes')->name('treatment-types');
    Route::get('/workflow-approvers', 'RiskManagement\RiskConfigController@workflowApprovers')->name('workflow-approvers');
  });

  Route::prefix('assessment')->name('assessment.')->middleware('can:risk-management.components.risks.view')->group(function () {
    Route::get('/likelihood-scales', 'RiskManagement\RiskAssessmentConfigController@likelihoodScales')->name('likelihood-scales');
    Route::get('/severity-scales', 'RiskManagement\RiskAssessmentConfigController@severityScales')->name('severity-scales');
    Route::post('/likelihood-scales', 'RiskManagement\RiskAssessmentConfigController@storeLikelihoodScale')->name('likelihood-scales.store');
    Route::post('/severity-scales', 'RiskManagement\RiskAssessmentConfigController@storeSeverityScale')->name('severity-scales.store');
    Route::put('/likelihood-scales/{id}', 'RiskManagement\RiskAssessmentConfigController@updateLikelihoodScale')->name('likelihood-scales.update');
    Route::put('/severity-scales/{id}', 'RiskManagement\RiskAssessmentConfigController@updateSeverityScale')->name('severity-scales.update');
    Route::delete('/likelihood-scales/{id}', 'RiskManagement\RiskAssessmentConfigController@destroyLikelihoodScale')->name('likelihood-scales.destroy');
    Route::delete('/severity-scales/{id}', 'RiskManagement\RiskAssessmentConfigController@destroySeverityScale')->name('severity-scales.destroy');
    Route::post('/likelihood-scales/{id}/toggle', 'RiskManagement\RiskAssessmentConfigController@toggleLikelihoodScale')->name('likelihood-scales.toggle');
    Route::post('/severity-scales/{id}/toggle', 'RiskManagement\RiskAssessmentConfigController@toggleSeverityScale')->name('severity-scales.toggle');
  });

  Route::prefix('settings')->name('settings.')->middleware('can:risk-management.components.risks.view')->group(function () {
    Route::get('/', 'RiskManagement\RiskConfigurationController@index')->name('index');
    Route::post('/', 'RiskManagement\RiskConfigurationController@store')->name('store');
    Route::post('/reorder', 'RiskManagement\RiskConfigurationController@reorder')->name('reorder');
    Route::get('/{optionType}', 'RiskManagement\RiskConfigurationController@show')->where('optionType', '[a-zA-Z0-9_-]+')->name('show');
    Route::put('/options/{id}', 'RiskManagement\RiskConfigurationController@update')->name('update');
    Route::delete('/options/{id}', 'RiskManagement\RiskConfigurationController@destroy')->name('destroy');
  });

  Route::prefix('risks')->name('risks.')->group(function () {
    Route::get('/', 'RiskManagement\RiskManagementController@index')->name('index')->middleware('can:risk-management.components.risks.view');
    Route::get('/create', 'RiskManagement\RiskManagementController@create')->name('create')->middleware('can:risk-management.components.risks.add');
    Route::post('/', 'RiskManagement\RiskManagementController@store')->name('store')->middleware('can:risk-management.components.risks.add');
    Route::get('/{id}', 'RiskManagement\RiskManagementController@show')->name('show')->middleware('can:risk-management.components.risks.view');
    Route::get('/{id}/edit', 'RiskManagement\RiskManagementController@edit')->name('edit')->middleware('can:risk-management.components.risks.edit');
    Route::put('/{id}', 'RiskManagement\RiskManagementController@update')->name('update')->middleware('can:risk-management.components.risks.edit');
    Route::delete('/{id}', 'RiskManagement\RiskManagementController@destroy')->name('destroy')->middleware('can:risk-management.components.risks.delete');
    Route::post('/{id}/change-status', 'RiskManagement\RiskManagementController@changeStatus')->name('change-status')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/approve-next-step', 'RiskManagement\RiskManagementController@approveToNextStep')->name('approve-next-step')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/assessment', 'RiskManagement\RiskManagementController@storeAssessment')->name('assessment.store')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/evaluation', 'RiskManagement\RiskManagementController@storeEvaluation')->name('evaluation.store')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/treatment-plan', 'RiskManagement\RiskManagementController@storeTreatmentPlan')->name('treatment-plan.store')->middleware('can:risk-management.components.risks.edit');
    Route::get('/{riskId}/treatment-plan/{treatmentPlanId}', 'RiskManagement\RiskManagementController@showTreatmentPlan')->name('treatment-plan.show')->middleware('can:risk-management.components.risks.view');
    Route::put('/{riskId}/treatment-plan/{treatmentPlanId}', 'RiskManagement\RiskManagementController@updateTreatmentPlan')->name('treatment-plan.update')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{riskId}/treatment-plan/{treatmentPlanId}/attachments/upload', 'RiskManagement\RiskManagementController@uploadTreatmentPlanAttachment')->name('treatment-plan.attachments.upload')->middleware('can:risk-management.components.risks.edit');
    Route::delete('/treatment-plan/attachments/{attachmentId}', 'RiskManagement\RiskManagementController@deleteTreatmentPlanAttachment')->name('treatment-plan.attachments.delete')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/review', 'RiskManagement\RiskManagementController@storeReview')->name('review.store')->middleware('can:risk-management.components.risks.edit');
    Route::put('/{riskId}/review/{reviewId}', 'RiskManagement\RiskManagementController@updateReview')->name('review.update')->middleware('can:risk-management.components.risks.edit');
    Route::put('/{id}/closure-justification', 'RiskManagement\RiskManagementController@updateClosureJustification')->name('closure-justification.update')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/close', 'RiskManagement\RiskManagementController@closeRisk')->name('close')->middleware('can:risk-management.components.risks.edit');
    Route::post('/{id}/attachments/upload', 'RiskManagement\RiskManagementController@uploadAttachment')->name('attachments.upload')->middleware('can:risk-management.components.risks.edit');
    Route::get('/attachments/{attachmentId}/download', 'RiskManagement\RiskManagementController@downloadAttachment')->name('attachments.download')->middleware('can:risk-management.components.risks.view');
    Route::delete('/attachments/{attachmentId}', 'RiskManagement\RiskManagementController@deleteAttachment')->name('attachments.delete')->middleware('can:risk-management.components.risks.delete');
    Route::get('/server-side', 'RiskManagement\RiskManagementController@serverSide')->name('server-side')->middleware('can:risk-management.components.risks.view');
    Route::post('/{id}/process-links', 'RiskManagement\RiskManagementController@storeProcessLink')->name('process-links.store')->middleware('can:risk-management.components.risks.edit');
    Route::put('/process-links/{id}', 'RiskManagement\RiskManagementController@updateProcessLink')->name('process-links.update')->middleware('can:risk-management.components.risks.edit');
    Route::delete('/process-links/{id}', 'RiskManagement\RiskManagementController@destroyProcessLink')->name('process-links.destroy')->middleware('can:risk-management.components.risks.edit');
  });
});
//###################################RISK MANAGEMENT#######################################

//###################################AUDIT MANAGEMENT#######################################
Route::prefix('audit')->name('audit.')->middleware(['auth', 'can:audit.module.access'])->group(function () {
    Route::get('/', 'AuditModule\AuditDashboardController@index')->name('dashboard');

    // Audit Management
    Route::prefix('audits')->name('audits.')->middleware('can:audit.components.audits.view')->group(function () {
        Route::get('/', 'AuditModule\AuditManagementController@index')->name('index');
        Route::get('/create', 'AuditModule\AuditManagementController@create')->name('create')->middleware('can:audit.components.audits.add');
        Route::post('/', 'AuditModule\AuditManagementController@store')->name('store')->middleware('can:audit.components.audits.add');
        Route::get('/{id}', 'AuditModule\AuditManagementController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\AuditManagementController@edit')->name('edit')->middleware('can:audit.components.audits.edit');
        Route::put('/{id}', 'AuditModule\AuditManagementController@update')->name('update')->middleware('can:audit.components.audits.edit');
        Route::delete('/{id}', 'AuditModule\AuditManagementController@destroy')->name('destroy')->middleware('can:audit.components.audits.delete');
        Route::post('/{id}/change-status', 'AuditModule\AuditManagementController@changeStatus')->name('change-status')->middleware('can:audit.components.audits.edit');
        Route::post('/{id}/approve-next-step', 'AuditModule\AuditManagementController@approveToNextStep')->name('approve-next-step')->middleware('can:audit.components.audits.edit');
        Route::get('/{id}/pdf', 'AuditModule\AuditManagementController@generatePdf')->name('pdf');
        Route::post('/{id}/attachments/upload', 'AuditModule\AuditManagementController@uploadAttachment')->name('attachments.upload')->middleware('can:audit.components.audits.edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\AuditManagementController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\AuditManagementController@deleteAttachment')->name('attachments.delete')->middleware('can:audit.components.audits.delete');
        Route::post('/{id}/team-members', 'AuditModule\AuditManagementController@addTeamMember')->name('team-members.store')->middleware('can:audit.components.audits.edit');
        Route::put('/team-members/{teamMemberId}', 'AuditModule\AuditManagementController@updateTeamMember')->name('team-members.update')->middleware('can:audit.components.audits.edit');
        Route::delete('/team-members/{teamMemberId}', 'AuditModule\AuditManagementController@removeTeamMember')->name('team-members.destroy')->middleware('can:audit.components.audits.delete');
        Route::post('/{id}/findings', 'AuditModule\AuditManagementController@storeFinding')->name('findings.store')->middleware('can:audit.components.audits.edit');
        Route::put('/{id}/findings/{findingId}', 'AuditModule\AuditManagementController@updateFinding')->name('findings.update')->middleware('can:audit.components.audits.edit');
    });

    // Non-Conformance Management
    Route::prefix('non-conformances')->name('nc.')->middleware('can:audit.components.non-conformances.view')->group(function () {
        Route::get('/', 'AuditModule\NonConformanceController@index')->name('index');
        Route::get('/create', 'AuditModule\NonConformanceController@create')->name('create')->middleware('can:audit.components.non-conformances.add');
        Route::post('/', 'AuditModule\NonConformanceController@store')->name('store')->middleware('can:audit.components.non-conformances.add');
        Route::get('/{id}', 'AuditModule\NonConformanceController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\NonConformanceController@edit')->name('edit')->middleware('can:audit.components.non-conformances.edit');
        Route::put('/{id}', 'AuditModule\NonConformanceController@update')->name('update')->middleware('can:audit.components.non-conformances.edit');
        Route::delete('/{id}', 'AuditModule\NonConformanceController@destroy')->name('destroy')->middleware('can:audit.components.non-conformances.delete');
        Route::post('/{id}/change-status', 'AuditModule\NonConformanceController@changeStatus')->name('change-status')->middleware('can:audit.components.non-conformances.edit');
        Route::get('/{id}/pdf', 'AuditModule\NonConformanceController@generatePdf')->name('pdf');
        Route::post('/{id}/attachments/upload', 'AuditModule\NonConformanceController@uploadAttachment')->name('attachments.upload')->middleware('can:audit.components.non-conformances.edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\NonConformanceController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\NonConformanceController@deleteAttachment')->name('attachments.delete')->middleware('can:audit.components.non-conformances.delete');
        Route::post('/{id}/rca', 'AuditModule\NonConformanceController@storeRca')->name('rca.store')->middleware('can:audit.components.non-conformances.edit');
        Route::get('/rca/{rcaId}/edit', 'AuditModule\NonConformanceController@editRca')->name('rca.edit')->middleware('can:audit.components.non-conformances.edit');
        Route::put('/rca/{rcaId}', 'AuditModule\NonConformanceController@updateRca')->name('rca.update')->middleware('can:audit.components.non-conformances.edit');
        Route::delete('/rca/{rcaId}', 'AuditModule\NonConformanceController@deleteRca')->name('rca.delete')->middleware('can:audit.components.non-conformances.delete');
        Route::post('/{id}/capa', 'AuditModule\NonConformanceController@storeCapa')->name('capa.store')->middleware('can:audit.components.non-conformances.edit');
        Route::get('/search/samples', 'AuditModule\NonConformanceController@searchSamples')->name('search.samples');
        Route::get('/search/equipment', 'AuditModule\NonConformanceController@searchEquipment')->name('search.equipment');
        Route::get('/search/methods', 'AuditModule\NonConformanceController@searchMethods')->name('search.methods');
    });

    // Corrective Actions
    Route::prefix('corrective-actions')->name('corrective-actions.')->middleware('can:audit.components.corrective actions.view')->group(function () {
        Route::get('/', 'AuditModule\CorrectiveActionController@index')->name('index');
        Route::get('/create', 'AuditModule\CorrectiveActionController@create')->name('create')->middleware('can:audit.components.corrective actions.add');
        Route::post('/', 'AuditModule\CorrectiveActionController@store')->name('store')->middleware('can:audit.components.corrective actions.add');
        Route::get('/{id}', 'AuditModule\CorrectiveActionController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\CorrectiveActionController@edit')->name('edit')->middleware('can:audit.components.corrective actions.edit');
        Route::put('/{id}', 'AuditModule\CorrectiveActionController@update')->name('update')->middleware('can:audit.components.corrective actions.edit');
        Route::delete('/{id}', 'AuditModule\CorrectiveActionController@destroy')->name('destroy')->middleware('can:audit.components.corrective actions.delete');
        Route::post('/{id}/change-status', 'AuditModule\CorrectiveActionController@changeStatus')->name('change-status')->middleware('can:audit.components.corrective actions.edit');
        Route::post('/{id}/implement', 'AuditModule\CorrectiveActionController@implement')->name('implement')->middleware('can:audit.components.corrective actions.edit');
        Route::post('/{id}/verify', 'AuditModule\CorrectiveActionController@verify')->name('verify')->middleware('can:audit.components.corrective actions.edit');
        Route::post('/{id}/attachments/upload', 'AuditModule\CorrectiveActionController@uploadAttachment')->name('attachments.upload')->middleware('can:audit.components.corrective actions.edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\CorrectiveActionController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\CorrectiveActionController@deleteAttachment')->name('attachments.delete')->middleware('can:audit.components.corrective actions.delete');
    });

    // CAPA alias routes
    Route::prefix('capa')->name('capa.')->middleware('can:audit.components.corrective actions.view')->group(function () {
        Route::get('/', 'AuditModule\CorrectiveActionController@index')->name('index');
        Route::get('/create', 'AuditModule\CorrectiveActionController@create')->name('create')->middleware('can:audit.components.corrective actions.add');
        Route::post('/', 'AuditModule\CorrectiveActionController@store')->name('store')->middleware('can:audit.components.corrective actions.add');
        Route::get('/{id}', 'AuditModule\CorrectiveActionController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\CorrectiveActionController@edit')->name('edit')->middleware('can:audit.components.corrective actions.edit');
        Route::put('/{id}', 'AuditModule\CorrectiveActionController@update')->name('update')->middleware('can:audit.components.corrective actions.edit');
        Route::delete('/{id}', 'AuditModule\CorrectiveActionController@destroy')->name('destroy')->middleware('can:audit.components.corrective actions.delete');
        Route::post('/{id}/change-status', 'AuditModule\CorrectiveActionController@changeStatus')->name('change-status')->middleware('can:audit.components.corrective actions.edit');
        Route::post('/{id}/implement', 'AuditModule\CorrectiveActionController@implement')->name('implement')->middleware('can:audit.components.corrective actions.edit');
        Route::post('/{id}/verify', 'AuditModule\CorrectiveActionController@verify')->name('verify')->middleware('can:audit.components.corrective actions.edit');
        Route::post('/{id}/attachments/upload', 'AuditModule\CorrectiveActionController@uploadAttachment')->name('attachments.upload')->middleware('can:audit.components.corrective actions.edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\CorrectiveActionController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\CorrectiveActionController@deleteAttachment')->name('attachments.delete')->middleware('can:audit.components.corrective actions.delete');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:audit.components.reports.view')->group(function () {
        Route::get('/', 'AuditModule\AuditReportController@index')->name('index');
        Route::get('/audit-summary', 'AuditModule\AuditReportController@auditSummary')->name('audit-summary');
        Route::get('/nc-register', 'AuditModule\AuditReportController@ncRegister')->name('nc-register');
        Route::get('/capa-status', 'AuditModule\AuditReportController@capaStatus')->name('capa-status');
        Route::get('/advanced-statistics', 'AuditModule\AuditReportController@advancedStatistics')->name('advanced-statistics');
        Route::get('/export/{type}', 'AuditModule\AuditReportController@export')->name('export');
    });

    // Configuration
    Route::prefix('config')->name('config.')->middleware('can:audit.components.configuration.view')->group(function () {
        Route::get('/approval-config', function () {
            return view('layouts.audit.config.approval-config');
        })->name('approval-config');

        Route::get('/verification-results', 'AuditModule\AuditConfigController@verificationResults')->name('verification-results');
        Route::post('/verification-results', 'AuditModule\AuditConfigController@storeVerificationResult')->name('verification-results.store')->middleware('can:audit.components.configuration.edit');
        Route::get('/verification-results/{id}', 'AuditModule\AuditConfigController@getVerificationResult')->name('verification-results.get');
        Route::put('/verification-results/{id}', 'AuditModule\AuditConfigController@updateVerificationResult')->name('verification-results.update')->middleware('can:audit.components.configuration.edit');

        Route::get('/audit-types', 'AuditModule\AuditConfigController@auditTypes')->name('audit-types');
        Route::get('/audit-statuses', 'AuditModule\AuditConfigController@auditStatuses')->name('audit-statuses');
        Route::get('/workflow-actions', 'AuditModule\AuditConfigController@workflowActions')->name('workflow-actions');
        Route::get('/workflow-action-rules', 'AuditModule\AuditConfigController@workflowActionRules')->name('workflow-action-rules');
        Route::get('/finding-categories', 'AuditModule\AuditConfigController@findingCategories')->name('finding-categories');
        Route::get('/risk-levels', 'AuditModule\AuditConfigController@riskLevels')->name('risk-levels');
        Route::get('/severity-scales', 'AuditModule\AuditConfigController@severityScales')->name('severity-scales');
        Route::get('/likelihood-scales', 'AuditModule\AuditConfigController@likelihoodScales')->name('likelihood-scales');
        Route::get('/rca-methods', 'AuditModule\AuditConfigController@rcaMethods')->name('rca-methods');
        Route::get('/capa-categories', 'AuditModule\AuditConfigController@capaCategories')->name('capa-categories');
        Route::get('/compliance-statuses', 'AuditModule\AuditConfigController@complianceStatuses')->name('compliance-statuses');

        Route::prefix('email-templates')->name('email-templates.')->group(function () {
            Route::get('/', 'AuditModule\AuditEmailTemplateController@index')->name('index');
            Route::get('/create', 'AuditModule\AuditEmailTemplateController@create')->name('create')->middleware('can:audit.components.configuration.add');
            Route::post('/', 'AuditModule\AuditEmailTemplateController@store')->name('store')->middleware('can:audit.components.configuration.add');
            Route::get('/{id}/edit', 'AuditModule\AuditEmailTemplateController@edit')->name('edit')->middleware('can:audit.components.configuration.edit');
            Route::put('/{id}', 'AuditModule\AuditEmailTemplateController@update')->name('update')->middleware('can:audit.components.configuration.edit');
            Route::delete('/{id}', 'AuditModule\AuditEmailTemplateController@destroy')->name('destroy')->middleware('can:audit.components.configuration.delete');
            Route::get('/{id}/preview', 'AuditModule\AuditEmailTemplateController@preview')->name('preview');
            Route::post('/{id}/test', 'AuditModule\AuditEmailTemplateController@test')->name('test')->middleware('can:audit.components.configuration.edit');
        });
    });
});
//###################################AUDIT MANAGEMENT#######################################

//###################################HELP DESK#######################################
Route::prefix('tickets')->name('tickets.')->middleware(['auth', 'can:tickets.module.access'])->group(function () {
    Route::get('/dashboard', 'Ticket\TicketController@dashboard')->name('dashboard')->middleware('can:helpdesk.components.dashboard.view');

    Route::prefix('categories')->middleware('can:helpdesk.components.categories.view')->group(function () {
        Route::get('/list', 'Ticket\TicketController@listCategories')->name('categories.list');
        Route::get('/', 'Ticket\TicketController@categories')->name('categories');
        Route::post('/', 'Ticket\TicketController@storeCategory')->name('categories.store')->middleware('can:helpdesk.components.categories.add');
        Route::put('/{id}', 'Ticket\TicketController@updateCategory')->name('categories.update')->middleware('can:helpdesk.components.categories.edit');
        Route::post('/{id}/toggle-status', 'Ticket\TicketController@toggleCategoryStatus')->name('categories.toggle-status')->middleware('can:helpdesk.components.categories.edit');
    });
    Route::get('/deleted', 'Ticket\TicketController@deleted')->name('deleted')->middleware('can:helpdesk.components.archived tickets.view');

    Route::get('/', 'Ticket\TicketController@myTickets')->name('index')->middleware('can:helpdesk.components.tickets.view');
    Route::get('/create', 'Ticket\TicketController@create')->name('create')->middleware('can:helpdesk.components.tickets.add');
    Route::post('/', 'Ticket\TicketController@store')->name('store')->middleware('can:helpdesk.components.tickets.add');
    Route::get('/{id}', 'Ticket\TicketController@show')->name('show')->middleware('can:helpdesk.components.tickets.view');
    Route::delete('/{id}', 'Ticket\TicketController@destroy')->name('destroy')->middleware('can:helpdesk.components.tickets.delete');
    Route::post('/{id}/upload', 'Ticket\TicketController@uploadFiles')->name('upload')->middleware('can:helpdesk.components.tickets.edit');
    Route::get('/{id}/chat', 'Ticket\TicketController@chat')->name('chat')->middleware('can:helpdesk.components.chat.view');
    Route::post('/{id}/chat', 'Ticket\TicketController@sendChatMessage')->name('chat.send')->middleware('can:helpdesk.components.chat.add');
    Route::get('/{id}/chat/messages', 'Ticket\TicketController@getChatMessages')->name('chat.messages')->middleware('can:helpdesk.components.chat.view');
});
//###################################HELP DESK#######################################

//###################################REQUISITION TRAIL#######################################
Route::get('/req/{stage}', 'RequisitionController@open_stage')->name('go_to_stage')->middleware('can:inventory.module.access');
Route::get('/get_req_enitites_server_side/{stage}/{type}', 'RequestEntityController@get_entities_server_side')->name('get_req_enitites_server_side')->middleware('can:inventory.module.access');
Route::post('/make-po-ammendment/{id}/{stage}', 'RequisitionController@make_po_ammendment')->name('make-po-ammendment')->middleware('can:inventory.module.access');

Route::get('/requester_verification_confirmation/{id}', 'RequisitionController@requester_verification_confirmation')->name('requester_verification_confirmation');

Route::post('/req/{stage}/{id}/delete', 'RequisitionController@removeRequestEntity')->name('delete-request-details')->middleware('can:inventory.module.access');
Route::post('/req/{stage}/cloned', 'RequisitionController@clone_entity')->name('clone-request-details')->middleware('can:inventory.module.access');
Route::get('/req/{stage}/{id}/{ammendement?}', 'RequisitionController@show')->name('view-request-details')->middleware('can:inventory.module.access');
Route::post('/req/{stage}/{id}/{ammendement?}', 'RequisitionController@update')->name('save-request-details')->middleware('can:inventory.module.access');
Route::get('/req-report-generate/{id}/{supply?}', 'ReportGeneratorController@generate_report')->name('req-report-generate')->middleware('can:inventory.module.access');
Route::get('/req-report-generate-pdf/{id}', 'ReportGeneratorController@generate_report_pdf')->name('req-report-generate-pdf')->middleware('can:inventory.module.access');
Route::get('/check-pdf-processing-progress/{id}', 'ReportGeneratorController@check_pdf_processing_progress')->name('check-pdf-processing-progress')->middleware('can:inventory.module.access');

Route::get('/supplier-rfq-pdf/{supplier}/{id}', 'ReportGeneratorController@generate_supplier_pdf')->name('generate-supplier-pdf')->middleware('can:inventory.components.request for quotation.view');

Route::get('/download-request-items/{id}/{isPDF?}', 'ReportGeneratorController@download_items_xlsx')->name('download-request-items')->middleware('can:inventory.module.access');

Route::post('/create-lpo-from-mr/{id}', 'RequisitionController@create_lpo_from_mr')->name('create-lpo-from-mr')->middleware('can:inventory.components.purchase orders.edit');
Route::post('/change-req-approver/{stage}/{id}', 'RequisitionController@change_req_approver')->name('change-req-approver')->middleware('can:inventory.module.access');

Route::post('/add-extra-charge/{id}', 'RequisitionController@add_extra_charge')->name('add-extra-charge')->middleware('can:inventory.components.purchase orders.edit');
Route::post('/remove-extra-charge/{id}', 'RequisitionController@remove_extra_charge')->name('remove-extra-charge')->middleware('can:inventory.components.purchase orders.edit');

Route::post('/reverse-entity-action/{id}', 'RequisitionController@reverse_entity_action')->name('reverse-entity-action')->middleware('can:inventory.module.access');

Route::post('/req/download/{id}/{type}', 'RequisitionController@download')->name('download-requisition-doc')->middleware('can:inventory.module.access');
Route::post('/mark-gr-as-complete/{id}', 'RequisitionController@mark_gr_as_complete')->name('mark-gr-as-complete')->middleware('can:inventory.components.goods receipt.edit');
Route::post('/submit-bank-details/{id}', 'RequisitionController@submit_bank_details')->name('submit-bank-details')->middleware('can:inventory.components.purchase orders.edit');
Route::post('/upload-bank-confirmation/{id}', 'RequisitionController@upload_bank_confirmation')->name('upload-bank-confirmation')->middleware('can:inventory.components.purchase orders.edit');
Route::post('/add-email-body-rfq/{id}', 'RequisitionController@add_email_body_rfq')->name('add-email-body-rfq')->middleware('can:inventory.components.request for quotation.edit');

// Route::post('/jump-request-to-status/{id}', 'RequisitionController@jump_request_to_status')->name('jump-request-to-status')->middleware('can:inventory.components.request for quotation.edit');

Route::post('/req-locations-add', 'RequisitionLocationController@add')->name('req-locations-add')->middleware('can:inventory.module.access');
Route::post('/req-locations-remove', 'RequisitionLocationController@remove')->name('req-locations-remove')->middleware('can:inventory.module.access');
//###################################REQUISITION TRAIL#######################################

//##############################################BATCH COMMENTS#######################################
Route::post('/add-batch-comment', 'BatchCommentController@add')->name('add-batch-comment');
Route::post('/edit-batch-comment/{id}', 'BatchCommentController@edit')->name('edit-batch-comment');
//###############################################BATCH COMMENTS#######################################

//##############################################My Approvals#######################################
Route::get('/my-approvals', 'HomeController@my_approvals')->name('my-approvals')->middleware('can:inventory.components.approval-requests.view');
//###############################################My Approvals#######################################

//###################################PERSONNEL LINKS#######################################
Route::get('/personnel-home', 'PersonnelController@index')->name('personnel-home')->middleware('can:personnel.module.access');
Route::get('/personnel-list', 'PersonnelController@personnel_list')->name('personnel-list')->middleware('can:personnel.personnel.view');
Route::post('/add-personnel/{id}', 'PersonnelController@add')->name('add-personnel')->middleware('can:personnel.personnel.edit');
Route::get('/view-personnel/{id}', 'PersonnelController@show_personnel')->name('view-personnel')->middleware('can:personnel.personnel.view');

Route::get('/organizational-departments', 'PersonnelController@departments')->name('show-organizational-departments')->middleware('can:personnel.departments.view');
Route::post('/organizational-departments', 'PersonnelController@add_department')->name('add-organizational-department')->middleware('can:personnel.departments.add');
Route::post('/personnel-organizational/{id}', 'PersonnelController@edit_department')->name('edit-organizational-department')->middleware('can:personnel.departments.edit');

Route::post('/add-personnel-role/{user_id}', 'UserRoleController@add')->name('add-personnel-role')->middleware('can:personnel.roles.add');
Route::post('/edit-approval-departments/{user_id}/{role}', 'UserRoleController@edit_departments')->name('edit-approval-departments')->middleware('can:personnel.roles.edit');
Route::post('/remove-personnel-role/{id}', 'UserRoleController@remove')->name('remove-personnel-role')->middleware('can:personnel.roles.delete');

Route::post('/personnel-state-change/{id}', 'PersonnelController@deactivate_personnel')->name('personnel-state')->middleware('can:personnel.personnel.edit');
Route::post('/reset-personnel-password/{id}', 'PersonnelController@reset_personnel_password')->name('reset-personnel')->middleware('can:personnel.personnel.edit');

Route::get('/locked-accounts', 'PersonnelController@lockedAccounts')->name('locked-accounts')->middleware('can:personnel.personnel.edit');
Route::post('/unlock-account/{id}', 'PersonnelController@unlockAccount')->name('unlock-account')->middleware('can:personnel.personnel.edit');

Route::get('/personel/certification-coniguration', 'Personel\CertificationController@index')->name('personnel-certification-home')->middleware('can:personnel.configurations.view');

Route::post('/add/personnel-certification/{id}', 'Personel\PersonnelCertificationController@add')->name('add-personnel-certification')->middleware('can:personnel.configurations.add');
Route::post('/edit/personnel-certification/{id}', 'Personel\PersonnelCertificationController@edit')->name('edit-personnel-certification')->middleware('can:personnel.configurations.edit');
Route::post('/delete/personnel-certification/{id}', 'Personel\PersonnelCertificationController@delete')->name('delete-personnel-certification')->middleware('can:personnel.configurations.delete');

Route::post('/personnel-managment/add-job-responsibility/{id}', 'ModulePreConfigsController@addResponsibilities')->name('addResponsibilities')->middleware('can:personnel.configurations.add');
Route::post('/personnel-managment/edit-job-responsibility/{id}', 'ModulePreConfigsController@editResposibility')->name('editResposibility')->middleware('can:personnel.configurations.edit');
Route::get('/personnel-managment/show-job-responsibility/{id}', 'ModulePreConfigsController@showResponsibility')->name('showResponsibility')->middleware('can:personnel.configurations.view');

Route::get('/personnel-user/profile', 'PersonnelController@user_profile')->name('user_profile')->middleware('can:personnel.personnel.view');
//###########################
//###################################PERSONNEL LINKS#######################################

//###################################ROLES LINKS#######################################
Route::get('/organizational-roles', 'RoleController@index')->name('organizational-roles')->middleware('can:personnel.roles.view');
Route::get('/organizational-role/{id}', 'RoleController@show')->name('view-organizational-role')->middleware('can:personnel.roles.view');
Route::post('/add-organizational-role', 'RoleController@add')->name('add-organizational-role')->middleware('can:personnel.roles.add');
Route::post('/edit-organizational-role/{id}', 'RoleController@edit')->name('edit-organizational-role')->middleware('can:personnel.roles.edit');
Route::post('/save-role-rights/{id}', 'RoleController@save_roles')->name('save-role-rights')->middleware('can:personnel.roles.edit');

Route::post('/add/role-certification/{id}', 'Personel\CertificationController@add_role_certification')->name('add-role-certification')->middleware('can:personnel.roles.add');
Route::post('/edit/role-certification/{id}', 'Personel\CertificationController@edit_role_certification')->name('edit-role-certification')->middleware('can:personnel.roles.edit');
Route::post('/delete/role-certification/{id}', 'Personel\CertificationController@delete_role_certification')->name('delete-role-certification')->middleware('can:personnel.roles.delete');
//###################################ROLES LINKS#######################################

//###################################APPROVALS LINKS#######################################
Route::post('/add-approval-to-stage/{stage}', 'ApprovalsController@add')->name('add-approval-to-stage');
Route::post('/edit-approval-to-stage/{id}', 'ApprovalsController@edit')->name('edit-approval-to-stage');
Route::post('/remove-approval-to-stage/{id}', 'ApprovalsController@remove')->name('remove-approval-to-stage');
//###################################APPROVALS LINKS#######################################

//###################################ATTACHMENT LINKS#######################################
Route::post('/add-page-attachment', 'HomeController@add_attachment')->name('add-page-attachment');
Route::post('/remove-page-attachment/{docID}', 'HomeController@remove_attachment')->name('remove-page-attachment');
//###################################ATTACHMENT LINKS#######################################

//##################################ASSETS LINKS######################################
Route::get('/asset-type-home', 'Asset\AssetTypeController@index')->name('asset-type-home')->middleware('can:equipment.components.asset-type.view');
Route::post('/asset-type', 'Asset\AssetTypeController@add')->name('add-asset-type')->middleware('can:equipment.components.asset-type.add');
Route::post('/edit/asset-type/{id}', 'Asset\AssetTypeController@edit')->name('edit-asset-type')->middleware('can:equipment.components.asset-type.edit');

Route::get('/asset-location-home', 'Asset\AssetLocationController@index')->name('asset-location-home')->middleware('can:equipment.components.asset-location.view');
Route::post('/asset-location', 'Asset\AssetLocationController@add')->name('add-asset-location')->middleware('can:equipment.components.asset-location.add');
Route::post('/edit/asset-location/{id}', 'Asset\AssetLocationController@edit')->name('edit-asset-location')->middleware('can:equipment.components.asset-location.edit');
//##################################ASSETS LINKS######################################

//###################################MODULE PRE_CONFIGS LINKS#######################################
Route::get('/module-pre-configs/{config}/{module}', 'ModulePreConfigsController@index')->name('module-pre-configs')->middleware('auth');
Route::post('/add-module-pre-configs/{id}/{config}/{module}', 'ModulePreConfigsController@update')->name('add-module-pre-configs')->middleware('auth');
Route::get('/view-currency-conversions', 'ModulePreConfigsController@view_currency_conversion')->name('view-currency-conversions')->middleware('auth');
Route::get('/view-uom-conversions', 'ModulePreConfigsController@view_uom_conversion')->name('view-uom-conversions')->middleware('auth');
Route::post('/add-currency-conversions', 'ModulePreConfigsController@currency_conversion')->name('add-currency-conversions')->middleware('auth');
Route::post('/add-uom-conversion', 'ModulePreConfigsController@uom_conversion')->name('add-uom-conversion')->middleware('auth');
Route::get('/show-material-type/{id}', 'ModulePreConfigsController@show_material_type')->name('show-material-type')->middleware('auth');
//###################################MODULE PRE_CONFIGS LINKS#######################################

//###################################PRICELISTS#######################################
Route::get('/pricelists', function () {
    return view('layouts.billing.pricelists-index');
})->name('view-pricelists')->middleware('can:laboratory.components.pricelists.view');
Route::post('/pricelist/{id?}', 'PricelistItemController@update')->name('update-pricelist');
Route::post('/pricelist/{id}/upload', 'PricelistItemController@upload')->name('upload-pricelist-pdf');
Route::post('/pricelist/{id}/email', 'PricelistItemController@email')->name('email-pricelist-pdf');
Route::get('/pricelist/{id}/{print?}', function ($id, $print = null) {
    return view('layouts.billing.pricelist-show', [
        'pricelistId' => $id,
        'print' => $print,
    ]);
})->name('show-pricelist')->middleware('can:laboratory.components.pricelists.view');
Route::post('/pricelist/{id}/item', 'PricelistItemController@update_item')->name('update-pricelist-item');
Route::post('/save-price-changes/{id}', 'PricelistItemController@save_price_changes')->name('save-price-changes');
Route::post('/clone-items-to-new-pricelist/{id}', 'PricelistItemController@clone_items_to_new_pricelist')->name('clone-items-to-new-pricelist');
Route::get('/move-pricelist-item/{direction}/{pricelist}/{element}', 'PricelistItemController@move_pricelist_item')->name('move-pricelist-item');
Route::post('/add-customer-to-pricelist/{id}', 'PricelistItemController@add_customer')->name('add-customer-to-pricelist');
Route::post('/remove-customer-to-pricelist/{id}', 'PricelistItemController@remove_customer')->name('remove-customer-to-pricelist');
//###################################PRICELISTS#######################################

//######################################SYSTEMS ##################################################################
Route::get('/system/configuration-type/home', 'System\SystemConfigurationTypeController@index')->name('configuration-type-home')->middleware(['can:settings.module.access', 'can:system.configuration_type.view']);
Route::post('/edit/system/configuration-type/{id}', 'System\SystemConfigurationTypeController@edit')->name('edit-configuration-type')->middleware(['can:settings.module.access', 'can:system.configuration_type.edit']);
Route::post('/add/system/configuration-type/', 'System\SystemConfigurationTypeController@add')->name('add-configuration-type')->middleware(['can:settings.module.access', 'can:system.configuration_type.add']);

Route::get('/system/configuration-home', 'System\SystemConfigurationsController@index')->name('configuration-system-home')->middleware(['can:settings.module.access', 'can:system.configuration.view']);
Route::post('/add/system/configuration/{id}', 'System\SystemConfigurationsController@add')->name('add-configuration')->middleware(['can:settings.module.access', 'can:system.configuration.add']);
Route::post('/edit/system/configuration/{id}', 'System\SystemConfigurationsController@edit')->name('edit-configuration')->middleware(['can:settings.module.access', 'can:system.configuration.edit']);
Route::post('/delete/system/configuration/{id}', 'System\SystemConfigurationsController@delete')->name('delete-configuration')->middleware(['can:settings.module.access', 'can:system.configuration.delete']);
//######################################SYSTEMS ##################################################################

//##########################################CRM DASHBOARD#######################################
Route::get('/dasboard/crm/client-home', 'CRM\Dashboard\DashboardController@index')->name('client-dashboard-home');
Route::get('/dashboard/crm/client-details', 'CRM\Dashboard\DashboardController@show')->name('show-client-details');
//##########################################CRM DASHBOARD#######################################

//##########################################SUPPLIER DASHBOARD#######################################
Route::get('/dashboard/supplier-home', 'Suppliers\SupplierDashboardController@index')->name('supplier-dashboard-home');
Route::get('/chat/userdetails', 'Suppliers\ChatMessageController@userdetails')->name('user-detail');
Route::get('/supplier/chat/userdetails', 'Suppliers\ChatMessageController@userdetails_supplier')->name('user-detail-supplier');
Route::post('/user/chat/add', 'Suppliers\ChatMessageController@add')->name('add-chat');
Route::post('/user/chat/view', 'Suppliers\ChatMessageController@userchat')->name('user-chat-view');
Route::get('/supplier/user/chats/{id}', 'Suppliers\ChatMessageController@userchat_supplier')->name('user-chat-supplier');
Route::get('/rfqs/items/{id}', 'Suppliers\ChatMessageController@getRequestItems')->name('get-rfqs-item');

Route::get('rfqs-item/{id}', 'Suppliers\ChatMessageController@getSingleRequestItem')->name('rfq-item');
Route::post('/add/supplier-quote/{id}', 'Suppliers\ChatMessageController@addSupplierQuote')->name('add-supplier-quote');

Route::post('/rating-criteria/{id?}', 'RatingCriteriaController@update')->name('rating-criteria');
Route::post('/update-rating-criteria-score/{id}', 'SuppliersRatingCriteriaController@update')->name('update-rating-criteria-score');

Route::get('rfq-item/quotation/{id}', 'Suppliers\QuotationAttachmentController@index')->name('get-quotation');
Route::post('add-quotation/notes', 'Suppliers\QuotationAttachmentController@addNotes')->name('add-quotation-note');
Route::post('add-quotation/attachments', 'Suppliers\QuotationAttachmentController@addAttachment')->name('add-attachments');
Route::get('delete/quotation-notes/{id}', 'Suppliers\QuotationAttachmentController@delete_notes')->name('delete-notes');
Route::get('delete/quotation-attachment/{id}', 'Suppliers\QuotationAttachmentController@delete_attachment')->name('delete-attachment');
//##########################################SUPPLIER DASHBOARD#######################################

//#################################INVOICE#######################################
Route::get('/invoice-home', 'Invoice\InvoiceController@index')->name('invoice-home')->middleware('can:laboratory.components.proforma invoices.view');
Route::get('/invoice/sample/{id}', 'Invoice\InvoiceController@show')->name('invoice-sample-header')->middleware('can:laboratory.components.proforma invoices.view');
Route::get('/invoice/generate/{id}', 'Invoice\InvoiceController@generateinvoice')->name('generate-invoice')->middleware('can:laboratory.components.proforma invoices.add');
Route::get('/print/invoice/{id}', 'Invoice\InvoiceController@print_invoice')->name('print-invoice');
Route::post('/upload/invoice', 'Invoice\InvoiceController@upload_invoice')->name('upload-invoice');
Route::post('/email/invoice', 'Invoice\InvoiceController@email_invoice')->name('email-invoice');
Route::post('/edit/invoice', 'Invoice\InvoiceController@edit_invoice')->name('edit_invoice');
Route::post('/add_tax_invoice', 'Invoice\InvoiceController@add_tax_invoice')->name('add_tax_invoice');

//#################################INVOICE#######################################

//#################################TAX REGIME#######################################
// New Livewire-based route
Route::get('/billing/tax-regime', function () {
    return view('layouts.billing.tax-regime-index');
})->name('billing.tax-regime')->middleware('can:laboratory.components.tax regime.view');

// Keep old routes for backward compatibility (commented out)
// Route::get('/tax-home', 'Invoice\InvoiceController@tax_index')->name('tax-home')->middleware('can:laboratory.components.tax regime.view');
// Route::post('/edit-tax/regime/{id}', 'Invoice\InvoiceController@edit_tax')->name('edit-tax')->middleware('can:laboratory.components.tax regime.edit');
// Route::post('/add-tax/regime', 'Invoice\InvoiceController@add_tax')->name('add-tax')->middleware('can:laboratory.components.tax regime.add');
//#################################TAX REGIME#######################################

//#################################LAB REPORTS#######################################
Route::get('/lab/reports-home', function (\Illuminate\Http\Request $request) {
    return redirect()->route('sample-workflow.kpis', $request->query());
})->name('lab-reports-home')->middleware('can:laboratory.components.lab-reports.view');
// Dormant while KPI dashboard is active — legacy batch/sample/profit report POST handler kept for restoration.
Route::post('/lab/report/show', 'Lab\Reports\SamplesReportsController@show')->name('lab-report-show')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/lab/reports/kpi/registration/summary', [\App\Http\Controllers\Lab\Reports\SampleWorkflowKpiExportController::class, 'registrationSummary'])->name('lab.kpi.registration.summary.export')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/lab/reports/kpi/registration/detail', [\App\Http\Controllers\Lab\Reports\SampleWorkflowKpiExportController::class, 'registrationDetail'])->name('lab.kpi.registration.detail.export')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/lab/reports/kpi/laboratory/summary', [\App\Http\Controllers\Lab\Reports\SampleWorkflowKpiExportController::class, 'laboratorySummary'])->name('lab.kpi.laboratory.summary.export')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/lab/reports/kpi/laboratory/detail', [\App\Http\Controllers\Lab\Reports\SampleWorkflowKpiExportController::class, 'laboratoryDetail'])->name('lab.kpi.laboratory.detail.export')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/module-reports', 'Lab\Reports\ModuleReportsController@index')->name('module-reports.index')->middleware('auth');
Route::post('/module-reports/view', 'Lab\Reports\ModuleReportsController@viewReport')->name('module-reports.view')->middleware('auth');
Route::get('/module-reports/print', 'Lab\Reports\ModuleReportsController@printReport')->name('module-reports.print')->middleware('auth');

Route::get('/lab/sample-generate/certificate-analysis/{id}', 'SampleWorkFlowController@certificate_analysis')->name('certificate-analysis')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/getAnalysisTypeBySampleTypeAjax/{type_id}', 'Lab\Reports\SamplesReportsController@getAnalysisTypeBySampleTypeAjax')->name('getAnalysisTypeBySampleTypeAjax')->middleware('can:laboratory.components.lab-reports.view');

Route::get('/lab/disposal/report', 'SampleWorkFlowController@disposalReportIndex')->name('lab-report-disposal')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/lab/tat/report', '\\' . \App\Livewire\Lab\Reports\TatReport::class)->name('lab-report-tat')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/lab/tat/report/export', \App\Http\Controllers\Lab\TatReportExportController::class)->name('lab-report-tat.export')->middleware('can:laboratory.components.lab-reports.view');

Route::get('/get-analysis-type/{id}/Ajax', 'SampleWorkFlowController@getAnalysisTypeAjax')->name('getAnalysisTypeAjax')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/get-Analyte/{id}/Ajax', 'SampleWorkFlowController@getAnalyteAjax')->name('getAnalyteAjax')->middleware('can:laboratory.components.lab-reports.view');
//#################################LAB REPORTSS#######################################

//###################################DISPOSED EQUIPMENT REPORTS######################################
Route::get('/equipment/reports/home', 'Equipment\EquipmentReportsController@index')->name('equipment-report-generate')->middleware('can:equipment.components.equipment-disposal.view');
Route::post('/equipment/reports/show', 'Equipment\EquipmentReportsController@show')->name('equipment-report-show')->middleware('can:equipment.components.equipment-disposal.view');
Route::get('/equipment/disposed-report/generate', 'Equipment\EquipmentReportsController@disposed_report')->name('disposed-equipment-generate')->middleware('can:equipment.components.equipment-disposal.view');
//###################################DISPOSED EQUIPMENT REPORTS######################################

//###################################Standards#######################################
Route::post('/lab/standard/add', 'Lab\StandardsController@addStandard')->name('add-standard')->middleware('can:laboratory.components.standards.add');
Route::post('/lab/standard/edit/{id}', 'Lab\StandardsController@editStandard')->name('edit-standard')->middleware('can:laboratory.components.standards.edit');
Route::post('/lab/standard-value/add', 'Lab\StandardsController@addStandardValues')->name('add-standard-value')->middleware('can:laboratory.components.standards.add');
Route::post('/lab/standard-value/edit/{id}', 'Lab\StandardsController@editStandardValue')->name('edit-standard-value')->middleware('can:laboratory.components.standards.edit');
Route::get('/lab/standard/show/{id}', 'Lab\StandardsController@show')->name('view-standard')->middleware('can:laboratory.components.standards.view');
//###################################Standards#######################################

//###################################API ROUTES#######################################
// Route::get('/api-get-available-items/{item_id}/{brand_id}/{request_id?}', 'API\APIController@items_available')->name('api-get-available-items');
//###################################API ROUTES#######################################

//###################################REMINDERS ROUTES#######################################
Route::get('/trigger-reminders/{send_reminder}/{type}/{days}/{entity_id?}', 'ReminderController@get_notifiable_entities')->name('trigger-system-reminders');
Route::get('/trigger-pending-approvals-reminder/{id?}', 'ReminderController@send_approval_reminders')->name('trigger-pending-approvals-reminder');
//###################################REMINDERS ROUTES#######################################

//###################################API ROUTES#######################################
Route::get('/get_personnel_via_ajax/{id?}', 'PersonnelController@get_personnel_via_ajax')->name('get_personnel_via_ajax');
Route::get('/get_items_via_ajax/{cat_id?}/{name?}', 'InventorySubCategoriesController@get_items_via_ajax')->name('get_items_via_ajax')->middleware('can:inventory.components.categories.view');
Route::get('/get_suppliers_via_ajax', 'SupplierController@get_suppliers_via_ajax')->name('get_suppliers_via_ajax');
Route::get('/get_item_details/{inv_sub_cat}/{req_id?}', 'InventorySubCategoriesController@get_item_details')->name('get_item_details')->middleware('can:inventory.components.categories.view');
Route::get('/workorder_resources/{wid}', 'WorkOrder\WorkOrderController@workorder_resources')->name('workorder_resources');
Route::get('/fetch-supplier-items/{sID}', 'SupplierController@fetch_supplier_items')->name('fetch_supplier_items');
//###################################API ROUTES#######################################

Route::get('/event/update/schedule', 'Event\EventController@eventUpdateSchedule')->name('eventUpdateSchedule');
Route::post('/get/confirmation/Callback-Url/Payload-wertyasdfgh', 'Mpesa\MpesaController@mpesaConfirmationCallbackUrl')->name('confirmation_url');
Route::post('/get/Validation/Callback-Url/payload-ghfjdks', 'Mpesa\MpesaController@mpesaValidationCallbackUrl')->name('mpesaValidationCallbackUrl');
Route::get('/displayMpesaValidation/gvdasdsgdud', 'Mpesa\MpesaController@displayMpesaValidation')->name('displayMpesaValidation');

//############################################QC Module###########################################
Route::get('Qc/mark-Qc-Sample/Complete/{id}', 'SampleWorkFlowController@markQcSampleComplete')->name('markQcSampleComplete')->middleware('can:laboratory.components.qc sample.edit');
Route::prefix('qualitycontrol')->middleware('can:laboratory.components.qc sample.view')->group(function () {
    Route::get('/', 'QcModule\QualityControlController@index');
    Route::get('/', 'QcModule\QualityControlController@index')->name('qc_index');
    Route::get('/configuration-index', 'QcModule\QualityControlController@configuration_index')->name('qc_configuration_index');

    Route::post('/delete/Qc-Types', 'QcModule\QualityControlController@deleteQCTypes')->name('qc_deleteQCTypes');
    Route::post('/create/Qc-Types', 'QcModule\QualityControlController@createQcTypes')->name('qc_createQcTypes');

    Route::post('/add/Qc-Standard', 'QcModule\QualityControlController@addQcStandard')->name('qc_addQcStandard');
    Route::post('/delete/Qc-Standard', 'QcModule\QualityControlController@deleteQcStandard')->name('qc_deleteQcStandard');

    Route::get('/qc-standard/show/{id}', 'QcModule\QualityControlController@qcStandardShow')->name('qc_StandardShow');
    Route::post('/add/Qc-Standard/Analyte', 'QcModule\QualityControlController@addQcStandardAnalyte')->name('qc_addQcStandardAnalyte');
    Route::post('/delete/Qc-Standard/Analyte', 'QcModule\QualityControlController@deleteQcStandardAnalyte')->name('qc_deleteQcStandardAnalyte');

    Route::post('/Maintain-Qc-Schemes', 'QcModule\QualityControlController@MaintainQcSchemes')->name('MaintainQcSchemes');
    Route::post('/Delete-Qc-Schemes', 'QcModule\QualityControlController@DeleteQcSchemes')->name('DeleteQcSchemes');
    Route::get('/qc-Workflow-Index', 'QcModule\QualityControlController@qcWorkflowIndex')->name('qcWorkflowIndex');
    Route::get('/get/Qc-Standards/{qc_type_id}/Ajax', 'QcModule\QualityControlController@getQcStandardsAjax')->name('getQcStandardsAjax');
    Route::get('/get/Qc-Analysis-Types/{sample_type_id}/Ajax', 'QcModule\QualityControlController@getQcAnalysisTypesAjax')->name('getQcAnalysisTypesAjax');

    Route::post('/generateQCReport', 'QcModule\QualityControlController@generateQCReport')->name('generateQCReport');
    Route::get('/get/Qc-Type/Config/{id}/Ajax', 'QcModule\QualityControlController@getQcTypeConfigAjax')->name('getQcTypeConfigAjax');

    Route::post('/add/Qc-Approvvers', 'QcModule\QualityControlController@addQcApprovvers')->name('addQcApprovvers');
    Route::post('/edit/qc-approver', 'QcModule\QualityControlController@editQcApprovers')->name('edit-qc-approver');
    Route::get('/deleteQcApprovvers/{id}', 'QcModule\QualityControlController@deleteQcApprovvers')->name('deleteQcApprovvers');

    Route::get('get/Analysis-Elements/By-Type-Id/{id}', 'QcModule\QualityControlController@getAnalysisElementsByTypeId')->name('getAnalysisElementsByTypeId');
    Route::post('/mark/qc/batch/complete', 'SampleWorkFlowController@markQCBatchComplete')->name('mark-batch-complete')->middleware('can:laboratory.components.qc sample.edit');

    Route::post('/process-qc/results', 'QcModule\QualityControlController@processResults')->name('process-qc-results');
    Route::get('/show-processing/results', 'QcModule\QualityControlController@showUnProcessed')->name('showUnProcessed');

    Route::get('/results-reports', 'QcModule\QualityControlController@showQcReport')->name('qc-reports');
    Route::get('/result-report/show/{result_id}', 'QcModule\QualityControlController@showQcReportGraph')->name('qc-result-show');
});
//############################################QC Module###########################################

//###############################################Polucon#########################################
Route::get('/assign-Lab/Section-To-Analysis-Element/{id}', 'SampleWorkFlowController@assignLabSectionToAnalysisElement')->name('assignLabSectionToAnalysisElement')->middleware('can:laboratory.components.inter-lab-logs.edit');

Route::get('/generate/Customer-Focus/Index/{batch_id}', 'SampleWorkFlowController@generateCustomerFocusIndex')->name('generateCustomerFocusIndex')->middleware('can:laboratory.components.customer-focus.view');
Route::post('/send/Batch-Schedule/Analysis', 'SampleWorkFlowController@sendBatchScheduleAnalysis')->name('sendBatchScheduleAnalysis')->middleware('can:laboratory.components.customer-focus.edit');
Route::post('/send/Batch-Payment/Reminder', 'SampleWorkFlowController@sendBatchPaymentReminder')->name('sendBatchPaymentReminder')->middleware('can:laboratory.components.customer-focus.edit');
Route::post('/send/batches-SOA', 'SampleWorkFlowController@sendBatchesScheduleAnalysis')->name('send-batches-soa')->middleware('can:laboratory.components.customer-focus.edit');
//###################Inter Lab Log ####################################
Route::get('/getSampleCurrentLabSection/{id}', 'SampleWorkFlowController@getSampleCurrentLabSection')->name('getSampleCurrentLabSection')->middleware('can:laboratory.components.inter-lab-logs.view');
Route::post('/create-sample-inter-lab-log', 'SampleWorkFlowController@create_sample_inter_lab_log')->name('create_sample_inter_lab_log')->middleware('can:laboratory.components.inter-lab-logs.add');
Route::post('/change/Inter-Lab-Log/Status', 'SampleWorkFlowController@changeInterLabLogStatus')->name('changeInterLabLogStatus')->middleware('can:laboratory.components.inter-lab-logs.edit');

Route::get('/inter-Lab/Transfer-Index/{is_archived?}', 'SampleWorkFlowController@interLabTransferIndex')->name('interLabTransferIndex')->middleware('can:laboratory.components.inter-lab-logs.view');
Route::post('/delete/Inter-Lab-Transfer/Logs', 'SampleWorkFlowController@deleteInterLabTransferLogs')->name('deleteInterLabTransferLogs')->middleware('can:laboratory.components.inter-lab-logs.delete');
Route::get('/get/Lab-Sections/By-Lab/{id}', 'SampleWorkFlowController@getLabSectionsByLab')->name('getLabSectionsByLab')->middleware('can:laboratory.components.inter-lab-logs.view');
Route::post('/moveToLab', 'SampleWorkFlowController@moveToLab')->name('moveToLab')->middleware('can:laboratory.components.inter-lab-logs.edit');
Route::get('/generate-test-request-report', 'SampleWorkFlowController@generateTestRequestReport')->name('generateTestRequestReport')->middleware('can:laboratory.components.lab-reports.view');
Route::post('/process-test-request-report', 'SampleWorkFlowController@processTestRequestReport')->name('processTestRequestReport')->middleware('can:laboratory.components.lab-reports.view');
Route::post('/deliver-test-request-report', 'SampleWorkFlowController@deliverTestRequestReport')->name('deliverTestRequestReport')->middleware('can:laboratory.components.lab-reports.view');
Route::get('/generate-shelf-life-study-report', 'SampleWorkFlowController@generateShelfLifeStudyReport')->name('generateShelfLifeStudyReport')->middleware('can:laboratory.components.lab-reports.view');
Route::post('/process-shelf-life-study-report', 'SampleWorkFlowController@processShelfLifeStudyReport')->name('processShelfLifeStudyReport')->middleware('can:laboratory.components.lab-reports.view');

Route::post('/add-Section/Approval', 'SampleAnalysisStageController@addSectionApproval')->name('addSectionApproval')->middleware('can:laboratory.components.sample-tracking-stages.edit');
Route::post('/delete-Section/Approval', 'SampleAnalysisStageController@deleteSectionApproval')->name('deleteSectionApproval')->middleware('can:laboratory.components.sample-tracking-stages.edit');

Route::post('move/To-Verification/Approval-Level', 'SampleWorkFlowController@moveToVerificationApprovalLevel')->name('moveToVerificationApprovalLevel')->middleware('can:laboratory.components.verification-approvals.edit');
Route::post('edit/Verification/Approver-Config', 'SampleWorkFlowController@editVerificationApproverConfig')->name('editVerificationApproverConfig')->middleware('can:laboratory.components.verification-approvals.edit');
Route::post('delete/Verification-Approver/Config', 'SampleWorkFlowController@deleteVerificationApproverConfig')->name('deleteVerificationApproverConfig')->middleware('can:laboratory.components.verification-approvals.delete');
Route::post('change/Batch-Approval/Status', 'SampleWorkFlowController@changeBatchApprovalStatus')->name('changeBatchApprovalStatus')->middleware('can:laboratory.components.verification-approvals.edit');
Route::post('send-back-to-lab-for-amendment', 'SampleWorkFlowController@sendBackToLabForAmendment')->name('sendBackToLabForAmendment')->middleware('can:laboratory.components.verification-approvals.edit');
Route::post('resubmit-amendment-for-verification', 'SampleWorkFlowController@resubmitAmendmentForVerification')->name('resubmitAmendmentForVerification')->middleware('can:laboratory.components.verification-approvals.edit');

Route::get('/sample-condition-index', 'SampleConditionController@index')->name('sample_condition_index')->middleware('can:laboratory.components.sample-types.view');
Route::get('/sample-products/index', 'CRM\CompanyProductController@index')->name('sample-product-index')->middleware('can:crm.products.view');

Route::get('/sample-type-category/index', 'SampleTypeCategoryController@index')->name('sample-type-category-index')->middleware('can:laboratory.components.sample-types.view');
Route::post('/sample-type-category/add', 'SampleTypeCategoryController@addCategory')->name('sample-type-category-add')->middleware('can:laboratory.components.sample-types.add');
Route::get('/get/Client-Details/Ajax/{id}', 'SampleWorkFlowController@getClientDetailsAjax')->name('getClientDetailsAjax')->middleware('can:laboratory.components.all samples.view');
Route::get('/ajax/clients', 'SampleWorkFlowController@searchClients')->name('sample-workflow.clients')->middleware('can:laboratory.components.all samples.view');

Route::get('generate/Tablet/Customer-Focus/Index', 'SampleWorkFlowController@generateTabletCustomerFocusIndex')->name('generateTabletCustomerFocusIndex')->middleware('can:laboratory.components.customer-focus.view');
Route::post('get/Table/Customer-Focus/Signing', 'SampleWorkFlowController@getTableCustomerFocusSigning')->name('getTableCustomerFocusSigning')->middleware('can:laboratory.components.customer-focus.edit');

Route::get('getSampleCodeToResultsAndCr', 'SampleWorkFlowController@getSampleCodeToResultsAndCr')->name('getSampleCodeToResultsAndCr')->middleware('can:laboratory.components.all samples.view');

Route::get('get/Analysis-Type/By/SampleTypeIDAjax/{sample_type_id}', 'SampleWorkFlowController@getAnalysisTypeBySampleTypeIDAjax')->name('getAnalysisTypeBySampleTypeIDAjax')->middleware('can:laboratory.components.all samples.view');
Route::get('get/Sample-Conditions/Ajax', 'SampleWorkFlowController@getSampleConditionsAjax')->name('getSampleConditionsAjax')->middleware('can:laboratory.components.all samples.view');
Route::get('get/Sample-Products/Ajax', 'SampleWorkFlowController@getSampleProductsAjax')->name('getSampleProductsAjax')->middleware('can:laboratory.components.all samples.view');
Route::get('get/Sample-Standards/Ajax', 'SampleWorkFlowController@getSampleStandardsAjax')->name('getSampleStandardsAjax')->middleware('can:laboratory.components.all samples.view');
Route::get('get/Crm-Customer-SamplePoint/{crm_id}/Ajax/{name}', 'SampleWorkFlowController@getCrmCustomerSamplePointAjax')->name('getCrmCustomerSamplePointAjax')->middleware('can:laboratory.components.all samples.view');
Route::get('get/Sample-Parameter/Data/Ajax/{sample_id}', 'SampleWorkFlowController@getShowSampleParameterDataAjax')->name('getShowSampleParameterDataAjax')->middleware('can:laboratory.components.all samples.view');

Route::post('/clone/Batch-Information', 'SampleWorkFlowController@cloneBatchInformation')->name('cloneBatchInformation')->middleware('can:laboratory.components.all samples.edit');
Route::get('get/Standard-Values/Data/Ajax', 'SampleWorkFlowController@getStandardValuesDataAjax')->name('getStandardValuesDataAjax')->middleware('can:laboratory.components.standards.view');
Route::post('update/Standard-Analyte/Limit', 'SampleWorkFlowController@updateStandardAnalyteLimit')->name('updateStandardAnalyteLimit')->middleware('can:laboratory.components.standards.edit');

Route::post('/analyte-type-elements-import', 'AnalysisElementsController@import')->name('analyte-type-elements-import')->middleware('can:laboratory.components.analysis types.add');
Route::post('/analysis-type-clone/{id}', 'AnalysisTypeController@clone')->name('analysis-type-clone')->middleware('can:laboratory.components.analysis types.add');
Route::post('/sample-type-clone/{id}', 'SampleTypeController@clone')->name('sample-type-clone')->middleware('can:laboratory.components.sample-types.add');
Route::get('/testSmsAlert', 'SampleWorkFlowController@testSmsAlert')->name('testSmsAlert')->middleware('can:laboratory.components.all samples.view');
Route::post('/save-Sample/AnalysisDate', 'SampleWorkFlowController@saveSampleAnalysisDate')->name('saveSampleAnalysisDate')->middleware('can:laboratory.components.all samples.edit');

Route::get('/get/Sample-IntelabLogs-Approval/Status', 'SampleWorkFlowController@getSampleIntelabLogsApprovalStatus')->name('getSampleIntelabLogsApprovalStatus')->middleware('can:laboratory.components.inter-lab-logs.view');
Route::get('/getSampleResultCapturedNot', 'SampleWorkFlowController@getSampleResultCapturedNot')->name('getSampleResultCapturedNot')->middleware('can:laboratory.components.all samples.view');

Route::post('/mark/finished-sample', 'SampleWorkFlowController@markBatchesFinished')->name('mark-finished')->middleware('can:laboratory.components.finished sample.edit');
Route::post('/return/finished-sample', 'SampleWorkFlowController@returnFromFinished')->name('return-finished')->middleware('can:laboratory.components.finished sample.edit');

//###############################################Polucon#########################################

//##############################################EMAILAPPROVALS#######################################
Route::get('/email-approval/{link_key}/{type}/{userid}', 'ExternalApprovalController@approve')->name('email-approval');
Route::get('/email-rejection/{link_key}/{type}/{userid}', 'ExternalApprovalController@reject')->name('email-rejection');
Route::post('/email-rejection/{link_key}/{type}/{userid}', 'ExternalApprovalController@reject')->name('send-email-rejection');
Route::get('/email-recheck/{link_key}/{type}/{userid}', 'ExternalApprovalController@recheck')->name('email-recheck');
Route::post('/email-recheck/{link_key}/{type}/{userid}', 'ExternalApprovalController@recheck')->name('send-email-recheck');
//##############################################EMAILAPPROVALS#######################################

###############################################NOTIFICATIONS#######################################
Route::get('/send-restock-notifications', 'InventoryItemController@sendReorderNotifications')->name('send-restock-notifications')->middleware('can:inventory.components.inventory-movement.edit');
###############################################NOTIFICATIONS#######################################

###############################################ZOHO INTEGRATION#######################################
Route::get('/zoho-auth-redirect', 'ZohoController@redirect')->name('zoho-auth-redirect');
Route::get('/zoho-purchase-orders', 'ZohoController@getPurchaseOrders')->name('zoho-purchase-orders');
Route::get('/zoho-get-things', 'ZohoController@sync_zoho_things')->name('zoho-sync-things');
Route::get('/zoho-sync-coa', 'ChartOfAccountController@synchronize')->name('zoho-sync-coa');
Route::get('/zoho-sync-all/{type}', 'ZohoController@sync_all')->name('zoho-sync-all');
Route::get('/zoho-authenticate', 'ZohoController@authenticate')->name('zoho-authenticate');
Route::get('/recreate-purchase-order/{id}', 'RequisitionController@resend_to_zoho')->name('recreate-purchase-order')->middleware('can:inventory.components.purchase orders.edit');
Route::get('/getItemsTest', 'ZohoController@getItemsTest')->name('getItemsTest');
Route::get('/changeSalesOrderStatus', 'ZohoController@changeSalesOrderStatus')->name('changeSalesOrderStatus');

Route::get('/matchCrmCurrency', 'SampleWorkFlowController@matchCrmCurrency')->name('matchCrmCurrency')->middleware('can:laboratory.components.proforma invoices.view');
// Route::get('/zoho-purchase-orders','ZohoController@getPurchaseOrders')->name('zoho-purchase-orders');
Route::get('/sync-all-suppliers-to-items', 'ZohoController@supplier_to_item_sync')->name('sync-all-suppliers-to-items');
###############################################ZOHO INTEGRATION#######################################

#################################### Matrix CONFIGURATIONS#######################################

/* MODULE PRECONFIG */
Route::get('/module-skills-pre-configs/{config}/{module}', 'SkillsMatrix\ModuleSkillsPreConfigsController@index')->name('module-skills-pre-configs')->middleware('auth');
Route::post('/add-module-skills-pre-configs/{id}/{config}/{module}', 'ModulePreConfigsController@update')->name('add-module-skills-pre-configs')->middleware('auth');
Route::post('/update-module-skills-pre-configs/{id}/{config}/{module}', 'SkillsMatrix\ModuleSkillsPreConfigsController@update')->name('update-module-skills-pre-configs')->middleware('auth');
Route::get('/move-skills-type/{direction}/{module}/{element}', 'SkillsMatrix\ModuleSkillsPreConfigsController@move_skills_types')->name('move-skills-type')->middleware('auth');
/* MODULE SKILLS MATRIX — Livewire UI */
Route::get('/matrix/dashboard', 'SkillsMatrix\SkillsMatrixAppController@dashboard')->name('matrix.dashboard')->middleware('can:matrix.module.access');
Route::get('/matrix', 'SkillsMatrix\SkillsMatrixAppController@skillsMatrixIndex')->name('matrix')->middleware('can:matrix.module.access');
Route::get('/matrix/show/{id}', 'SkillsMatrix\SkillsMatrixAppController@skillsMatrixShow')->name('show-matrix')->middleware('can:skills-matrix.components.skills-matrix.view');
Route::get('/matrix/staff', 'SkillsMatrix\SkillsMatrixAppController@staffProfiles')->name('matrix.staff')->middleware('can:skills-matrix.components.capability.view');
Route::get('/matrix/staff/{userId}', 'SkillsMatrix\SkillsMatrixAppController@staffProfileShow')->name('matrix.staff.show')->middleware('can:skills-matrix.components.capability.view');
Route::get('/matrix/education', 'SkillsMatrix\SkillsMatrixAppController@educationRequirements')->name('matrix.education')->middleware('can:skills-matrix.components.skills-matrix.view');
Route::get('/matrix/reports', 'SkillsMatrix\SkillsMatrixAppController@reports')->name('matrix.reports')->middleware('can:skills-matrix.components.skills-matrix.view');
Route::get('/matrix/evaluations', 'SkillsMatrix\SkillsMatrixAppController@evaluationApprovals')->name('matrix.evaluations')->middleware('can:skills-matrix.components.training-plan.evaluation.approve');
Route::get('/matrix/training-material/{materialId}', 'SkillsMatrix\SkillsMatrixAppController@downloadTrainingMaterial')->name('matrix.training.material.download')->middleware('can:skills-matrix.components.training-plan.view');

Route::post('/matrix', 'SkillsMatrix\SkillsMatrixController@add')->name('assign-matrix')->middleware('can:skills-matrix.components.skills-matrix.add');
Route::post('/matrix/edit', 'SkillsMatrix\SkillsMatrixController@edit')->name('edit-matrix')->middleware('can:skills-matrix.components.skills-matrix.edit');
Route::post('/matrix/create', 'SkillsMatrix\SkillsMatrixController@createSkillsMatrix')->name('create-matrix')->middleware('can:skills-matrix.components.skills-matrix.add');
Route::post('/matrix/detail/delete', 'SkillsMatrix\SkillsMatrixController@deleteMatrixDetail')->name('delete-matrix-detail')->middleware('can:skills-matrix.components.skills-matrix.delete');
Route::post('/matrix/detail/role/edit', 'SkillsMatrix\SkillsMatrixController@editMatrixdetailRole')->name('edit-matrix-detail-role')->middleware('can:skills-matrix.components.skills-matrix.edit');

Route::get('/matrix/capability/index', 'SkillsMatrix\SkillsMatrixAppController@capabilityIndex')->name('capability-index')->middleware('can:skills-matrix.components.capability.view');
Route::post('/matrix/capability/add', 'SkillsMatrix\CapabilityController@store')->name('capability.add')->middleware('can:skills-matrix.components.capability.add');
Route::post('/matrix/capability/edit', 'SkillsMatrix\CapabilityController@editCapabaility')->name('capability.edit')->middleware('can:skills-matrix.components.capability.edit');
Route::post('/matrix/capability/delete', 'SkillsMatrix\CapabilityController@deleteCapabaility')->name('capability.delete')->middleware('can:skills-matrix.components.capability.delete');

Route::get('/matrix/get/role/{matrix_id}/ajax', 'SkillsMatrix\CapabilityController@getSkillMatrixRolesAjax')->name('capability.get.role')->middleware('can:skills-matrix.components.capability.view');
Route::post('/matrix/get/user/position/ajax', 'SkillsMatrix\CapabilityController@getMatrixUsersByPositionAjax')->name('capability.get.userby.position')->middleware('can:skills-matrix.components.capability.view');
Route::get('/matrix/capability/show/{id}', 'SkillsMatrix\SkillsMatrixAppController@capabilityShow')->name('capability.show')->middleware('can:skills-matrix.components.capability.view');
Route::post('/matrix/capability/show/{id}', 'SkillsMatrix\CapabilityController@show')->name('capability.show-post')->middleware('can:skills-matrix.components.capability.view');
Route::post('/matrix/capability/details/store', 'SkillsMatrix\CapabilityController@storeDetails')->name('capability.detail.store')->middleware('can:skills-matrix.components.capability.edit');

Route::get('/matrix/training-needs', 'SkillsMatrix\SkillsMatrixAppController@trainingNeedsIndex')->name('train.needs.index')->middleware('can:skills-matrix.components.training-needs.view');
Route::post('/matrix/train-needs/store', 'SkillsMatrix\TrainingNeedsController@store')->name('train.needs.store')->middleware('can:skills-matrix.components.training-needs.add');
Route::get('/matrix/get-capability-users/{id}', 'SkillsMatrix\TrainingNeedsController@getCapabilityUsers')->name('train.needs.get.cabailityusers')->middleware('can:skills-matrix.components.training-needs.view');
Route::get('/matrix/train-needs/{id}', 'SkillsMatrix\SkillsMatrixAppController@trainingNeedsShow')->name('train.needs.show')->middleware('can:skills-matrix.components.training-needs.view');
Route::post('/matrix/train-need/edit', 'SkillsMatrix\TrainingNeedsController@editTrainNeed')->name('train.needs.edit')->middleware('can:skills-matrix.components.training-needs.edit');
Route::post('/matrix/train-need/delete', 'SkillsMatrix\TrainingNeedsController@deleteTrainNeed')->name('train.needs.delete')->middleware('can:skills-matrix.components.training-needs.delete');


Route::get('/matrix/train-plan/index', 'SkillsMatrix\SkillsMatrixAppController@trainingPlanIndex')->name('train.plan.index')->middleware('can:skills-matrix.components.training-plan.view');
Route::post('/matrix/train-plan/store', 'SkillsMatrix\TrainingPlanController@store')->name('train.plan.store')->middleware('can:skills-matrix.components.training-plan.add');
Route::post('/matrix/train/plan/edit', 'SkillsMatrix\TrainingPlanController@editPlan')->name('train.plan.edit')->middleware('can:skills-matrix.components.training-plan.edit');
Route::post('/matrix/train/plan/delete', 'SkillsMatrix\TrainingPlanController@deletePlan')->name('train.plan.delete')->middleware('can:skills-matrix.components.training-plan.delete');
Route::get('/matrix/train-plan/show/{id}', 'SkillsMatrix\SkillsMatrixAppController@trainingPlanShow')->name('train.plan.show')->middleware('can:skills-matrix.components.training-plan.view');
Route::post('/matrix/train-plan/show/{id}', 'SkillsMatrix\TrainingPlanController@show')->name('train.plan.show-post')->middleware('can:skills-matrix.components.training-plan.view');

Route::post('/matrix/train/plan/other/store', 'SkillsMatrix\TrainingPlanController@storeOther')->name('train.plan.store.other')->middleware('can:skills-matrix.components.training-plan.add');
Route::post('/matrix/train/planner/detail/store', 'SkillsMatrix\TrainingPlanController@storeDetail')->name('train.plan.detail.store')->middleware('can:skills-matrix.components.training-plan.edit');
Route::post('/matrix/train/plan/others/delete', 'SkillsMatrix\TrainingPlanController@deleteOtherDetail')->name('train.plan.others.delete')->middleware('can:skills-matrix.components.training-plan.delete');

Route::get('/matrix-config/{module?}', 'SkillsMatrix\SkillsMatrixAppController@legacyConfigRedirect')->name('matrix-config')->middleware('can:matrix.module.access');
Route::get('/matrix-config-topology/{any?}', 'SkillsMatrix\SkillsMatrixAppController@legacyConfigRedirect')->where('any', '.*')->middleware('can:matrix.module.access');
Route::get('/matrix-competence', 'SkillsMatrix\SkillsMatrixAppController@legacyConfigRedirect')->name('matrix-competence')->middleware('can:matrix.module.access');
Route::redirect('/topology-module', '/matrix/dashboard');
Route::redirect('/topology-parent', '/matrix/dashboard');
/* MODULE OTHER TRAINING */

Route::get('/other-training', 'Training\SkillsOtherTrainingController@index')->name('other-training')->middleware('can:skills-matrix.components.other-training.view');
Route::post('/other-training', 'Training\SkillsOtherTrainingController@add')->name('assign-other-training')->middleware('can:skills-matrix.components.other-training.add');
Route::post('/update-other-trainner/{condition}', 'Training\SkillsOtherTrainingController@edit')->name('update-other-training')->middleware('can:skills-matrix.components.other-training.edit');
Route::get('/training-acceptance/{training_id}/{dept_number}/{user_id}/{acceptance?}', 'Training\SkillsOtherTrainingController@training_acceptance')->name('training-acceptance')->middleware('can:skills-matrix.components.other-training.edit');

#################################### Matrix CONFIGURATIONS#######################################
###############################VGM MODULE###############################
Route::get('/vgm/index', 'Inspection\InspectionController@index')->name('vgm.index');
Route::get('/vgm/show/{id}', 'Inspection\InspectionController@show')->name('vgm.show');
Route::post('/vgm/store', 'Inspection\InspectionController@store')->name('vgm.store');
Route::post('/vgm/delete', 'Inspection\InspectionController@delete')->name('vgm.delete');

#################################SAMPLE WORKFLOW SEND SALES ORDER#######################
Route::post('/validate/client-batches', 'SampleWorkFlowController@validateClientBatches')->name('validate-clients')->middleware('can:laboratory.components.draft-invoices.add');
Route::post('/ajax/send-schedule', 'SampleWorkFlowController@sendScheduleAjax')->name('ajax-send-schedule')->middleware('can:laboratory.components.draft-invoices.add');
#######################################################################################

##################################### IMARACHAT AI #######################
Route::get('/imara-ai','HomeController@aiIndex')->middleware(['auth', 'twofactor', 'can:ai.module.access'])->name('imara-ai');
/* Settings and Knowledge Manager routes removed: functionality merged into DMS Active Documents */

Route::post('/imara-ai/search', 'AI\KnowledgeAssistantController@search')
  ->middleware(['auth', 'twofactor', 'throttle:20,1'])
  ->name('ai.knowledge.search');
Route::post('/imara-ai/ask', 'AI\KnowledgeAssistantController@ask')
  ->middleware(['auth', 'twofactor', 'throttle:20,1'])
  ->name('ai.knowledge.ask');
Route::post('/imara-ai/ask-stream', 'AI\KnowledgeAssistantController@askStream')
  ->middleware(['auth', 'twofactor', 'throttle:20,1'])
  ->name('ai.knowledge.ask-stream');
Route::post('/imara-ai/cancel', 'AI\KnowledgeAssistantController@cancel')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.cancel');

Route::get('/imara-ai/lookup-documents', 'AI\KnowledgeAssistantController@lookupDocuments')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.lookup-documents');

Route::post('/imara-ai/action/confirm', 'AI\KnowledgeAssistantController@confirmAction')
  ->middleware(['auth', 'twofactor', 'throttle:20,1'])
  ->name('ai.knowledge.action.confirm');

// LiveData / Operational Assistant Routes


Route::get('/imara-ai/knowledge', 'AI\AiKnowledgeBaseController@index')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.dashboard');

Route::get('/imara-ai/knowledge/{id}', 'AI\AiKnowledgeBaseController@show')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.show');

Route::post('/imara-ai/knowledge', 'AI\AiKnowledgeBaseController@store')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.store');

Route::put('/imara-ai/knowledge/{id}', 'AI\AiKnowledgeBaseController@update')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.update');

Route::delete('/imara-ai/knowledge/{id}', 'AI\AiKnowledgeBaseController@destroy')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.destroy');

Route::post('/imara-ai/knowledge/{id}/reindex', 'AI\AiKnowledgeBaseController@reindex')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.reindex');

Route::post('/imara-ai/knowledge/bulk-delete', 'AI\AiKnowledgeBaseController@batchDestroy')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.bulk-delete');

Route::post('/imara-ai/knowledge/bulk-reindex', 'AI\AiKnowledgeBaseController@batchReindex')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.bulk-reindex');

Route::post('/imara-ai/knowledge/search-preview', 'AI\AiKnowledgeBaseController@search')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.search-preview');

Route::get('/imara-ai/knowledge-editor/{id?}', 'AI\AiKnowledgeBaseController@editor')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.editor');

Route::post('/imara-ai/knowledge/upload-image', 'AI\AiKnowledgeBaseController@uploadImage')
  ->middleware(['auth', 'twofactor'])
  ->name('ai.knowledge.upload-image');

// AI conversation persistence
Route::get('/imara-ai/conversations', 'AI\KnowledgeAssistantController@listConversations')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.list');
Route::post('/imara-ai/conversations', 'AI\KnowledgeAssistantController@createConversation')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.create');
Route::get('/imara-ai/conversations/{id}/messages', 'AI\KnowledgeAssistantController@getMessages')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.messages');
Route::post('/imara-ai/conversations/{id}/messages', 'AI\KnowledgeAssistantController@saveMessage')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.save-message');
Route::delete('/imara-ai/conversations/{id}', 'AI\KnowledgeAssistantController@deleteConversation')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.delete');
Route::delete('/imara-ai/conversations', 'AI\KnowledgeAssistantController@bulkDeleteConversations')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.bulk-delete');
Route::patch('/imara-ai/conversations/{id}', 'AI\KnowledgeAssistantController@renameConversation')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.rename');
Route::patch('/imara-ai/conversations/{id}/toggle-pin', 'AI\KnowledgeAssistantController@togglePin')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.toggle-pin');
Route::post('/imara-ai/conversations/{convoId}/messages/{messageId}/feedback', 'AI\KnowledgeAssistantController@saveFeedback')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.feedback');
Route::post('/imara-ai/conversations/{id}/attachments', 'AI\KnowledgeAssistantController@uploadAttachment')
  ->middleware(['auth', 'twofactor'])->name('ai.conversations.upload-attachment');

Route::get('/imara-ai/{id}', 'HomeController@aiIndex')
  ->middleware(['auth', 'twofactor', 'can:ai.module.access'])
  ->where('id', '[0-9a-fA-F\-]{36}')
  ->name('imara-ai.view');



########################################### AI ANALYTICS #######################################
Route::group(['prefix' => 'mas', 'middleware' => ['web', 'auth', 'can:ai_analytics.module.access']], function() {
    Route::get('/', '\App\Livewire\Mas\Overview')->name('mas.index');
    // Lab Insights (Nested structure for distinct URLs)
    Route::get('/lab', function() { return redirect()->route('mas.lab.tat'); });
    Route::get('/lab/tat', '\App\Livewire\Mas\LabTat')->name('mas.lab.tat');
    Route::get('/lab/general', '\App\Livewire\Mas\LabGeneral')->name('mas.lab.general');
    Route::get('/lab/qc', '\App\Livewire\Mas\LabQc')->name('mas.lab.qc');
    Route::get('/lab/logistics', '\App\Livewire\Mas\LabLogistics')->name('mas.lab.logistics');
    Route::get('/inventory', '\App\Livewire\Mas\Inventory')->name('mas.inventory');
    Route::get('/crm', '\App\Livewire\Mas\Crm')->name('mas.crm');
    Route::get('/risk', '\App\Livewire\Mas\Risk')->name('mas.risk');
    Route::get('/equipment', '\App\Livewire\Mas\Equipment')->name('mas.equipment');
    Route::get('/personnel', '\App\Livewire\Mas\Personnel')->name('mas.personnel');
    Route::get('/qc', '\App\Livewire\Mas\Qc')->name('mas.qc');
    Route::get('/audit', '\App\Livewire\Mas\Audit')->name('mas.audit');
    Route::get('/ai-monitoring', '\App\Livewire\Mas\AiMonitoring')->name('mas.ai-monitoring');
    Route::get('/export/{module}', 'Mas\MasController@export')->name('mas.export');
    Route::match(['get', 'post'], '/export/{module}/visuals', 'Mas\MasController@exportWithVisuals')->name('mas.export.visuals');
});






#STORAGE ROUTES
Route::get('storage/{type}/{filename}', function ($type, $filename) {
    // Add folder path here instead of storing in the database.
    $path = storage_path('app/' . $type . '/' . $filename);
    // return $path;
    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header('Content-Type', $type);

    return $response;
});
Route::get('storage/{type}/{company}/{filename}', function ($type, $company, $filename) {
    // Add folder path here instead of storing in the database.
    $path = storage_path('app/' . $type . '/' . $company . '/' . $filename);
    // return $path;
    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header('Content-Type', $type);

    return $response;
});

Route::get('/set-available-stock', function () {
    $items = \App\InventorySubCategories::all();

    foreach ($items as $item) {
        echo calculateAvailableStock($item->id) . '<br>';
        echo setItemReorderLevel($item->id, $item->reorder_level()) . '<br>';
    }

    return 'OK';
});

Route::get('/pinfo', function () {
    phpinfo();
    return 'OK';
});

// Test routes for enhanced form data loading
Route::middleware(['auth'])->group(function () {
    Route::get('/test-form-data/{instanceId}', 'TestFormDataController@testFormData')->name('test.form-data');
    Route::get('/test-enhanced-form/{instanceId}', 'TestFormDataController@showEnhancedForm')->name('test.enhanced-form');
    Route::get('/debug-enhanced-form/{instanceId}', 'TestFormDataController@debugEnhancedForm')->name('debug.enhanced-form');
    Route::get('/simple-form/{instanceId}', 'TestFormDataController@showSimpleForm')->name('simple.form');
});

// Worksheet Engine Routes
Route::middleware(['auth'])->prefix('formulars')->name('formulars.')->group(function () {
    Route::get('/', 'Formulars\FormulaController@index')->name('index');
    Route::get('/manage', 'Formulars\FormulaController@manage')->name('manage');
    Route::get('/steps/{formulaVersion}', 'Formulars\FormulaController@steps')->name('steps');
    Route::get('/execute/{formulaVersion}', 'Formulars\FormulaController@execute')->name('execute');
    Route::get('/history', 'Formulars\FormulaController@history')->name('history');
    Route::get('/global-variables', 'Formulars\FormulaController@globalVariables')->name('global-variables');
    Route::get('/lookup-tables', 'Formulars\FormulaController@lookupTables')->name('lookup-tables');
    Route::get('/lookup-tables/{lookupTable}/entries', 'Formulars\FormulaController@lookupTableEntries')->name('lookup-table-entries');

    // Procedure Worksheets
    Route::prefix('procedures')->name('procedures.')->group(function () {
        Route::get('/manage', 'Procedures\ProcedureWorksheetController@manage')->name('manage');
        Route::get('/{procedureWorksheet}/edit', 'Procedures\ProcedureWorksheetController@edit')->name('edit');
    });

    // Grouped worksheet pipelines
    Route::prefix('grouped-worksheets')->name('grouped-worksheets.')->group(function () {
        Route::get('/manage', 'GroupedWorksheets\GroupedWorksheetHolderController@manage')->name('manage');
        Route::get('/{groupedWorksheetHolder}/edit', 'GroupedWorksheets\GroupedWorksheetHolderController@edit')->name('edit');
    });

    // Hybrid worksheets
    Route::prefix('hybrid-worksheets')->name('hybrid-worksheets.')->group(function () {
        Route::get('/manage', 'HybridWorksheets\HybridWorksheetController@manage')->name('manage');
        Route::get('/{hybridWorksheet}/versions/{hybridWorksheetVersion}/edit', 'HybridWorksheets\HybridWorksheetController@edit')->name('edit');
    });

    // Log entry worksheets
    Route::prefix('log-entry-worksheets')->name('log-entry-worksheets.')->group(function () {
        Route::get('/manage', 'LogEntryWorksheets\LogEntryWorksheetController@manage')->name('manage');
        Route::get('/{logEntryWorksheet}/preview', 'LogEntryWorksheets\LogEntryWorksheetController@preview')->name('preview');
        Route::get('/{logEntryWorksheet}/edit', 'LogEntryWorksheets\LogEntryWorksheetController@edit')->name('edit');
    });
});

Route::middleware(['auth'])->prefix('method-sequences')->name('method-sequences.')->group(function () {
    Route::get('/manage', 'MethodSequences\MethodSequenceController@manage')->name('manage');
    Route::get('/stages/{methodSequenceVersion}', 'MethodSequences\MethodSequenceController@stages')->name('stages');
    Route::post('/clone/{methodSequence}', 'MethodSequences\MethodSequenceController@clone')->name('clone');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('stage-headers', \App\Http\Controllers\StageHeaderController::class);
    Route::post('stage-headers/{stageHeader}/add-stage', [\App\Http\Controllers\StageHeaderController::class, 'addStage'])->name('stage-headers.add-stage');
    Route::put('stage-headers/{stageHeader}/stages/{testStage}', [\App\Http\Controllers\StageHeaderController::class, 'updateStage'])->name('stage-headers.update-stage');
    Route::delete('stage-headers/remove-stage/{testStage}', [\App\Http\Controllers\StageHeaderController::class, 'removeStage'])->name('stage-headers.remove-stage');
    Route::post('stage-headers/remove-stage', [\App\Http\Controllers\StageHeaderController::class, 'removeStagePost'])->name('stage-headers.remove-stage-post');
    Route::post('stage-headers/{stageHeader}/reorder-stages', [\App\Http\Controllers\StageHeaderController::class, 'reorderStages'])->name('stage-headers.reorder-stages');
    Route::get('/lab/stage-headers/progress', [\App\Http\Controllers\StageHeaderController::class, 'progress'])->name('stage-headers.progress');
    Route::post('/lab/stage-headers/{stageHeader}/start-test', [\App\Http\Controllers\StageHeaderController::class, 'startTest'])->name('stage-headers.start-test');
    Route::post('/lab/progress/{progress}/start', [\App\Http\Controllers\StageHeaderController::class, 'startProgress'])->name('stage-headers.start-progress');
    Route::post('/lab/progress/{progress}/record-result', [\App\Http\Controllers\StageHeaderController::class, 'recordResult'])->name('stage-headers.record-result');
    Route::post('/lab/progress/{progress}/cancel', [\App\Http\Controllers\StageHeaderController::class, 'cancelTest'])->name('stage-headers.cancel-test');
    Route::get('/lab/progress/{progress}/view', [\App\Http\Controllers\StageHeaderController::class, 'viewProgress'])->name('stage-headers.view-progress');
});

// Document Management System (DMS) Routes
Route::middleware(['auth', 'can:dms.module.access'])->prefix('dms')->name('dms.')->group(function () {
    Route::get('/', 'LivewireControllers\DMSController@dashboard')->name('dashboard')->middleware('can:documents.components.document management.view');
    Route::get('/document-types', 'LivewireControllers\DMSController@documentTypes')->name('types')->middleware('can:documents.components.document types.view');
    Route::get('/active-documents', 'LivewireControllers\DMSController@activeDocuments')->name('active')->middleware('can:documents.components.document management.view');
    Route::get('/archived-documents', 'LivewireControllers\DMSController@archivedDocuments')->name('archived')->middleware('can:documents.components.document management.view');
    Route::get('/amendments', 'LivewireControllers\DMSController@amendments')->name('amendments')->middleware('can:documents.components.document publishing.view');
    Route::get('/reports', 'LivewireControllers\DMSController@reports')->name('reports')->middleware('can:documents.components.reports.view');

    // File operations
    Route::get('/documents/{id}/download', 'DMSController@download')->name('download')->middleware('can:documents.components.document management.view');
    Route::get('/documents/{id}/preview', 'DMSController@preview')->name('preview')->middleware('can:documents.components.document management.view');
    Route::get('/documents/{documentId}/versions/{versionId}/download', 'DMSController@downloadVersion')->name('download-version')->middleware('can:documents.components.document management.view');
});

// Documents Module
Route::prefix('documents')->name('documents.')->middleware('can:documents.permission')->group(function () {
    // Dashboard
    Route::get('/dashboard', 'Documents\DocumentController@dashboard')->name('dashboard')->middleware('can:documents.components.document management.view');

    // Document Types
    Route::prefix('types')->name('types.')->group(function () {
        Route::get('/', 'Documents\DocumentTypeController@index')->name('index')->middleware('can:documents.components.document types.view');
        Route::get('/create', 'Documents\DocumentTypeController@create')->name('create')->middleware('can:documents.components.document types.add');
        Route::post('/', 'Documents\DocumentTypeController@store')->name('store')->middleware('can:documents.components.document types.add');
        Route::get('/{id}', 'Documents\DocumentTypeController@show')->name('show')->middleware('can:documents.components.document types.view');
        Route::get('/{id}/edit', 'Documents\DocumentTypeController@edit')->name('edit')->middleware('can:documents.components.document types.edit');
        Route::put('/{id}', 'Documents\DocumentTypeController@update')->name('update')->middleware('can:documents.components.document types.edit');
        Route::delete('/{id}', 'Documents\DocumentTypeController@destroy')->name('destroy')->middleware('can:documents.components.document types.delete');
    });

    // Notification Frequencies
    Route::prefix('notification-frequencies')->name('notification-frequencies.')->group(function () {
        Route::get('/', 'Documents\NotificationFrequencyController@index')->name('index')->middleware('can:documents.components.notification frequencies.view');
        Route::get('/create', 'Documents\NotificationFrequencyController@create')->name('create')->middleware('can:documents.components.notification frequencies.add');
        Route::post('/', 'Documents\NotificationFrequencyController@store')->name('store')->middleware('can:documents.components.notification frequencies.add');
        Route::get('/{id}', 'Documents\NotificationFrequencyController@show')->name('show')->middleware('can:documents.components.notification frequencies.view');
        Route::get('/{id}/edit', 'Documents\NotificationFrequencyController@edit')->name('edit')->middleware('can:documents.components.notification frequencies.edit');
        Route::put('/{id}', 'Documents\NotificationFrequencyController@update')->name('update')->middleware('can:documents.components.notification frequencies.edit');
        Route::delete('/{id}', 'Documents\NotificationFrequencyController@destroy')->name('destroy')->middleware('can:documents.components.notification frequencies.delete');
    });

    // Document Management
    Route::get('/', 'Documents\DocumentController@index')->name('index')->middleware('can:documents.components.document management.view');
    Route::get('/unpublished', 'Documents\DocumentController@unpublished')->name('unpublished')->middleware('can:documents.components.document management.view');
    Route::get('/expired', 'Documents\DocumentController@expired')->name('expired')->middleware('can:documents.components.document management.view');
    Route::get('/create', 'Documents\DocumentController@create')->name('create')->middleware('can:documents.components.document management.add');
    Route::post('/', 'Documents\DocumentController@store')->name('store')->middleware('can:documents.components.document management.add');
    Route::post('/bulk-store', 'Documents\DocumentController@bulkStore')->name('bulk-store')->middleware('can:documents.components.document management.add');
    Route::get('/{id}/edit', 'Documents\DocumentController@edit')->name('edit')->middleware('can:documents.components.document management.edit');
    Route::put('/{id}', 'Documents\DocumentController@update')->name('update')->middleware('can:documents.components.document management.edit');
    Route::delete('/{id}', 'Documents\DocumentController@destroy')->name('destroy')->middleware('can:documents.components.document management.delete');

    // Publishing
    Route::get('/{id}/publish', 'Documents\DocumentController@publish')->name('publish')->middleware('can:documents.components.document publishing.add');
    Route::post('/{id}/publish', 'Documents\DocumentController@storePublish')->name('store-publish')->middleware('can:documents.components.document publishing.add');
    Route::post('/{id}/unpublish', 'Documents\DocumentController@unpublish')->name('unpublish')->middleware('can:documents.components.document publishing.edit');

    // Downloads and attachments
    Route::get('/{id}/download', 'Documents\DocumentController@download')->name('download')->middleware('can:documents.components.document management.view');
    Route::get('/attachments/{id}/download', 'Documents\DocumentController@downloadAttachment')->name('attachments.download')->middleware('can:documents.components.document management.view');
    Route::delete('/attachments/{id}', 'Documents\DocumentController@deleteAttachment')->name('attachments.delete')->middleware('can:documents.components.document management.delete');

    // Validation
    Route::post('/check-duplicate', 'Documents\DocumentController@checkDuplicate')->name('check-duplicate')->middleware('can:documents.components.document management.view');

    // Show document (keep last)
    Route::get('/{id}', 'Documents\DocumentController@show')->name('show')->middleware('can:documents.components.document management.view');
});

//###################################REGISTRY / CORPORATE SERVICES#######################################
Route::prefix('registry')->name('registry.')->middleware(['auth', 'can:registry.module.access'])->group(function () {
    Route::get('/', 'Registry\RegistryDashboardController@index')->name('dashboard')->middleware('can:registry.components.dashboard.view');

    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', 'Registry\RegistryRequestController@index')->name('index')->middleware('can:registry.components.requests.view');
        Route::get('/create', 'Registry\RegistryRequestController@create')->name('create')->middleware('can:registry.components.requests.add');
        Route::post('/', 'Registry\RegistryRequestController@store')->name('store')->middleware('can:registry.components.requests.add');
        Route::get('/{id}', 'Registry\RegistryRequestController@show')->name('show')->middleware('can:registry.components.requests.view');
        Route::post('/{id}/approve', 'Registry\RegistryRequestController@approve')->name('approve')->middleware('can:registry.components.approval queue.edit');
        Route::post('/{id}/reject', 'Registry\RegistryRequestController@reject')->name('reject')->middleware('can:registry.components.approval queue.edit');
        Route::post('/{id}/assign', 'Registry\RegistryRequestController@assign')->name('assign')->middleware('can:registry.components.assignments.edit');
        Route::post('/{id}/close', 'Registry\RegistryRequestController@close')->name('close')->middleware('can:registry.components.requests.edit');
        Route::post('/{id}/escalate', 'Registry\RegistryRequestController@escalate')->name('escalate')->middleware('can:registry.components.requests.edit');
        Route::post('/{id}/documents', 'Registry\RegistryDocumentController@store')->name('documents.store')->middleware('can:registry.components.documents.add');
    });

    Route::get('/documents/{documentId}/download', 'Registry\RegistryDocumentController@download')->name('documents.download')->middleware('can:registry.components.documents.view');

    Route::get('/approved', 'Registry\RegistryApprovedRequestController@index')->name('approved.index')->middleware('can:registry.components.requests.view');
    Route::get('/approvals', 'Registry\RegistryApprovalController@index')->name('approvals.index')->middleware('can:registry.components.approval queue.view');
    Route::get('/correspondence', 'Registry\RegistryCorrespondenceController@index')->name('correspondence.index')->middleware('can:registry.components.correspondence register.view');
    Route::get('/workflows', 'Registry\RegistryWorkflowController@index')->name('workflows.index')->middleware('can:registry.components.workflow configuration.view');
    Route::get('/reports/export/{format}', 'Registry\RegistryReportController@export')->name('reports.export')->middleware('can:registry.components.reports.view');
});
//###################################REGISTRY / CORPORATE SERVICES#######################################

