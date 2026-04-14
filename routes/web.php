<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

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

Route::get('/mark-Accreditted-Samples', 'SampleWorkFlowController@markAccredittedSamples')->name('markAccredittedSamples')->middleware('haspermission:Laboratory.components.All Samples.Edit');

Route::get('/resolveTest', 'SampleWorkFlowController@resolveTest')->name('resolveTest')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/fillCapturedresultOperator', 'SampleWorkFlowController@fillCapturedresultOperator')->name('fillCapturedresultOperator')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('add/suppliers-user', 'SupplierController@make_suppliers_users')->name('add-crm-to-users')->middleware('haspermission:Inventory.components.Suppliers.Edit');

// Route::get('/send/event-notifications-cron', 'API\APIController@send_event_notifications')->name('send_event_notifications');

Route::post('/logout/app/', 'Auth\TwoFactor@mylogout')->name('mylogout');
Route::get('/verify/user', 'Auth\TwoFactor@index')->name('verify-user');
Route::post('/verify-code/store', 'Auth\TwoFactor@storeVerifyCode')->name('verify-store');
Route::post('/verify-code/store/ext', 'Auth\TwoFactor@storeVerifyCodeExt')->name('verify-store-ext');
Route::get('/verify-code/resend', 'Auth\TwoFactor@resendVerifyCode')->name('verify-resend');

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/search-sample-code', 'HomeController@searchsample')->name('search-sample-code');
Route::post('/set-default-company', 'HomeController@default_company')->name('set-default-company');

//#############CONFIGURATIONS###############################################################################
Route::get('/system-settings', 'ConfigurationController@index')->name('system-settings')->middleware('haspermission:System.components.System Settings.View');
Route::get('/system-settings/module-visibility', 'ConfigurationController@moduleVisibility')->name('system-settings.module-visibility')->middleware('haspermission:System.components.System Settings.View');
Route::post('/import-my-users', 'PersonnelController@importUser')->name('importUser');
/* COMPANIES */
Route::get('/companies', 'CompanyController@index')->name('companies');
Route::post('/companies', 'CompanyController@add')->name('add-companies');
Route::post('/company/{id}', 'CompanyController@edit')->name('edit-company');

Route::post('/company-activate', 'CompanyController@activate_company')->name('activate-company');

//######################################SYSTEM###########################################
Route::get('/full-calendar/view/{date?}', 'Event\EventController@index')->name('full-calendar')->middleware('haspermission:Sampling-Planner.components.All Events.View');
Route::post('/full-calendar/add', 'Event\EventController@created')->name('full-calendar-create')->middleware('haspermission:Sampling-Planner.components.All Events.Add');

Route::post('/fullcalendareventmaster/create', 'Event\EventController@create')->middleware('haspermission:Sampling-Planner.components.All Events.Add');
Route::post('/full-calendar/update', 'Event\EventController@update')->name('editRoutineEvent')->middleware('haspermission:Sampling-Planner.components.All Events.Edit');
Route::post('/fullcalendareventmaster/delete', 'Event\EventController@destroy')->middleware('haspermission:Sampling-Planner.components.All Events.Delete');
Route::post('/fullcalendar/user-task', 'Event\EventController@getEventByUser')->middleware('haspermission:Sampling-Planner.components.All Events.View');
Route::get('/fullcalendar/print-user-task', 'Event\EventController@printUserEvents')->name('printUserEvents')->middleware('haspermission:Sampling-Planner.components.All Events.View');
Route::post('/full-callendar/edit', 'Event\EventController@editEvent')->name('editEvent')->middleware('haspermission:Sampling-Planner.components.All Events.Edit');
Route::post('/delete-events', 'Event\EventController@delete_event')->name('delete-events')->middleware('haspermission:Sampling-Planner.components.All Events.Delete');
Route::get('/get/event/id/{id}', 'Event\EventController@getEvent')->name('getEventByID')->middleware('haspermission:Sampling-Planner.components.All Events.View');

//#############CONFIGURATIONS END###############################################################################
//######################################dashboard Ajax###############################################
Route::get('/getSamplesByCustomer/{year?}', 'Lab\LabDashboardController@getSamplesByCustomer')->name('getSamplesByCustomer')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getSamplesByGps/{year?}', 'Lab\LabDashboardController@getSamplesByGps')->name('getSamplesByGps')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getSamplesByMonth/{year?}', 'Lab\LabDashboardController@getSamplesByMonth')->name('getSamplesByMonth')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getsamplesBySampletype/{year?}', 'Lab\LabDashboardController@getsamplesBySampletype')->name('getsamplesBySampletype')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getSamplesByLabSection', 'Lab\LabDashboardController@getSamplesByLabSection')->name('getSamplesByLabSection')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getSamplesByStatus', 'Lab\LabDashboardController@getSamplesByStatus')->name('getSamplesByStatus')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getTestingMatrix', 'Lab\LabDashboardController@getTestingMatrix')->name('getTestingMatrix')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getActiveMethods', 'Lab\LabDashboardController@getActiveMethods')->name('getActiveMethods')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getSmartGridTasks', 'Lab\LabDashboardController@getSmartGridTasks')->name('getSmartGridTasks')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/getCustomerSampleTypes', 'Lab\LabDashboardController@getCustomerSampleTypes')->name('getCustomerSampleTypes')->middleware('haspermission:Laboratory.components.Dashboard.View');

//######################################dashboard Ajax###############################################

//#####################LABS######################################################################################
Route::get('/lab-home', 'LabController@index')->name('lab-home')->middleware('haspermission:Laboratory.components.Dashboard.View');
Route::get('/lab-dashboard', 'Lab\LabDashboardController@index')->name('dashboard-lab')->middleware('haspermission:Laboratory.components.Dashboard.View');

Route::get('/labs', 'LabController@index')->name('labs')->middleware('haspermission:Laboratory.components.Labs.View');
Route::get('/lab/{labid?}/analysis-types', 'AnalysisTypeController@index')->name('show-lab-analysis-types')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::post('/labs', 'LabController@add')->name('add-labs')->middleware('haspermission:Laboratory.components.Labs.Add');
Route::post('/lab/{id}', 'LabController@edit')->name('edit-lab')->middleware('haspermission:Laboratory.components.Labs.Edit');

Route::get('/analytes', [\App\Http\Controllers\LivewireControllers\LabAppController::class, 'analytes'])->name('analytes')->middleware('haspermission:Laboratory.components.Analytes.View');

Route::get('/sample-types', 'SampleTypeController@index')->name('sample-types')->middleware('haspermission:Laboratory.components.Sample-Types.View');
Route::get('/sample-type/{id}', 'SampleTypeController@show')->name('sample-type')->middleware('haspermission:Laboratory.components.Sample-Types.View');
Route::post('/sample-type-delete', 'SampleTypeController@delete_sample_type')->name('delete-sample-type')->middleware('haspermission:Laboratory.components.Sample-Types.Delete');
Route::post('/sample-types', 'SampleTypeController@add')->name('add-sample-types')->middleware('haspermission:Laboratory.components.Sample-Types.Add');
Route::post('/sample-conditions', 'SampleConditionController@add')->name('add-sample-conditions')->middleware('haspermission:Laboratory.components.Sample-Types.Add');
Route::post('/sample-condition/Edit', 'SampleConditionController@edit')->name('edit-sample-condition')->middleware('haspermission:Laboratory.components.Sample-Types.Edit');
Route::post('/sample-type/{id}', 'SampleTypeController@edit')->name('edit-sample-type')->middleware('haspermission:Laboratory.components.Sample-Types.Edit');
Route::post('/add/sample-type-qualification/{id}', 'Lab\Samples\SampleQualificationsController@add')->name('add-sample-type-qualification')->middleware('haspermission:Laboratory.components.Sample-Types.Add');
Route::post('/edit/sample-type-qualification/{id}', 'Lab\Samples\SampleQualificationsController@edit')->name('edit-sample-type-qualification')->middleware('haspermission:Laboratory.components.Sample-Types.Edit');
Route::post('/delete/sample-type-qualification/{id}', 'Lab\Samples\SampleQualificationsController@delete')->name('delete-sample-type-qualification')->middleware('haspermission:Laboratory.components.Sample-Types.Delete');

// Livewire Sample Types Management
Route::get('/livewire/sample-types', [LabAppController::class, 'sampleTypes'])
    ->name('livewire.sample-types')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Remedies Management Routes
Route::get('/remedies', [LabAppController::class, 'remedies'])
    ->name('remedies.index')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

Route::get('/remedies/{remedyHeaderId}/details', [LabAppController::class, 'remedyDetails'])
    ->name('remedies.details')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Rating Hub Management Routes
Route::get('/rating-hub', [LabAppController::class, 'ratingHub'])
    ->name('ratings.index')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

Route::get('/rating-hub/{ratingHeaderId}/details', [LabAppController::class, 'ratingDetails'])
    ->name('ratings.details')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Analysis Types Management
Route::get('/livewire/analysis-types/{sampleTypeId}', [LabAppController::class, 'analysisTypes'])
    ->name('livewire.analysis-types')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Elements Management
Route::get('/livewire/elements/{analysisTypeId}', [LabAppController::class, 'elements'])
    ->name('livewire.elements')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Standards Management
Route::get('/livewire/standards', [StandardsController::class, 'index'])
    ->name('livewire.standards')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Standard Analytes Management
Route::get('/livewire/standard-analytes/{standardId}', [StandardsController::class, 'standardAnalytes'])
    ->name('livewire.standard-analytes')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Report Formats Management
Route::get('/livewire/report-formats', [LabAppController::class, 'reportFormats'])
    ->name('livewire.report-formats')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Dedicated Report Format Builder
Route::get('/livewire/report-formats/builder/{id}', [LabAppController::class, 'reportFormatBuilder'])
    ->name('livewire.report-formats.builder')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Standard Manager
Route::get('/livewire/standard-manager', [LabAppController::class, 'standardManager'])
    ->name('livewire.standard-manager')
    ->middleware('haspermission:Laboratory.components.Sample-Types.View');

// Livewire Test Page
Route::get('/livewire-test', function () {
    return view('livewire-test');
})->name('livewire-test');


// Livewire CRM Management Routes
Route::get('/crm/dashboard', [CRMAppController::class, 'dashboard'])
    ->name('crm.dashboard')
    ->middleware('haspermission:CRM.components.Customer-List.View');

Route::get('/livewire/customers', [CRMAppController::class, 'customers'])
    ->name('livewire.customers')
    ->middleware('haspermission:CRM.components.Customer-List.View');

Route::get('/livewire/customers/{customerId}/profile', [CRMAppController::class, 'customerProfile'])
    ->name('livewire.customer-profile')
    ->middleware('haspermission:CRM.components.Customer-List.View');

Route::get('/crm/sample-points', [CRMAppController::class, 'samplePoints'])
    ->name('crm.sample-points')
    ->middleware('haspermission:CRM.components.Customer-List.View');

Route::get('/crm/areas', [CRMAppController::class, 'areas'])
    ->name('crm.areas')
    ->middleware('haspermission:CRM.components.Customer-List.View');

Route::get('/crm/complaints-manager/{stage?}', [CRMAppController::class, 'complaintsManager'])
    ->name('crm.complaints-manager')
    ->middleware('haspermission:CRM.components.Complaints.View');

// Livewire Billing Management Routes
Route::get('/billing/invoicable-items', function () {
    return view('layouts.billing.invoicable-items-index');
})->name('billing.invoicable-items')->middleware('auth');

Route::get('/billing/dynamics-customers', function () {
    return view('layouts.billing.dynamics-customers-index');
})->name('billing.dynamics-customers')->middleware('auth');

Route::get('/billing/currencies', function () {
    return view('layouts.billing.currencies-index');
})->name('billing.currencies')->middleware('auth');

Route::get('/billing/invoices', function () {
    return view('layouts.billing.invoices-index');
})->name('billing.invoices')->middleware('auth');

Route::get('/billing/quotations', function () {
    return view('layouts.billing.quotations-index');
})->name('billing.quotations')->middleware('auth');

Route::get('/billing/sales-order/create', function () {
    $batchCodes = request()->get('batches', []);
    return view('layouts.billing.sales-order-create', ['batchCodes' => $batchCodes]);
})->name('billing.sales-order.create')->middleware('auth');

Route::get('/analysis-types', 'AnalysisTypeController@index')->name('analysis-types')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::post('/analysis-types', 'AnalysisTypeController@add')->name('add-analysis-types')->middleware('haspermission:Laboratory.components.Analysis Types.Add');
Route::post('/analysis-type/{id}', 'AnalysisTypeController@edit')->name('edit-analysis-type')->middleware('haspermission:Laboratory.components.Analysis Types.Edit');
Route::get('/analysis-type/{id}', 'AnalysisTypeController@show')->name('analysis-type')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::get('/get/Analyte/{id}/Methods', 'AnalysisElementsController@getAnalyteMethods')->name('getAnalyteMethods')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::get('/change/Labsection-By-Captured-Results', 'AnalysisElementsController@changeLabsectionByCapturedResults')->name('changeLabsectionByCapturedResults')->middleware('haspermission:Laboratory.components.Analysis Types.View');

Route::post('/add-analyte-guide', 'AnalysisMethodElementsController@update_guide')->name('add-analyte-guide')->middleware('haspermission:Laboratory.components.Methods.Edit');
Route::post('/clone-analyte-guide', 'AnalysisMethodElementsController@clone_analysis_guide')->name('clone_analysis_guide')->middleware('haspermission:Laboratory.components.Methods.Edit');
Route::post('/delete-analyte-guide', 'AnalysisMethodElementsController@delete_analysis_guide')->name('delete_analysis_guide')->middleware('haspermission:Laboratory.components.Methods.Delete');

Route::post('/analysis-elements', 'AnalysisElementsController@add')->name('add-analysis-elements')->middleware('haspermission:Laboratory.components.Analysis Types.Add');
Route::post('/analysis-element/{id}', 'AnalysisElementsController@edit')->name('edit-analysis-element')->middleware('haspermission:Laboratory.components.Analysis Types.Edit');
Route::get('/move-analysis-analyte/{direction}/{analysis}/{element}', 'AnalysisElementsController@move_analysis_analyte')->name('move-analysis-analyte')->middleware('haspermission:Laboratory.components.Analysis Types.Edit');
Route::post('/delete-Analysis-Element', 'AnalysisElementsController@deleteAnalysisElement')->name('deleteAnalysisElement')->middleware('haspermission:Laboratory.components.Analysis Types.Delete');

Route::get('/analysis-methods', function () {
    return view('livewire.lab.method-manager-page');
})->name('analysis-methods')->middleware('haspermission:Laboratory.components.Methods.View');
Route::post('/analysis-methods', 'AnalysisMethodController@add')->name('add-analysis-methods')->middleware('haspermission:Laboratory.components.Methods.Add');
Route::post('/analysis-method/edit', 'AnalysisMethodController@edit')->name('edit-analysis-method')->middleware('haspermission:Laboratory.components.Methods.Edit');
Route::get('/analysis-method/{id}', function ($id) {
    return view('livewire.lab.method-detail-page', ['methodId' => (int) $id]);
})->name('analysis-method')->middleware('haspermission:Laboratory.components.Methods.View');

Route::post('/check_rft_no', 'SampleWorkFlowController@check_rft_no')->name('check_rft_no')->middleware('haspermission:Laboratory.components.RFT Form.View');
Route::post('/reject-approval-request', 'SampleWorkFlowController@return_batch_reception')->name('return_batch_reception')->middleware('haspermission:Laboratory.components.Approve For Analysis.Edit');

Route::post('/analysis-method-elements', 'AnalysisMethodElementsController@add')->name('add-analysis-method-elements')->middleware('haspermission:Laboratory.components.Methods.Add');
Route::post('/analysis-method-element/{id}', 'AnalysisMethodElementsController@edit')->name('edit-analysis-method-element')->middleware('haspermission:Laboratory.components.Methods.Edit');
Route::post('/analysis-method-element/{id}/delete', 'AnalysisMethodElementsController@delete')->name('delete-analysis-method-element')->middleware('haspermission:Laboratory.components.Methods.Delete');
Route::post('/update-method-reagents/{method_id}', 'MethodReagentController@modify')->name('update-method-reagents')->middleware('haspermission:Laboratory.components.Methods.Edit');

Route::get('/reporting-units', 'ReportingUnitController@index')->name('reporting-units')->middleware('haspermission:Laboratory.components.Reporting-Units.View');
Route::get('/reporting-units/{module?}', 'ReportingUnitController@index')->name('inventory-reporting-units')->middleware('haspermission:Laboratory.components.Reporting-Units.View');
Route::post('/reporting-units', 'ReportingUnitController@add')->name('add-reporting-unit')->middleware('haspermission:Laboratory.components.Reporting-Units.Add');
Route::post('/reporting-unit/{id}', 'ReportingUnitController@update')->name('edit-reporting-unit')->middleware('haspermission:Laboratory.components.Reporting-Units.Edit');
Route::get('/reporting-unit/addAjax', 'ReportingUnitController@addAjax')->name('reporting-addAjax')->middleware('haspermission:Laboratory.components.Reporting-Units.Add');

Route::get('/sample-analysis-stages', 'SampleAnalysisStageController@index')->name('sample-analysis-stages')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.View');
Route::post('/sample-analysis-stages', 'SampleAnalysisStageController@add')->name('add-sample-analysis-stage')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Add');
Route::post('/sample-analysis-stage/update', 'SampleAnalysisStageController@update')->name('update-sample_analysis_stage')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Edit');
Route::post('/sample-stages/delete', 'SampleAnalysisStageController@deleteStage')->name('delete-stage')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Delete');

Route::post('/sample-analysis-stages-to-sample-type/{sample_type_id}', 'SampleToSampleAnalysisStageController@add')->name('add-sample-analysis-stage-to-sample-type')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Add');
Route::post('/sample-analysis-stages-to-sample-type/{id}/inactivate', 'SampleToSampleAnalysisStageController@update')->name('update-sample-analysis-stage-to-sample-type')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Edit');

Route::get('/move-sample-type/{direction}/{analysis}/{element}', 'SampleTypeController@move_sample_types')->name('move-sample-type')->middleware('haspermission:Laboratory.components.Sample-Types.Edit');

