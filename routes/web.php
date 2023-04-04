<?php

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

Route::get('/', function () {
  return redirect()->route('home');
});

Auth::routes();

Route::get('/resolveTest','SampleWorkFlowController@resolveTest')->name('resolveTest');
Route::get('/fillCapturedresultOperator','SampleWorkFlowController@fillCapturedresultOperator')->name('fillCapturedresultOperator');
Route::get('add/suppliers-user','SupplierController@make_suppliers_users')->name('add-crm-to-users');

Route::get('/send/event-notifications-cron','API\APIController@send_event_notifications')->name('send_event_notifications');

Route::post('/logout/app/','Auth\TwoFactor@mylogout')->name('mylogout');
Route::get('/verify/user','Auth\TwoFactor@index')->name('verify-user');
Route::post('/verify-code/store','Auth\TwoFactor@storeVerifyCode')->name('verify-store');
Route::post('/verify-code/store/ext','Auth\TwoFactor@storeVerifyCodeExt')->name('verify-store-ext');
Route::get('/verify-code/resend','Auth\TwoFactor@resendVerifyCode')->name('verify-resend');

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/search-sample-code','HomeController@searchsample')->name('search-sample-code');
Route::post('/set-default-company', 'HomeController@default_company')->name('set-default-company');

##############CONFIGURATIONS###############################################################################
Route::get('/system-settings', 'ConfigurationController@index')->name('system-settings');
Route::post('/import-my-users','PersonnelController@importUser')->name('importUser');
/* COMPANIES */
Route::get('/companies', 'CompanyController@index')->name('companies');
Route::post('/companies', 'CompanyController@add')->name('add-companies');
Route::post('/company/{id}', 'CompanyController@edit')->name('edit-company');

Route::post('/company-activate','CompanyController@activate_company')->name('activate-company');

#######################################SYSTEM###########################################
Route::get('/full-calendar/view/{date?}','Event\EventController@index')->name('full-calendar');
Route::post('/full-calendar/add','Event\EventController@created')->name('full-calendar-create');

Route::post('/fullcalendareventmaster/create','Event\EventController@create');
Route::post('/full-calendar/update','Event\EventController@update')->name('editRoutineEvent');
Route::post('/fullcalendareventmaster/delete','Event\EventController@destroy');
Route::post('/fullcalendar/user-task','Event\EventController@getEventByUser');
Route::get('/fullcalendar/print-user-task','Event\EventController@printUserEvents')->name('printUserEvents');
Route::post('/full-callendar/edit','Event\EventController@editEvent')->name('editEvent');
Route::post('/delete-events','Event\EventController@delete_event')->name('delete-events');
Route::get('/get/event/id/{id}','Event\EventController@getEvent')->name('getEventByID');


##############CONFIGURATIONS END###############################################################################
#######################################dashboard Ajax###############################################
Route::get('/getSamplesByCustomer/{year?}','Lab\LabDashboardController@getSamplesByCustomer')->name('getSamplesByCustomer');
Route::get('/getSamplesByGps/{year?}','Lab\LabDashboardController@getSamplesByGps')->name('getSamplesByGps');
Route::get('/getSamplesByMonth/{year?}','Lab\LabDashboardController@getSamplesByMonth')->name('getSamplesByMonth');
Route::get('/getsamplesBySampletype/{year?}','Lab\LabDashboardController@getsamplesBySampletype')->name('getsamplesBySampletype');

#######################################dashboard Ajax###############################################

######################LABS######################################################################################
Route::get('/lab-home', 'LabController@index')->name('lab-home')->middleware('haspermission:Laboratory.permission');
Route::get('/lab-dashboard','Lab\LabDashboardController@index')->name('dashboard-lab')->middleware('haspermission:Laboratory.permission');

Route::get('/labs', 'LabController@index')->name('labs')->middleware('haspermission:Laboratory.components.Labs.View');
Route::get('/lab/{labid?}/analysis-types', 'AnalysisTypeController@index')->name('show-lab-analysis-types');
Route::post('/labs', 'LabController@add')->name('add-labs')->middleware('haspermission:Laboratory.components.Labs.Add');
Route::post('/lab/{id}', 'LabController@edit')->name('edit-lab')->middleware('haspermission:Laboratory.components.Labs.Edit');

Route::get('/analytes', 'AnalyteController@index')->name('analytes')->middleware('haspermission:Laboratory.components.Analytes.View');
Route::post('/analytes', 'AnalyteController@add')->name('add-analytes')->middleware('haspermission:Laboratory.components.Analytes.Add');
Route::post('/analyte/{id}', 'AnalyteController@edit')->name('edit-analyte')->middleware('haspermission:Laboratory.components.Analytes.Edit');

Route::get('/sample-types', 'SampleTypeController@index')->name('sample-types')->middleware('haspermission:Laboratory.components.Sample-Types.View');
Route::get('/sample-type/{id}', 'SampleTypeController@show')->name('sample-type');
Route::post('/sample-type-delete','SampleTypeController@delete_sample_type')->name('delete-sample-type')->middleware('haspermission:Laboratory.components.Sample-Types.Delete');
Route::post('/sample-types', 'SampleTypeController@add')->name('add-sample-types')->middleware('haspermission:Laboratory.components.Sample-Types.Add');
Route::post('/sample-conditions', 'SampleConditionController@add')->name('add-sample-conditions');
Route::post('/sample-condition/{condition}', 'SampleConditionController@edit')->name('edit-sample-condition');
Route::post('/sample-type/{id}', 'SampleTypeController@edit')->name('edit-sample-type')->middleware('haspermission:Laboratory.components.Sample-Types.Edit');
Route::post('/add/sample-type-qualification/{id}','Lab\Samples\SampleQualificationsController@add')->name('add-sample-type-qualification');
Route::post('/edit/sample-type-qualification/{id}','Lab\Samples\SampleQualificationsController@edit')->name('edit-sample-type-qualification');
Route::post('/delete/sample-type-qualification/{id}','Lab\Samples\SampleQualificationsController@delete')->name('delete-sample-type-qualification');


Route::get('/analysis-types', 'AnalysisTypeController@index')->name('analysis-types')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::post('/analysis-types', 'AnalysisTypeController@add')->name('add-analysis-types')->middleware('haspermission:Laboratory.components.Analysis Types.Add');
Route::post('/analysis-type/{id}', 'AnalysisTypeController@edit')->name('edit-analysis-type')->middleware('haspermission:Laboratory.components.Analysis Types.Edit');
Route::get('/analysis-type/{id}', 'AnalysisTypeController@show')->name('analysis-type')->middleware('haspermission:Laboratory.components.Analysis Types.View');
Route::get('/get/Analyte/{id}/Methods','AnalysisElementsController@getAnalyteMethods')->name('getAnalyteMethods');



Route::post('/add-analyte-guide', 'AnalysisMethodElementsController@update_guide')->name('add-analyte-guide');
Route::post('/clone-analyte-guide','AnalysisMethodElementsController@clone_analysis_guide')->name('clone_analysis_guide');
Route::post('/delete-analyte-guide','AnalysisMethodElementsController@delete_analysis_guide')->name('delete_analysis_guide');

Route::post('/analysis-elements', 'AnalysisElementsController@add')->name('add-analysis-elements');
Route::post('/analysis-element/{id}', 'AnalysisElementsController@edit')->name('edit-analysis-element');
Route::get('/move-analysis-analyte/{direction}/{analysis}/{element}', 'AnalysisElementsController@move_analysis_analyte')->name('move-analysis-analyte');

Route::get('/analysis-methods', 'AnalysisMethodController@index')->name('analysis-methods')->middleware('haspermission:Laboratory.components.Methods.View');
Route::post('/analysis-methods', 'AnalysisMethodController@add')->name('add-analysis-methods')->middleware('haspermission:Laboratory.components.Methods.Add');
Route::post('/analysis-method/{id}', 'AnalysisMethodController@edit')->name('edit-analysis-method')->middleware('haspermission:Laboratory.components.Methods.Edit');
Route::get('/analysis-method/{id}', 'AnalysisMethodController@show')->name('analysis-method');

Route::post('/check_rft_no','SampleWorkFlowController@check_rft_no')->name('check_rft_no');
Route::post('/reject-approval-request','SampleWorkFlowController@return_batch_reception')->name('return_batch_reception');


