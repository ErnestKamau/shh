<?php

namespace App\Http\Controllers\Lab\Reports;

use App\SamplesCategory;

use App\Http\Controllers\Controller;
use App\SampleDetails;
use App\SampleHeader;
use App\Invoice;
use App\InvoiceDetails;
use App\ModulePreConfigs;
use App\AnalysisType;
use App\Analyte;
use App\CapturedResult;
use App\Models\CRM\CRMCustomer;
use App\PricelistCustomer;
use App\Pricelist;
use App\SampleType;
use App\Services\Lab\SampleWorkflowKpiStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SamplesReportsController extends Controller
{
    public function __construct(
        private readonly SampleWorkflowKpiStatisticsService $kpiStatisticsService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $metrics = $this->kpiStatisticsService->getOverviewMetrics();
        $registrationKpiPeriod = $this->resolveRegistrationKpiPeriodMetrics($request);
        $laboratoryKpiPeriod = $this->resolveLaboratoryKpiPeriodMetrics($request);

        [$registrationStart, $registrationEnd] = $this->resolveKpiDateRange($request, 'kpi_start_date', 'kpi_end_date');
        [$laboratoryStart, $laboratoryEnd] = $this->resolveKpiDateRange($request, 'lab_kpi_start_date', 'lab_kpi_end_date');

        $registrationDetailRows = $this->kpiStatisticsService->getRegistrationDetailRows($registrationStart, $registrationEnd);
        $registrationDetailRows = $this->kpiStatisticsService->filterRegistrationDetailRows(
            $registrationDetailRows,
            $this->registrationFiltersFromRequest($request)
        );

        $laboratoryDetailRows = $this->kpiStatisticsService->getLaboratoryDetailRows($laboratoryStart, $laboratoryEnd);
        $laboratoryDetailRows = $this->kpiStatisticsService->filterLaboratoryDetailRows(
            $laboratoryDetailRows,
            $this->laboratoryFiltersFromRequest($request)
        );

        $activeKpiTab = $request->string('kpi_tab')->toString() ?: 'overview';
        $registrationFilters = $this->registrationFiltersFromRequest($request);
        $laboratoryFilters = $this->laboratoryFiltersFromRequest($request);

        return view('layouts.lab.reports.index', compact(
            'metrics',
            'registrationKpiPeriod',
            'laboratoryKpiPeriod',
            'registrationDetailRows',
            'laboratoryDetailRows',
            'activeKpiTab',
            'registrationFilters',
            'laboratoryFilters',
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRegistrationKpiPeriodMetrics(Request $request): array
    {
        [$startDate, $endDate] = $this->resolveKpiDateRange($request);

        return $this->kpiStatisticsService->getRegistrationPeriodMetrics($startDate, $endDate);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveLaboratoryKpiPeriodMetrics(Request $request): array
    {
        [$startDate, $endDate] = $this->resolveKpiDateRange($request, 'lab_kpi_start_date', 'lab_kpi_end_date');

        return $this->kpiStatisticsService->getLaboratoryPeriodMetrics($startDate, $endDate);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveKpiDateRange(Request $request, string $startKey = 'kpi_start_date', string $endKey = 'kpi_end_date'): array
    {
        $startDate = $request->filled($startKey)
            ? Carbon::parse($request->input($startKey))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled($endKey)
            ? Carbon::parse($request->input($endKey))->endOfDay()
            : now()->endOfMonth();

        return [$startDate, $endDate];
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationFiltersFromRequest(Request $request): array
    {
        return [
            'client' => $request->input('reg_client', ''),
            'sampler_name' => $request->input('reg_sampler_name', ''),
            'sampler_id' => $request->input('reg_sampler_id', ''),
            'equipment_id' => $request->input('reg_equipment_id', ''),
            'job_id' => $request->input('reg_job_id', ''),
            'sample_id' => $request->input('reg_sample_id', ''),
            'location' => $request->input('reg_location', ''),
            'sampling_points' => $request->input('reg_sampling_points', ''),
            'registered_by' => $request->input('reg_registered_by', ''),
            'registration_type' => $request->input('reg_type', 'all'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function laboratoryFiltersFromRequest(Request $request): array
    {
        return [
            'client' => $request->input('lab_client', ''),
            'job_id' => $request->input('lab_job_id', ''),
            'data_entry_status' => $request->input('lab_data_entry_status', 'all'),
            'review_status' => $request->input('lab_review_status', ''),
        ];
    }

    /**
     * Legacy batch/sample/profit report generator.
     *
     * @deprecated Dormant while KPI dashboard is active on lab-reports-home.
     */
    public function show(Request $request)
    {
        $filter = $request->all();
        $data = SamplesCategory::query();
        $data = isset($request->sample_type_id) && $request->sample_type_id != 'all' ? $data->where('samples_by_category.sample_type_id', $request->sample_type_id) : $data;
        $data = isset($request->client_id) && $request->client_id != 'all' ? $data->where('samples_by_category.crm_customer_id', $request->client_id) : $data;
        $data = isset($request->start_date) && $request->start_date != '' ? $data->where('samples_by_category.receipt_date', '>=', $request->start_date) : $data;
        $data = isset($request->end_date) && $request->end_date != '' ? $data->where('samples_by_category.receipt_date', '<=', $request->end_date) : $data;
        $data = isset($request->status) && $request->status != 'all' ? $data->where('samples_by_category.priority', $request->status) : $data;
        $data = isset($request->workflow) && $request->workflow != 'all' ? $data->where('samples_by_category.workflow_stage', $request->workflow) : $data;
        if ($request->report_name == 'batch_report') {
            $theads = ['Receipt Date','Batch Code', 'Client','Submitted By','Sampling Date','Approval Date','Batch Scope','Customer Survey','Invoice No' ,'Sample Type','Workflow','Priority'];
            $lab_report = $data->get();
        } elseif ($request->report_name == 'sample_report') {
            $theads = ['Receipt Date','Sample Code', 'Client', 'Submitted By','Sample Type', 'Analysis Type', 'Analytes','Sampling Date','Invoice Number','Batch Scope','Workflow','Priority'];
            $lab_report = $this->sample_report($data, $request);
            // return response()->json($lab_report);
        } elseif ($request->report_name == 'profit_report') {
            $theads = ['Sample Code','Sample Type','Client','Submitted By','Analysis Type','Invoice Number','Cost Price','Tax','Selling Price','Profit'];
            $lab_report = $this->profit_reports($data,$request);
            // return response()->json($lab_report, 200);    
        }
        $reports = [];
        if($request->group_by != 'none' || $request->report_name == 'profit_report'){
            $group_name = $request->group_by == 'none' && $request->report_name == 'profit_report' ? 'analysis_type_id' :  $request->group_by;
            if($request->report_name != 'profit_report'){

                foreach($lab_report as $report){
                    $group_attr='';
                    $group_attr = $group_name == 'analysis_type_id' ? explode(',',$report->analysis_name) : $group_attr;
                    $group_attr = $group_name == 'sample_type_id' ? $report->sample_type_name : $group_attr;
                    $group_attr = $group_name == 'crm_customer_id' ? $report->crm_name : $group_attr;
                    if($group_name != 'analysis_type_id'){
                        !isset($reports[$group_attr]) ? $reports[$group_attr] = []: '';
                        array_push($reports[$group_attr],$report);
                    }else{
                        foreach($group_attr as $attr){
                            !isset($reports[$attr]) ? $reports[$attr] = []: ''; 
                            array_push($reports[$attr],$report);
                        }
                    }
                }
            }else{
                foreach($lab_report as $report){
                    // return response()->json($report);
                    $group_attr= '';
                    $group_attr = $group_name == 'analysis_type_id' ? getAnalysisTypeID($report->analysis_type)->name : $group_attr;
                    $group_attr = $group_name == 'sample_type_id' ? getSampleTypeByID($report->$group_name)->name : $group_attr;
                    $group_attr = $group_name == 'crm_customer_id' ? getCrmCustomerByID($report->$group_name)->name : $group_attr;
                    !isset($reports[$group_attr]['samples']) ? $reports[$group_attr]['samples'] = [] : '';
                    !isset($reports[$group_attr]['profit']) ? $reports[$group_attr]['profit'] =[] :'';
                    !isset($reports[$group_attr]['cost_price']) ? $reports[$group_attr]['cost_price'] =[] :'';
                    !isset($reports[$group_attr]['selling_price']) ? $reports[$group_attr]['selling_price'] =[] :'';
                    !isset($reports[$group_attr]['tax']) ? $reports[$group_attr]['tax'] =[] :'';
                    array_push($reports[$group_attr]['samples'],$report);
                    $profit = $report->selling_price - $report->cost_price;
                    array_push($reports[$group_attr]['profit'],$profit);
                    array_push($reports[$group_attr]['selling_price'],$report->selling_price);
                    array_push($reports[$group_attr]['tax'],$report->tax_amount);
                    array_push($reports[$group_attr]['cost_price'],$report->cost_price);
                    $profit =0;
                }

            }
        }else{
            $reports = $lab_report;
        }
        if($request->sample_type_id == 'all'){
            $analysis_type = $request->analysis_type != 'all' ? AnalysisType::where('name','LIKE','%'.$request->analysis_type.'%')->pluck('name')->toArray() : [];
        }else{
            $analysis_type = $request->analysis_type != 'all' ? explode(',',$request->analysis_type) : [];
        }
        // return response()->json($filter,200);
        isset($filter['sample_type_id']) && $filter['sample_type_id'] != 'all' ? $filter['sample_type'] = getSampleTypeByID($filter['sample_type_id'])->name : $filter['sample_type'] = $filter['sample_type_id'];
        isset($filter['analysis_type']) && sizeof($analysis_type) > 0 ? $filter['analysis_type_'] = implode(', ',$analysis_type) : 
        $filter['analysis_type_'] = $filter['analysis_type'];
        isset($filter['analyte_id']) && $filter['analyte_id'] != 'all' ? $filter['analyte_name'] = getAnalyteByID($filter['analyte_id'])->name : $filter['analyte_name']  =  $filter['analyte_id'];
        isset($filter['client_id']) && $filter['client_id'] != 'all' ? $filter['client'] = getCrmCustomerByID($filter['client_id'])->name : $filter['client'] = $filter['client_id'];
        isset($filter['currency_id']) && $filter['currency_id'] != 'all' ? $filter['currency'] = getCurrencyById($filter['currency_id'])->name :  $filter['currency'] = $filter['currency_id'];
        $company = getActiveCompany();
        $filter_remove = ['_token','sample_type_id','analysis_type_id','client_id','analyte_id','currency_id'];
        return view('layouts.lab.reports.show',compact('reports','filter','company','theads','filter_remove'));
    }
    private function sample_report($data, $request)
    {
        if($request->sample_type_id == 'all'){
            $analysis_type = $request->analysis_type != 'all' ? AnalysisType::where('name','LIKE','%'.$request->analysis_type.'%')->pluck('id')->toArray() : [];
        }else{
            $analysis_type = $request->analysis_type != 'all' ? explode(',',$request->analysis_type) : [];
        }
        $data->join('sample_headers as sh', 'sh.batch_code', 'samples_by_category.batch_code');
        $data->join('sample_details as sd', 'sd.sample_header_id', 'sh.id');
        $data->join('captured_results as cr', 'cr.sample_detail_id', 'sd.id');
        $data = isset($request->analysis_type) && sizeof($analysis_type) > 0 ? $data->whereIn('cr.analysis_type_id', $analysis_type) : $data;
        $data = isset($request->analyte_id) && $request->analyte_id != 'all' ? $data->where('cr.analyte_id', $request->analyte_id) : $data;
        $samples = $data->distinct('sd.id')->selectRaw('sd.*,samples_by_category.sample_type_name,samples_by_category.sample_type_id,samples_by_category.crm_customer_id,samples_by_category.crm_name,samples_by_category.workflow_stage,samples_by_category.priority,samples_by_category.receipt_date,samples_by_category.date_collected,samples_by_category.approval_date,samples_by_category.batch_scope,samples_by_category.invoice_number,sh.submit_by')->get();
        $crs = $data->distinct('cr.id')->selectRaw('cr.*')->get();
        $cr_data = [];
        foreach ($crs as $cr) {
            !isset($cr_data[$cr->sample_detail_id]) ? $cr_data[$cr->sample_detail_id] = [] : '';
            !isset($cr_data[$cr->sample_detail_id]['analysis']) ? $cr_data[$cr->sample_detail_id]['analysis'] = [] : '';
            !isset($cr_data[$cr->sample_detail_id]['analytes']) ? $cr_data[$cr->sample_detail_id]['analytes'] = [] : '';
            $analysis = getAnalysisTypeID($cr->analysis_type_id);
            $analyte = getAnalyteByID($cr->analyte_id);
            isset($analysis->id) ? array_push($cr_data[$cr->sample_detail_id]['analysis'], $analysis->name) : '';
            isset($analyte->id) ?  array_push($cr_data[$cr->sample_detail_id]['analytes'], $analyte->code) : '';
        }
        foreach ($samples as $s) {
            $s['analysis_name'] = implode(',', array_unique($cr_data[$s->id]['analysis']));
            $s['analyte_name'] = str_replace(' ', '', implode(',', array_unique($cr_data[$s->id]['analytes'])));
        }
        return $samples;
    }
    private function profit_reports($data, $request)
    {
        $data->join('sample_headers as sh', 'sh.batch_code', 'samples_by_category.batch_code');
        $data->whereNotIn('sh.invoice_id', [0]);
        $data->join('invoice_details as vd', 'vd.invoice_id', 'sh.invoice_id');
        $data->join('sample_details as sd', 'sd.id', 'vd.sample_detail_id');
        $data = isset($request->analysis_type) && $request->analysis_type != 'all' ? $data->where('analysis_type_id', $request->analysis_type_id) : $data;

        $invoice_details = $data->distinct('vd.id')->selectRaw('vd.*,sd.sample_code,samples_by_category.sample_type_id,samples_by_category.crm_customer_id')->get();
        return $invoice_details;
        
    }

    public function getAnalysisTypeBySampleTypeAjax($type_id){
        if($type_id == 'all'){
            $analysis_type = AnalysisType::where('active',1)->pluck('name')->toArray();
            return array_unique($analysis_type);
        }else{
            $analysis_type = AnalysisType::where('active',1)->where('sample_type_id',$type_id)->get(); 
        }
        return $analysis_type;
    }
    
}