//#############################################Buffer###################################################
Route::get('/stock-monitoring/categories', 'Lab\BufferManagementController@categories_index')->name('stock-monitoring-categories')->middleware('haspermission:Laboratory.components.Stock-Monitoring.View');
Route::get('/stock-management/sub-categories', 'Lab\BufferManagementController@stock_management_livewire_index')->name('stock_management_index')->middleware('haspermission:Laboratory.components.Stock-Monitoring.View');
Route::get('/stock-monitoring/sub-categories-delete/{id}', 'Lab\BufferManagementController@delete_category')->name('delete_category')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Delete');
Route::post('/stock-monitoring/categories-add', 'Lab\BufferManagementController@add_lab_inventory_categories')->name('add_lab_inventory_categories')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Add');
Route::post('/stock-monitoring/filter', 'Lab\BufferManagementController@filter_data')->name('filter_data_category')->middleware('haspermission:Laboratory.components.Stock-Monitoring.View');
Route::post('/stock-monitoring/sub-categories-add', 'Lab\BufferManagementController@add_lab_sub_category')->name('add_lab_sub_inventory_categories')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Add');
Route::get('/stock-monitoring/sub-categories/show/{id}', 'Lab\BufferManagementController@stock_management_livewire_show')->name('show_lab_sub_category')->middleware('haspermission:Laboratory.components.Stock-Monitoring.View');
Route::post('/stock-monitoring/lab-category-item', 'Lab\BufferManagementController@add_lab_category_item')->name('add_lab_category_item')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Add');
Route::post('/stock-monitoring/lab-category-item/delete', 'Lab\BufferManagementController@delete_show_lab_category_item')->name('delete_show_lab_category_item')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Delete');
Route::post('/stock-monitoring/lab-category-item/edit', 'Lab\BufferManagementController@edit_lab_category_item')->name('edit_lab_category_item')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Edit');
Route::post('/stock-monitoring/lab-sub-category/edit', 'Lab\BufferManagementController@edit_lab_sub_category')->name('edit_lab_sub_category')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Edit');
Route::post('/stock-monitoring/lab-sub-category/delete', 'Lab\BufferManagementController@delete_sub_category')->name('delete_sub_category')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Delete');
Route::post('/stock-monitoring/lab-sub-category/clone', 'Lab\BufferManagementController@clone_sub_category')->name('clone_sub_category')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Add');

Route::get('/solutions-movement', 'Lab\BufferStockMovementController@livewire_index')->name('solution-movement-index')->middleware('haspermission:Laboratory.components.Stock-Monitoring.View');
Route::get('/solutions-movement/show/{id}', 'Lab\BufferStockMovementController@livewire_show')->name('solution-movement-show')->middleware('haspermission:Laboratory.components.Stock-Monitoring.View');
Route::post('/solutions-movement/add', 'Lab\BufferStockMovementController@add')->name('solution-movement-add')->middleware('haspermission:Laboratory.components.Stock-Monitoring.Add');

Route::post('/stock-taking-counter/{id}/add', 'StockTakingCounterController@add')->name('add-stock-taking-counter')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');
Route::post('/stock-taking-counter/{id}/remove', 'StockTakingCounterController@remove')->name('remove-stock-taking-counter')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');

//#############################################Sample Workflow###################################################
Route::get('/get-Tat/Delayed/Sample', 'SampleWorkFlowController@getTatDelayedSample')->name('getTatDelayedSample')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/awaiting/Approval/Samples/{status}', 'SampleWorkFlowController@awaitingApprovalSamples')->name('awaitingApprovalSamples')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/updateTatCaptured', 'SampleWorkFlowController@updateTatCaptured')->name('updateTatCaptured')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/get/Tat/Batch/ApprovalCounter/Ajax/{status}', 'SampleWorkFlowController@getTatBatchApprovalCounterAjax')->name('getTatBatchApprovalCounterAjax')->middleware('haspermission:Laboratory.components.All Samples.View');

Route::post('/process-raw-results/lab', 'SampleWorkFlowController@processRawResultsLab')->name('process-raw-results-lab')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/sample-workflow/{status?}', 'SampleWorkFlowController@index')->name('sample-workflow')->middleware('haspermission:Laboratory.components.All Samples.View');
//   Route::get('/sample-workflow/{status?}/stage', 'SampleWorkFlowController@index')->name('sample-workflow')->middleware('haspermission:Laboratory.components.status.View');
Route::get('/sample-workflow/{status?}/stage', 'SampleWorkFlowController@index')->name('sample-workflow-stage')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/sample-workflow/batch/{batch}/details/{client?}/{portal?}/{status?}', 'SampleWorkFlowController@show')->name('view-batch-details')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/sample-workflow/batch/{batch}/worksheets', 'WorksheetsController@index')
    ->name('batch-worksheets')
    ->middleware('haspermission:Laboratory.components.All Samples.View');
// Temporary design route for procedure worksheet PDF template preview.
Route::get(
    '/sample-workflow/batch/{batch}/worksheets/{worksheet}/procedure-preview',
    'WorksheetsController@previewProcedureWorksheetPdf'
)->name('batch-worksheets.procedure-preview')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/add-batch-info/{batch}', 'SampleWorkFlowController@add_batch_info')->name('add-batch-info')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/add-batch-samples/{batch}', 'SampleWorkFlowController@add_batch_samples')->name('add-batch-samples')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/add-new-samples', 'SampleWorkFlowController@add_batch_samples')->name('add-new-samples')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/delete-sample/{id}', 'SampleDetailsController@delete')->name('delete-sample')->middleware('haspermission:Laboratory.components.All Samples.Delete');
Route::post('/bulk-update-sample-data', 'SampleWorkFlowController@bulkUpdateSampleData')->name('bulk-update-sample-data')->middleware('haspermission:Laboratory.components.All Samples.Edit');

// Submission Form Integration Routes
Route::get('/sample-workflow-forms/submission-forms', 'SampleWorkFlowController@getAvailableSubmissionForms')->name('sample-workflow.submission-forms')->middleware('haspermission:Laboratory.components.RFT Form.View');
Route::post('/sample-workflow-forms/submission-forms/create-instance', 'SampleWorkFlowController@createSubmissionFormInstance')->name('sample-workflow.create-form-instance')->middleware('haspermission:Laboratory.components.RFT Form.Add');
Route::get('/sample-submission-forms/forms', 'FormInstanceController@index')->name('sample-workflow.saved-forms')->middleware('haspermission:Laboratory.components.RFT Form.View');

// Sample Submissions Management Page (Livewire)
Route::get('/sample-submissions', function () {
    return view('layouts.lab.sample-workflow.sign-customer-focus-index');
})->name('sample-submissions')->middleware('haspermission:Laboratory.components.RFT Form.View');

Route::post('/print-labels', 'SampleWorkFlowController@print_labels')->name('print-labels')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/send-out-email-reports', 'SampleWorkFlowController@send_report_email')->name('send-out-email-reports')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/lab/batch/approve/{id}', 'SampleWorkFlowController@approve_batch')->name('approve-batch-analysis')->middleware('haspermission:Laboratory.components.Approve For Analysis.Edit');

Route::post('/change-batch-workflow', 'SampleWorkFlowController@change_workflow_status')->name('change-batch-workflow')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/batch-approve-payment', 'SampleWorkFlowController@generate_batch_invoice')->name('generate_batch_invoice')->middleware('haspermission:Laboratory.components.Generate Invoice.View');
Route::post('/batch-payment-reminders', 'SampleWorkFlowController@send_payment_notification')->name('send_payment_notification')->middleware('haspermission:Laboratory.components.Proforma Invoices.Edit');
Route::post('/return-back-verification', 'SampleWorkFlowController@return_back_verification')->name('return_back_verification')->middleware('haspermission:Laboratory.components.Approve For Analysis.Edit');

Route::post('/update-invoice', 'SampleWorkFlowController@updateInvoiceDetails')->name('updateinvoicedetail')->middleware('haspermission:Laboratory.components.Proforma Invoices.Edit');
Route::get('/get-invoice/itemData/{invoice_id}/{item_id}', 'SampleWorkFlowController@getInvoiceItemData')->name('getInvoiceItemData')->middleware('haspermission:Laboratory.components.Proforma Invoices.View');
// -----------------------------------SALES ORDERS----------------------
Route::post('/generate/batch-invoice/ajax', 'SampleWorkFlowController@generate_batch_invoice_ajax')->name('generate_batch_invoice_ajax')->middleware('haspermission:Laboratory.components.Sales-Orders.Add');
Route::get('/send/Sales-Order/{id}', 'SampleWorkFlowController@sendSalesOrder')->name('sendSalesOrder')->middleware('haspermission:Laboratory.components.Sales-Orders.Add');
Route::get('/delete/sales-order/{id}', 'SampleWorkFlowController@deleteSalesOrder')->name('deleteSalesOrder')->middleware('haspermission:Laboratory.components.Sales-Orders.Delete');
Route::post('/send/sales/order-ajax', 'SampleWorkFlowController@moveToLabAjax')->name('move-to-lab-ajax')->middleware('haspermission:Laboratory.components.Sales-Orders.Add');

Route::get('/zoho-item/analysis-types', 'SampleTypeController@zohotoAnalysisTypes')->name('zoho-item-analysis')->middleware('haspermission:Laboratory.components.Sales-Orders.View');
Route::post('zoho/item/analysis-store', 'SampleTypeController@zohoAnalysisStore')->name('zoho-item-analysis-store')->middleware('haspermission:Laboratory.components.Sales-Orders.Add');
// -----------------------------------SALES ORDERS----------------------


Route::get('/send_notification_reminders', 'Event\EventController@send_notification_reminders')->name('send_notification_reminders');

Route::get('/regerateCustomerInvoice/{id}', 'SampleWorkFlowController@regerateCustomerInvoice')->name('regerateCustomerInvoice')->middleware('haspermission:Laboratory.components.Proforma Invoices.Edit');
Route::get('/split-contact', 'SampleWorkFlowController@splitSchoolContacts')->name('/split-contact')->middleware('haspermission:Laboratory.components.All Samples.View');
//############################################################################################################################
Route::get('/billing-quotation/{stage?}', 'Invoice\QuotationController@index')->name('quotation-index')->middleware('haspermission:Laboratory.components.Quotation.View');
Route::get('/billing/change-quotation-workflow/{id}/{stage}', 'Invoice\QuotationController@change_quotation_workflow')->name('change_quotation_workflow')->middleware('haspermission:Laboratory.components.Quotation.Edit');
Route::get('/billing-add-quote-detail-index/{id}/{stage?}', 'Invoice\QuotationController@view_quote_header_detail')->name('add-qoute-details-view')->middleware('haspermission:Laboratory.components.Quotation.View');
Route::post('/billing-add-quote-header', 'Invoice\QuotationController@add_quotation_header')->name('add-quotation-header')->middleware('haspermission:Laboratory.components.Quotation.Add');
Route::post('/api/get-currency-by-code', 'Invoice\QuotationController@getCurrencyByCode');
Route::post('/billing/add-quotation-detail/{id}', 'Invoice\QuotationController@add_quotation_detail')->name('add_quotation_detail')->middleware('haspermission:Laboratory.components.Quotation.Add');
Route::get('/billing-quotation-view-final/{id}/{stage?}', 'Invoice\QuotationController@view_quotation_final')->name('view_quotation_final')->middleware('haspermission:Laboratory.components.Quotation.View');
Route::post('/billing/edit_quotation_detail', 'Invoice\QuotationController@edit_quotation_detail')->name('edit_quotation_detail')->middleware('haspermission:Laboratory.components.Quotation.Edit');
Route::get('/billing/delete_quotation_detail/{id}', 'Invoice\QuotationController@delete_quotation_detail')->name('delete_quotation_detail')->middleware('haspermission:Laboratory.components.Quotation.Delete');
Route::post('/billing/save_draft/{id}', 'Invoice\QuotationController@save_draft')->name('save_draft')->middleware('haspermission:Laboratory.components.Quotation.Edit');
Route::get('/billing/redirect_from_docs/{id}/{stage?}', 'Invoice\QuotationController@redirect_from_docs')->name('redirect_from_docs')->middleware('haspermission:Laboratory.components.Quotation.View');
Route::get('/billing/clone_quotation/{id}', 'Invoice\QuotationController@clone_quotation')->name('clone_quotation')->middleware('haspermission:Laboratory.components.Quotation.Add');
Route::post('/billing/save-quotation-final/{id}', 'Invoice\QuotationController@save_quotation_final')->name('save_quotation_final')->middleware('haspermission:Laboratory.components.Quotation.Edit');
Route::post('/billing/delete_quotation/{id}', 'Invoice\QuotationController@delete_quotation')->name('delete_quotation')->middleware('haspermission:Laboratory.components.Quotation.Delete');
Route::get('/billing/print_quotation/{id}', 'Invoice\QuotationController@print_quotation')->name('print_quotation')->middleware('haspermission:Laboratory.components.Quotation.View');
Route::post('/billing/upload_quotation/{id}', 'Invoice\QuotationController@upload_quotation')->name('upload_quotation')->middleware('haspermission:Laboratory.components.Quotation.Edit');
Route::post('/approve-workflow', 'Invoice\QuotationController@approve_workflow')->name('approve-workflow')->middleware('haspermission:Laboratory.components.Quotation.Edit');
Route::post('/convert-quote/batch', 'Invoice\QuotationController@convertQuoteToBatch')->name('convert-quote-batch')->middleware('haspermission:Laboratory.components.Quotation.Edit');

Route::post('/billing/payment-detail-add', 'InvoicePaymentDetailController@add')->name('payment-detail-add');
Route::post('/billing/payment-detail-edit', 'InvoicePaymentDetailController@edit')->name('payment-detail-edit');
Route::post('/billing/payment-detail-delete', 'InvoicePaymentDetailController@delete')->name('payment-detail-delete');

Route::post('/approve/ready-proccess', 'SampleWorkFlowController@approve_batch_begin_process')->name('approve_batch_begin_process')->middleware('haspermission:Laboratory.components.Approve For Analysis.Edit');

Route::get('/fetch-sample-type/{id}', 'SampleWorkFlowController@fetch_sample_type')->name('fetch_sample_type')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/fetch-sample-analytes/{id}/{analysis}/{detail?}', 'SampleWorkFlowController@fetch_sample_analyte')->name('fetch_sample_analytes')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/fetch-detail-data/{id}', 'Invoice\QuotationController@get_quotation_detail')->name('get_quotation_detail');
Route::get('/addBatchSamplesDynamically', 'SampleWorkFlowController@addBatchSamplesDynamically')->name('addBatchSamplesDynamically')->middleware('haspermission:Laboratory.components.All Samples.Edit');

Route::post('/sample-workflow/staging/update/{id}', 'SampleWorkFlowController@updateStagingDetail')->name('update-staging-detail')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::delete('/sample-workflow/staging/delete/{id}', 'SampleWorkFlowController@deleteStagingDetail')->name('delete-staging-detail')->middleware('haspermission:Laboratory.components.All Samples.Delete');

Route::post('filter-Quotations', 'Invoice\QuotationController@filterQuotations')->name('filterQuotations');
Route::get('populate/Quotation-Detail/Split', 'Invoice\QuotationController@populateQuotationDetailSplit')->name('populateQuotationDetailSplit');
Route::get('get/Labs-By-Analysis/Type-Id-Ajax', 'SampleWorkFlowController@getLabsByAnalysisTypeIdAjax')->name('getLabsByAnalysisTypeIdAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/add/Batch-Invoice', 'SampleWorkFlowController@addBatchInvoice')->name('addBatchInvoice')->middleware('haspermission:Laboratory.components.Proforma Invoices.Add');


//#####################################################################################################################################

Route::post('/move-to-stage/{stage}/{batch_id}', 'SampleWorkFlowController@move_to_stage')->name('move-to-stage')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/move-to-workflow/{status}/{batch_id}', 'SampleWorkFlowController@move_to_workflow')->name('move-to-workflow')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/add-analytes-to-sample-analysis', 'SampleWorkFlowController@add_analyte_to_sample_analysis')->name('add-analytes-to-sample-analysis')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/capture-raw-results', 'SampleWorkFlowController@capture_raw_results')->name('capture-raw-results')->middleware('haspermission:Laboratory.components.All Samples.Edit');

// Captured Results Modal AJAX Routes
Route::post('/captured-results/update-parameter-settings', 'SampleWorkFlowController@updateParameterSettings')->name('update-parameter-settings')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/captured-results/update-standard-limit', 'SampleWorkFlowController@updateStandardLimit')->name('update-standard-limit')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/captured-results/update-result', 'SampleWorkFlowController@updateResult')->name('update-result')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/captured-results/get-parameter-settings/{resultId}', 'SampleWorkFlowController@getParameterSettings')->name('get-parameter-settings')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/captured-results/get-standard-settings/{resultId}', 'SampleWorkFlowController@getStandardSettings')->name('get-standard-settings')->middleware('haspermission:Laboratory.components.All Samples.View');

Route::get('/process-raw-results/{batch_id}', 'SampleWorkFlowController@process_results')->name('process-raw-results')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/report-interpretations/{batch_id}', 'ReportHeaderDetailController@report_interpretations')->name('report-interpretations')->middleware('haspermission:Laboratory.components.Lab-Reports.Edit');
Route::get('/process-pdf-report/{batch_id}/{report_format}', 'ReportHeaderDetailController@process_pdf_report')->name('process-pdf-report')->middleware('haspermission:Laboratory.components.Lab-Reports.View');
Route::get('colorQrCode/', 'ReportHeaderDetailController@colorQrCode')->name('colorQrCode')->middleware('haspermission:Laboratory.components.Lab-Reports.View');

Route::get('/fetch-unit-stuff/{name}/{client}', 'SampleWorkFlowController@fetch_unit_stuff')->name('fetch-unit-stuff')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/mail-report', 'MailController@html_email')->name('mail-report');

Route::post('/lab/delete/batch', 'SampleWorkFlowController@delete_batch')->name('delete-batch')->middleware('haspermission:Laboratory.components.All Samples.Delete');
Route::post('/sample-interpretations/{sample_id}', 'ReportHeaderDetailController@sample_interpretations')->name('sample-interpretations')->middleware('haspermission:Laboratory.components.Lab-Reports.Edit');

Route::post('/add_batch_attachment', 'SampleWorkFlowController@add_batch_attachment')->name('add_batch_attachment')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/sample-workflow/batch/{batch}/regenerate-submission-form', 'SampleWorkFlowController@regenerateSubmissionForm')->name('regenerate-submission-form')->middleware('haspermission:Laboratory.components.RFT Form.Edit');
Route::post('/store-attachment-type', 'SampleWorkFlowController@store_attachment_type')->name('store-attachment-type')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/delete_batch_attachmment', 'SampleWorkFlowController@delete_batch_attachmment')->name('delete_batch_attachmment')->middleware('haspermission:Laboratory.components.All Samples.Delete');
Route::post('/merge-attachments', 'SampleWorkFlowController@merge_attachments')->name('merge-attachments')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/batch/attachments/{id}/download', 'SampleWorkFlowController@downloadBatchAttachment')->name('download-attachment')->middleware('haspermission:Laboratory.components.All Samples.View');

// PDF Annotation routes
Route::get('/batch/attachments/{id}/annotate', 'SampleWorkFlowController@showAnnotationPage')->name('show-pdf-annotation-page')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/batch/attachments/annotate/save', 'SampleWorkFlowController@saveAnnotatedPdf')->name('save-annotated-pdf')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/batch/attachments/annotate/upload-image', 'SampleWorkFlowController@uploadAnnotationImage')->name('upload-annotation-image')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/batch/attachments/{id}/annotations', 'SampleWorkFlowController@getAnnotations')->name('get-pdf-annotations')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/batch/attachments/{id}/annotations/delete', 'SampleWorkFlowController@deleteAnnotations')->name('delete-pdf-annotations')->middleware('haspermission:Laboratory.components.All Samples.Delete');

Route::post('/lab/batch/ammendment', 'BatchAmmendmentController@add')->name('add-batch-ammendment')->middleware('haspermission:Laboratory.components.All Samples.Edit');

//#####################LABS######################################################################################

//############################################INVENTORY##########################################################

Route::get('/inventory-home', 'HomeController@inventory')->name('inventory-home')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::get('/inventory-activity', 'InventoryItemController@index')->name('inventory-activity')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::get('/inventory-activity/server-side', 'InventoryItemController@activity_serverside')->name('get-stock-movement')->middleware('haspermission:Inventory.components.Inventory-Movement.View');