Route::post('/analysis-method-elements', 'AnalysisMethodElementsController@add')->name('add-analysis-method-elements');
Route::post('/analysis-method-element/{id}', 'AnalysisMethodElementsController@edit')->name('edit-analysis-method-element');
Route::post('/analysis-method-element/{id}/delete', 'AnalysisMethodElementsController@delete')->name('delete-analysis-method-element');
Route::post('/update-method-reagents/{method_id}', 'MethodReagentController@modify')->name('update-method-reagents');


Route::get('/reporting-units', 'ReportingUnitController@index')->name('reporting-units')->middleware('haspermission:Laboratory.components.Reporting-Units.View');
Route::post('/reporting-units', 'ReportingUnitController@add')->name('add-reporting-unit')->middleware('haspermission:Laboratory.components.Reporting-Units.Add');
Route::post('/reporting-unit/{id}', 'ReportingUnitController@update')->name('edit-reporting-unit')->middleware('haspermission:Laboratory.components.Reporting-Units.Edit');

Route::get('/sample-analysis-stages', 'SampleAnalysisStageController@index')->name('sample-analysis-stages')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.View');
Route::post('/sample-analysis-stages', 'SampleAnalysisStageController@add')->name('add-sample-analysis-stage')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Add');
Route::post('/sample-analysis-stage/{id}', 'SampleAnalysisStageController@update')->name('update-sample_analysis_stage')->middleware('haspermission:Laboratory.components.Sample-Tracking-Stages.Edit');


Route::post('/sample-analysis-stages-to-sample-type/{sample_type_id}', 'SampleToSampleAnalysisStageController@add')->name('add-sample-analysis-stage-to-sample-type');
Route::post('/sample-analysis-stages-to-sample-type/{id}/inactivate', 'SampleToSampleAnalysisStageController@update')->name('update-sample-analysis-stage-to-sample-type');

Route::get('/move-sample-type/{direction}/{analysis}/{element}', 'SampleTypeController@move_sample_types')->name('move-sample-type');

##############################################Buffer###################################################
Route::get('/stock-monitoring/categories','Lab\BufferManagementController@index')->name('stock-monitoring-categories');
Route::get('/stock-management/sub-categories','Lab\BufferManagementController@stock_management_index')->name('stock_management_index');
Route::get('/stock-monitoring/sub-categories-delete/{id}','Lab\BufferManagementController@delete_category')->name('delete_category');
Route::post('/stock-monitoring/categories-add','Lab\BufferManagementController@add_lab_inventory_categories')->name('add_lab_inventory_categories');
Route::post('/stock-monitoring/filter','Lab\BufferManagementController@filter_data')->name('filter_data_category');
Route::post('/stock-monitoring/sub-categories-add','Lab\BufferManagementController@add_lab_sub_category')->name('add_lab_sub_inventory_categories');
Route::get('/stock-monitoring/sub-categories/show/{id}','Lab\BufferManagementController@show_lab_sub_category')->name('show_lab_sub_category');
Route::post('/stock-monitoring/lab-category-item','Lab\BufferManagementController@add_lab_category_item')->name('add_lab_category_item');
Route::post('/stock-monitoring/lab-category-item/delete','Lab\BufferManagementController@delete_show_lab_category_item')->name('delete_show_lab_category_item');
Route::post('/stock-monitoring/lab-category-item/edit','Lab\BufferManagementController@edit_lab_category_item')->name('edit_lab_category_item');
Route::post('/stock-monitoring/lab-sub-category/edit','Lab\BufferManagementController@edit_lab_sub_category')->name('edit_lab_sub_category');
Route::post('/stock-monitoring/lab-sub-category/delete','Lab\BufferManagementController@delete_sub_category')->name('delete_sub_category');
Route::post('/stock-monitoring/lab-sub-category/clone','Lab\BufferManagementController@clone_sub_category')->name('clone_sub_category');


Route::post('/stock-taking-counter/{id}/add','StockTakingCounterController@add')->name('add-stock-taking-counter');
Route::post('/stock-taking-counter/{id}/remove','StockTakingCounterController@remove')->name('remove-stock-taking-counter');


##############################################Sample Workflow###################################################
  Route::get('/sample-workflow/{status?}', 'SampleWorkFlowController@index')->name('sample-workflow');
  Route::get('/sample-workflow/{status?}/stage', 'SampleWorkFlowController@index')->name('sample-workflow')->middleware('haspermission:Laboratory.components.empty.View');
  Route::get('/sample-workflow/batch/{batch}/details/{client?}/{portal?}', 'SampleWorkFlowController@show')->name('view-batch-details');
  Route::post('/add-batch-info/{batch}', 'SampleWorkFlowController@add_batch_info')->name('add-batch-info');
  Route::post('/add-batch-samples/{batch}', 'SampleWorkFlowController@add_batch_samples')->name('add-batch-samples');
  Route::post('/add-new-samples', 'SampleWorkFlowController@add_batch_samples')->name('add-new-samples');
  Route::post('/delete-sample/{id}', 'SampleDetailsController@delete')->name('delete-sample');
  Route::post('/print-labels', 'SampleWorkFlowController@print_labels')->name('print-labels');
  Route::post('/send-out-email-reports', 'SampleWorkFlowController@send_report_email')->name('send-out-email-reports');
  Route::get('/lab/batch/approve/{id}','SampleWorkFlowController@approve_batch')->name('approve-batch-analysis');

  Route::post('/change-batch-workflow', 'SampleWorkFlowController@change_workflow_status')->name('change-batch-workflow');
  Route::post('/batch-approve-payment','SampleWorkFlowController@generate_batch_invoice')->name('generate_batch_invoice')->middleware('haspermission:Laboratory.components.Generate Invoice.View');
  Route::post('/batch-payment-reminders','SampleWorkFlowController@send_payment_notification')->name('send_payment_notification');
  Route::post('/return-back-verification','SampleWorkFlowController@return_back_verification')->name('return_back_verification');

  Route::get('/send_notification_reminders','Event\EventController@send_notification_reminders')->name('send_notification_reminders');

  Route::get('/regerateCustomerInvoice/{id}','SampleWorkFlowController@regerateCustomerInvoice')->name('regerateCustomerInvoice');
  // Route::get('/sample-workflow', 'SampleWorkFlowController@index')->name('sample-workflow');
  #############################################################################################################################
  Route::get('/billing-quotation/{stage?}','Invoice\QuotationController@index')->name('quotation-index')->middleware('haspermission:Laboratory.components.Quotation.View');
  Route::get('/billing/change-quotation-workflow/{id}/{stage}','Invoice\QuotationController@change_quotation_workflow')->name('change_quotation_workflow');
  Route::get('/billing-add-quote-detail-index/{id}/{stage?}','Invoice\QuotationController@view_quote_header_detail')->name('add-qoute-details-view');
  Route::post('/billing-add-quote-header','Invoice\QuotationController@add_quotation_header')->name('add-quotation-header');
  Route::post('/billing/add-quotation-detail/{id}','Invoice\QuotationController@add_quotation_detail')->name('add_quotation_detail');
  Route::get('/billing-quotation-view-final/{id}/{stage?}','Invoice\QuotationController@view_quotation_final')->name('view_quotation_final');
  Route::post('/billing/edit_quotation_detail','Invoice\QuotationController@edit_quotation_detail')->name('edit_quotation_detail');
  Route::get('/billing/delete_quotation_detail/{id}','Invoice\QuotationController@delete_quotation_detail')->name('delete_quotation_detail');
  Route::post('/billing/save_draft/{id}','Invoice\QuotationController@save_draft')->name('save_draft');
  Route::get('/billing/redirect_from_docs/{id}/{stage?}','Invoice\QuotationController@redirect_from_docs')->name('redirect_from_docs');
  Route::get('/billing/clone_quotation/{id}','Invoice\QuotationController@clone_quotation')->name('clone_quotation');
  Route::post('/billing/save-quotation-final/{id}','Invoice\QuotationController@save_quotation_final')->name('save_quotation_final');
  Route::post('/billing/delete_quotation/{id}','Invoice\QuotationController@delete_quotation')->name('delete_quotation');
  Route::get('/billing/print_quotation/{id}','Invoice\QuotationController@print_quotation')->name('print_quotation');
  Route::post('/billing/upload_quotation/{id}','Invoice\QuotationController@upload_quotation')->name('upload_quotation');
  Route::post('/approve-workflow','Invoice\QuotationController@approve_workflow')->name('approve-workflow');

  Route::post('/billing/payment-detail-add','InvoicePaymentDetailController@add')->name('payment-detail-add');
  Route::post('/billing/payment-detail-edit','InvoicePaymentDetailController@edit')->name('payment-detail-edit');
  Route::post('/billing/payment-detail-delete','InvoicePaymentDetailController@delete')->name('payment-detail-delete');

  Route::post('/approve/ready-proccess','SampleWorkFlowController@approve_batch_begin_process')->name('approve_batch_begin_process')->middleware('haspermission:Laboratory.components.Approve For Analysis.Edit');

  Route::get('/fetch-sample-type/{id}','SampleWorkFlowController@fetch_sample_type')->name('fetch_sample_type');
  Route::get('/fetch-sample-analytes/{id}/{analysis}/{detail?}','SampleWorkFlowController@fetch_sample_analyte')->name('fetch_sample_analytes');
  Route::get('/fetch-detail-data/{id}','Invoice\QuotationController@get_quotation_detail')->name('get_quotation_detail');
  Route::get('/addBatchSamplesDynamically','SampleWorkFlowController@addBatchSamplesDynamically')->name('addBatchSamplesDynamically');

  ######################################################################################################################################

	Route::post('/move-to-stage/{stage}/{batch_id}', 'SampleWorkFlowController@move_to_stage')->name('move-to-stage');
	Route::post('/move-to-workflow/{status}/{batch_id}', 'SampleWorkFlowController@move_to_workflow')->name('move-to-workflow');
	Route::post('/add-analytes-to-sample-analysis', 'SampleWorkFlowController@add_analyte_to_sample_analysis')->name('add-analytes-to-sample-analysis');
	Route::post('/capture-raw-results', 'SampleWorkFlowController@capture_raw_results')->name('capture-raw-results');

	Route::get('/process-raw-results/{batch_id}', 'SampleWorkFlowController@process_results')->name('process-raw-results');
	Route::post('/report-interpretations/{batch_id}', 'ReportHeaderDetailController@report_interpretations')->name('report-interpretations');
	Route::get('/process-pdf-report/{batch_id}', 'ReportHeaderDetailController@process_pdf_report')->name('process-pdf-report');
  Route::get('colorQrCode/', 'ReportHeaderDetailController@colorQrCode')->name('colorQrCode');


	Route::get('/fetch-unit-stuff/{name}/{client}', 'SampleWorkFlowController@fetch_unit_stuff')->name('fetch-unit-stuff');
  Route::get('/mail-report', 'MailController@html_email')->name('mail-report');

  Route::post('/lab/delete/batch','SampleWorkFlowController@delete_batch')->name('delete-batch');
  Route::post('/sample-interpretations/{sample_id}','ReportHeaderDetailController@sample_interpretations')->name('sample-interpretations');

  Route::post('/add_batch_attachment','SampleWorkFlowController@add_batch_attachment')->name('add_batch_attachment');
  Route::post('/delete_batch_attachmment','SampleWorkFlowController@delete_batch_attachmment')->name('delete_batch_attachmment');

