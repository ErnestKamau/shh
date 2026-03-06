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

    public function index(Request $request)
    {

        $samples = SampleHeader::where('isactive', 1)->where('status', '!=', 'Completed')->orderBy('id', 'desc')->limit(100)->get();;
        // return response($samples->count(),200);
        $samples_lab = SampleHeader::where('status', 'Samples In Lab')->get()->count();
        $samples_approval = SampleHeader::where('status', 'Sample Approval')->get()->count();
        $samples_verification = SampleHeader::where('status', 'Sample Verification')->get()->count();
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


        return view('layouts.lab.dashboard', compact('samples', 'samples_reception', 'samples_lab', 'complaint', 'samples_approval', 'samples_verification', 'notifications'));
    }
}