//#REPORTS##
Route::get('/inventory-reports', 'ReportGeneratorController@inventory_reports')->name('inventory-reports')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('report/configuration/update/{id}', 'ReportGeneratorController@save_report')->name('update_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::get('/reports/fields/{table}', 'ReportGeneratorController@fields')->name('fields')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/save', 'ReportGeneratorController@store')->name('store_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/fetch', 'ReportGeneratorController@fetch')->name('fetch_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/delete', 'ReportGeneratorController@delete')->name('delete_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/print', 'ReportGeneratorController@print')->name('report_print')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/csv', 'ReportGeneratorController@exportCsv')->name('report_csv')->middleware('haspermission:Inventory.components.Inventory-Movement.View');

Route::get('/reports/consumption-reports', 'ReportGeneratorController@consumption')->name('consumption-reports')->middleware('haspermission:Inventory.components.Inventory-Movement.View');


Route::get('/inventory-categories', 'InventoryCategoriesController@index')->name('inventory-categories')->middleware('haspermission:Inventory.components.Categories.View');
Route::post('/inventory-categories', 'InventoryCategoriesController@add')->name('add-inventory-category')->middleware('haspermission:Inventory.components.Categories.Add');
Route::post('/inventory-category/{id}', 'InventoryCategoriesController@edit')->name('edit-inventory-category')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/inventory-category/{id}/delete', 'InventoryCategoriesController@destroy')->name('delete-inventory-category')->middleware('haspermission:Inventory.components.Categories.Delete');
Route::get('/inventory-category/{id}', 'InventoryCategoriesController@show')->name('show-inventory-category')->middleware('haspermission:Inventory.components.Categories.View');
Route::post('/inventory-category/set-default-store/{id}', 'InventoryCategoriesController@set_default_store')->name('set-default-store')->middleware('haspermission:Inventory.components.Categories.Edit');

Route::post('/change-brand-details/{id}', 'ItemBrandController@edit')->name('change-brand-image')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/add-item-brand/{subcategory}', 'ItemBrandController@add')->name('add-item-brand')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/delete-item-brand/{id}', 'ItemBrandController@delete')->name('delete-item-brand')->middleware('haspermission:Inventory.components.Categories.Delete');

Route::get('/inventory-stores', 'InventoryStoreController@index')->name('inventory-stores')->middleware('haspermission:Inventory.components.Store.View');
Route::post('/inventory-stores', 'InventoryStoreController@add')->name('add-inventory-store')->middleware('haspermission:Inventory.components.Store.Add');
Route::post('/inventory-store/{id}', 'InventoryStoreController@edit')->name('edit-inventory-store')->middleware('haspermission:Inventory.components.Store.Edit');
Route::post('/add-store-cost-center/{id}', 'InventoryStoreController@add_cost_center')->name('add-store-cost-center')->middleware('haspermission:Inventory.components.Store.Edit');
Route::post('/remove-store-cost-center/{id}', 'InventoryStoreController@remove_cost_center')->name('remove-store-cost-center')->middleware('haspermission:Inventory.components.Store.Edit');
Route::post('/inventory-store/{id}/delete', 'InventoryStoreController@delete')->name('delete-inventory-store')->middleware('haspermission:Inventory.components.Store.Delete');

Route::get('/inventory-store-slots/{store}', 'InventoryStoreSlotController@index')->name('inventory-store-slots')->middleware('haspermission:Inventory.components.Store.View');
Route::post('/inventory-store-slots/{store}', 'InventoryStoreSlotController@add')->name('add-inventory-store-slot')->middleware('haspermission:Inventory.components.Store.Add');
Route::post('/inventory-store-slots/{id}/slot', 'InventoryStoreSlotController@edit')->name('edit-inventory-store-slot')->middleware('haspermission:Inventory.components.Store.Edit');
Route::post('/inventory-store-slots/{id}/delete', 'InventoryStoreSlotController@delete')->name('delete-inventory-store-slot')->middleware('haspermission:Inventory.components.Store.Delete');

Route::get('/inventory-slot-contents/{slot}/{store}', 'InventoryStoreSlotContentController@index')->name('inventory-slot-contents')->middleware('haspermission:Inventory.components.Store.View');
Route::post('/inventory-slot-contents/{slot}/{store}', 'InventoryStoreSlotContentController@add')->name('add-inventory-slot-content')->middleware('haspermission:Inventory.components.Store.Add');

//############################################SUBMISSION FORMS##########################################################
Route::prefix('submission-forms')->name('submission-forms.')->middleware('auth')->group(function () {
    Route::get('/', 'SubmissionFormController@index')->name('index');
    Route::get('/create', 'SubmissionFormController@create')->name('create');
    Route::post('/', 'SubmissionFormController@store')->name('store');

    // Dynamic Options for Custom Elements (must be before /{submissionForm} route)
    Route::get('/dynamic-options', 'SubmissionFormController@getDynamicOptions')->name('dynamic-options');
    Route::get('/user-signature', 'SubmissionFormController@getUserSignature')->name('user-signature');
    Route::get('/contact-signature', 'SubmissionFormController@getContactSignature')->name('contact-signature');
    Route::post('/contact-signature', 'SubmissionFormController@saveContactSignature')->name('save-contact-signature');

    // Quick Store Routes for Modal Forms (must be before /{submissionForm} route)
    Route::post('/quick-store/client', 'SubmissionFormController@quickStoreClient')->name('quick-store.client');
    Route::post('/quick-store/client-unit', 'SubmissionFormController@quickStoreClientUnit')->name('quick-store.client-unit');
    Route::post('/quick-store/client-contact', 'SubmissionFormController@quickStoreClientContact')->name('quick-store.client-contact');
    Route::post('/quick-store/sample-condition', 'SubmissionFormController@quickStoreSampleCondition')->name('quick-store.sample-condition');
    Route::post('/quick-store/sample-point', 'SubmissionFormController@quickStoreSamplePoint')->name('quick-store.sample-point');

    // Mapping Fields (must be before /{submissionForm} route)
    Route::get('/mapping-fields', 'FormBuilderController@getMappingFields')->name('mapping-fields');

    Route::get('/{submissionForm}', 'SubmissionFormController@show')->name('show');
    Route::get('/{submissionForm}/edit', 'SubmissionFormController@edit')->name('edit');
    Route::put('/{submissionForm}', 'SubmissionFormController@update')->name('update');
    Route::delete('/{submissionForm}', 'SubmissionFormController@destroy')->name('destroy');
    Route::get('/{submissionForm}/preview', 'SubmissionFormController@preview')->name('preview');
    Route::post('/{submissionForm}/toggle-published', 'SubmissionFormController@togglePublished')->name('toggle-published');
    Route::post('/{submissionForm}/clone', 'SubmissionFormController@clone')->name('clone');
    Route::get('/{submissionForm}/export', 'SubmissionFormController@export')->name('export');

    // Form Builder Routes
    Route::get('/{submissionForm}/builder', 'FormBuilderController@index')->name('builder');
    Route::get('/{submissionForm}/structure', 'FormBuilderController@getFormStructure')->name('structure');
    Route::get('/{submissionForm}/validate-element-name', 'FormBuilderController@validateElementName')->name('validate-element-name');

    // Section Management
    Route::post('/{submissionForm}/sections', 'FormBuilderController@addSection')->name('sections.store');
    Route::put('/sections/{section}', 'FormBuilderController@updateSection')->name('sections.update');
    Route::delete('/sections/{section}', 'FormBuilderController@deleteSection')->name('sections.destroy');
    Route::post('/{submissionForm}/sections/reorder', 'FormBuilderController@reorderSections')->name('sections.reorder');
    Route::post('/sections/{section}/clone', 'FormBuilderController@cloneSection')->name('sections.clone');
    Route::post('/sections/{section}/move', 'FormBuilderController@moveSectionToPosition')->name('sections.move');

    // Element Holder Management
    Route::get('/holders/{holder}', 'FormBuilderController@getElementHolder')->name('holders.show');
    Route::post('/sections/{section}/holders', 'FormBuilderController@addElementHolder')->name('holders.store');
    Route::put('/holders/{holder}', 'FormBuilderController@updateElementHolder')->name('holders.update');
    Route::delete('/holders/{holder}', 'FormBuilderController@deleteElementHolder')->name('holders.destroy');
    Route::post('/sections/{section}/holders/reorder', 'FormBuilderController@reorderElementHolders')->name('holders.reorder');
    Route::post('/holders/{holder}/clone', 'FormBuilderController@cloneElementHolder')->name('holders.clone');
    Route::post('/holders/{holder}/move', 'FormBuilderController@moveHolderToSection')->name('holders.move');

    // Element Management
    Route::post('/holders/{holder}/elements', 'FormBuilderController@addElement')->name('elements.store');
    Route::put('/elements/{element}', 'FormBuilderController@updateElement')->name('elements.update');
    Route::delete('/elements/{element}', 'FormBuilderController@deleteElement')->name('elements.destroy');
    Route::post('/holders/{holder}/elements/reorder', 'FormBuilderController@reorderElements')->name('elements.reorder');
    Route::post('/elements/{element}/clone', 'FormBuilderController@cloneElement')->name('elements.clone');
    Route::post('/elements/{element}/move', 'FormBuilderController@moveElementToHolder')->name('elements.move');

    // Form Instance Routes
    Route::prefix('instances')->name('instances.')->group(function () {
        Route::get('/', 'FormInstanceController@index')->name('index');

        // Batch View - Display form instance with all linked batches and samples (safe route pattern)
        Route::get('/batch/{instance}/view', function () {
            abort(404);
        })->name('batch-view')->where('instance', '[0-9]+');

        // Batch View Print - Print version of batch view
        Route::get('/batch/{instance}/print', 'FormInstanceController@batchViewPrint')->name('batch-view-print')->where('instance', '[0-9]+');

        // Dynamic options route
        Route::get('/dynamic-options', 'FormInstanceController@getDynamicOptions')->name('dynamic-options');

        // Form creation routes
        Route::get('/{submissionForm}/create', 'FormInstanceController@create')->name('create');
        Route::post('/{submissionForm}', 'FormInstanceController@store')->name('store');

        // Sample creation / sync (numeric {instance} only; register before generic /{submissionForm}/{instance} routes)
        Route::post('/{instance}/apply-to-batches', 'FormInstanceController@applyToBatches')->name('apply-to-batches')->where('instance', '[0-9]+');
        Route::post('/{instance}/create-samples', 'SampleCreationController@createFromForm')->name('create-samples');
        Route::get('/{instance}/sample-status', 'SampleCreationController@getStatus')->name('sample-status');
        Route::post('/bulk-create-samples', 'SampleCreationController@bulkCreate')->name('bulk-create-samples');

        // Instance-specific routes
        Route::get('/{submissionForm}/{instance}/fill', 'FormInstanceController@fill')->name('fill');
        Route::get('/{submissionForm}/{instance}/fill-sample', 'FormInstanceController@fillSample')->name('fill-sample');
        Route::put('/{submissionForm}/{instance}', 'FormInstanceController@update')->name('update');
        Route::get('/{submissionForm}/{instance}', 'FormInstanceController@show')->name('show');
        Route::get('/{submissionForm}/{instance}/edit', 'FormInstanceController@edit')->name('edit');
        Route::get('/{submissionForm}/{instance}/print', 'FormInstanceController@print')->name('print');
        Route::delete('/{submissionForm}/{instance}', 'FormInstanceController@destroy')->name('destroy');
    });
});