###############################################################################################################
  Route::post('/lab/batch/ammendment','BatchAmmendmentController@add')->name('add-batch-ammendment');

######################LABS######################################################################################

#############################################INVENTORY##########################################################

Route::get('/inventory-home', 'HomeController@inventory')->name('inventory-home')->middleware('haspermission:Inventory.permission');
Route::get('/inventory-activity', 'InventoryItemController@index')->name('inventory-activity')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::get('/inventory-activity/server-side', 'InventoryItemController@activity_serverside')->name('get-stock-movement')->middleware('haspermission:Inventory.components.Inventory-Movement.View');


##REPORTS##
Route::get('/inventory-reports', 'ReportGeneratorController@inventory_reports')->name('inventory-reports')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('report/configuration/update/{id}', 'ReportGeneratorController@save_report')->name('update_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::get('/reports/fields/{table}', 'ReportGeneratorController@fields')->name('fields')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/save', 'ReportGeneratorController@store')->name('store_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/fetch', 'ReportGeneratorController@fetch')->name('fetch_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/delete', 'ReportGeneratorController@delete')->name('delete_report')->middleware('haspermission:Inventory.components.Inventory-Movement.View');
Route::post('/reports/print','ReportGeneratorController@print')->name('report_print')->middleware('haspermission:Inventory.components.Inventory-Movement.View');


Route::get('/inventory-categories', 'InventoryCategoriesController@index')->name('inventory-categories')->middleware('haspermission:Inventory.components.Categories.View');
Route::post('/inventory-categories', 'InventoryCategoriesController@add')->name('add-inventory-category')->middleware('haspermission:Inventory.components.Categories.Add');
Route::post('/inventory-category/{id}', 'InventoryCategoriesController@edit')->name('edit-inventory-category')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/inventory-category/{id}/delete', 'InventoryCategoriesController@destroy')->name('delete-inventory-category')->middleware('haspermission:Inventory.components.Categories.Delete');
Route::get('/inventory-category/{id}', 'InventoryCategoriesController@show')->name('show-inventory-category');

Route::post('/change-brand-details/{id}', 'ItemBrandController@edit')->name('change-brand-image')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/add-item-brand/{subcategory}', 'ItemBrandController@add')->name('add-item-brand')->middleware('haspermission:Inventory.components.Categories.Edit');
Route::post('/delete-item-brand/{id}', 'ItemBrandController@delete')->name('delete-item-brand')->middleware('haspermission:Inventory.components.Categories.Delete');

Route::get('/inventory-stores', 'InventoryStoreController@index')->name('inventory-stores')->middleware('haspermission:Inventory.components.Store.View');
Route::post('/inventory-stores', 'InventoryStoreController@add')->name('add-inventory-store')->middleware('haspermission:Inventory.components.Store.Add');
Route::post('/inventory-store/{id}', 'InventoryStoreController@edit')->name('edit-inventory-store')->middleware('haspermission:Inventory.components.Store.Edit');
Route::post('/inventory-store/{id}/delete', 'InventoryStoreController@delete')->name('delete-inventory-store')->middleware('haspermission:Inventory.components.Store.Delete');

Route::get('/inventory-store-slots/{store}', 'InventoryStoreSlotController@index')->name('inventory-store-slots');
Route::post('/inventory-store-slots/{store}', 'InventoryStoreSlotController@add')->name('add-inventory-store-slot');
Route::post('/inventory-store-slots/{id}/slot', 'InventoryStoreSlotController@edit')->name('edit-inventory-store-slot');
Route::post('/inventory-store-slots/{id}/delete', 'InventoryStoreSlotController@delete')->name('delete-inventory-store-slot');

Route::get('/inventory-slot-contents/{slot}/{store}', 'InventoryStoreSlotContentController@index')->name('inventory-slot-contents');
Route::post('/inventory-slot-contents/{slot}/{store}', 'InventoryStoreSlotContentController@add')->name('add-inventory-slot-content');
Route::post('/inventory-slot-contents/{id}/delete', 'InventoryStoreSlotContentController@delete')->name('delete-inventory-slot-content');

Route::get('/show-inventory-items/{category}/{id}', 'InventorySubCategoriesController@index')->name('show-inventory-items');
Route::post('/inventory-sub-categories', 'InventorySubCategoriesController@add')->name('add-inventory-sub-category');
Route::post('/inventory-sub-category/{id}', 'InventorySubCategoriesController@edit')->name('edit-inventory-sub-category');
Route::post('/inventory-sub-category/{id}/delete', 'InventorySubCategoriesController@destroy')->name('delete-inventory-sub-category');

Route::post('/add-subcategory-conversion/{id}', 'UnitOfMeasureConversionController@update')->name('add-subcategory-conversion');
Route::post('/delete-item-conversion', 'UnitOfMeasureConversionController@delete')->name('delete-item-conversion');

Route::post('/add-subcategory-item-state/{id}', 'ItemStateController@update')->name('add-subcategory-item-state');
Route::post('/delete-item-state', 'ItemStateController@delete')->name('delete-item-state');

Route::post('/add-store-contacts/{id}', 'InventoryStoreContactController@add')->name('add-store-contacts');
Route::post('/delete-store-contacts', 'InventoryStoreContactController@delete')->name('delete-store-contacts');

Route::get('/inventory-departments', 'InventoryDepartmentController@index')->name('show-inventory-departments')->middleware('haspermission:Inventory.components.Departments.View');
Route::post('/inventory-departments/{module?}', 'InventoryDepartmentController@add')->name('add-inventory-department')->middleware('haspermission:Inventory.components.Departments.Add');
Route::post('/inventory-department/{id}', 'InventoryDepartmentController@edit')->name('edit-inventory-department')->middleware('haspermission:Inventory.components.Departments.Edit');
Route::get('/inventory-department/{id}', 'InventoryDepartmentController@show')->name('show-inventory-department');
Route::post('/inventory-sub-category/{id}/delete', 'InventoryDepartmentController@destroy')->name('delete-inventory-department')->middleware('haspermission:Inventory.components.Departments.Delete');

