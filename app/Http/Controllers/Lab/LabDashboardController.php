<?php

namespace App\Http\Controllers\Lab;

use App\SampleHeader;
use App\SampleDetails;
use App\SampleAnalysisStage;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\Complaint;

use App\Http\Controllers\Controller;
use App\SampleType;
use Illuminate\Http\Request;
use App\Models\CRM\CRMCompanyUnit;
use App\Result;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LabDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getsamplesBySampletype(Request $request)
    {
        $results = [];
        if (isset($request->year)) {
            $samplesTypes = SampleHeader::where('isactive', 1)->whereYear('created_at', $request->year)->where('status','!=','Completed')->pluck('sample_type_id')->toArray();
            array_unique($samplesTypes);
            foreach ($samplesTypes as $id) {
                $stype = getSampleTypeByID($id);
                if ($stype) {
                    $results[$stype->name] = SampleHeader::where('sample_type_id', $id)->whereYear('created_at', $request->year)->where('status','!=','Completed')->where('isactive', 1)->get()->count();
                }
            }
        } else {
            $currentY = date('Y');
            $samplesTypes = SampleHeader::where('isactive', 1)->whereYear('created_at', $currentY)->where('status','!=','Completed')->pluck('sample_type_id')->toArray();
            array_unique($samplesTypes);
            $results = [];
            foreach ($samplesTypes as $id) {
                $stype = getSampleTypeByID($id);
                if ($stype) {
                    $results[$stype->name] = SampleHeader::where('sample_type_id', $id)->where('status','!=','Completed')->whereYear('created_at', $currentY)->where('isactive', 1)->get()->count();
                }
            }
        }
        return response()->json($results);
        // return response()->json($samplesTypes);

    }
    public function getSamplesByMonth(Request $request)
    {
        $results = [];
        if (isset($request->year)) {
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $loop = 1;
            foreach ($months as $month) {
                $results[$month] = SampleHeader::whereMonth('receipt_date', $loop)->where('isactive', 1)->whereYear('receipt_date', $request->year)->count();
                ++$loop;
            }
        } else {
            $currentY = date('Y');
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $loop = 1;
            foreach ($months as $month) {
                $results[$month] = SampleHeader::whereMonth('receipt_date', $loop)->whereYear('receipt_date', $currentY)->where('isactive', 1)->count();
                ++$loop;
            }
        }

        return response()->json($results);
    }
    public function getSamplesByGps(Request $request)
    {
        $results = [];
        if (isset($request->year)) {
            $samples = SampleHeader::where('isactive', 1)->whereYear('created_at', $request->year)->where('status','!=','Completed')->get();
            foreach ($samples as $sample) {
                $details = SampleDetails::where('sample_header_id', $sample->id)->get();
                foreach ($details as $detail) {
                    $sample_point = SamplePoint::find($detail->sample_point_id);
                    if (!$sample_point || !isset($sample_point->id)) {

                        $crm_unit = CRMCompanyUnit::where('name', $sample->crm_unit_name)->first();
                        if ($crm_unit && isset($crm_unit->id)) {
                            $sample_point_ = SamplePoint::where('crm_company_unit_id', $crm_unit->id)->first();
                            // return response()->json($sample_point_);
                            if ($sample_point_ && isset($sample_point_->gps)) {
                                if (!isset($results[$sample_point_->gps])) {
                                    $results[$sample_point_->gps] = 0;
                                }
                                ++$results[$sample_point_->gps];
                            }
                        }
                    } else {
                        if ($sample_point && isset($sample_point->gps)) {
                            if (!isset($results[$sample_point->gps])) {
                                $results[$sample_point->gps] = 0;
                            }
                            ++$results[$sample_point->gps];
                        }
                    }
                }
            }
        } else {
            $currentY = date('Y');
            $samples = SampleHeader::where('isactive', 1)->whereYear('created_at', $currentY)->where('status','!=','Completed')->get();
            foreach ($samples as $sample) {
                $details = SampleDetails::where('sample_header_id', $sample->id)->get();
                foreach ($details as $detail) {
                    $sample_point = SamplePoint::find($detail->sample_point_id);
                    if (!$sample_point || !isset($sample_point->id)) {

                        $crm_unit = CRMCompanyUnit::where('name', $sample->crm_unit_name)->first();
                        if ($crm_unit && isset($crm_unit->id)) {
                            $sample_point_ = SamplePoint::where('crm_company_unit_id', $crm_unit->id)->first();
                            // return response()->json($sample_point_);
                            if ($sample_point_ && isset($sample_point_->gps)) {
                                if (!isset($results[$sample_point_->gps])) {
                                    $results[$sample_point_->gps] = 0;
                                }
                                ++$results[$sample_point_->gps];
                            }
                        }
                    } else {
                        if ($sample_point && isset($sample_point->gps)) {
                            if (!isset($results[$sample_point->gps])) {
                                $results[$sample_point->gps] = 0;
                            }
                            ++$results[$sample_point->gps];
                        }
                    }
                }
            }
        }
        return response()->json($results);
    }
    public function getSamplesByCustomer(Request $request)
    {
        $results = [];
        if (isset($request->year)) {
            $customers  = SampleHeader::where('isactive', 1)->whereYear('created_at', $request->year)->where('status','!=','Completed')->pluck('crm_customer_id')->toArray();
            array_unique($customers);
            foreach ($customers as $cust) {
                $customer = getCrmCustomerByID($cust);
                $results[$customer->name] = SampleHeader::where('isactive', 1)->where('status','!=','Completed')->where('crm_customer_id', $cust)->join('sample_details as sd', 'sd.sample_header_id', '=', 'sample_headers.id')->selectRaw('sd.id')->get()->count();
            }
        } else {
            $currentY = date('Y');
            $customers  = SampleHeader::where('isactive', 1)->where('status','!=','Completed')->whereYear('created_at', $currentY)->pluck('crm_customer_id')->toArray();
            array_unique($customers);

            foreach ($customers as $cust) {
                $customer = getCrmCustomerByID($cust);
                $results[$customer->name] = SampleHeader::where('isactive', 1)->where('status','!=','Completed')->where('crm_customer_id', $cust)->join('sample_details as sd', 'sd.sample_header_id', '=', 'sample_headers.id')->selectRaw('sd.id')->get()->count();
            }
        }


        return response()->json($results);
    }

    /**
     * Get sample (batch) count grouped by lab section for dashboard chart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSamplesByLabSection(Request $request): \Illuminate\Http\JsonResponse
    {
        $year = $request->get('year') ?? date('Y');
        $baseQuery = SampleHeader::query()
            ->where('isactive', 1)
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'Completed');

        $sections = SampleAnalysisStage::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $results = [];
        foreach ($sections as $section) {
            $count = (clone $baseQuery)
                ->whereNotNull('lab_section_ids')
                ->where('lab_section_ids', '!=', '')
                ->whereRaw('FIND_IN_SET(?, lab_section_ids) > 0', [$section->id])
                ->count();
            if ($count > 0) {
                $results[$section->name] = $count;
            }
        }

        return response()->json($results);
    }

    /**
     * Get sample (batch) count by workflow status for dashboard chart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSamplesByStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $year = $request->get('year') ?? date('Y');
        $statuses = [
            'Samples Reception' => 'Samples Reception',
            'Samples In Lab' => 'Samples In Lab',
            'Sample Verification' => 'Sample Verification',
            'Sample Approval' => 'Sample Approval',
        ];

        $results = [];
        foreach ($statuses as $label => $status) {
            $query = SampleHeader::query()
                ->where('isactive', 1)
                ->whereYear('created_at', $year)
                ->where('status', $status);
            if ($status === 'Samples Reception') {
                $query->where('isactive', 1);
            }
            $results[$label] = $query->count();
        }

        return response()->json($results);
    }

    /**
     * Get data for the Sunburst Testing Matrix (Sample Type -> Lab Section)
     */
    public function getTestingMatrix(Request $request): \Illuminate\Http\JsonResponse
    {
        $year = $request->get('year') ?? date('Y');
        
        // Simplified version: Group by Sample Type, then count. 
        // In a real scenario, this would group by SampleType -> LabSection -> Analyte.
        // For the UI demonstration, we will group by Sample Type and Lab Section.
        $matrix = [];
        
        $samples = SampleHeader::where('isactive', 1)
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'Completed')
            ->with(['sample_type'])
            ->get();
            
        $sections = SampleAnalysisStage::where('active', 1)->get()->keyBy('id');
        
        foreach ($samples as $sample) {
            $typeName = $sample->sample_type ? $sample->sample_type->name : 'Unknown';
            if (!isset($matrix[$typeName])) {
                $matrix[$typeName] = [];
            }
            
            if ($sample->lab_section_ids) {
                $sectionIds = explode(',', $sample->lab_section_ids);
                foreach ($sectionIds as $secId) {
                    if (isset($sections[$secId])) {
                        $secName = $sections[$secId]->name;
                        if (!isset($matrix[$typeName][$secName])) {
                            $matrix[$typeName][$secName] = 0;
                        }
                        $matrix[$typeName][$secName]++;
                    }
                }
            } else {
                if (!isset($matrix[$typeName]['Unassigned'])) {
                    $matrix[$typeName]['Unassigned'] = 0;
                }
                $matrix[$typeName]['Unassigned']++;
            }
        }
        
        // Format for hierarchical charts like sunburst or treemap
        $formatted = [];
        foreach ($matrix as $type => $sectionsArray) {
            $children = [];
            foreach ($sectionsArray as $sec => $count) {
                $children[] = ['name' => $sec, 'value' => $count];
            }
            $formatted[] = [
                'name' => $type,
                'children' => $children
            ];
        }

        return response()->json($formatted);
    }

    /**
     * Get data for Active Methods / Instruments Leaderboard
     */
    public function getActiveMethods(Request $request): \Illuminate\Http\JsonResponse
    {
        // Mocked or derived from Analysis Types/Equipment for the dashboard
        // We will sum active parameters that belong to active batches.
        $methods = DB::table('sample_details')
            ->join('sample_headers', 'sample_headers.id', '=', 'sample_details.sample_header_id')
            ->join('analysis_types', 'analysis_types.id', '=', 'sample_details.analysis_type_id')
            ->where('sample_headers.status', 'Samples In Lab')
            ->where('sample_headers.isactive', 1)
            ->select('analysis_types.name', DB::raw('count(*) as total'))
            ->groupBy('analysis_types.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
            
        return response()->json($methods);
    }

    /**
     * Get datatable items for the Smart Action Grid (My Tasks, Urgent, Approvals)
     */
    public function getCustomerSampleTypes(Request $request): \Illuminate\Http\JsonResponse
    {
        // Get the top 10 customers by active batches
        $topClientIds = \App\SampleHeader::where('isactive', 1)
            ->where('status', '!=', 'Completed')
            ->whereNotNull('crm_customer_id')
            ->select('crm_customer_id', DB::raw('count(*) as total'))
            ->groupBy('crm_customer_id')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('crm_customer_id');
            
        $samples = \App\SampleHeader::where('isactive', 1)
            ->where('status', '!=', 'Completed')
            ->whereIn('crm_customer_id', $topClientIds)
            ->with(['client', 'sample_type'])
            ->get();
            
        $data = [];
        $sampleTypes = [];
        
        foreach ($samples as $sample) {
            $clientName = $sample->client ? $sample->client->name : 'Unknown';
            $typeName = $sample->sample_type ? $sample->sample_type->name : 'Unknown';
            
            // Shorten client name for the chart labels
            if (strlen($clientName) > 20) {
                $clientName = substr($clientName, 0, 20) . '...';
            }
            
            if (!isset($data[$clientName])) {
                $data[$clientName] = [];
            }
            if (!isset($data[$clientName][$typeName])) {
                $data[$clientName][$typeName] = 0;
            }
            $data[$clientName][$typeName]++;
            $sampleTypes[$typeName] = true;
        }
        
        return response()->json([
            'clients' => array_keys($data),
            'types' => array_keys($sampleTypes),
            'data' => $data
        ]);
    }

    public function getSmartGridTasks(Request $request): \Illuminate\Http\JsonResponse
    {
        $tab = $request->get('tab', 'my_tasks'); // my_tasks, urgent, approvals, tat_awareness, pending_submissions

        if ($tab === 'pending_submissions') {
            $forms = SubmissionFormInstance::query()
                ->with(['submissionForm', 'submittedBy'])
                ->whereNotIn('status', ['approved', 'rejected', 'cancelled'])
                ->latest()
                ->limit(50)
                ->get()
                ->map(function ($instance) {
                    $priority = match (strtolower((string) $instance->status)) {
                        'submitted' => 'High',
                        'draft' => 'Normal',
                        default => 'Normal',
                    };

                    $statusLabel = ucwords(str_replace('_', ' ', (string) $instance->status));
                    $targetDate = $instance->created_at
                        ? $instance->created_at->format('Y-m-d')
                        : 'N/A';

                    $detailUrl = null;
                    if ((int) $instance->batches()->count() > 0) {
                        $detailUrl = route('submission-forms.instances.batch-view', $instance->id);
                    } elseif ($instance->submission_form_id) {
                        $detailUrl = route('submission-forms.instances.show', [
                            $instance->submission_form_id,
                            $instance->id,
                        ]);
                    }

                    return [
                        'id' => $instance->id,
                        'priority' => $priority,
                        'batch_code' => $instance->form_number ?: ('Form #' . $instance->id),
                        'client_name' => $instance->submissionForm->name ?? 'Submission Form',
                        'sample_type' => $instance->submittedBy->name ?? 'Unassigned',
                        'status' => $statusLabel,
                        'target_date' => $targetDate,
                        'tat_status' => null,
                        'sample_count' => 0,
                        'detail_url' => $detailUrl,
                    ];
                });

            return response()->json($forms);
        }
        
        $query = SampleHeader::with(['client', 'sample_type'])->where('isactive', 1)->where('status', '!=', 'Completed');
        
        if ($tab === 'urgent') {
            // Priority or high urgency, or close to target date
            $query->where(function($q) {
                $q->where('priority', 'Urgent')
                  ->orWhere('priority', 'High');
            })->orderBy('id', 'desc');
        } elseif ($tab === 'approvals') {
            $query->where('status', 'Sample Approval')->orderBy('id', 'desc');
        } elseif ($tab === 'tat_awareness') {
            // Batches where Target Date is within 3 days or past
            $query->whereHas('get_target_date', function($q) {
                $q->where('date', '<=', \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'));
            })->orderBy('id', 'desc');
        } else {
            // Default to My Tasks
            $query->where(function ($q) {
                $q->where('status', 'Samples In Lab')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'Sample Verification')
                          ->whereHas('approvers', function ($approverQuery) {
                              $approverQuery->where('user_id', Auth::id())
                                            ->whereIn('status', [0, 2]); 
                          });
                  });
            })->orderBy('id', 'desc');
        }
        
        $batches = $query->limit(50)->get()->map(function($item) {
            $targetDate = $item->get_date('Target Date');
            $dateFormatted = $targetDate ? date('Y-m-d', strtotime($targetDate->date)) : 'N/A';
            
            $tatStatus = '';
            if ($targetDate) {
                $target = \Carbon\Carbon::parse($targetDate->date);
                $now = \Carbon\Carbon::now()->startOfDay();
                $diff = $now->diffInDays($target, false); // Negative if past
                
                if ($diff < 0) {
                    $tatStatus = abs((int)$diff) . ' Days Overdue';
                } elseif ($diff == 0) {
                    $tatStatus = 'Due Today';
                } else {
                    $tatStatus = (int)$diff . ' Days Left';
                }
            }

            return [
                'id' => $item->id,
                'priority' => $item->priority,
                'batch_code' => $item->batch_code,
                'client_name' => $item->client ? $item->client->name : 'N/A',
                'sample_type' => $item->sample_type ? $item->sample_type->name : 'N/A',
                'status' => $item->status,
                'target_date' => $dateFormatted,
                'tat_status' => $tatStatus,
                'sample_count' => $item->samples()->count()
            ];
        });
        
        return response()->json($batches);
    }

    public function index(Request $request)
    {

        $samples = SampleHeader::where('isactive', 1)->where('status', '!=', 'Completed')->orderBy('id', 'desc')->limit(100)->get();
        // return response($samples->count(),200);
        $samples_lab = SampleHeader::where('status', 'Samples In Lab')->get()->count();
        $samples_approval = SampleHeader::where('status', 'Sample Approval')->get()->count();
        $samples_verification = SampleHeader::where('status', 'Sample Verification')->get()->count();
        
        // Count Pending Submission Forms instead of Samples in Reception
        $pending_submission_forms = SubmissionFormInstance::whereNotIn('status', ['approved', 'rejected', 'cancelled'])->count();
        $submitted_forms = SubmissionFormInstance::where('status', 'submitted')->count();
        $draft_forms = SubmissionFormInstance::where('status', 'draft')->count();
        $samples_reception = SampleHeader::where('status', 'Samples Reception')->where('isactive', 1)->get()->count();
        $complaint = Complaint::where('complaint_workflow', '!=', 5)->get();
        $notification = getBatchNotificationUser();
        foreach ($notification as $note) {
            $batch = getSampleHeaderByID($note->batch_id);
            if ($batch && isset($batch->status) && $batch->status != $note->status) {
                $note->delete();
            }
        }
        $notifications = getBatchNotificationUser();

        // Calculate TAT Warnings (Target date <= today + 3 days)
        $tat_warnings_count = SampleHeader::where('isactive', 1)
            ->where('status', '!=', 'Completed')
            ->whereHas('get_target_date', function($q) {
                $q->where('date', '<=', \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'));
            })->count();

        return view('layouts.lab.dashboard', compact('samples', 'samples_reception', 'samples_lab', 'complaint', 'samples_approval', 'samples_verification', 'notifications', 'pending_submission_forms', 'submitted_forms', 'draft_forms', 'tat_warnings_count'));
    }
}