// Sample Staging Routes (outside submission-forms group)
Route::middleware(['auth'])->group(function () {
    Route::get('/lab/samples/staging/{staging}/load-assignment-data', 'SampleCreationController@loadAssignmentData')->name('staging.load-assignment-data')->middleware('haspermission:Laboratory.components.All Samples.View');
    Route::post('/lab/samples/assign-samples', 'SampleCreationController@assignSamples')->name('samples.assign')->middleware('haspermission:Laboratory.components.All Samples.Edit');
    Route::get('/lab/samples/staging/{staging}/available-areas-points', 'SampleCreationController@getAvailableAreasAndPoints')->name('staging.available-areas-points')->middleware('haspermission:Laboratory.components.All Samples.View');
    Route::post('/lab/samples/add-customer-sample-point', 'SampleCreationController@addCustomerSamplePoint')->name('samples.add-customer-point')->middleware('haspermission:Laboratory.components.All Samples.Add');
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

Route::post('/inventory-slot-contents/{id}/delete', 'InventoryStoreSlotContentController@delete')->name('delete-inventory-slot-content')->middleware('haspermission:Inventory.components.Store.Delete');

Route::get('/show-inventory-items/{category}/{id}', 'InventorySubCategoriesController@index')->name('show-inventory-items')->middleware('haspermission:Inventory.components.Categories.View');
Route::post('/inventory-sub-categories', 'InventorySubCategoriesController@add')->name('add-inventory-sub-category')->middleware('haspermission:Inventory.components.Categories.Add');
Route::post('/inventory-sub-category/{id}', 'InventorySubCategoriesController@edit')->name('edit-inventory-sub-category')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/inventory-sub-category/{id}/delete', 'InventorySubCategoriesController@destroy')->name('delete-inventory-sub-category')->middleware('haspermission:Inventory.components.Categories.Delete');

Route::post('/add-subcategory-conversion/{id}', 'UnitOfMeasureConversionController@update')->name('add-subcategory-conversion')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/delete-item-conversion', 'UnitOfMeasureConversionController@delete')->name('delete-item-conversion')->middleware('haspermission:Inventory.components.Categories.Delete');

Route::post('/add-subcategory-item-state/{id}', 'ItemStateController@update')->name('add-subcategory-item-state')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/delete-item-state', 'ItemStateController@delete')->name('delete-item-state')->middleware('haspermission:Inventory.components.Categories.Delete');

Route::post('/add-store-contacts/{id}', 'InventoryStoreContactController@add')->name('add-store-contacts')->middleware('haspermission:Inventory.components.Store.Edit');
Route::post('/delete-store-contacts', 'InventoryStoreContactController@delete')->name('delete-store-contacts')->middleware('haspermission:Inventory.components.Store.Delete');

Route::get('/inventory-departments', 'InventoryDepartmentController@index')->name('show-inventory-departments')->middleware('haspermission:Inventory.components.Departments.View');
Route::post('/inventory-departments/{module?}', 'InventoryDepartmentController@add')->name('add-inventory-department')->middleware('haspermission:Inventory.components.Departments.Add');
Route::post('/inventory-department/{id}', 'InventoryDepartmentController@edit')->name('edit-inventory-department')->middleware('haspermission:Inventory.components.Departments.Edit');
Route::get('/inventory-department/{id}', 'InventoryDepartmentController@show')->name('show-inventory-department')->middleware('haspermission:Inventory.components.Departments.View');
Route::post('/inventory-sub-category/{id}/delete', 'InventoryDepartmentController@destroy')->name('delete-inventory-department')->middleware('haspermission:Inventory.components.Departments.Delete');

Route::post('/inventory-items', 'InventoryItemController@add')->name('add-inventory-items')->middleware('haspermission:Inventory.components.Inventory-Movement.Add');
Route::post('/inventory-item-transfer', 'InventoryItemController@transfer')->name('transfer-inventory-items')->middleware('haspermission:Inventory.components.Inventory-Movement.Edit');
Route::post('/stock-keeping', 'InventoryItemController@stock_keeping')->name('stock-keeping')->middleware('haspermission:Inventory.components.Inventory-Movement.Edit');
Route::post('/item-disposal', 'InventoryItemController@item_disposal')->name('item-disposal')->middleware('haspermission:Inventory.components.Inventory-Movement.Delete');
Route::post('/return-item-to-store', 'InventoryItemController@return_2_store')->name('return-item-to-store')->middleware('haspermission:Inventory.components.Inventory-Movement.Edit');

Route::get('/stock-taking-list', 'StockTakingController@index')->name('stock-taking-list')->middleware('haspermission:Inventory.components.Stock-Taking.View');
Route::get('/stock-taking-update/{id}/{print?}', 'StockTaking\Main@show')->name('stock-taking-sheet')->middleware('haspermission:Inventory.components.Stock-Taking.View');
Route::post('/stock-taking-update/{id?}', 'StockTakingController@update')->name('stock-taking-update')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');
Route::post('/stock-taking-freeze-stores/{id?}', 'StockTakingController@freeze_stores')->name('stock-taking-freeze-stores')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');
Route::post('/stock-taking-save-capture/{id?}', 'StockTakingController@save_capture')->name('stock-taking-save-capture')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');
Route::post('/adjust-stock-keeping/{catid}/{subid}', 'StockTakingController@adjust_stock')->name('adjust-stock-keeping')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');

Route::get('/stock-transfer-list', 'StockTransferController@index')->name('stock-transfer-list')->middleware('haspermission:Inventory.components.Stock-Transfer.View');
Route::get('/stock-transfer-update/{id}', 'StockTransferController@show')->name('stock-transfer-sheet')->middleware('haspermission:Inventory.components.Stock-Transfer.View');
Route::post('/stock-transfer-update/{id?}', 'StockTransferController@update')->name('stock-transfer-update')->middleware('haspermission:Inventory.components.Stock-Transfer.Edit');
Route::post('/stock-transfer-items-update/{id?}', 'StockTransferController@update_items')->name('stock-transfer-items-update')->middleware('haspermission:Inventory.components.Stock-Transfer.Edit');
Route::post('/stock-transfer-item-delete', 'StockTransferController@delete_item')->name('stock-transfer-item-delete')->middleware('haspermission:Inventory.components.Stock-Transfer.Delete');

Route::post('/edit/supplier-quote/{id}', 'SupplierQuoteController@edit')->name('edit-supplier-item-quote')->middleware('haspermission:Inventory.components.Suppliers.Edit');
Route::post('/remove/supplier-quote/{id}/{itemID?}', 'SupplierQuoteController@remove')->name('remove-supplier-item-quote')->middleware('haspermission:Inventory.components.Suppliers.Delete');

Route::post('/undo-supplier-award/{id}', 'SupplierQuoteController@undo_supplier_award')->name('undo-supplier-award')->middleware('haspermission:Inventory.components.Suppliers.Edit');

Route::post('/remove-this-supplier/{id}/{itemID}', 'SupplierController@remove_supplier_from_inventory')->name('remove-this-supplier')->middleware('haspermission:Inventory.components.Suppliers.Delete');

//############################################INVENTORY##########################################################

//############################################GENERAL REQUISITION#############################################

Route::prefix('general-requisition')->group(function () {
    Route::get('/list', 'GeneralRequisition\GeneralRequisitionController@index')->name('general-requisition-list');
    Route::get('/show/{id?}', 'GeneralRequisition\GeneralRequisitionController@show')->name('general-requisition-view');
    Route::get('/show/{id}/document', 'GeneralRequisition\GeneralRequisitionController@view_document')->name('general-requisition-document');
    Route::get('/get-supplier-list/{search?}', 'GeneralRequisition\GeneralRequisitionController@suppliers')->name('get-supplier-list');
    Route::get('/get-gr-items-list/{search?}', 'GeneralRequisition\GeneralRequisitionController@inventory_items')->name('get-gr-items-list');
    Route::post('/remove-general-requisition-rows/{id}', 'GeneralRequisition\GeneralRequisitionController@remove_items')->name('remove-general-requisition-rows');
    Route::post('/update/{id?}', 'GeneralRequisition\GeneralRequisitionController@update')->name('general-requisition-update');
    Route::post('/add-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@add')->name('add-gr-quote')->middleware('haspermission:Inventory.components.Request for Quotation.Add');
    Route::post('/update-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@update')->name('update-gr-quotes')->middleware('haspermission:Inventory.components.Request for Quotation.Edit');
    Route::post('/delete-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@remove')->name('delete-gr-quotes')->middleware('haspermission:Inventory.components.Request for Quotation.Delete');
    Route::post('/change-status/{id}/{type}', 'GeneralRequisition\GeneralRequisitionController@change_status')->name('change-status');

    Route::post('/jump-request-to-status/{id}', 'RequisitionController@jump_request_to_status')->name('jump-request-to-status')->middleware('haspermission:Inventory.components.Request for Quotation.Edit');

    Route::post('/send-notification/{id}/{type}', 'GeneralRequisition\GeneralRequisitionController@sendApprovalNotifications')->name('send-notification-gr');

    Route::post('/change-approver-this-gr/{id?}/{field?}', 'GeneralRequisition\GeneralRequisitionController@change_approver')->name('change-approver-this-gr');
    Route::post('/approve-this-gr/{id?}/{type?}', 'GeneralRequisition\GeneralRequisitionController@approve_this')->name('approve-this-gr');
});

//############################################GENERAL REQUISITION#############################################

//############################################LOCATIONS##########################################################
Route::get('/organizational-locations', 'InventoryLocationController@index')->name('inventory-locations')->middleware('haspermission:Inventory.components.Departments.View');
Route::post('/organizational-locations', 'InventoryLocationController@add')->name('add-inventory-location')->middleware('haspermission:Inventory.components.Departments.Add');
Route::get('/organizational-locations/{id}', 'InventoryLocationController@show')->name('show-inventory-locations')->middleware('haspermission:Inventory.components.Departments.View');
Route::post('/organizational-locations/{id}', 'InventoryLocationController@edit')->name('edit-inventory-location')->middleware('haspermission:Inventory.components.Departments.Edit');
Route::post('/organizational-locations/{id}/delete', 'InventoryLocationController@destroy')->name('delete-inventory-location')->middleware('haspermission:Inventory.components.Departments.Delete');

Route::get('/set-user-location/{id}', 'InventoryLocationController@set_user_location')->name('set-user-location')->middleware('haspermission:Inventory.components.Departments.Edit');

Route::post('/add-user-access/{id}', 'InventoryLocationController@add_user')->name('add-user-access')->middleware('haspermission:Inventory.components.Departments.Edit');
Route::post('/remove-user-access/{id}/{user}', 'InventoryLocationController@remove_user_access')->name('remove-user-access')->middleware('haspermission:Inventory.components.Departments.Edit');
//############################################LOCATIONS##########################################################

// Asset Management Routes
Route::get('/equipment/asset-types', [EquipmentAppController::class, 'assetTypeManager'])->name('equipment.asset-types.index')->middleware(['auth', 'haspermission:Equipment.components.Asset-Type.View']);
Route::get('/equipment/asset-locations', [EquipmentAppController::class, 'assetLocationManager'])->name('equipment.asset-locations.index')->middleware(['auth', 'haspermission:Equipment.components.Asset-Location.View']);

//############################################EQUIPMENT##########################################################
Route::get('/equipment-home', [EquipmentAppController::class, 'equipmentManager'])->name('equipment-home')->middleware('haspermission:Equipment.permission');
Route::get('/equipment-dashboard', [EquipmentAppController::class, 'equipmentDashboard'])->name('equipment-dashboard')->middleware('haspermission:Equipment.permission');
Route::post('/equipment', 'Equipment\EquipmentController@add')->name('add-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Add');
Route::get('/equipment/{equipmentId}', [EquipmentAppController::class, 'equipmentDetail'])->name('view-equipment')->middleware('haspermission:Equipment.components.Equipment-List.View');
Route::post('/equipment/{id}', 'Equipment\EquipmentController@edit')->name('edit-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Edit');
Route::post('/schedule-maintainance/{id}', 'Equipment\MaintainanceCalibrationLogController@add')->name('new-maintainance')->middleware('haspermission:Equipment.components.Maintainance-Log.Add');
Route::post('/edit-maintainance', 'Equipment\MaintainanceCalibrationLogController@edit')->name('edit-maintainance')->middleware('haspermission:Equipment.components.Maintainance-Log.Edit');
Route::post('/usage-log/{id}/{equipment}', 'Equipment\EquipmentUsageController@update')->name('usage-log')->middleware('haspermission:Equipment.components.Maintainance-Log.Edit');
Route::post('/add-operators/{equipment}', 'Equipment\EquipmentOperatorController@add')->name('add-operators')->middleware('haspermission:Equipment.components.Operator-Log.Add');
Route::post('/remove-operator/{id}', 'Equipment\EquipmentOperatorController@destroy')->name('remove-operator')->middleware('haspermission:Equipment.components.Operator-Log.Delete');

Route::post('/add-equipment-attachment', 'Equipment\MaintainanceCalibrationLogController@add_equipment_attachment')->name('add_equipment_attachment')->middleware('haspermission:Equipment.components.Maintainance-Log.Edit');

Route::post('/verification-log/{id}', 'Equipment\VerificationLogController@add')->name('add-verification')->middleware('haspermission:Equipment.components.Verification-Log.Add');
Route::post('/edit/verification-log/{id}', 'Equipment\VerificationLogController@edit')->name('edit-verification')->middleware('haspermission:Equipment.components.Verification-Log.Edit');
Route::get('/delete/verification-log/{id}', 'Equipment\VerificationLogController@delete')->name('delete-verification')->middleware('haspermission:Equipment.components.Verification-Log.Delete');
Route::post('/dispose/equipment/{id}', 'Equipment\EquipmentController@dispose')->name('dispose-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Delete');
Route::get('/revert/equipment/{id}', 'Equipment\EquipmentController@revert')->name('revert-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Delete');
Route::post('/delete/part-repaired', 'Equipment\MaintainanceCalibrationLogController@delete')->name('delete-part-repaired')->middleware('haspermission:Equipment.components.Repair-Log.Delete');
Route::post('/delete/log', 'Equipment\MaintainanceCalibrationLogController@delete_logs')->name('delete-logs')->middleware('haspermission:Equipment.components.Repair-Log.Delete');

Route::post('/add/equipment/frequency', 'Equipment\EquipmentController@addEquipmentNotification')->name('add-equipment-frequency')->middleware('haspermission:Equipment.components.Equipment-List.Edit');
Route::post('/delete/equipment/notification', 'Equipment\EquipmentController@deleteEquipmentNotification')->name('delete-equipment-frequency')->middleware('haspermission:Equipment.components.Equipment-List.Edit');


// Equipment Disposal Workflow Routes
Route::get('/equipment-disposal/workflows', [EquipmentAppController::class, 'workflowManager'])->name('equipment.disposal.workflow.index')->middleware(['auth', 'haspermission:Equipment.components.Equipment-Disposal.View']);
Route::get('/equipment-disposal/workflows/create', [EquipmentAppController::class, 'workflowForm'])->name('equipment.disposal.workflow.create')->middleware(['auth', 'haspermission:Equipment.components.Equipment-Disposal.View']);
Route::get('/equipment-disposal/workflows/{id}/edit', [EquipmentAppController::class, 'workflowForm'])->name('equipment.disposal.workflow.edit')->middleware(['auth', 'haspermission:Equipment.components.Equipment-Disposal.View']);

// Equipment Disposal Routes
Route::get('/equipment-disposal', [EquipmentAppController::class, 'disposalManager'])->name('equipment-disposal-home')->middleware(['auth', 'haspermission:Equipment.components.Equipment-Disposal.View']);
Route::get('/equipment-disposal/{disposalId}', [EquipmentAppController::class, 'disposalDetail'])->name('equipment-disposal-detail')->middleware(['auth', 'haspermission:Equipment.components.Equipment-Disposal.View']);
Route::get('/equipment-disposal/{disposalId}/download-report', 'Equipment\DisposalController@downloadReport')->name('equipment-disposal-download-report')->middleware(['auth', 'haspermission:Equipment.components.Equipment-Disposal.View']);


//############################################EQUIPMENT##########################################################

//############################################SUPPLIER###########################################################
Route::get('/inventory-suppliers', 'SupplierController@index')->name('inventory-suppliers')->middleware('haspermission:Inventory.components.Suppliers.View');
Route::post('/inventory-suppliers', 'SupplierController@add')->name('add-inventory-supplier')->middleware('haspermission:Inventory.components.Suppliers.Add');
Route::get('/inventory-supplier/{id}', 'SupplierController@show')->name('show-inventory-supplier');
Route::post('/inventory-supplier/{id}', 'SupplierController@edit')->name('edit-inventory-supplier')->middleware('haspermission:Inventory.components.Suppliers.Edit');
Route::post('/inventory-supplier/{id}/delete', 'SupplierController@delete_supplier')->name('delete-inventory-supplier')->middleware('haspermission:Inventory.components.Suppliers.Edit');

Route::post('/add-supplier-category/{supplier}', 'SupplierCategoryController@add')->name('add-supplier-category')->middleware('haspermission:Inventory.components.Suppliers.Add');
Route::post('/add-supplier-main-category/{supplier_id}', 'SupplierByCategoryController@add')->name('add-supplier-main-category')->middleware('haspermission:Inventory.components.Suppliers.Add');
Route::post('/add-supplier-to-inventory/{itemID}', 'SupplierController@add_supplier_to_inventory')->name('add-supplier-to-inventory')->middleware('haspermission:Inventory.components.Suppliers.Add');
Route::post('/delete-supplier-category/{id}', 'SupplierCategoryController@destroy')->name('delete-supplier-category')->middleware('haspermission:Inventory.components.Suppliers.Edit');
Route::post('/delete-supplier-main-category/{id}', 'SupplierByCategoryController@remove')->name('delete-supplier-main-category')->middleware('haspermission:Inventory.components.Suppliers.Edit');
Route::post('/change-category-image/{id}', 'SupplierCategoryController@change_image')->name('change-category-image');
Route::post('/supplier-rating', 'InventorySupplierRatingController@add')->name('supplier-rating')->middleware('haspermission:Inventory.components.Suppliers.Edit');

Route::post('/update-supplier-contact/{supplier_id}/{contact_id?}', 'SupplierContactController@update')->name('update-supplier-contact')->middleware('haspermission:Inventory.components.Suppliers.Edit');
Route::post('/remove-supplier-contact/{contact_id}', 'SupplierContactController@destroy')->name('remove-supplier-contact')->middleware('haspermission:Inventory.components.Suppliers.Edit');
//############################################SUPPLIER##########################################################

//############################################SUPPLIER CONTRACTS##########################################################
Route::post('/create-supplier-contract/{supplier}', 'SupplierContractController@modify')->name('create-supplier-contract');
Route::post('/edit-supplier-contract/{supplier}/{id}', 'SupplierContractController@modify')->name('edit-supplier-contract');
//############################################SUPPLIER CONTRACTS##########################################################

//############################################ORDERS############################################################
Route::post('/create-order/{supplier?}', 'InventoryOrderController@add')->name('create-order');
Route::post('/edit-order/{supplier}/{order_id}', 'InventoryOrderController@edit')->name('edit-order');
Route::post('/edit-order/{supplier}/{order_id}', 'InventoryOrderController@edit')->name('edit-order');
Route::get('/server-side/{field}/{fieldID}', 'InventoryOrderController@server_side')->name('server-side-orders');
Route::get('/server-side/{field}/{fieldID}/{type?}/requisition', 'InventoryOrderController@server_side_po')->name('server-side-purchase-orders');
Route::post('/delete-order-items/{order_item}', 'InventoryOrderItemController@delete')->name('delete-order-item');
Route::get('/get-order-items/{field}/{fieldID}', 'InventoryOrderItemController@getItems')->name('get-order-items');
Route::post('/accept-order-items/{order_id}', 'InventoryOrderItemToInventoryItemController@acceptItems')->name('accept-order-items')->middleware('haspermission:Inventory.components.Inventory-Movement.Edit');
//############################################ORDERS############################################################

//############################################CUSTOMERS##########################################################
Route::prefix('crm/v2')->middleware(['auth', 'haspermission:CRM.permission'])->name('crm.v2.')->group(function () {
    Route::get('/', function () {
        return view('layouts.crm.v2-home');
    })->name('home');
    Route::get('/customers', fn () => view('layouts.crm.v2-customers'))->name('customers');
});

Route::get('/crm/customer/{id}', function ($id) {
    $customerId = (int) $id;
    $customer = \App\Models\CRM\CRMCustomer::find($customerId);

    if (!$customer) {
        abort(404);
    }

    return view('layouts.crm.v2-customer-show', [
        'customerId' => $customerId,
        'customer' => $customer,
    ]);
})->middleware(['auth', 'haspermission:CRM.components.Customer-List.View'])->name('crm.customer.show');

Route::get('/crm-dashboard', '\\' . \App\Livewire\Crm\CrmDashboard::class)
    ->name('crm-dashboard')
    ->middleware('auth')
    ->middleware('haspermission:CRM.permission');

Route::get('/crm-home', 'CRM\CRMCustomerController@index')->name('customers-list')->middleware('haspermission:CRM.permission');
Route::post('/fetch-client-quotes', 'CRM\CRMCustomerController@fetch_client_quote')->name('fetch-client-qoutes')->middleware('haspermission:CRM.components.Customer-List.View');
Route::get('/crm-home-config', 'CRM\CRMCustomerController@checkConfig')->name('add-config-customer')->middleware('haspermission:CRM.permission');
Route::post('/customers', 'CRM\CRMCustomerController@add')->name('add-customers')->middleware('haspermission:CRM.components.Customer-List.Add');
Route::get('/customer/{id}', 'CRM\CRMCustomerController@show')->name('show-customer')->middleware('haspermission:CRM.components.Customer-List.View');
Route::post('/customer/{id}', 'CRM\CRMCustomerController@edit')->name('edit-customer')->middleware('haspermission:CRM.components.Customer-List.Edit');
Route::post('/customer/{id}/label', 'CRM\CRMCustomerController@edit_label')->name('change-client-label-name')->middleware('haspermission:CRM.components.Customer-List.Edit');
Route::post('/delete-customer', 'CRM\CRMCustomerController@delete_customer')->name('delete_customer')->middleware('haspermission:CRM.components.Customer-List.Delete');

Route::post('/add/customer-certification/{id}', 'CRM\CustomerCertificationController@add')->name('add-customer-certification')->middleware('haspermission:CRM.components.Certificates.Add');
Route::post('/edit/customer-certification/{id}', 'CRM\CustomerCertificationController@edit')->name('edit-customer-certification')->middleware('haspermission:CRM.components.Certificates.Edit');
Route::post('/delete/customer-certification/{id}', 'CRM\CustomerCertificationController@delete')->name('delete-customer-certification')->middleware('haspermission:CRM.components.Certificates.Delete');

Route::get('/complaint-type/home', 'CRM\Complaint\ComplaintTypeController@index')->name('complaint-type-home')->middleware('haspermission:CRM.components.Complaint Type.View');
Route::post('/edit/complaint-type/{id}', 'CRM\Complaint\ComplaintTypeController@edit')->name('edit-complaint-type')->middleware('haspermission:CRM.components.Complaint Type.Edit');
Route::post('/add/complaint-type', 'CRM\Complaint\ComplaintTypeController@add')->name('add-complaint-type')->middleware('haspermission:CRM.components.Complaint Type.Add');

// Old complaint workflow route - redirect to new Livewire route
Route::get('/complaint/{stage}', function ($stage) {
    return redirect()->route('crm.complaints-manager', ['stage' => $stage]);
})->name('complaint-workflow')->middleware('haspermission:CRM.components.Complaints.View');
Route::post('/add/open-complaint', 'CRM\Complaint\ComplaintController@add')->name('add-complaint')->middleware('haspermission:CRM.components.Open Complaints.Add');
Route::post('/add-open-complaint/customer', 'CRM\Complaint\ComplaintController@customer_add')->name('customer-add-complaint')->middleware('haspermission:CRM.components.Open Complaints.Add');
Route::post('/edit-complaint/{id}', 'CRM\Complaint\ComplaintController@edit')->name('edit-complaint')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::get('/show-complaint/{id}', 'CRM\Complaint\ComplaintController@show')->name('show-complaint')->middleware('haspermission:CRM.components.Complaints.View');
Route::post('/add/complaint-notes/{id}', 'CRM\Complaint\ComplaintNotesController@add')->name('add-notes')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/edit/complaint-notes/{id}', 'CRM\Complaint\ComplaintNotesController@edit')->name('edit-notes')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/add/complaint-attachment/{id}', 'CRM\Complaint\ComplaintAttachmentController@add')->name('add-attachment')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/edit/complaint-attachment/{id}', 'CRM\Complaint\ComplaintAttachmentController@edit')->name('edit-attachment')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/approve-complaint/{id}', 'CRM\Complaint\ComplaintWorkflowController@approve_next')->name('approve-complaint')->middleware('haspermission:CRM.components.Complaints Approval.Edit');
Route::post('/reverse-complaint/{id}', 'CRM\Complaint\ComplaintWorkflowController@reverse_approval')->name('reverse-complaint')->middleware('haspermission:CRM.components.Complaints Approval.Delete');
Route::post('/reject-complaint/{id}', 'CRM\Complaint\ComplaintWorkflowController@reject_complaint')->name('reject-complaint')->middleware('haspermission:CRM.components.Complaints Approval.Delete');
Route::post('/add/complaint-resolution/{id}', 'CRM\Complaint\ComplaintResolutionController@add')->name('add-resolution')->middleware('haspermission:CRM.components.Complaints Resolution.Add');
Route::post('/edit/complaint-resolution/{id}', 'CRM\Complaint\ComplaintResolutionController@edit')->name('edit-resolution')->middleware('haspermission:CRM.components.Complaints Resolution.Edit');
Route::get('/show/complaint/{id}', 'CRM\Complaint\ComplaintController@show_all')->name('complaint-show')->middleware('haspermission:CRM.components.Complaints.View');

Route::get('/customer-feedback/home', 'CRM\CustomerFeedbackController@index')->name('feedback-home')->middleware('haspermission:CRM.components.Feedbacks.View');
Route::get('/customer-feedback/configuration', '\\' . \App\Livewire\Crm\Feedback\EvaluationMetricManager::class)
    ->name('feedback-config')
    ->middleware('auth')
    ->middleware('haspermission:CRM.components.Feedbacks.View');
Route::post('/add/customer-feedback', 'CRM\CustomerFeedbackController@add')->name('add-feedback')->middleware('haspermission:CRM.components.Customer Feedback.Add');
Route::post('/add-feedback/customer', 'CRM\CustomerFeedbackController@customer_add')->name('customer-add-feedback')->middleware('haspermission:CRM.components.Customer Feedback.Add');
Route::post('/edit/customer-feedback/{id}', 'CRM\CustomerFeedbackController@edit')->name('edit-feedback')->middleware('haspermission:CRM.components.Feedbacks.Edit');