Route::post('/inventory-items', 'InventoryItemController@add')->name('add-inventory-items');
Route::post('/inventory-item-transfer', 'InventoryItemController@transfer')->name('transfer-inventory-items');
Route::post('/stock-keeping', 'InventoryItemController@stock_keeping')->name('stock-keeping');
Route::post('/item-disposal', 'InventoryItemController@item_disposal')->name('item-disposal');
Route::post('/return-item-to-store', 'InventoryItemController@return_2_store')->name('return-item-to-store');

Route::get('/stock-taking-list', 'StockTakingController@index')->name('stock-taking-list')->middleware('haspermission:Inventory.components.Stock-Taking.View');
Route::get('/stock-taking-update/{id}/{print?}', 'StockTakingController@show')->name('stock-taking-sheet');
Route::post('/stock-taking-update/{id?}', 'StockTakingController@update')->name('stock-taking-update')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');
Route::post('/stock-taking-freeze-stores/{id?}', 'StockTakingController@freeze_stores')->name('stock-taking-freeze-stores')->middleware('haspermission:Inventory.components.Stock-Taking.Edit');
Route::post('/stock-taking-save-capture/{id?}', 'StockTakingController@save_capture')->name('stock-taking-save-capture');

Route::get('/stock-transfer-list', 'StockTransferController@index')->name('stock-transfer-list')->middleware('haspermission:Inventory.components.Stock-Transfer.View');
Route::get('/stock-transfer-update/{id}', 'StockTransferController@show')->name('stock-transfer-sheet');
Route::post('/stock-transfer-update/{id?}', 'StockTransferController@update')->name('stock-transfer-update')->middleware('haspermission:Inventory.components.Stock-Transfer.Edit');
Route::post('/stock-transfer-items-update/{id?}', 'StockTransferController@update_items')->name('stock-transfer-items-update')->middleware('haspermission:Inventory.components.Stock-Transfer.Edit');
Route::post('/stock-transfer-item-delete', 'StockTransferController@delete_item')->name('stock-transfer-item-delete')->middleware('haspermission:Inventory.components.Stock-Transfer.Delete');

Route::post('/edit/supplier-quote/{id}','SupplierQuoteController@edit')->name('edit-supplier-item-quote');
Route::post('/remove/supplier-quote/{id}/{itemID?}','SupplierQuoteController@remove')->name('remove-supplier-item-quote');

Route::post('/undo-supplier-award/{id}','SupplierQuoteController@undo_supplier_award')->name('undo-supplier-award');

Route::post('/remove-this-supplier/{id}/{itemID}','SupplierController@remove_supplier_from_inventory')->name('remove-this-supplier');

#############################################INVENTORY##########################################################

#############################################GENERAL REQUISITION#############################################

Route::prefix('general-requisition')->group(function(){
  Route::get('/list', 'GeneralRequisition\GeneralRequisitionController@index')->name('general-requisition-list');
  Route::get('/show/{id?}', 'GeneralRequisition\GeneralRequisitionController@show')->name('general-requisition-view');
  Route::get('/show/{id}/document', 'GeneralRequisition\GeneralRequisitionController@view_document')->name('general-requisition-document');
  Route::get('/get-supplier-list/{search?}', 'GeneralRequisition\GeneralRequisitionController@suppliers')->name('get-supplier-list');
  Route::get('/get-gr-items-list/{search?}', 'GeneralRequisition\GeneralRequisitionController@inventory_items')->name('get-gr-items-list');
  Route::post('/remove-general-requisition-rows/{id}', 'GeneralRequisition\GeneralRequisitionController@remove_items')->name('remove-general-requisition-rows');
  Route::post('/update/{id?}', 'GeneralRequisition\GeneralRequisitionController@update')->name('general-requisition-update');
  Route::post('/add-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@add')->name('add-gr-quote');
  Route::post('/update-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@update')->name('update-gr-quotes');
  Route::post('/delete-quote/{id?}', 'GeneralRequisition\GeneralRequisitionSupplierQuotesController@remove')->name('delete-gr-quotes');
  Route::post('/change-status/{id}/{type}', 'GeneralRequisition\GeneralRequisitionController@change_status')->name('change-status');

  Route::post('/jump-request-to-status/{id}', 'RequisitionController@jump_request_to_status')->name('jump-request-to-status')->middleware('haspermission:Inventory.components.Request for Quotation.Edit');
  
  Route::post('/send-notification/{id}/{type}', 'GeneralRequisition\GeneralRequisitionController@sendApprovalNotifications')->name('send-notification-gr');
  
  Route::post('/change-approver-this-gr/{id?}/{field?}', 'GeneralRequisition\GeneralRequisitionController@change_approver')->name('change-approver-this-gr');
  Route::post('/approve-this-gr/{id?}/{type?}', 'GeneralRequisition\GeneralRequisitionController@approve_this')->name('approve-this-gr');
});

#############################################GENERAL REQUISITION#############################################

#############################################LOCATIONS##########################################################
Route::get('/organizational-locations', 'InventoryLocationController@index')->name('inventory-locations');
Route::post('/organizational-locations', 'InventoryLocationController@add')->name('add-inventory-location');
Route::get('/organizational-locations/{id}', 'InventoryLocationController@show')->name('show-inventory-locations');
Route::post('/organizational-locations/{id}', 'InventoryLocationController@edit')->name('edit-inventory-location');
Route::post('/organizational-locations/{id}/delete', 'InventoryLocationController@destroy')->name('delete-inventory-location');

Route::get('/set-user-location/{id}', 'InventoryLocationController@set_user_location')->name('set-user-location');

Route::post('/add-user-access/{id}', 'InventoryLocationController@add_user')->name('add-user-access');
Route::post('/remove-user-access/{id}/{user}', 'InventoryLocationController@remove_user_access')->name('remove-user-access');
#############################################LOCATIONS##########################################################


#############################################EQUIPMENT##########################################################
Route::get('/equipment-home', 'Equipment\EquipmentController@index')->name('equipment-home')->middleware('haspermission:Equipment.permission');
Route::post('/equipment', 'Equipment\EquipmentController@add')->name('add-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Add');
Route::get('/equipment/{id}', 'Equipment\EquipmentController@show')->name('view-equipment')->middleware('haspermission:Equipment.components.Equipment-List.View');
Route::post('/equipment/{id}', 'Equipment\EquipmentController@edit')->name('edit-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Edit');
Route::post('/schedule-maintainance/{id}', 'Equipment\MaintainanceCalibrationLogController@add')->name('new-maintainance')->middleware('haspermission:Equipment.components.Maintainance-Log.Add');
Route::post('/edit-maintainance', 'Equipment\MaintainanceCalibrationLogController@edit')->name('edit-maintainance')->middleware('haspermission:Equipment.components.Maintainance-Log.Edit');
Route::post('/usage-log/{id}/{equipment}', 'Equipment\EquipmentUsageController@update')->name('usage-log')->middleware('haspermission:Equipment.components.Maintainance-Log.Edit');
Route::post('/add-operators/{equipment}', 'Equipment\EquipmentOperatorController@add')->name('add-operators')->middleware('haspermission:Equipment.components.Operator-Log.Add');
Route::post('/remove-operator/{id}', 'Equipment\EquipmentOperatorController@destroy')->name('remove-operator')->middleware('haspermission:Equipment.components.Operator-Log.Delete');

Route::post('/add-equipment-attachment','Equipment\MaintainanceCalibrationLogController@add_equipment_attachment')->name('add_equipment_attachment');

Route::post('/verification-log/{id}','Equipment\VerificationLogController@add')->name('add-verification')->middleware('haspermission:Equipment.components.Verification-Log.Add');
Route::post('/edit/verification-log/{id}','Equipment\VerificationLogController@edit')->name('edit-verification')->middleware('haspermission:Equipment.components.Verification-Log.Edit');
Route::get('/delete/verification-log/{id}','Equipment\VerificationLogController@delete')->name('delete-verification')->middleware('haspermission:Equipment.components.Verification-Log.Delete');
Route::post('/dispose/equipment/{id}','Equipment\EquipmentController@dispose')->name('dispose-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Delete');
Route::get('/revert/equipment/{id}','Equipment\EquipmentController@revert')->name('revert-equipment')->middleware('haspermission:Equipment.components.Equipment-List.Delete');
Route::post('/delete/part-repaired','Equipment\MaintainanceCalibrationLogController@delete')->name('delete-part-repaired')->middleware('haspermission:Equipment.components.Repair-Log.Delete');
Route::post('/delete/log','Equipment\MaintainanceCalibrationLogController@delete_logs')->name('delete-logs')->middleware('haspermission:Equipment.components.Repair-Log.Delete');

#############################################EQUIPMENT##########################################################


#############################################SUPPLIER###########################################################
Route::get('/inventory-suppliers', 'SupplierController@index')->name('inventory-suppliers')->middleware('haspermission:Inventory.components.Suppliers.View');
Route::post('/inventory-suppliers', 'SupplierController@add')->name('add-inventory-supplier')->middleware('haspermission:Inventory.components.Suppliers.Add');
Route::get('/inventory-supplier/{id}', 'SupplierController@show')->name('show-inventory-supplier');
Route::post('/inventory-supplier/{id}', 'SupplierController@edit')->name('edit-inventory-supplier')->middleware('haspermission:Inventory.components.Suppliers.Edit');

Route::post('/add-supplier-category/{supplier}', 'SupplierCategoryController@add')->name('add-supplier-category');
Route::post('/add-supplier-to-inventory/{itemID}', 'SupplierController@add_supplier_to_inventory')->name('add-supplier-to-inventory');
Route::post('/delete-supplier-category/{id}', 'SupplierCategoryController@destroy')->name('delete-supplier-category');
Route::post('/change-category-image/{id}', 'SupplierCategoryController@change_image')->name('change-category-image');
Route::post('/supplier-rating', 'InventorySupplierRatingController@add')->name('supplier-rating');
#############################################SUPPLIER##########################################################

#############################################SUPPLIER CONTRACTS##########################################################
Route::post('/create-supplier-contract/{supplier}', 'SupplierContractController@modify')->name('create-supplier-contract');
Route::post('/edit-supplier-contract/{supplier}/{id}', 'SupplierContractController@modify')->name('edit-supplier-contract');
#############################################SUPPLIER CONTRACTS##########################################################

#############################################ORDERS############################################################
Route::post('/create-order/{supplier?}', 'InventoryOrderController@add')->name('create-order');
Route::post('/edit-order/{supplier}/{order_id}', 'InventoryOrderController@edit')->name('edit-order');
Route::post('/edit-order/{supplier}/{order_id}', 'InventoryOrderController@edit')->name('edit-order');
Route::get('/server-side/{field}/{fieldID}', 'InventoryOrderController@server_side')->name('server-side-orders');
Route::get('/server-side/{field}/{fieldID}/{type?}/requisition', 'InventoryOrderController@server_side_po')->name('server-side-purchase-orders');
Route::post('/delete-order-items/{order_item}', 'InventoryOrderItemController@delete')->name('delete-order-item');
Route::get('/get-order-items/{field}/{fieldID}', 'InventoryOrderItemController@getItems')->name('get-order-items');
Route::post('/accept-order-items/{order_id}', 'InventoryOrderItemToInventoryItemController@acceptItems')->name('accept-order-items');
#############################################ORDERS############################################################

#############################################CUSTOMERS##########################################################
Route::get('/crm-home', 'CRM\CRMCustomerController@index')->name('customers-list')->middleware('haspermission:CRM.permission');
Route::post('/fetch-client-quotes','CRM\CRMCustomerController@fetch_client_quote')->name('fetch-client-qoutes');
Route::get('/crm-home-config', 'CRM\CRMCustomerController@checkConfig')->name('add-config-customer')->middleware('haspermission:CRM.permission');
Route::post('/customers', 'CRM\CRMCustomerController@add')->name('add-customers')->middleware('haspermission:CRM.components.Customer-List.Add')
;
Route::get('/customer/{id}', 'CRM\CRMCustomerController@show')->name('show-customer')->middleware('haspermission:CRM.components.Customer-List.View');
Route::post('/customer/{id}', 'CRM\CRMCustomerController@edit')->name('edit-customer')->middleware('haspermission:CRM.components.Customer-List.Edit');
Route::post('/customer/{id}/label', 'CRM\CRMCustomerController@edit_label')->name('change-client-label-name')->middleware('haspermission:CRM.components.Customer-List.Edit');
Route::post('/delete-customer','CRM\CRMCustomerController@delete_customer')->name('delete_customer')->middleware('haspermission:CRM.components.Customer-List.Delete');

Route::post('/add/customer-certification/{id}','CRM\CustomerCertificationController@add')->name('add-customer-certification')->middleware('haspermission:CRM.components.Certificates.Add');
Route::post('/edit/customer-certification/{id}','CRM\CustomerCertificationController@edit')->name('edit-customer-certification')->middleware('haspermission:CRM.components.Certificates.Edit');
Route::post('/delete/customer-certification/{id}','CRM\CustomerCertificationController@delete')->name('delete-customer-certification')->middleware('haspermission:CRM.components.Certificates.Delete');


Route::get('/complaint-type/home','CRM\Complaint\ComplaintTypeController@index')->name('complaint-type-home')->middleware('haspermission:CRM.components.Complaint Type.View');
Route::post('/edit/complaint-type/{id}','CRM\Complaint\ComplaintTypeController@edit')->name('edit-complaint-type')->middleware('haspermission:CRM.components.Complaint Type.Edit');
Route::post('/add/complaint-type','CRM\Complaint\ComplaintTypeController@add')->name('add-complaint-type')->middleware('haspermission:CRM.components.Complaint Type.Add');

Route::get('/complaint/{stage}','CRM\Complaint\ComplaintController@index')->name('complaint-workflow')->middleware('haspermission:CRM.components.empty.View');
Route::post('/add/open-complaint','CRM\Complaint\ComplaintController@add')->name('add-complaint')->middleware('haspermission:CRM.components.Open Complaints.Add');
Route::post('/add-open-complaint/customer','CRM\Complaint\ComplaintController@customer_add')->name('customer-add-complaint');
Route::post('/edit-complaint/{id}','CRM\Complaint\ComplaintController@edit')->name('edit-complaint')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::get('/show-complaint/{id}','CRM\Complaint\ComplaintController@show')->name('show-complaint')->middleware('haspermission:CRM.components.Complaints.View');
Route::post('/add/complaint-notes/{id}','CRM\Complaint\ComplaintNotesController@add')->name('add-notes')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/edit/complaint-notes/{id}','CRM\Complaint\ComplaintNotesController@edit')->name('edit-notes')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/add/complaint-attachment/{id}','CRM\Complaint\ComplaintAttachmentController@add')->name('add-attachment')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/edit/complaint-attachment/{id}','CRM\Complaint\ComplaintAttachmentController@edit')->name('edit-attachment')->middleware('haspermission:CRM.components.Complaints.Edit');
Route::post('/approve-complaint/{id}','CRM\Complaint\ComplaintWorkflowController@approve_next')->name('approve-complaint')->middleware('haspermission:CRM.components.Complaints Approval.Edit');
Route::post('/reverse-complaint/{id}','CRM\Complaint\ComplaintWorkflowController@reverse_approval')->name('reverse-complaint')->middleware('haspermission:CRM.components.Complaints Approval.Delete');
Route::post('/reject-complaint/{id}','CRM\Complaint\ComplaintWorkflowController@reject_complaint')->name('reject-complaint')->middleware('haspermission:CRM.components.Complaints Approval.Delete');
Route::post('/add/complaint-resolution/{id}','CRM\Complaint\ComplaintResolutionController@add')->name('add-resolution')->middleware('haspermission:CRM.components.Complaints Resolution.Add');
Route::post('/edit/complaint-resolution/{id}','CRM\Complaint\ComplaintResolutionController@edit')->name('edit-resolution')->middleware('haspermission:CRM.components.Complaints Resolution.Edit');
Route::get('/show/complaint/{id}','CRM\Complaint\ComplaintController@show_all')->name('complaint-show')->middleware('haspermission:CRM.components.Complaints.View');

Route::get('/customer-feedback/home','CRM\CustomerFeedbackController@index')->name('feedback-home')->middleware('haspermission:CRM.components.Feedbacks.View');
Route::post('/add/customer-feedback','CRM\CustomerFeedbackController@add')->name('add-feedback')->middleware('haspermission:CRM.components.Customer Feedback.View');
Route::post('/add-feedback/customer','CRM\CustomerFeedbackController@customer_add')->name('customer-add-feedback');
Route::post('/edit/customer-feedback/{id}','CRM\CustomerFeedbackController@edit')->name('edit-feedback')->middleware('haspermission:CRM.components.Feedbacks.Edit');