Route::post('/request-resolution-approval/{id}', 'CRM\Complaint\ComplaintWorkflowController@request_resolution_approve')->name('request-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Add');
Route::post('/reverse-resolution/{id}', 'CRM\Complaint\ComplaintWorkflowController@reverse_resolution')->name('reverse-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Edit');
Route::post('/reject-resolution/{id}', 'CRM\Complaint\ComplaintWorkflowController@reject_resolution')->name('reject-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Delete');
Route::post('/approve-resolution/{id}', 'CRM\Complaint\ComplaintWorkflowController@approve_resolution')->name('approve-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Edit');

Route::post('/company-units/{cust_id}', 'CRM\CRMCompanyUnitController@add')->name('add-company-units')->middleware('haspermission:CRM.components.Company-Units.Add');
Route::post('/company-unit/{id}/{cust_id}', 'CRM\CRMCompanyUnitController@edit')->name('edit-company-unit')->middleware('haspermission:CRM.components.Company-Units.Edit');

Route::post('/sample-point', 'CRM\SamplePointController@add')->name('add-sample-point')->middleware('haspermission:CRM.components.Sample-Points.Add');
Route::post('/sample-point/{id}', 'CRM\SamplePointController@edit')->name('edit-sample-point')->middleware('haspermission:CRM.components.Sample-Points.Edit');

Route::post('/customer-product', 'CRM\CompanyProductController@add')->name('add-customer-product')->middleware('haspermission:CRM.components.Products.Add');
Route::post('/customer-product/edit/{id?}', 'CRM\CompanyProductController@edit')->name('edit-customer-product')->middleware('haspermission:CRM.components.Products.Edit');

Route::post('/company-contacts/{cust_id}', 'CRM\CustomerContactController@add')->name('add-company-contacts')->middleware('haspermission:CRM.components.Contacts.Add');
Route::post('/company-contact/{id}/{cust_id}', 'CRM\CustomerContactController@edit')->name('edit-company-contact')->middleware('haspermission:CRM.components.Contacts.Edit');
Route::post('/customer-contact/add', 'CRM\CustomerContactController@addAjax')->name('customer-contact-add-ajax')->middleware('haspermission:CRM.components.Contacts.Add');
Route::get('/get/customer/ajax/{id}', 'CRM\CustomerContactController@getCustomerUnits')->name('getCustomerUnits')->middleware('haspermission:CRM.components.Contacts.View');

Route::get('/fetch-customer-contacts/{id}', 'CRM\CustomerContactController@get_customer_client')->name('get_customer_client')->middleware('haspermission:CRM.components.Contacts.View');
Route::get('/validate-Crm-Customer/Name/{name}/Ajax', 'CRM\CRMCustomerController@validateCrmCustomerNameAjax')->name('validateCrmCustomerNameAjax')->middleware('haspermission:CRM.components.Customer-List.View');
Route::get('/crm-batch-reports', 'CRM\CRMCustomerController@batch_reports')->name('crm-batch-reports')->middleware('haspermission:CRM.components.Results.View');
Route::post('/crm-batch-report/data', 'CRM\CRMCustomerController@batch_report_data')->name('crm.batch-report.data')->middleware('haspermission:CRM.components.Results.View');
Route::post('/crm-batch-report/export', 'CRM\CRMCustomerController@batch_report_export')->name('crm.batch-report.export')->middleware('haspermission:CRM.components.Results.View');
//############################################SUPPLIER##########################################################

//############################################## QUALIFICATIONS #############################################################
Route::get('/qualification-home', 'Lab\QualificationsController@index')->name('qualification-home')->middleware('haspermission:Laboratory.components.Qc Sample.View');
Route::post('/edit/qualification/{id}', 'Lab\QualificationsController@edit')->name('edit-qualification')->middleware('haspermission:Laboratory.components.Qc Sample.Edit');
Route::post('/add/qualification', 'Lab\QualificationsController@add')->name('add-qualification')->middleware('haspermission:Laboratory.components.Qc Sample.Add');
//############################################## END UALIFICATIONS #############################################################

//###################################AJAX LINKS#######################################
Route::get('/analysis-types/{id}', 'AnalysisTypeController@by_sample_id')->name('api-analysis-types-by-sample')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::get('/missing_analysis_parameters_by_sample_code', 'SampleWorkFlowController@missing_analysis_parameters_by_sample_code')->name('missing_analysis_parameters_by_sample_code')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/remove-analyte-from-captured-result', 'SampleWorkFlowController@remove_analyte_from_captured_result')->name('remove-analyte-from-captured-result')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::post('/fetch/results-remark', 'SampleWorkFlowController@fetch_results_remark')->name('fetch_results_remark')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/get-available-methods', 'SampleWorkFlowController@getAvailableMethods')->name('get-available-methods')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/capture-results-save', 'SampleWorkFlowController@saveCaptureResults')->name('capture-results-save')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('/get-customer-contacts/{type}/{customer_id}', 'CRM\CustomerContactController@get_contacts')->name('get-customer-contacts')->middleware('haspermission:CRM.components.Contacts.View');
Route::get('/stock-transfer-json', 'StockTransferController@getJson')->name('stock-transfer-json');
Route::get('/get-material-type-states', 'StockTransferController@getMaterialTypeStates')->name('get-material-type-states');
Route::get('/get-store-slots-by-item/{item}', 'InventoryStoreController@store_slots_by_item')->name('get-store-slots-by-item');
//###################################AJAX LINKS#######################################

//###################################AUDIT TRAIL#######################################
Route::get('/audit-logs', 'AuditController@index')->name('get-audit-logs')->middleware('haspermission:Personnel.components.Audit Trail.View');
Route::get('/server-side-audit_logs/{user_id?}', 'AuditController@server_side')->name('server-side-audit_logs')->middleware('haspermission:Personnel.components.Audit Trail.View');
Route::get('/server-side-audit_log/{id}/details', 'AuditController@server_side_details')->name('server-side-audit_logs-details')->middleware('haspermission:Personnel.components.Audit Trail.View');
//###################################AUDIT TRAIL#######################################

//###################################RISK MANAGEMENT#######################################
Route::prefix('risk')->name('risk.')->middleware(['auth'])->group(function () {
  Route::get('/', 'RiskManagement\RiskDashboardController@index')->name('dashboard')->middleware('haspermission:Risk-Management.components.Risk Dashboard.View');

  Route::prefix('config')->name('config.')->middleware('haspermission:Risk-Management.components.Risks.View')->group(function () {
    Route::get('/risk-categories', 'RiskManagement\RiskConfigController@riskCategories')->name('risk-categories');
    Route::get('/risk-sources', 'RiskManagement\RiskConfigController@riskSources')->name('risk-sources');
    Route::get('/risk-statuses', 'RiskManagement\RiskConfigController@riskStatuses')->name('risk-statuses');
    Route::get('/treatment-types', 'RiskManagement\RiskConfigController@treatmentTypes')->name('treatment-types');
    Route::get('/workflow-approvers', 'RiskManagement\RiskConfigController@workflowApprovers')->name('workflow-approvers');
  });

  Route::prefix('assessment')->name('assessment.')->middleware('haspermission:Risk-Management.components.Risks.View')->group(function () {
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

  Route::prefix('settings')->name('settings.')->middleware('haspermission:Risk-Management.components.Risks.View')->group(function () {
    Route::get('/', 'RiskManagement\RiskConfigurationController@index')->name('index');
    Route::post('/', 'RiskManagement\RiskConfigurationController@store')->name('store');
    Route::post('/reorder', 'RiskManagement\RiskConfigurationController@reorder')->name('reorder');
    Route::get('/{optionType}', 'RiskManagement\RiskConfigurationController@show')->where('optionType', '[a-zA-Z0-9_-]+')->name('show');
    Route::put('/options/{id}', 'RiskManagement\RiskConfigurationController@update')->name('update');
    Route::delete('/options/{id}', 'RiskManagement\RiskConfigurationController@destroy')->name('destroy');
  });

  Route::prefix('risks')->name('risks.')->group(function () {
    Route::get('/', 'RiskManagement\RiskManagementController@index')->name('index')->middleware('haspermission:Risk-Management.components.Risks.View');
    Route::get('/create', 'RiskManagement\RiskManagementController@create')->name('create')->middleware('haspermission:Risk-Management.components.Risks.Add');
    Route::post('/', 'RiskManagement\RiskManagementController@store')->name('store')->middleware('haspermission:Risk-Management.components.Risks.Add');
    Route::get('/{id}', 'RiskManagement\RiskManagementController@show')->name('show')->middleware('haspermission:Risk-Management.components.Risks.View');
    Route::get('/{id}/edit', 'RiskManagement\RiskManagementController@edit')->name('edit')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::put('/{id}', 'RiskManagement\RiskManagementController@update')->name('update')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::delete('/{id}', 'RiskManagement\RiskManagementController@destroy')->name('destroy')->middleware('haspermission:Risk-Management.components.Risks.Delete');
    Route::post('/{id}/change-status', 'RiskManagement\RiskManagementController@changeStatus')->name('change-status')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/approve-next-step', 'RiskManagement\RiskManagementController@approveToNextStep')->name('approve-next-step')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/assessment', 'RiskManagement\RiskManagementController@storeAssessment')->name('assessment.store')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/evaluation', 'RiskManagement\RiskManagementController@storeEvaluation')->name('evaluation.store')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/treatment-plan', 'RiskManagement\RiskManagementController@storeTreatmentPlan')->name('treatment-plan.store')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::get('/{riskId}/treatment-plan/{treatmentPlanId}', 'RiskManagement\RiskManagementController@showTreatmentPlan')->name('treatment-plan.show')->middleware('haspermission:Risk-Management.components.Risks.View');
    Route::put('/{riskId}/treatment-plan/{treatmentPlanId}', 'RiskManagement\RiskManagementController@updateTreatmentPlan')->name('treatment-plan.update')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{riskId}/treatment-plan/{treatmentPlanId}/attachments/upload', 'RiskManagement\RiskManagementController@uploadTreatmentPlanAttachment')->name('treatment-plan.attachments.upload')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::delete('/treatment-plan/attachments/{attachmentId}', 'RiskManagement\RiskManagementController@deleteTreatmentPlanAttachment')->name('treatment-plan.attachments.delete')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/review', 'RiskManagement\RiskManagementController@storeReview')->name('review.store')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::put('/{riskId}/review/{reviewId}', 'RiskManagement\RiskManagementController@updateReview')->name('review.update')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::put('/{id}/closure-justification', 'RiskManagement\RiskManagementController@updateClosureJustification')->name('closure-justification.update')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/close', 'RiskManagement\RiskManagementController@closeRisk')->name('close')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::post('/{id}/attachments/upload', 'RiskManagement\RiskManagementController@uploadAttachment')->name('attachments.upload')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::get('/attachments/{attachmentId}/download', 'RiskManagement\RiskManagementController@downloadAttachment')->name('attachments.download')->middleware('haspermission:Risk-Management.components.Risks.View');
    Route::delete('/attachments/{attachmentId}', 'RiskManagement\RiskManagementController@deleteAttachment')->name('attachments.delete')->middleware('haspermission:Risk-Management.components.Risks.Delete');
    Route::get('/server-side', 'RiskManagement\RiskManagementController@serverSide')->name('server-side')->middleware('haspermission:Risk-Management.components.Risks.View');
    Route::post('/{id}/process-links', 'RiskManagement\RiskManagementController@storeProcessLink')->name('process-links.store')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::put('/process-links/{id}', 'RiskManagement\RiskManagementController@updateProcessLink')->name('process-links.update')->middleware('haspermission:Risk-Management.components.Risks.Edit');
    Route::delete('/process-links/{id}', 'RiskManagement\RiskManagementController@destroyProcessLink')->name('process-links.destroy')->middleware('haspermission:Risk-Management.components.Risks.Edit');
  });
});
//###################################RISK MANAGEMENT#######################################

//###################################AUDIT MANAGEMENT#######################################
Route::prefix('audit')->name('audit.')->middleware(['auth', 'haspermission:Audit.permission'])->group(function () {
    Route::get('/', 'AuditModule\AuditDashboardController@index')->name('dashboard');

    // Audit Management
    Route::prefix('audits')->name('audits.')->middleware('haspermission:Audit.components.Audits.View')->group(function () {
        Route::get('/', 'AuditModule\AuditManagementController@index')->name('index');
        Route::get('/create', 'AuditModule\AuditManagementController@create')->name('create')->middleware('haspermission:Audit.components.Audits.Add');
        Route::post('/', 'AuditModule\AuditManagementController@store')->name('store')->middleware('haspermission:Audit.components.Audits.Add');
        Route::get('/{id}', 'AuditModule\AuditManagementController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\AuditManagementController@edit')->name('edit')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::put('/{id}', 'AuditModule\AuditManagementController@update')->name('update')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::delete('/{id}', 'AuditModule\AuditManagementController@destroy')->name('destroy')->middleware('haspermission:Audit.components.Audits.Delete');
        Route::post('/{id}/change-status', 'AuditModule\AuditManagementController@changeStatus')->name('change-status')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::post('/{id}/approve-next-step', 'AuditModule\AuditManagementController@approveToNextStep')->name('approve-next-step')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::get('/{id}/pdf', 'AuditModule\AuditManagementController@generatePdf')->name('pdf');
        Route::post('/{id}/attachments/upload', 'AuditModule\AuditManagementController@uploadAttachment')->name('attachments.upload')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\AuditManagementController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\AuditManagementController@deleteAttachment')->name('attachments.delete')->middleware('haspermission:Audit.components.Audits.Delete');
        Route::post('/{id}/team-members', 'AuditModule\AuditManagementController@addTeamMember')->name('team-members.store')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::put('/team-members/{teamMemberId}', 'AuditModule\AuditManagementController@updateTeamMember')->name('team-members.update')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::delete('/team-members/{teamMemberId}', 'AuditModule\AuditManagementController@removeTeamMember')->name('team-members.destroy')->middleware('haspermission:Audit.components.Audits.Delete');
        Route::post('/{id}/findings', 'AuditModule\AuditManagementController@storeFinding')->name('findings.store')->middleware('haspermission:Audit.components.Audits.Edit');
        Route::put('/{id}/findings/{findingId}', 'AuditModule\AuditManagementController@updateFinding')->name('findings.update')->middleware('haspermission:Audit.components.Audits.Edit');
    });

    // Non-Conformance Management
    Route::prefix('non-conformances')->name('nc.')->middleware('haspermission:Audit.components.Non-Conformances.View')->group(function () {
        Route::get('/', 'AuditModule\NonConformanceController@index')->name('index');
        Route::get('/create', 'AuditModule\NonConformanceController@create')->name('create')->middleware('haspermission:Audit.components.Non-Conformances.Add');
        Route::post('/', 'AuditModule\NonConformanceController@store')->name('store')->middleware('haspermission:Audit.components.Non-Conformances.Add');
        Route::get('/{id}', 'AuditModule\NonConformanceController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\NonConformanceController@edit')->name('edit')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::put('/{id}', 'AuditModule\NonConformanceController@update')->name('update')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::delete('/{id}', 'AuditModule\NonConformanceController@destroy')->name('destroy')->middleware('haspermission:Audit.components.Non-Conformances.Delete');
        Route::post('/{id}/change-status', 'AuditModule\NonConformanceController@changeStatus')->name('change-status')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::get('/{id}/pdf', 'AuditModule\NonConformanceController@generatePdf')->name('pdf');
        Route::post('/{id}/attachments/upload', 'AuditModule\NonConformanceController@uploadAttachment')->name('attachments.upload')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\NonConformanceController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\NonConformanceController@deleteAttachment')->name('attachments.delete')->middleware('haspermission:Audit.components.Non-Conformances.Delete');
        Route::post('/{id}/rca', 'AuditModule\NonConformanceController@storeRca')->name('rca.store')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::get('/rca/{rcaId}/edit', 'AuditModule\NonConformanceController@editRca')->name('rca.edit')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::put('/rca/{rcaId}', 'AuditModule\NonConformanceController@updateRca')->name('rca.update')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::delete('/rca/{rcaId}', 'AuditModule\NonConformanceController@deleteRca')->name('rca.delete')->middleware('haspermission:Audit.components.Non-Conformances.Delete');
        Route::post('/{id}/capa', 'AuditModule\NonConformanceController@storeCapa')->name('capa.store')->middleware('haspermission:Audit.components.Non-Conformances.Edit');
        Route::get('/search/samples', 'AuditModule\NonConformanceController@searchSamples')->name('search.samples');
        Route::get('/search/equipment', 'AuditModule\NonConformanceController@searchEquipment')->name('search.equipment');
        Route::get('/search/methods', 'AuditModule\NonConformanceController@searchMethods')->name('search.methods');
    });

    // Corrective Actions
    Route::prefix('corrective-actions')->name('corrective-actions.')->middleware('haspermission:Audit.components.Corrective Actions.View')->group(function () {
        Route::get('/', 'AuditModule\CorrectiveActionController@index')->name('index');
        Route::get('/create', 'AuditModule\CorrectiveActionController@create')->name('create')->middleware('haspermission:Audit.components.Corrective Actions.Add');
        Route::post('/', 'AuditModule\CorrectiveActionController@store')->name('store')->middleware('haspermission:Audit.components.Corrective Actions.Add');
        Route::get('/{id}', 'AuditModule\CorrectiveActionController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\CorrectiveActionController@edit')->name('edit')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::put('/{id}', 'AuditModule\CorrectiveActionController@update')->name('update')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::delete('/{id}', 'AuditModule\CorrectiveActionController@destroy')->name('destroy')->middleware('haspermission:Audit.components.Corrective Actions.Delete');
        Route::post('/{id}/change-status', 'AuditModule\CorrectiveActionController@changeStatus')->name('change-status')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::post('/{id}/implement', 'AuditModule\CorrectiveActionController@implement')->name('implement')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::post('/{id}/verify', 'AuditModule\CorrectiveActionController@verify')->name('verify')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::post('/{id}/attachments/upload', 'AuditModule\CorrectiveActionController@uploadAttachment')->name('attachments.upload')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\CorrectiveActionController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\CorrectiveActionController@deleteAttachment')->name('attachments.delete')->middleware('haspermission:Audit.components.Corrective Actions.Delete');
    });

    // CAPA alias routes
    Route::prefix('capa')->name('capa.')->middleware('haspermission:Audit.components.Corrective Actions.View')->group(function () {
        Route::get('/', 'AuditModule\CorrectiveActionController@index')->name('index');
        Route::get('/create', 'AuditModule\CorrectiveActionController@create')->name('create')->middleware('haspermission:Audit.components.Corrective Actions.Add');
        Route::post('/', 'AuditModule\CorrectiveActionController@store')->name('store')->middleware('haspermission:Audit.components.Corrective Actions.Add');
        Route::get('/{id}', 'AuditModule\CorrectiveActionController@show')->name('show');
        Route::get('/{id}/edit', 'AuditModule\CorrectiveActionController@edit')->name('edit')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::put('/{id}', 'AuditModule\CorrectiveActionController@update')->name('update')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::delete('/{id}', 'AuditModule\CorrectiveActionController@destroy')->name('destroy')->middleware('haspermission:Audit.components.Corrective Actions.Delete');
        Route::post('/{id}/change-status', 'AuditModule\CorrectiveActionController@changeStatus')->name('change-status')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::post('/{id}/implement', 'AuditModule\CorrectiveActionController@implement')->name('implement')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::post('/{id}/verify', 'AuditModule\CorrectiveActionController@verify')->name('verify')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::post('/{id}/attachments/upload', 'AuditModule\CorrectiveActionController@uploadAttachment')->name('attachments.upload')->middleware('haspermission:Audit.components.Corrective Actions.Edit');
        Route::get('/attachments/{attachmentId}/download', 'AuditModule\CorrectiveActionController@downloadAttachment')->name('attachments.download');
        Route::delete('/attachments/{attachmentId}', 'AuditModule\CorrectiveActionController@deleteAttachment')->name('attachments.delete')->middleware('haspermission:Audit.components.Corrective Actions.Delete');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('haspermission:Audit.components.Reports.View')->group(function () {
        Route::get('/', 'AuditModule\AuditReportController@index')->name('index');
        Route::get('/audit-summary', 'AuditModule\AuditReportController@auditSummary')->name('audit-summary');
        Route::get('/nc-register', 'AuditModule\AuditReportController@ncRegister')->name('nc-register');
        Route::get('/capa-status', 'AuditModule\AuditReportController@capaStatus')->name('capa-status');
        Route::get('/advanced-statistics', 'AuditModule\AuditReportController@advancedStatistics')->name('advanced-statistics');
        Route::get('/export/{type}', 'AuditModule\AuditReportController@export')->name('export');
    });

    // Configuration
    Route::prefix('config')->name('config.')->middleware('haspermission:Audit.components.Configuration.View')->group(function () {
        Route::get('/approval-config', function () {
            return view('layouts.audit.config.approval-config');
        })->name('approval-config');

        Route::get('/verification-results', 'AuditModule\AuditConfigController@verificationResults')->name('verification-results');
        Route::post('/verification-results', 'AuditModule\AuditConfigController@storeVerificationResult')->name('verification-results.store')->middleware('haspermission:Audit.components.Configuration.Edit');
        Route::get('/verification-results/{id}', 'AuditModule\AuditConfigController@getVerificationResult')->name('verification-results.get');
        Route::put('/verification-results/{id}', 'AuditModule\AuditConfigController@updateVerificationResult')->name('verification-results.update')->middleware('haspermission:Audit.components.Configuration.Edit');

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
            Route::get('/create', 'AuditModule\AuditEmailTemplateController@create')->name('create')->middleware('haspermission:Audit.components.Configuration.Add');
            Route::post('/', 'AuditModule\AuditEmailTemplateController@store')->name('store')->middleware('haspermission:Audit.components.Configuration.Add');
            Route::get('/{id}/edit', 'AuditModule\AuditEmailTemplateController@edit')->name('edit')->middleware('haspermission:Audit.components.Configuration.Edit');
            Route::put('/{id}', 'AuditModule\AuditEmailTemplateController@update')->name('update')->middleware('haspermission:Audit.components.Configuration.Edit');
            Route::delete('/{id}', 'AuditModule\AuditEmailTemplateController@destroy')->name('destroy')->middleware('haspermission:Audit.components.Configuration.Delete');
            Route::get('/{id}/preview', 'AuditModule\AuditEmailTemplateController@preview')->name('preview');
            Route::post('/{id}/test', 'AuditModule\AuditEmailTemplateController@test')->name('test')->middleware('haspermission:Audit.components.Configuration.Edit');
        });
    });
});
//###################################AUDIT MANAGEMENT#######################################