Route::post('/request-resolution-approval/{id}','CRM\Complaint\ComplaintWorkflowController@request_resolution_approve')->name('request-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Add');
Route::post('/reverse-resolution/{id}','CRM\Complaint\ComplaintWorkflowController@reverse_resolution')->name('reverse-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Edit');
Route::post('/reject-resolution/{id}','CRM\Complaint\ComplaintWorkflowController@reject_resolution')->name('reject-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Delete');
Route::post('/approve-resolution/{id}','CRM\Complaint\ComplaintWorkflowController@approve_resolution')->name('approve-resolution')->middleware('haspermission:CRM.components.Resolution Approval.Edit');

Route::post('/company-units/{cust_id}', 'CRM\CRMCompanyUnitController@add')->name('add-company-units')->middleware('haspermission:CRM.components.Company-Units.Add');
Route::post('/company-unit/{id}/{cust_id}', 'CRM\CRMCompanyUnitController@edit')->name('edit-company-unit')->middleware('haspermission:CRM.components.Company-Units.Edit');

Route::post('/sample-point', 'CRM\SamplePointController@add')->name('add-sample-point')->middleware('haspermission:CRM.components.Sample-Points.Add');
Route::post('/sample-point/{id}', 'CRM\SamplePointController@edit')->name('edit-sample-point')->middleware('haspermission:CRM.components.Sample-Points.Edit');

Route::post('/customer-product', 'CRM\CompanyProductController@add')->name('add-customer-product')->middleware('haspermission:CRM.components.Products.Add');
Route::post('/customer-product/{id}', 'CRM\CompanyProductController@edit')->name('edit-customer-product')->middleware('haspermission:CRM.components.Products.Edit');

Route::post('/company-contacts/{cust_id}', 'CRM\CustomerContactController@add')->name('add-company-contacts')->middleware('haspermission:CRM.components.Contacts.Add');
Route::post('/company-contact/{id}/{cust_id}', 'CRM\CustomerContactController@edit')->name('edit-company-contact')->middleware('haspermission:CRM.components.Contacts.Edit');

Route::get('/fetch-customer-contacts/{id}','CRM\CustomerContactController@get_customer_client')->name('get_customer_client');
Route::get('/validate-Crm-Customer/Name/{name}/Ajax','CRM\CRMCustomerController@validateCrmCustomerNameAjax')->name('validateCrmCustomerNameAjax');
#############################################SUPPLIER##########################################################

############################################### QUALIFICATIONS #############################################################
Route::get('/qualification-home','Lab\QualificationsController@index')->name('qualification-home');
Route::post('/edit/qualification/{id}','Lab\QualificationsController@edit')->name('edit-qualification');
Route::post('/add/qualification','Lab\QualificationsController@add')->name('add-qualification');
############################################### END UALIFICATIONS #############################################################

####################################AJAX LINKS#######################################
Route::get('/analysis-types/{id}', 'AnalysisTypeController@by_sample_id')->name('api-analysis-types-by-sample');
Route::get('/missing_analysis_parameters_by_sample_code', 'SampleWorkFlowController@missing_analysis_parameters_by_sample_code')->name('missing_analysis_parameters_by_sample_code');
Route::post('/remove-analyte-from-captured-result', 'SampleWorkFlowController@remove_analyte_from_captured_result')->name('remove-analyte-from-captured-result');
Route::post('/fetch/results-remark','SampleWorkFlowController@fetch_results_remark')->name('fetch_results_remark');
Route::get('/get-customer-contacts/{type}/{customer_id}', 'CRM\CustomerContactController@get_contacts')->name('get-customer-contacts');
Route::get('/stock-transfer-json', 'StockTransferController@getJson')->name('stock-transfer-json');
Route::get('/get-material-type-states', 'StockTransferController@getMaterialTypeStates')->name('get-material-type-states');
Route::get('/get-store-slots-by-item/{item}', 'InventoryStoreController@store_slots_by_item')->name('get-store-slots-by-item');
####################################AJAX LINKS#######################################

####################################AUDIT TRAIL#######################################
Route::get('/audit-logs', 'AuditController@index')->name('get-audit-logs');
Route::get('/server-side-audit_logs/{user_id?}', 'AuditController@server_side')->name('server-side-audit_logs');
Route::get('/server-side-audit_log/{id}/details', 'AuditController@server_side_details')->name('server-side-audit_logs-details');
####################################AUDIT TRAIL#######################################

####################################REQUISITION TRAIL#######################################
Route::get('/req/{stage}', 'RequisitionController@open_stage')->name('go_to_stage')->middleware('haspermission:Inventory.components.empty.View');
Route::get('/req/{stage}/{id}/{ammendement?}', 'RequisitionController@show')->name('view-request-details');
Route::post('/req/{stage}/{id}/{ammendement?}', 'RequisitionController@update')->name('save-request-details');
Route::get('/req-report-generate/{id}/{supply?}', 'ReportGeneratorController@generate_report')->name('req-report-generate');

Route::get('/download-request-items/{id}/{isPDF?}', 'ReportGeneratorController@download_items_xlsx')->name('download-request-items');

Route::post('/create-lpo-from-mr/{id}', 'RequisitionController@create_lpo_from_mr')->name('create-lpo-from-mr');
Route::post('/change-req-approver/{id}', 'RequisitionController@change_req_approver')->name('change-req-approver');

Route::post('/req/download/{id}/{type}', 'RequisitionController@download')->name('download-requisition-doc');
Route::post('/mark-gr-as-complete/{id}', 'RequisitionController@mark_gr_as_complete')->name('mark-gr-as-complete');
####################################REQUISITION TRAIL#######################################

###############################################BATCH COMMENTS#######################################
Route::post('/add-batch-comment', 'BatchCommentController@add')->name('add-batch-comment');
Route::post('/edit-batch-comment/{id}', 'BatchCommentController@edit')->name('edit-batch-comment');
################################################BATCH COMMENTS#######################################

###############################################My Approvals#######################################
Route::get('/my-approvals', 'HomeController@my_approvals')->name('my-approvals')->middleware('haspermission:Inventory.components.Approval-Requests.View');
################################################My Approvals#######################################

####################################PERSONNEL LINKS#######################################
Route::get('/personnel-home', 'PersonnelController@index')->name('personnel-home');
Route::post('/add-personnel/{id}', 'PersonnelController@add')->name('add-personnel');
Route::get('/view-personnel/{id}', 'PersonnelController@show_personnel')->name('view-personnel');

Route::get('/organizational-departments', 'PersonnelController@departments')->name('show-organizational-departments');
Route::post('/organizational-departments', 'PersonnelController@add_department')->name('add-organizational-department');
Route::post('/personnel-organizational/{id}', 'PersonnelController@edit_department')->name('edit-organizational-department');

Route::post('/add-personnel-role/{user_id}', 'UserRoleController@add')->name('add-personnel-role');
Route::post('/remove-personnel-role/{id}', 'UserRoleController@remove')->name('remove-personnel-role');

Route::post('/personnel-state-change/{id}','PersonnelController@deactivate_personnel')->name('personnel-state');
Route::post('/reset-personnel-password/{id}','PersonnelController@reset_personnel_password')->name('reset-personnel');

Route::get('/personel/certification-coniguration','Personel\CertificationController@index')->name('personnel-certification-home');

Route::post('/add/personnel-certification/{id}','Personel\PersonnelCertificationController@add')->name('add-personnel-certification');
Route::post('/edit/personnel-certification/{id}','Personel\PersonnelCertificationController@edit')->name('edit-personnel-certification');
Route::post('/delete/personnel-certification/{id}','Personel\PersonnelCertificationController@delete')->name('delete-personnel-certification');

Route::post('/personnel-managment/add-job-responsibility/{id}','ModulePreConfigsController@addResponsibilities')->name('addResponsibilities');
Route::post('/personnel-managment/edit-job-responsibility/{id}','ModulePreConfigsController@editResposibility')->name('editResposibility');
Route::get('/personnel-managment/show-job-responsibility/{id}','ModulePreConfigsController@showResponsibility')->name('showResponsibility');

Route::get('/personnel-user/profile','PersonnelController@user_profile')->name('user_profile');
############################
####################################PERSONNEL LINKS#######################################

####################################ROLES LINKS#######################################
Route::get('/organizational-roles', 'RoleController@index')->name('organizational-roles');
Route::get('/organizational-role/{id}', 'RoleController@show')->name('view-organizational-role');
Route::post('/add-organizational-role', 'RoleController@add')->name('add-organizational-role');
Route::post('/edit-organizational-role/{id}', 'RoleController@edit')->name('edit-organizational-role');
Route::post('/save-role-rights/{id}', 'RoleController@save_roles')->name('save-role-rights');

Route::post('/add/role-certification/{id}','Personel\CertificationController@add_role_certification')->name('add-role-certification');
Route::post('/edit/role-certification/{id}','Personel\CertificationController@edit_role_certification')->name('edit-role-certification');
Route::post('/delete/role-certification/{id}','Personel\CertificationController@delete_role_certification')->name('delete-role-certification');
####################################ROLES LINKS#######################################

####################################APPROVALS LINKS#######################################
Route::post('/add-approval-to-stage/{stage}', 'ApprovalsController@add')->name('add-approval-to-stage');
Route::post('/edit-approval-to-stage/{id}', 'ApprovalsController@edit')->name('edit-approval-to-stage');
Route::post('/remove-approval-to-stage/{id}', 'ApprovalsController@remove')->name('remove-approval-to-stage');
####################################APPROVALS LINKS#######################################

####################################ATTACHMENT LINKS#######################################
Route::post('/add-page-attachment', 'HomeController@add_attachment')->name('add-page-attachment');
Route::post('/remove-page-attachment/{docID}', 'HomeController@remove_attachment')->name('remove-page-attachment');
####################################ATTACHMENT LINKS#######################################

###################################ASSETS LINKS######################################
Route::get('/asset-type-home','Asset\AssetTypeController@index')->name('asset-type-home')->middleware('haspermission:Equipment.components.Asset-Type.View');
Route::post('/asset-type','Asset\AssetTypeController@add')->name('add-asset-type')->middleware('haspermission:Equipment.components.Asset-Type.Add');
Route::post('/edit/asset-type/{id}','Asset\AssetTypeController@edit')->name('edit-asset-type')->middleware('haspermission:Equipment.components.Asset-Type.Edit');

Route::get('/asset-location-home','Asset\AssetLocationController@index')->name('asset-location-home')->middleware('haspermission:Equipment.components.Asset-Location.View');
Route::post('/asset-location','Asset\AssetLocationController@add')->name('add-asset-location')->middleware('haspermission:Equipment.components.Asset-Location.Add');
Route::post('/edit/asset-location/{id}','Asset\AssetLocationController@edit')->name('edit-asset-location')->middleware('haspermission:Equipment.components.Asset-Location.Edit');
###################################ASSETS LINKS######################################

####################################MODULE PRE_CONFIGS LINKS#######################################
Route::get('/module-pre-configs/{config}/{module}', 'ModulePreConfigsController@index')->name('module-pre-configs')->middleware('haspermission:Inventory.components.Configuration.View');
Route::post('/add-module-pre-configs/{id}/{config}/{module}', 'ModulePreConfigsController@update')->name('add-module-pre-configs')->middleware('haspermission:Inventory.components.Configuration.Add');
Route::get('/view-currency-conversions', 'ModulePreConfigsController@view_currency_conversion')->name('view-currency-conversions')->middleware('haspermission:Inventory.components.Configuration.View');
Route::get('/view-uom-conversions', 'ModulePreConfigsController@view_uom_conversion')->name('view-uom-conversions')->middleware('haspermission:Inventory.components.Configuration.View');
Route::post('/add-currency-conversions', 'ModulePreConfigsController@currency_conversion')->name('add-currency-conversions')->middleware('haspermission:Inventory.components.Configuration.Add');
Route::post('/add-uom-conversion', 'ModulePreConfigsController@uom_conversion')->name('add-uom-conversion')->middleware('haspermission:Inventory.components.Configuration.Add');
Route::get('/show-material-type/{id}', 'ModulePreConfigsController@show_material_type')->name('show-material-type')->middleware('haspermission:Inventory.components.Configuration.View');
####################################MODULE PRE_CONFIGS LINKS#######################################

####################################PRICELISTS#######################################
Route::get('/pricelists', 'PricelistItemController@index')->name('view-pricelists')->middleware('haspermission:Laboratory.components.Pricelists.View');;
Route::post('/pricelist/{id?}', 'PricelistItemController@update')->name('update-pricelist');

Route::post('/pricelist/{id}/upload', 'PricelistItemController@upload')->name('upload-pricelist-pdf');
Route::post('/pricelist/{id}/email', 'PricelistItemController@email')->name('email-pricelist-pdf');

Route::get('/pricelist/{id}/{print?}', 'PricelistItemController@show')->name('show-pricelist');
Route::post('/pricelist/{id}/item', 'PricelistItemController@update_item')->name('update-pricelist-item');
Route::post('/save-price-changes/{id}', 'PricelistItemController@save_price_changes')->name('save-price-changes');
Route::post('/clone-items-to-new-pricelist/{id}', 'PricelistItemController@clone_items_to_new_pricelist')->name('clone-items-to-new-pricelist');

Route::get('/move-pricelist-item/{direction}/{pricelist}/{element}', 'PricelistItemController@move_pricelist_item')->name('move-pricelist-item');

Route::post('/add-customer-to-pricelist/{id}', 'PricelistItemController@add_customer')->name('add-customer-to-pricelist');
Route::post('/remove-customer-to-pricelist/{id}', 'PricelistItemController@remove_customer')->name('remove-customer-to-pricelist');

####################################PRICELISTS#######################################

#######################################SYSTEMS ##################################################################
Route::get('/system/configuration-type/home','System\SystemConfigurationTypeController@index')->name('configuration-type-home');
Route::post('/edit/system/configuration-type/{id}','System\SystemConfigurationTypeController@edit')->name('edit-configuration-type');
Route::post('/add/system/configuration-type/','System\SystemConfigurationTypeController@add')->name('add-configuration-type');

Route::get('/system/configuration-home','System\SystemConfigurationsController@index')->name('configuration-system-home');
Route::post('/add/system/configuration/{id}','System\SystemConfigurationsController@add')->name('add-configuration');
Route::post('/edit/system/configuration/{id}','System\SystemConfigurationsController@edit')->name('edit-configuration');
Route::post('/delete/system/configuration/{id}','System\SystemConfigurationsController@delete')->name('delete-configuration');
#######################################SYSTEMS ##################################################################

###########################################CRM DASHBOARD#######################################
Route::get('/dasboard/crm/client-home','CRM\Dashboard\DashboardController@index')->name('client-dashboard-home');
Route::get('/dashboard/crm/client-details','CRM\Dashboard\DashboardController@show')->name('show-client-details');
###########################################CRM DASHBOARD#######################################

###########################################SUPPLIER DASHBOARD#######################################
Route::get('/dashboard/supplier-home','Suppliers\SupplierDashboardController@index')->name('supplier-dashboard-home');
Route::get('/chat/userdetails','Suppliers\ChatMessageController@userdetails')->name('user-detail');
Route::get('/supplier/chat/userdetails','Suppliers\ChatMessageController@userdetails_supplier')->name('user-detail-supplier');
Route::post('/user/chat/add','Suppliers\ChatMessageController@add')->name('add-chat');
Route::post('/user/chat/view','Suppliers\ChatMessageController@userchat')->name('user-chat-view');
Route::get('/supplier/user/chats/{id}','Suppliers\ChatMessageController@userchat_supplier')->name('user-chat-supplier');
Route::get('/rfqs/items/{id}','Suppliers\ChatMessageController@getRequestItems')->name('get-rfqs-item');

Route::get('rfqs-item/{id}','Suppliers\ChatMessageController@getSingleRequestItem')->name('rfq-item');
Route::post('/add/supplier-quote/{id}','Suppliers\ChatMessageController@addSupplierQuote')->name('add-supplier-quote');



Route::get('rfq-item/quotation/{id}','Suppliers\QuotationAttachmentControler@index')->name('get-quotation');
Route::post('add-quotation/notes','Suppliers\QuotationAttachmentControler@addNotes')->name('add-quotation-note');
Route::post('add-quotation/attachments','Suppliers\QuotationAttachmentControler@addAttachment')->name('add-attachments');
Route::get('delete/quotation-notes/{id}','Supplier\QuotationAttachmentControler@delete_notes')->name('delete-notes');
Route::get('delete/quotation-attachment/{id}','Supplier\QuotationAttachmmentControler@delete_attachment')->name('delete-attachment');
###########################################SUPPLIER DASHBOARD#######################################