//###################################HELP DESK#######################################
Route::prefix('tickets')->name('tickets.')->middleware(['auth', 'haspermission:Helpdesk.permission'])->group(function () {
    Route::get('/dashboard', 'Ticket\TicketController@dashboard')->name('dashboard')->middleware('haspermission:Helpdesk.components.Dashboard.View');

    Route::prefix('categories')->middleware('haspermission:Helpdesk.components.Categories.View')->group(function () {
        Route::get('/list', 'Ticket\TicketController@listCategories')->name('categories.list');
        Route::get('/', 'Ticket\TicketController@categories')->name('categories');
        Route::post('/', 'Ticket\TicketController@storeCategory')->name('categories.store')->middleware('haspermission:Helpdesk.components.Categories.Add');
        Route::put('/{id}', 'Ticket\TicketController@updateCategory')->name('categories.update')->middleware('haspermission:Helpdesk.components.Categories.Edit');
        Route::post('/{id}/toggle-status', 'Ticket\TicketController@toggleCategoryStatus')->name('categories.toggle-status')->middleware('haspermission:Helpdesk.components.Categories.Edit');
    });
    Route::get('/deleted', 'Ticket\TicketController@deleted')->name('deleted')->middleware('haspermission:Helpdesk.components.Archived Tickets.View');

    Route::get('/', 'Ticket\TicketController@myTickets')->name('index')->middleware('haspermission:Helpdesk.components.Tickets.View');
    Route::get('/create', 'Ticket\TicketController@create')->name('create')->middleware('haspermission:Helpdesk.components.Tickets.Add');
    Route::post('/', 'Ticket\TicketController@store')->name('store')->middleware('haspermission:Helpdesk.components.Tickets.Add');
    Route::get('/{id}', 'Ticket\TicketController@show')->name('show')->middleware('haspermission:Helpdesk.components.Tickets.View');
    Route::delete('/{id}', 'Ticket\TicketController@destroy')->name('destroy')->middleware('haspermission:Helpdesk.components.Tickets.Delete');
    Route::post('/{id}/upload', 'Ticket\TicketController@uploadFiles')->name('upload')->middleware('haspermission:Helpdesk.components.Tickets.Edit');
    Route::get('/{id}/chat', 'Ticket\TicketController@chat')->name('chat')->middleware('haspermission:Helpdesk.components.Chat.View');
    Route::post('/{id}/chat', 'Ticket\TicketController@sendChatMessage')->name('chat.send')->middleware('haspermission:Helpdesk.components.Chat.Add');
    Route::get('/{id}/chat/messages', 'Ticket\TicketController@getChatMessages')->name('chat.messages')->middleware('haspermission:Helpdesk.components.Chat.View');
});
//###################################HELP DESK#######################################

//###################################REQUISITION TRAIL#######################################
Route::get('/req/{stage}', 'RequisitionController@open_stage')->name('go_to_stage')->middleware('haspermission:Inventory.components.stage.View');
Route::get('/get_req_enitites_server_side/{stage}/{type}', 'RequestEntityController@get_entities_server_side')->name('get_req_enitites_server_side');
Route::post('/make-po-ammendment/{id}/{stage}', 'RequisitionController@make_po_ammendment')->name('make-po-ammendment')->middleware('haspermission:Inventory.components.stage.Delete');

Route::get('/requester_verification_confirmation/{id}', 'RequisitionController@requester_verification_confirmation')->name('requester_verification_confirmation');

Route::post('/req/{stage}/{id}/delete', 'RequisitionController@removeRequestEntity')->name('delete-request-details')->middleware('haspermission:Inventory.components.stage.Delete');
Route::post('/req/{stage}/cloned', 'RequisitionController@clone_entity')->name('clone-request-details')->middleware('haspermission:Inventory.components.stage.Edit');
Route::get('/req/{stage}/{id}/{ammendement?}', 'RequisitionController@show')->name('view-request-details')->middleware('haspermission:Inventory.components.stage.View');
Route::post('/req/{stage}/{id}/{ammendement?}', 'RequisitionController@update')->name('save-request-details')->middleware('haspermission:Inventory.components.stage.Edit');
Route::get('/req-report-generate/{id}/{supply?}', 'ReportGeneratorController@generate_report')->name('req-report-generate');
Route::get('/req-report-generate-pdf/{id}', 'ReportGeneratorController@generate_report_pdf')->name('req-report-generate-pdf');
Route::get('/check-pdf-processing-progress/{id}', 'ReportGeneratorController@check_pdf_processing_progress')->name('check-pdf-processing-progress');

Route::get('/supplier-rfq-pdf/{supplier}/{id}', 'ReportGeneratorController@generate_supplier_pdf')->name('generate-supplier-pdf');

Route::get('/download-request-items/{id}/{isPDF?}', 'ReportGeneratorController@download_items_xlsx')->name('download-request-items');

Route::post('/create-lpo-from-mr/{id}', 'RequisitionController@create_lpo_from_mr')->name('create-lpo-from-mr')->middleware('haspermission:Inventory.components.Purchase Orders.Edit');
Route::post('/change-req-approver/{stage}/{id}', 'RequisitionController@change_req_approver')->name('change-req-approver')->middleware('haspermission:Inventory.components.stage.Edit');

Route::post('/add-extra-charge/{id}', 'RequisitionController@add_extra_charge')->name('add-extra-charge')->middleware('haspermission:Inventory.components.Purchase Orders.Edit');
Route::post('/remove-extra-charge/{id}', 'RequisitionController@remove_extra_charge')->name('remove-extra-charge')->middleware('haspermission:Inventory.components.Purchase Orders.Edit');

Route::post('/reverse-entity-action/{id}', 'RequisitionController@reverse_entity_action')->name('reverse-entity-action');

Route::post('/req/download/{id}/{type}', 'RequisitionController@download')->name('download-requisition-doc');
Route::post('/mark-gr-as-complete/{id}', 'RequisitionController@mark_gr_as_complete')->name('mark-gr-as-complete')->middleware('haspermission:Inventory.components.Goods Receipt.Edit');
Route::post('/submit-bank-details/{id}', 'RequisitionController@submit_bank_details')->name('submit-bank-details')->middleware('haspermission:Inventory.components.Purchase Orders.Edit');
Route::post('/upload-bank-confirmation/{id}', 'RequisitionController@upload_bank_confirmation')->name('upload-bank-confirmation')->middleware('haspermission:Inventory.components.Purchase Orders.Edit');
Route::post('/add-email-body-rfq/{id}', 'RequisitionController@add_email_body_rfq')->name('add-email-body-rfq')->middleware('haspermission:Inventory.components.Request for Quotation.Edit');

// Route::post('/jump-request-to-status/{id}', 'RequisitionController@jump_request_to_status')->name('jump-request-to-status')->middleware('haspermission:Inventory.components.Request for Quotation.Edit');

Route::post('/req-locations-add', 'RequisitionLocationController@add')->name('req-locations-add');
Route::post('/req-locations-remove', 'RequisitionLocationController@remove')->name('req-locations-remove')->middleware('haspermission:Inventory.components.stage.Edit');
//###################################REQUISITION TRAIL#######################################

//##############################################BATCH COMMENTS#######################################
Route::post('/add-batch-comment', 'BatchCommentController@add')->name('add-batch-comment');
Route::post('/edit-batch-comment/{id}', 'BatchCommentController@edit')->name('edit-batch-comment');
//###############################################BATCH COMMENTS#######################################

//##############################################My Approvals#######################################
Route::get('/my-approvals', 'HomeController@my_approvals')->name('my-approvals')->middleware('haspermission:Inventory.components.Approval-Requests.View');
//###############################################My Approvals#######################################

//###################################PERSONNEL LINKS#######################################
Route::get('/personnel-home', 'PersonnelController@index')->name('personnel-home')->middleware('haspermission:Personnel.components.Personnel.View');
Route::get('/personnel-list', 'PersonnelController@personnel_list')->name('personnel-list')->middleware('haspermission:Personnel.components.Personnel.View');
Route::post('/add-personnel/{id}', 'PersonnelController@add')->name('add-personnel')->middleware('haspermission:Personnel.components.Personnel.Edit');
Route::get('/view-personnel/{id}', 'PersonnelController@show_personnel')->name('view-personnel')->middleware('haspermission:Personnel.components.Personnel.View');

Route::get('/organizational-departments', 'PersonnelController@departments')->name('show-organizational-departments')->middleware('haspermission:Personnel.components.Departments.View');
Route::post('/organizational-departments', 'PersonnelController@add_department')->name('add-organizational-department')->middleware('haspermission:Personnel.components.Departments.Add');
Route::post('/personnel-organizational/{id}', 'PersonnelController@edit_department')->name('edit-organizational-department')->middleware('haspermission:Personnel.components.Departments.Edit');

Route::post('/add-personnel-role/{user_id}', 'UserRoleController@add')->name('add-personnel-role')->middleware('haspermission:Personnel.components.Roles.Add');
Route::post('/edit-approval-departments/{user_id}/{role}', 'UserRoleController@edit_departments')->name('edit-approval-departments')->middleware('haspermission:Personnel.components.Roles.Edit');
Route::post('/remove-personnel-role/{id}', 'UserRoleController@remove')->name('remove-personnel-role')->middleware('haspermission:Personnel.components.Roles.Delete');

Route::post('/personnel-state-change/{id}', 'PersonnelController@deactivate_personnel')->name('personnel-state')->middleware('haspermission:Personnel.components.Personnel.Edit');
Route::post('/reset-personnel-password/{id}', 'PersonnelController@reset_personnel_password')->name('reset-personnel')->middleware('haspermission:Personnel.components.Personnel.Edit');

Route::get('/personel/certification-coniguration', 'Personel\CertificationController@index')->name('personnel-certification-home')->middleware('haspermission:Personnel.components.Configurations.View');

Route::post('/add/personnel-certification/{id}', 'Personel\PersonnelCertificationController@add')->name('add-personnel-certification')->middleware('haspermission:Personnel.components.Configurations.Add');
Route::post('/edit/personnel-certification/{id}', 'Personel\PersonnelCertificationController@edit')->name('edit-personnel-certification')->middleware('haspermission:Personnel.components.Configurations.Edit');
Route::post('/delete/personnel-certification/{id}', 'Personel\PersonnelCertificationController@delete')->name('delete-personnel-certification')->middleware('haspermission:Personnel.components.Configurations.Delete');

Route::post('/personnel-managment/add-job-responsibility/{id}', 'ModulePreConfigsController@addResponsibilities')->name('addResponsibilities')->middleware('haspermission:Personnel.components.Configurations.Add');
Route::post('/personnel-managment/edit-job-responsibility/{id}', 'ModulePreConfigsController@editResposibility')->name('editResposibility')->middleware('haspermission:Personnel.components.Configurations.Edit');
Route::get('/personnel-managment/show-job-responsibility/{id}', 'ModulePreConfigsController@showResponsibility')->name('showResponsibility')->middleware('haspermission:Personnel.components.Configurations.View');

Route::get('/personnel-user/profile', 'PersonnelController@user_profile')->name('user_profile')->middleware('haspermission:Personnel.components.Personnel.View');
//###########################
//###################################PERSONNEL LINKS#######################################

//###################################ROLES LINKS#######################################
Route::get('/organizational-roles', 'RoleController@index')->name('organizational-roles')->middleware('haspermission:Personnel.components.Roles.View');
Route::get('/organizational-role/{id}', 'RoleController@show')->name('view-organizational-role')->middleware('haspermission:Personnel.components.Roles.View');
Route::post('/add-organizational-role', 'RoleController@add')->name('add-organizational-role')->middleware('haspermission:Personnel.components.Roles.Add');
Route::post('/edit-organizational-role/{id}', 'RoleController@edit')->name('edit-organizational-role')->middleware('haspermission:Personnel.components.Roles.Edit');
Route::post('/save-role-rights/{id}', 'RoleController@save_roles')->name('save-role-rights')->middleware('haspermission:Personnel.components.Roles.Edit');

Route::post('/add/role-certification/{id}', 'Personel\CertificationController@add_role_certification')->name('add-role-certification')->middleware('haspermission:Personnel.components.Roles.Add');
Route::post('/edit/role-certification/{id}', 'Personel\CertificationController@edit_role_certification')->name('edit-role-certification')->middleware('haspermission:Personnel.components.Roles.Edit');
Route::post('/delete/role-certification/{id}', 'Personel\CertificationController@delete_role_certification')->name('delete-role-certification')->middleware('haspermission:Personnel.components.Roles.Delete');
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
Route::get('/asset-type-home', 'Asset\AssetTypeController@index')->name('asset-type-home')->middleware('haspermission:Equipment.components.Asset-Type.View');
Route::post('/asset-type', 'Asset\AssetTypeController@add')->name('add-asset-type')->middleware('haspermission:Equipment.components.Asset-Type.Add');
Route::post('/edit/asset-type/{id}', 'Asset\AssetTypeController@edit')->name('edit-asset-type')->middleware('haspermission:Equipment.components.Asset-Type.Edit');

Route::get('/asset-location-home', 'Asset\AssetLocationController@index')->name('asset-location-home')->middleware('haspermission:Equipment.components.Asset-Location.View');
Route::post('/asset-location', 'Asset\AssetLocationController@add')->name('add-asset-location')->middleware('haspermission:Equipment.components.Asset-Location.Add');
Route::post('/edit/asset-location/{id}', 'Asset\AssetLocationController@edit')->name('edit-asset-location')->middleware('haspermission:Equipment.components.Asset-Location.Edit');
//##################################ASSETS LINKS######################################

//###################################MODULE PRE_CONFIGS LINKS#######################################
Route::get('/module-pre-configs/{config}/{module}', 'ModulePreConfigsController@index')->name('module-pre-configs')->middleware('haspermission:Inventory.components.Configuration.View');
Route::post('/add-module-pre-configs/{id}/{config}/{module}', 'ModulePreConfigsController@update')->name('add-module-pre-configs')->middleware('haspermission:Inventory.components.Configuration.Add');
Route::get('/view-currency-conversions', 'ModulePreConfigsController@view_currency_conversion')->name('view-currency-conversions')->middleware('haspermission:Inventory.components.Configuration.View');
Route::get('/view-uom-conversions', 'ModulePreConfigsController@view_uom_conversion')->name('view-uom-conversions')->middleware('haspermission:Inventory.components.Configuration.View');
Route::post('/add-currency-conversions', 'ModulePreConfigsController@currency_conversion')->name('add-currency-conversions')->middleware('haspermission:Inventory.components.Configuration.Add');
Route::post('/add-uom-conversion', 'ModulePreConfigsController@uom_conversion')->name('add-uom-conversion')->middleware('haspermission:Inventory.components.Configuration.Add');
Route::get('/show-material-type/{id}', 'ModulePreConfigsController@show_material_type')->name('show-material-type')->middleware('haspermission:Inventory.components.Configuration.View');
//###################################MODULE PRE_CONFIGS LINKS#######################################

//###################################PRICELISTS#######################################
// DEPRECATED: Pricelist routes - replaced by invoicable items system
// Route::get('/pricelists', 'PricelistItemController@index')->name('view-pricelists')->middleware('haspermission:Laboratory.components.Pricelists.View');
// Route::post('/pricelist/{id?}', 'PricelistItemController@update')->name('update-pricelist');
// Route::post('/pricelist/{id}/upload', 'PricelistItemController@upload')->name('upload-pricelist-pdf');
// Route::post('/pricelist/{id}/email', 'PricelistItemController@email')->name('email-pricelist-pdf');
// Route::get('/pricelist/{id}/{print?}', 'PricelistItemController@show')->name('show-pricelist');
// Route::post('/pricelist/{id}/item', 'PricelistItemController@update_item')->name('update-pricelist-item');
// Route::post('/save-price-changes/{id}', 'PricelistItemController@save_price_changes')->name('save-price-changes');
// Route::post('/clone-items-to-new-pricelist/{id}', 'PricelistItemController@clone_items_to_new_pricelist')->name('clone-items-to-new-pricelist');
// Route::get('/move-pricelist-item/{direction}/{pricelist}/{element}', 'PricelistItemController@move_pricelist_item')->name('move-pricelist-item');
// Route::post('/add-customer-to-pricelist/{id}', 'PricelistItemController@add_customer')->name('add-customer-to-pricelist');
// Route::post('/remove-customer-to-pricelist/{id}', 'PricelistItemController@remove_customer')->name('remove-customer-to-pricelist');