##################################INVOICE#######################################
Route::get('/invoice-home','Invoice\InvoiceController@index')->name('invoice-home')->middleware('haspermission:Laboratory.components.Proforma Invoices.View');
Route::get('/invoice/sample/{id}','Invoice\InvoiceController@show')->name('invoice-sample-header')->middleware('haspermission:Laboratory.components.Proforma Invoices.View');
Route::get('/invoice/generate/{id}','Invoice\InvoiceController@generateinvoice')->name('generate-invoice')->middleware('haspermission:Laboratory.components.Proforma Invoices.Add');
Route::post('/print/invoice/{id}','Invoice\InvoiceController@print_invoice')->name('print-invoice');
Route::post('/upload/invoice','Invoice\InvoiceController@upload_invoice')->name('upload-invoice');
Route::post('/email/invoice','Invoice\InvoiceController@email_invoice')->name('email-invoice');
Route::post('/edit/invoice','Invoice\InvoiceController@edit_invoice')->name('edit_invoice');
Route::post('/add_tax_invoice','Invoice\InvoiceController@add_tax_invoice')->name('add_tax_invoice');

##################################INVOICE#######################################

##################################TAX REGIME#######################################
Route::get('/tax-home','Invoice\InvoiceController@tax_index')->name('tax-home')->middleware('haspermission:Laboratory.components.Tax Regime.View');
Route::post('/edit-tax/regime/{id}','Invoice\InvoiceController@edit_tax')->name('edit-tax')->middleware('haspermission:Laboratory.components.Tax Regime.Edit');
Route::post('/add-tax/regime','Invoice\InvoiceController@add_tax')->name('add-tax')->middleware('haspermission:Laboratory.components.Tax Regime.Add');
##################################TAX REGIME#######################################

##################################LAB REPORTS#######################################
Route::get('/lab/reports-home','Lab\Reports\SamplesReportsController@index')->name('lab-reports-home');
Route::post('/lab/report/show','Lab\Reports\SamplesReportsController@show')->name('lab-report-show');

Route::get('/lab/sample-generate/certificate-analysis/{id}','SampleWorkFlowController@certificate_analysis')->name('certificate-analysis');
##################################LAB REPORTSS#######################################

####################################DISPOSED EQUIPMENT REPORTS######################################
Route::get('/equipment/reports/home','Equipment\EquipmentReportsController@index')->name('equipment-report-generate');
Route::post('/equipment/reports/show','Equipment\EquipmentReportsController@show')->name('equipment-report-show');
Route::get('/equipment/disposed-report/generate','Equipment\EquipmentReportsController@disposed_report')->name('disposed-equipment-generate');
####################################DISPOSED EQUIPMENT REPORTS######################################

####################################Standards#######################################
Route::post('/lab/standard/add','Lab\StandardsController@addStandard')->name('add-standard');
Route::post('/lab/standard/edit/{id}','Lab\StandardsController@editStandard')->name('edit-standard');
Route::post('/lab/standard-value/add','Lab\StandardsController@addStandardValues')->name('add-standard-value');
Route::post('/lab/standard-value/edit/{id}','Lab\StandardsController@editStandardValue')->name('edit-standard-value');
Route::get('/lab/standard/show/{id}','Lab\StandardsController@show')->name('view-standard');
####################################Standards#######################################

####################################API ROUTES#######################################
Route::get('/api-get-available-items/{item_id}/{brand_id}','API\APIController@items_available')->name('api-get-available-items');
####################################API ROUTES#######################################

####################################REMINDERS ROUTES#######################################
Route::get('/trigger-reminders/{send_reminder}/{type}/{days}/{entity_id?}','ReminderController@get_notifiable_entities')->name('trigger-system-reminders');
####################################REMINDERS ROUTES#######################################

####################################API ROUTES#######################################
Route::get('/get_items_via_ajax','InventorySubCategoriesController@get_items_via_ajax')->name('get_items_via_ajax');
Route::get('/get_suppliers_via_ajax','SupplierController@get_suppliers_via_ajax')->name('get_suppliers_via_ajax');
Route::get('/get_item_details/{inv_sub_cat}','InventorySubCategoriesController@get_item_details')->name('get_item_details');
####################################API ROUTES#######################################

Route::get('/event/update/schedule','Event\EventController@eventUpdateSchedule')->name('eventUpdateSchedule');
Route::post('/get/confirmation/Callback-Url/Payload-wertyasdfgh','Mpesa\MpesaController@mpesaConfirmationCallbackUrl')->name('confirmation_url');
Route::post('/get/Validation/Callback-Url/payload-ghfjdks','Mpesa\MpesaController@mpesaValidationCallbackUrl')->name('mpesaValidationCallbackUrl');
Route::get('/displayMpesaValidation/gvdasdsgdud','Mpesa\MpesaController@displayMpesaValidation')->name('displayMpesaValidation');

#############################################QC Module###########################################
Route::get('Qc/mark-Qc-Sample/Complete/{id}','SampleWorkFlowController@markQcSampleComplete')->name('markQcSampleComplete');
Route::prefix('qualitycontrol')->group(function() {
  Route::get('/', 'QcModule\QualityControlController@index');
  Route::get('/', 'QcModule\QualityControlController@index')->name('qc_index');
  Route::get('/configuration-index','QcModule\QualityControlController@configuration_index')->name('qc_configuration_index');

  Route::post('/delete/Qc-Types','QcModule\QualityControlController@deleteQCTypes')->name('qc_deleteQCTypes');
  Route::post('/create/Qc-Types','QcModule\QualityControlController@createQcTypes')->name('qc_createQcTypes');

  Route::post('/add/Qc-Standard','QcModule\QualityControlController@addQcStandard')->name('qc_addQcStandard');
  Route::post('/delete/Qc-Standard','QcModule\QualityControlController@deleteQcStandard')->name('qc_deleteQcStandard');

  Route::get('/qc-standard/show/{id}','QcModule\QualityControlController@qcStandardShow')->name('qc_StandardShow');
  Route::post('/add/Qc-Standard/Analyte','QcModule\QualityControlController@addQcStandardAnalyte')->name('qc_addQcStandardAnalyte');
  Route::post('/delete/Qc-Standard/Analyte','QcModule\QualityControlController@deleteQcStandardAnalyte')->name('qc_deleteQcStandardAnalyte');

  Route::post('/Maintain-Qc-Schemes','QcModule\QualityControlController@MaintainQcSchemes')->name('MaintainQcSchemes');
  Route::post('/Delete-Qc-Schemes','QcModule\QualityControlController@DeleteQcSchemes')->name('DeleteQcSchemes');
  Route::get('/qc-Workflow-Index','QcModule\QualityControlController@qcWorkflowIndex')->name('qcWorkflowIndex');
  Route::get('/get/Qc-Standards/{qc_type_id}/Ajax','QcModule\QualityControlController@getQcStandardsAjax')->name('getQcStandardsAjax');
  Route::get('/get/Qc-Analysis-Types/{sample_type_id}/Ajax','QcModule\QualityControlController@getQcAnalysisTypesAjax')->name('getQcAnalysisTypesAjax');

  Route::post('/generateQCReport','QcModule\QualityControlController@generateQCReport')->name('generateQCReport');
  Route::get('/get/Qc-Type/Config/{id}/Ajax','QcModule\QualityControlController@getQcTypeConfigAjax')->name('getQcTypeConfigAjax');

});
#############################################QC Module###########################################

#STORAGE ROUTES
Route::get('storage/{type}/{filename}', function ($type, $filename)
{
  // Add folder path here instead of storing in the database.
  $path = storage_path('app/'.$type.'/'. $filename);
  // return $path;
  if (!File::exists($path)) {
    abort(404);
  }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});
Route::get('storage/{type}/{company}/{filename}', function ($type,$company, $filename)
{
  // Add folder path here instead of storing in the database.
  $path = storage_path('app/'.$type.'/'.$company.'/'. $filename);
  // return $path;
  if (!File::exists($path)) {
    abort(404);
  }

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);

  return $response;
});

Route::get('/set-available-stock', function () {
  $items = \App\InventorySubCategories::all();

  foreach($items as $item){
    echo calculateAvailableStock($item->id)."<br>";
    echo setItemReorderLevel($item->id, $item->reorder_level())."<br>";
  }

  return "OK";
});