//###################################PRICELISTS#######################################

//######################################SYSTEMS ##################################################################
Route::get('/system/configuration-type/home', 'System\SystemConfigurationTypeController@index')->name('configuration-type-home')->middleware('haspermission:System.components.Configuration Types.View');
Route::post('/edit/system/configuration-type/{id}', 'System\SystemConfigurationTypeController@edit')->name('edit-configuration-type')->middleware('haspermission:System.components.Configuration Types.Edit');
Route::post('/add/system/configuration-type/', 'System\SystemConfigurationTypeController@add')->name('add-configuration-type')->middleware('haspermission:System.components.Configuration Types.Add');

Route::get('/system/configuration-home', 'System\SystemConfigurationsController@index')->name('configuration-system-home')->middleware('haspermission:System.components.Configurations.View');
Route::post('/add/system/configuration/{id}', 'System\SystemConfigurationsController@add')->name('add-configuration')->middleware('haspermission:System.components.Configurations.Add');
Route::post('/edit/system/configuration/{id}', 'System\SystemConfigurationsController@edit')->name('edit-configuration')->middleware('haspermission:System.components.Configurations.Edit');
Route::post('/delete/system/configuration/{id}', 'System\SystemConfigurationsController@delete')->name('delete-configuration')->middleware('haspermission:System.components.Configurations.Delete');
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
Route::get('/invoice-home', 'Invoice\InvoiceController@index')->name('invoice-home')->middleware('haspermission:Laboratory.components.Proforma Invoices.View');
Route::get('/invoice/sample/{id}', 'Invoice\InvoiceController@show')->name('invoice-sample-header')->middleware('haspermission:Laboratory.components.Proforma Invoices.View');
Route::get('/invoice/generate/{id}', 'Invoice\InvoiceController@generateinvoice')->name('generate-invoice')->middleware('haspermission:Laboratory.components.Proforma Invoices.Add');
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
})->name('billing.tax-regime')->middleware('auth');

// Keep old routes for backward compatibility (commented out)
// Route::get('/tax-home', 'Invoice\InvoiceController@tax_index')->name('tax-home')->middleware('haspermission:Laboratory.components.Tax Regime.View');
// Route::post('/edit-tax/regime/{id}', 'Invoice\InvoiceController@edit_tax')->name('edit-tax')->middleware('haspermission:Laboratory.components.Tax Regime.Edit');
// Route::post('/add-tax/regime', 'Invoice\InvoiceController@add_tax')->name('add-tax')->middleware('haspermission:Laboratory.components.Tax Regime.Add');
//#################################TAX REGIME#######################################

//#################################LAB REPORTS#######################################
Route::get('/lab/reports-home', 'Lab\Reports\SamplesReportsController@index')->name('lab-reports-home')->middleware('haspermission:Laboratory.components.Lab-Reports.View');
Route::post('/lab/report/show', 'Lab\Reports\SamplesReportsController@show')->name('lab-report-show')->middleware('haspermission:Laboratory.components.Lab-Reports.View');

Route::get('/lab/sample-generate/certificate-analysis/{id}', 'SampleWorkFlowController@certificate_analysis')->name('certificate-analysis')->middleware('haspermission:Laboratory.components.Lab-Reports.View');
Route::get('/getAnalysisTypeBySampleTypeAjax/{type_id}', 'Lab\Reports\SamplesReportsController@getAnalysisTypeBySampleTypeAjax')->name('getAnalysisTypeBySampleTypeAjax')->middleware('haspermission:Laboratory.components.Lab-Reports.View');

Route::get('/lab/disposal/report', 'SampleWorkFlowController@disposalReportIndex')->name('lab-report-disposal')->middleware('haspermission:Laboratory.components.Lab-Reports.View');
Route::get('/lab/tat/report', 'SampleWorkFlowController@tatReportIndex')->name('lab-report-tat')->middleware('haspermission:Laboratory.components.Lab-Reports.View');

Route::get('/get-analysis-type/{id}/Ajax', 'SampleWorkFlowController@getAnalysisTypeAjax')->name('getAnalysisTypeAjax')->middleware('haspermission:Laboratory.components.Lab-Reports.View');
Route::get('/get-Analyte/{id}/Ajax', 'SampleWorkFlowController@getAnalyteAjax')->name('getAnalyteAjax')->middleware('haspermission:Laboratory.components.Lab-Reports.View');
//#################################LAB REPORTSS#######################################

//###################################DISPOSED EQUIPMENT REPORTS######################################
Route::get('/equipment/reports/home', 'Equipment\EquipmentReportsController@index')->name('equipment-report-generate')->middleware('haspermission:Equipment.components.Equipment-Disposal.View');
Route::post('/equipment/reports/show', 'Equipment\EquipmentReportsController@show')->name('equipment-report-show')->middleware('haspermission:Equipment.components.Equipment-Disposal.View');
Route::get('/equipment/disposed-report/generate', 'Equipment\EquipmentReportsController@disposed_report')->name('disposed-equipment-generate')->middleware('haspermission:Equipment.components.Equipment-Disposal.View');
//###################################DISPOSED EQUIPMENT REPORTS######################################

//###################################Standards#######################################
Route::post('/lab/standard/add', 'Lab\StandardsController@addStandard')->name('add-standard')->middleware('haspermission:Laboratory.components.Standards.Add');
Route::post('/lab/standard/edit/{id}', 'Lab\StandardsController@editStandard')->name('edit-standard')->middleware('haspermission:Laboratory.components.Standards.Edit');
Route::post('/lab/standard-value/add', 'Lab\StandardsController@addStandardValues')->name('add-standard-value')->middleware('haspermission:Laboratory.components.Standards.Add');
Route::post('/lab/standard-value/edit/{id}', 'Lab\StandardsController@editStandardValue')->name('edit-standard-value')->middleware('haspermission:Laboratory.components.Standards.Edit');
Route::get('/lab/standard/show/{id}', 'Lab\StandardsController@show')->name('view-standard')->middleware('haspermission:Laboratory.components.Standards.View');
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
Route::get('/get_items_via_ajax/{cat_id?}/{name?}', 'InventorySubCategoriesController@get_items_via_ajax')->name('get_items_via_ajax')->middleware('haspermission:Inventory.components.Categories.View');
Route::get('/get_suppliers_via_ajax', 'SupplierController@get_suppliers_via_ajax')->name('get_suppliers_via_ajax');
Route::get('/get_item_details/{inv_sub_cat}/{req_id?}', 'InventorySubCategoriesController@get_item_details')->name('get_item_details')->middleware('haspermission:Inventory.components.Categories.View');
Route::get('/workorder_resources/{wid}', 'WorkOrder\WorkOrderController@workorder_resources')->name('workorder_resources');
Route::get('/fetch-supplier-items/{sID}', 'SupplierController@fetch_supplier_items')->name('fetch_supplier_items');
//###################################API ROUTES#######################################

Route::get('/event/update/schedule', 'Event\EventController@eventUpdateSchedule')->name('eventUpdateSchedule');
Route::post('/get/confirmation/Callback-Url/Payload-wertyasdfgh', 'Mpesa\MpesaController@mpesaConfirmationCallbackUrl')->name('confirmation_url');
Route::post('/get/Validation/Callback-Url/payload-ghfjdks', 'Mpesa\MpesaController@mpesaValidationCallbackUrl')->name('mpesaValidationCallbackUrl');
Route::get('/displayMpesaValidation/gvdasdsgdud', 'Mpesa\MpesaController@displayMpesaValidation')->name('displayMpesaValidation');

//############################################QC Module###########################################
Route::get('Qc/mark-Qc-Sample/Complete/{id}', 'SampleWorkFlowController@markQcSampleComplete')->name('markQcSampleComplete')->middleware('haspermission:Laboratory.components.Qc Sample.Edit');
Route::prefix('qualitycontrol')->middleware('haspermission:Laboratory.components.Qc Sample.View')->group(function () {
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
    Route::post('/mark/qc/batch/complete', 'SampleWorkFlowController@markQCBatchComplete')->name('mark-batch-complete')->middleware('haspermission:Laboratory.components.Qc Sample.Edit');

    Route::post('/process-qc/results', 'QcModule\QualityControlController@processResults')->name('process-qc-results');
    Route::get('/show-processing/results', 'QcModule\QualityControlController@showUnProcessed')->name('showUnProcessed');

    Route::get('/results-reports', 'QcModule\QualityControlController@showQcReport')->name('qc-reports');
    Route::get('/result-report/show/{result_id}', 'QcModule\QualityControlController@showQcReportGraph')->name('qc-result-show');
});
//############################################QC Module###########################################

//###############################################Polucon#########################################
Route::get('/assign-Lab/Section-To-Analysis-Element/{id}', 'SampleWorkFlowController@assignLabSectionToAnalysisElement')->name('assignLabSectionToAnalysisElement')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.Edit');

Route::get('/generate/Customer-Focus/Index/{batch_id}', 'SampleWorkFlowController@generateCustomerFocusIndex')->name('generateCustomerFocusIndex')->middleware('haspermission:Laboratory.components.Customer-Focus.View');
Route::post('/send/Batch-Schedule/Analysis', 'SampleWorkFlowController@sendBatchScheduleAnalysis')->name('sendBatchScheduleAnalysis')->middleware('haspermission:Laboratory.components.Customer-Focus.Edit');
Route::post('/send/Batch-Payment/Reminder', 'SampleWorkFlowController@sendBatchPaymentReminder')->name('sendBatchPaymentReminder')->middleware('haspermission:Laboratory.components.Customer-Focus.Edit');
Route::post('/send/batches-SOA', 'SampleWorkFlowController@sendBatchesScheduleAnalysis')->name('send-batches-soa')->middleware('haspermission:Laboratory.components.Customer-Focus.Edit');
//###################Inter Lab Log ####################################
Route::get('/getSampleCurrentLabSection/{id}', 'SampleWorkFlowController@getSampleCurrentLabSection')->name('getSampleCurrentLabSection')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.View');
Route::post('/create-sample-inter-lab-log', 'SampleWorkFlowController@create_sample_inter_lab_log')->name('create_sample_inter_lab_log')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.Add');
Route::post('/change/Inter-Lab-Log/Status', 'SampleWorkFlowController@changeInterLabLogStatus')->name('changeInterLabLogStatus')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.Edit');

Route::get('/inter-Lab/Transfer-Index/{is_archived?}', 'SampleWorkFlowController@interLabTransferIndex')->name('interLabTransferIndex')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.View');
Route::post('/delete/Inter-Lab-Transfer/Logs', 'SampleWorkFlowController@deleteInterLabTransferLogs')->name('deleteInterLabTransferLogs')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.Delete');
Route::get('/get/Lab-Sections/By-Lab/{id}', 'SampleWorkFlowController@getLabSectionsByLab')->name('getLabSectionsByLab')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.View');
Route::post('/moveToLab', 'SampleWorkFlowController@moveToLab')->name('moveToLab')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.Edit');
Route::get('/showBatchCOA', 'SampleWorkFlowController@showBatchCOA')->name('showBatchCOA')->middleware('haspermission:Laboratory.components.Lab-Reports.View');

Route::post('/add-Section/Approval', 'SampleAnalysisStageController@addSectionApproval')->name('addSectionApproval')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Edit');
Route::post('/delete-Section/Approval', 'SampleAnalysisStageController@deleteSectionApproval')->name('deleteSectionApproval')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Edit');

Route::post('move/To-Verification/Approval-Level', 'SampleWorkFlowController@moveToVerificationApprovalLevel')->name('moveToVerificationApprovalLevel')->middleware('haspermission:Laboratory.components.Verification-Approvals.Edit');
Route::post('edit/Verification/Approver-Config', 'SampleWorkFlowController@editVerificationApproverConfig')->name('editVerificationApproverConfig')->middleware('haspermission:Laboratory.components.Verification-Approvals.Edit');
Route::post('delete/Verification-Approver/Config', 'SampleWorkFlowController@deleteVerificationApproverConfig')->name('deleteVerificationApproverConfig')->middleware('haspermission:Laboratory.components.Verification-Approvals.Delete');
Route::post('change/Batch-Approval/Status', 'SampleWorkFlowController@changeBatchApprovalStatus')->name('changeBatchApprovalStatus')->middleware('haspermission:Laboratory.components.Verification-Approvals.Edit');
Route::get('/get/Show-Batch/COA/{batch_code}/{format}', 'SampleWorkFlowController@getShowBatchCOA')->name('getShowBatchCOA')->middleware('haspermission:Laboratory.components.Lab-Reports.View');

Route::get('/sample-condition-index', 'SampleConditionController@index')->name('sample_condition_index')->middleware('haspermission:Laboratory.components.Sample-Types.View');
Route::get('/sample-products/index', 'CRM\CompanyProductController@index')->name('sample-product-index');

Route::get('/sample-type-category/index', 'SampleTypeCategoryController@index')->name('sample-type-category-index');
Route::post('/sample-type-category/add', 'SampleTypeCategoryController@addCategory')->name('sample-type-category-add');
Route::get('/get/Client-Details/Ajax/{id}', 'SampleWorkFlowController@getClientDetailsAjax')->name('getClientDetailsAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('/ajax/clients', 'SampleWorkFlowController@searchClients')->name('sample-workflow.clients')->middleware('haspermission:Laboratory.components.All Samples.View');

Route::get('generate/Tablet/Customer-Focus/Index', 'SampleWorkFlowController@generateTabletCustomerFocusIndex')->name('generateTabletCustomerFocusIndex')->middleware('haspermission:Laboratory.components.Customer-Focus.View');
Route::post('get/Table/Customer-Focus/Signing', 'SampleWorkFlowController@getTableCustomerFocusSigning')->name('getTableCustomerFocusSigning')->middleware('haspermission:Laboratory.components.Customer-Focus.Edit');

Route::get('getSampleCodeToResultsAndCr', 'SampleWorkFlowController@getSampleCodeToResultsAndCr')->name('getSampleCodeToResultsAndCr')->middleware('haspermission:Laboratory.components.All Samples.View');

Route::get('get/Analysis-Type/By/SampleTypeIDAjax/{sample_type_id}', 'SampleWorkFlowController@getAnalysisTypeBySampleTypeIDAjax')->name('getAnalysisTypeBySampleTypeIDAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('get/Sample-Conditions/Ajax', 'SampleWorkFlowController@getSampleConditionsAjax')->name('getSampleConditionsAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('get/Sample-Products/Ajax', 'SampleWorkFlowController@getSampleProductsAjax')->name('getSampleProductsAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('get/Sample-Standards/Ajax', 'SampleWorkFlowController@getSampleStandardsAjax')->name('getSampleStandardsAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('get/Crm-Customer-SamplePoint/{crm_id}/Ajax/{name}', 'SampleWorkFlowController@getCrmCustomerSamplePointAjax')->name('getCrmCustomerSamplePointAjax')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::get('get/Sample-Parameter/Data/Ajax/{sample_id}', 'SampleWorkFlowController@getShowSampleParameterDataAjax')->name('getShowSampleParameterDataAjax')->middleware('haspermission:Laboratory.components.All Samples.View');

Route::post('/clone/Batch-Information', 'SampleWorkFlowController@cloneBatchInformation')->name('cloneBatchInformation')->middleware('haspermission:Laboratory.components.All Samples.Edit');
Route::get('get/Standard-Values/Data/Ajax', 'SampleWorkFlowController@getStandardValuesDataAjax')->name('getStandardValuesDataAjax')->middleware('haspermission:Laboratory.components.Standards.View');
Route::post('update/Standard-Analyte/Limit', 'SampleWorkFlowController@updateStandardAnalyteLimit')->name('updateStandardAnalyteLimit')->middleware('haspermission:Laboratory.components.Standards.Edit');

Route::post('/analyte-type-elements-import', 'AnalysisElementsController@import')->name('analyte-type-elements-import')->middleware('haspermission:Laboratory.components.Analysis Types.Add');
Route::post('/analysis-type-clone/{id}', 'AnalysisTypeController@clone')->name('analysis-type-clone')->middleware('haspermission:Laboratory.components.Analysis Types.Add');
Route::post('/sample-type-clone/{id}', 'SampleTypeController@clone')->name('sample-type-clone')->middleware('haspermission:Laboratory.components.Sample-Types.Add');
Route::get('/testSmsAlert', 'SampleWorkFlowController@testSmsAlert')->name('testSmsAlert')->middleware('haspermission:Laboratory.components.All Samples.View');
Route::post('/save-Sample/AnalysisDate', 'SampleWorkFlowController@saveSampleAnalysisDate')->name('saveSampleAnalysisDate')->middleware('haspermission:Laboratory.components.All Samples.Edit');

Route::get('/get/Sample-IntelabLogs-Approval/Status', 'SampleWorkFlowController@getSampleIntelabLogsApprovalStatus')->name('getSampleIntelabLogsApprovalStatus')->middleware('haspermission:Laboratory.components.Inter-Lab-Logs.View');
Route::get('/getSampleResultCapturedNot', 'SampleWorkFlowController@getSampleResultCapturedNot')->name('getSampleResultCapturedNot')->middleware('haspermission:Laboratory.components.All Samples.View');

Route::post('/mark/finished-sample', 'SampleWorkFlowController@markBatchesFinished')->name('mark-finished')->middleware('haspermission:Laboratory.components.Finished Sample.Edit');
Route::post('/return/finished-sample', 'SampleWorkFlowController@returnFromFinished')->name('return-finished')->middleware('haspermission:Laboratory.components.Finished Sample.Edit');

//###############################################Polucon#########################################

//##############################################EMAILAPPROVALS#######################################
Route::get('/email-approval/{link_key}/{type}/{userid}', 'ExternalApprovalController@approve')->name('email-approval');
Route::get('/email-rejection/{link_key}/{type}/{userid}', 'ExternalApprovalController@reject')->name('email-rejection');
Route::post('/email-rejection/{link_key}/{type}/{userid}', 'ExternalApprovalController@reject')->name('send-email-rejection');
Route::get('/email-recheck/{link_key}/{type}/{userid}', 'ExternalApprovalController@recheck')->name('email-recheck');
Route::post('/email-recheck/{link_key}/{type}/{userid}', 'ExternalApprovalController@recheck')->name('send-email-recheck');
//##############################################EMAILAPPROVALS#######################################

###############################################NOTIFICATIONS#######################################
Route::get('/send-restock-notifications', 'InventoryItemController@sendReorderNotifications')->name('send-restock-notifications')->middleware('haspermission:Inventory.components.Inventory-Movement.Edit');
###############################################NOTIFICATIONS#######################################

###############################################ZOHO INTEGRATION#######################################
Route::get('/zoho-auth-redirect', 'ZohoController@redirect')->name('zoho-auth-redirect');
Route::get('/zoho-purchase-orders', 'ZohoController@getPurchaseOrders')->name('zoho-purchase-orders');
Route::get('/zoho-get-things', 'ZohoController@sync_zoho_things')->name('zoho-sync-things');
Route::get('/zoho-sync-coa', 'ChartOfAccountController@synchronize')->name('zoho-sync-coa');
Route::get('/zoho-sync-all/{type}', 'ZohoController@sync_all')->name('zoho-sync-all');
Route::get('/zoho-authenticate', 'ZohoController@authenticate')->name('zoho-authenticate');
Route::get('/recreate-purchase-order/{id}', 'RequisitionController@resend_to_zoho')->name('recreate-purchase-order');
Route::get('/getItemsTest', 'ZohoController@getItemsTest')->name('getItemsTest');
Route::get('/changeSalesOrderStatus', 'ZohoController@changeSalesOrderStatus')->name('changeSalesOrderStatus');

Route::get('/matchCrmCurrency', 'SampleWorkFlowController@matchCrmCurrency')->name('matchCrmCurrency')->middleware('haspermission:Laboratory.components.Proforma Invoices.View');
// Route::get('/zoho-purchase-orders','ZohoController@getPurchaseOrders')->name('zoho-purchase-orders');
Route::get('/sync-all-suppliers-to-items', 'ZohoController@supplier_to_item_sync')->name('sync-all-suppliers-to-items');
###############################################ZOHO INTEGRATION#######################################

#################################### Matrix CONFIGURATIONS#######################################

/* MODULE PRECONFIG */
Route::get('/module-skills-pre-configs/{config}/{module}', 'SkillsMatrix\ModuleSkillsPreConfigsController@index')->name('module-skills-pre-configs')->middleware('haspermission:Skills-Matrix.components.Module-Preconfigs.View');
Route::post('/add-module-skills-pre-configs/{id}/{config}/{module}', 'ModulePreConfigsController@update')->name('add-module-skills-pre-configs')->middleware('haspermission:Skills-Matrix.components.Module-Preconfigs.Add');
Route::post('/update-module-skills-pre-configs/{id}/{config}/{module}', 'SkillsMatrix\ModuleSkillsPreConfigsController@update')->name('update-module-skills-pre-configs')->middleware('haspermission:Skills-Matrix.components.Module-Preconfigs.Edit');
Route::get('/move-skills-type/{direction}/{module}/{element}', 'SkillsMatrix\ModuleSkillsPreConfigsController@move_skills_types')->name('move-skills-type')->middleware('haspermission:Skills-Matrix.components.Module-Preconfigs.Edit');
/* MODULE SKILLS MATRIX */
Route::get('/matrix', 'SkillsMatrix\SkillsMatrixController@index')->name('matrix')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.View');
Route::post('/matrix', 'SkillsMatrix\SkillsMatrixController@add')->name('assign-matrix')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.Add');
Route::post('/matrix/edit', 'SkillsMatrix\SkillsMatrixController@edit')->name('edit-matrix')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.Edit');
Route::get('/matrix/show/{id}', 'SkillsMatrix\SkillsMatrixController@show')->name('show-matrix')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.View');
Route::post('/matrix/create', 'SkillsMatrix\SkillsMatrixController@createSkillsMatrix')->name('create-matrix')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.Add');
Route::post('/matrix/detail/delete', 'SkillsMatrix\SkillsMatrixController@deleteMatrixDetail')->name('delete-matrix-detail')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.Delete');
Route::post('/matrix/detail/role/edit', 'SkillsMatrix\SkillsMatrixController@editMatrixdetailRole')->name('edit-matrix-detail-role')->middleware('haspermission:Skills-Matrix.components.Skills-Matrix.Edit');

Route::get('/matrix/capability/index', 'SkillsMatrix\CapabilityController@index')->name('capability-index')->middleware('haspermission:Skills-Matrix.components.Capability.View');
Route::post('/matrix/capability/add', 'SkillsMatrix\CapabilityController@store')->name('capability.add')->middleware('haspermission:Skills-Matrix.components.Capability.Add');
Route::post('/matrix/capability/edit', 'SkillsMatrix\CapabilityController@editCapabaility')->name('capability.edit')->middleware('haspermission:Skills-Matrix.components.Capability.Edit');
Route::post('/matrix/capability/delete', 'SkillsMatrix\CapabilityController@deleteCapabaility')->name('capability.delete')->middleware('haspermission:Skills-Matrix.components.Capability.Delete');

Route::get('/matrix/get/role/{matrix_id}/ajax', 'SkillsMatrix\CapabilityController@getSkillMatrixRolesAjax')->name('capability.get.role')->middleware('haspermission:Skills-Matrix.components.Capability.View');
Route::post('/matrix/get/user/position/ajax', 'SkillsMatrix\CapabilityController@getMatrixUsersByPositionAjax')->name('capability.get.userby.position')->middleware('haspermission:Skills-Matrix.components.Capability.View');
Route::get('/matrix/capability/show/{id}', 'SkillsMatrix\CapabilityController@show')->name('capability.show')->middleware('haspermission:Skills-Matrix.components.Capability.View');
Route::post('/matrix/capability/show/{id}', 'SkillsMatrix\CapabilityController@show')->name('capability.show-post')->middleware('haspermission:Skills-Matrix.components.Capability.View');
Route::post('/matrix/capability/details/store', 'SkillsMatrix\CapabilityController@storeDetails')->name('capability.detail.store')->middleware('haspermission:Skills-Matrix.components.Capability.Edit');

Route::get('/matrix/training-needs', 'SkillsMatrix\TrainingNeedsController@index')->name('train.needs.index')->middleware('haspermission:Skills-Matrix.components.Training-Needs.View');
Route::post('/matrix/train-needs/store', 'SkillsMatrix\TrainingNeedsController@store')->name('train.needs.store')->middleware('haspermission:Skills-Matrix.components.Training-Needs.Add');
Route::get('/matrix/get-capability-users/{id}', 'SkillsMatrix\TrainingNeedsController@getCapabilityUsers')->name('train.needs.get.cabailityusers')->middleware('haspermission:Skills-Matrix.components.Training-Needs.View');
Route::get('/matrix/train-needs/{id}', 'SkillsMatrix\TrainingNeedsController@show')->name('train.needs.show')->middleware('haspermission:Skills-Matrix.components.Training-Needs.View');
Route::post('/matrix/train-need/edit', 'SkillsMatrix\TrainingNeedsController@editTrainNeed')->name('train.needs.edit')->middleware('haspermission:Skills-Matrix.components.Training-Needs.Edit');
Route::post('/matrix/train-need/delete', 'SkillsMatrix\TrainingNeedsController@deleteTrainNeed')->name('train.needs.delete')->middleware('haspermission:Skills-Matrix.components.Training-Needs.Delete');


Route::get('/matrix/train-plan/index', 'SkillsMatrix\TrainingPlanController@index')->name('train.plan.index')->middleware('haspermission:Skills-Matrix.components.Training-Plan.View');
Route::post('/matrix/train-plan/store', 'SkillsMatrix\TrainingPlanController@store')->name('train.plan.store')->middleware('haspermission:Skills-Matrix.components.Training-Plan.Add');
Route::post('/matrix/train/plan/edit', 'SkillsMatrix\TrainingPlanController@editPlan')->name('train.plan.edit')->middleware('haspermission:Skills-Matrix.components.Training-Plan.Edit');
Route::post('/matrix/train/plan/delete', 'SkillsMatrix\TrainingPlanController@deletePlan')->name('train.plan.delete')->middleware('haspermission:Skills-Matrix.components.Training-Plan.Delete');
Route::get('/matrix/train-plan/show/{id}', 'SkillsMatrix\TrainingPlanController@show')->name('train.plan.show')->middleware('haspermission:Skills-Matrix.components.Training-Plan.View');
Route::post('/matrix/train-plan/show/{id}', 'SkillsMatrix\TrainingPlanController@show')->name('train.plan.show-post')->middleware('haspermission:Skills-Matrix.components.Training-Plan.View');

Route::post('/matrix/train/plan/other/store', 'SkillsMatrix\TrainingPlanController@storeOther')->name('train.plan.store.other')->middleware('haspermission:Skills-Matrix.components.Training-Plan.Add');
Route::post('/matrix/train/planner/detail/store', 'SkillsMatrix\TrainingPlanController@storeDetail')->name('train.plan.detail.store')->middleware('haspermission:Skills-Matrix.components.Training-Plan.Edit');
Route::post('/matrix/train/plan/others/delete', 'SkillsMatrix\TrainingPlanController@deleteOtherDetail')->name('train.plan.others.delete')->middleware('haspermission:Skills-Matrix.components.Training-Plan.Delete');

Route::get('/matrix-config/{module}', 'SkillsMatrix\SkillsMatrixConfigController@index')->name('matrix-config')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::get('/matrix-config/{module}/{id}', 'SkillsMatrix\SkillsMatrixConfigController@getTopologies')->name('topology-module')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::post('/matrix-config-add/{matrix_id}/{id}', 'SkillsMatrix\SkillsMatrixConfigController@add')->name('topology-add')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Add');
Route::post('/update-matrix-Config', 'SkillsMatrix\SkillsMatrixConfigController@updat_matrix_Config')->name('update-matrix-Config')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Edit');
Route::post('/update-user-role-matrix-Config', 'SkillsMatrix\SkillsMatrixConfigController@updat_user_role_matrix_Config')->name('update-user-role-matrix-Config')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Edit');
Route::get('/matrix-config-topology', 'SkillsMatrix\SkillsMatrixConfigController@index')->name('topology')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::get('/matrix-config-topology/{id}/{matrix_id}/', 'SkillsMatrix\SkillsMatrixConfigController@getTopologies')->name('topology-parent')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::post('/matrix-config-topology/{id}/{matrix_id}/', 'SkillsMatrix\SkillsMatrixConfigController@add')->name('topology-add-post')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Add');
Route::post('/matrix-config-topology/{id}/{matrix_id}/remove', 'SkillsMatrix\SkillsMatrixConfigController@remove')->name('topology-remove')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Delete');
Route::get('/matrix-competence', 'SkillsMatrix\SkillsMatrixConfigController@competence_history')->name('matrix-competence')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::post('/get-week-listing', 'SkillsMatrix\SkillsMatrixConfigController@get_weeks_listing')->name('get-week-listing')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::post('/save-new-week', 'SkillsMatrix\SkillsMatrixConfigController@save_new_week')->name('save-new-week')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Add');
Route::post('/assign-trainner', 'SkillsMatrix\SkillsMatrixConfigController@assign_trainner')->name('assign-trainner')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Edit');
Route::post('/update-trainner', 'SkillsMatrix\SkillsMatrixConfigController@update_trainner')->name('update-trainner')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Edit');
Route::post('/get-skills-phase-comments', 'SkillsMatrix\SkillsMatrixConfigController@get_phase_comments')->name('get-skills-phase-comments')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.View');
Route::post('/assign-skills-phase-comments', 'SkillsMatrix\SkillsMatrixConfigController@assign_phase_comments')->name('assign-skills-phase-comments')->middleware('haspermission:Skills-Matrix.components.Matrix-Configuration.Edit');
/* MODULE OTHER TRAINING */

Route::get('/other-training', 'Training\SkillsOtherTrainingController@index')->name('other-training')->middleware('haspermission:Skills-Matrix.components.Other-Training.View');
Route::post('/other-training', 'Training\SkillsOtherTrainingController@add')->name('assign-other-training')->middleware('haspermission:Skills-Matrix.components.Other-Training.Add');
Route::post('/update-other-trainner/{condition}', 'Training\SkillsOtherTrainingController@edit')->name('update-other-training')->middleware('haspermission:Skills-Matrix.components.Other-Training.Edit');
Route::get('/training-acceptance/{training_id}/{dept_number}/{user_id}/{acceptance?}', 'Training\SkillsOtherTrainingController@training_acceptance')->name('training-acceptance')->middleware('haspermission:Skills-Matrix.components.Other-Training.Edit');

#################################### Matrix CONFIGURATIONS#######################################
###############################VGM MODULE###############################
Route::get('/vgm/index', 'Inspection\InspectionController@index')->name('vgm.index');
Route::get('/vgm/show/{id}', 'Inspection\InspectionController@show')->name('vgm.show');
Route::post('/vgm/store', 'Inspection\InspectionController@store')->name('vgm.store');
Route::post('/vgm/delete', 'Inspection\InspectionController@delete')->name('vgm.delete');

#################################SAMPLE WORKFLOW SEND SALES ORDER#######################
Route::post('/validate/client-batches', 'SampleWorkFlowController@validateClientBatches')->name('validate-clients')->middleware('haspermission:Laboratory.components.Sales-Orders.Add');
Route::post('/ajax/send-schedule', 'SampleWorkFlowController@sendScheduleAjax')->name('ajax-send-schedule')->middleware('haspermission:Laboratory.components.Sales-Orders.Add');
#######################################################################################

#####################################IMARA AI#######################
Route::get('/imara/ai/index', 'HomeController@aiIndex')->name('imara-ai-index');




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
});

Route::middleware(['auth'])->prefix('method-sequences')->name('method-sequences.')->group(function () {
    Route::get('/manage', 'MethodSequences\MethodSequenceController@manage')->name('manage');
    Route::get('/stages/{methodSequenceVersion}', 'MethodSequences\MethodSequenceController@stages')->name('stages');
    Route::post('/clone/{methodSequence}', 'MethodSequences\MethodSequenceController@clone')->name('clone');
});

// Document Management System (DMS) Routes
Route::middleware(['auth', 'haspermission:Documents.permission'])->prefix('dms')->name('dms.')->group(function () {
    Route::get('/', 'LivewireControllers\DMSController@dashboard')->name('dashboard')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/document-types', 'LivewireControllers\DMSController@documentTypes')->name('types')->middleware('haspermission:Documents.components.Document Types.View');
    Route::get('/active-documents', 'LivewireControllers\DMSController@activeDocuments')->name('active')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/archived-documents', 'LivewireControllers\DMSController@archivedDocuments')->name('archived')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/amendments', 'LivewireControllers\DMSController@amendments')->name('amendments')->middleware('haspermission:Documents.components.Document Publishing.View');
    Route::get('/reports', 'LivewireControllers\DMSController@reports')->name('reports')->middleware('haspermission:Documents.components.Reports.View');

    // File operations
    Route::get('/documents/{id}/download', 'DMSController@download')->name('download')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/documents/{id}/preview', 'DMSController@preview')->name('preview')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/documents/{documentId}/versions/{versionId}/download', 'DMSController@downloadVersion')->name('download-version')->middleware('haspermission:Documents.components.Document Management.View');
});

// Documents Module
Route::prefix('documents')->name('documents.')->middleware('haspermission:Documents.permission')->group(function () {
    // Dashboard
    Route::get('/dashboard', 'Documents\DocumentController@dashboard')->name('dashboard')->middleware('haspermission:Documents.components.Document Management.View');

    // Document Types
    Route::prefix('types')->name('types.')->group(function () {
        Route::get('/', 'Documents\DocumentTypeController@index')->name('index')->middleware('haspermission:Documents.components.Document Types.View');
        Route::get('/create', 'Documents\DocumentTypeController@create')->name('create')->middleware('haspermission:Documents.components.Document Types.Add');
        Route::post('/', 'Documents\DocumentTypeController@store')->name('store')->middleware('haspermission:Documents.components.Document Types.Add');
        Route::get('/{id}', 'Documents\DocumentTypeController@show')->name('show')->middleware('haspermission:Documents.components.Document Types.View');
        Route::get('/{id}/edit', 'Documents\DocumentTypeController@edit')->name('edit')->middleware('haspermission:Documents.components.Document Types.Edit');
        Route::put('/{id}', 'Documents\DocumentTypeController@update')->name('update')->middleware('haspermission:Documents.components.Document Types.Edit');
        Route::delete('/{id}', 'Documents\DocumentTypeController@destroy')->name('destroy')->middleware('haspermission:Documents.components.Document Types.Delete');
    });

    // Notification Frequencies
    Route::prefix('notification-frequencies')->name('notification-frequencies.')->group(function () {
        Route::get('/', 'Documents\NotificationFrequencyController@index')->name('index')->middleware('haspermission:Documents.components.Notification Frequencies.View');
        Route::get('/create', 'Documents\NotificationFrequencyController@create')->name('create')->middleware('haspermission:Documents.components.Notification Frequencies.Add');
        Route::post('/', 'Documents\NotificationFrequencyController@store')->name('store')->middleware('haspermission:Documents.components.Notification Frequencies.Add');
        Route::get('/{id}', 'Documents\NotificationFrequencyController@show')->name('show')->middleware('haspermission:Documents.components.Notification Frequencies.View');
        Route::get('/{id}/edit', 'Documents\NotificationFrequencyController@edit')->name('edit')->middleware('haspermission:Documents.components.Notification Frequencies.Edit');
        Route::put('/{id}', 'Documents\NotificationFrequencyController@update')->name('update')->middleware('haspermission:Documents.components.Notification Frequencies.Edit');
        Route::delete('/{id}', 'Documents\NotificationFrequencyController@destroy')->name('destroy')->middleware('haspermission:Documents.components.Notification Frequencies.Delete');
    });

    // Document Management
    Route::get('/', 'Documents\DocumentController@index')->name('index')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/unpublished', 'Documents\DocumentController@unpublished')->name('unpublished')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/expired', 'Documents\DocumentController@expired')->name('expired')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/create', 'Documents\DocumentController@create')->name('create')->middleware('haspermission:Documents.components.Document Management.Add');
    Route::post('/', 'Documents\DocumentController@store')->name('store')->middleware('haspermission:Documents.components.Document Management.Add');
    Route::post('/bulk-store', 'Documents\DocumentController@bulkStore')->name('bulk-store')->middleware('haspermission:Documents.components.Document Management.Add');
    Route::get('/{id}/edit', 'Documents\DocumentController@edit')->name('edit')->middleware('haspermission:Documents.components.Document Management.Edit');
    Route::put('/{id}', 'Documents\DocumentController@update')->name('update')->middleware('haspermission:Documents.components.Document Management.Edit');
    Route::delete('/{id}', 'Documents\DocumentController@destroy')->name('destroy')->middleware('haspermission:Documents.components.Document Management.Delete');

    // Publishing
    Route::get('/{id}/publish', 'Documents\DocumentController@publish')->name('publish')->middleware('haspermission:Documents.components.Document Publishing.Add');
    Route::post('/{id}/publish', 'Documents\DocumentController@storePublish')->name('store-publish')->middleware('haspermission:Documents.components.Document Publishing.Add');
    Route::post('/{id}/unpublish', 'Documents\DocumentController@unpublish')->name('unpublish')->middleware('haspermission:Documents.components.Document Publishing.Edit');

    // Downloads and attachments
    Route::get('/{id}/download', 'Documents\DocumentController@download')->name('download')->middleware('haspermission:Documents.components.Document Management.View');
    Route::get('/attachments/{id}/download', 'Documents\DocumentController@downloadAttachment')->name('attachments.download')->middleware('haspermission:Documents.components.Document Management.View');
    Route::delete('/attachments/{id}', 'Documents\DocumentController@deleteAttachment')->name('attachments.delete')->middleware('haspermission:Documents.components.Document Management.Delete');

    // Validation
    Route::post('/check-duplicate', 'Documents\DocumentController@checkDuplicate')->name('check-duplicate')->middleware('haspermission:Documents.components.Document Management.View');

    // Show document (keep last)
    Route::get('/{id}', 'Documents\DocumentController@show')->name('show')->middleware('haspermission:Documents.components.Document Management.View');
});
