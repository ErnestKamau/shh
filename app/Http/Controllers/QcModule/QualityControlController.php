<?php

namespace App\Http\Controllers\QcModule;

use App\AnalysisElements;
use App\Models\QcModule\Configurations\Approvers;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\Configurations\QcTypes;
use App\Models\QcModule\QCProcessedResults;
use App\SamplesCategory;
use App\StandardAnalytes;
use App\Standards;
use App\AnalysisType;
use App\Services\Qc\QcPassFailEvaluator;
use App\Services\Qc\QcStatisticsService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class QualityControlController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct(
        private readonly QcStatisticsService $qcStatisticsService,
        private readonly QcPassFailEvaluator $qcPassFailEvaluator,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.qcmodule.configurations.index');
    }

    public function createQcTypes(Request $request)
    {
        $qc_type = $request->qc_type_id > 0 ? QcTypes::find($request->qc_type_id) : new QcTypes();
        $qc_type->name = $request->name;
        $qc_type->code = $request->code;
        $qc_type->has_standards = isset($request->has_standards) ? 1 : 0;
        $qc_type->has_configured_samples = isset($request->has_configured_samples) ? 1 : 0;
        $qc_type->is_active = isset($request->is_active) ? 1 : 0;
        $qc_type->created_by = auth()->user()->id;
        $qc_type->use_existing_sample = isset($request->use_existing_sample) ? 1 : 0;
        $qc_type->save();

        return redirect()->back()->with('success', 'Qc Type record updated successfully');
    }

    public function deleteQCTypes(Request $request)
    {
        $qc_type = QcTypes::find($request->qc_type_id);
        $qc_type->is_active = 0;
        $qc_type->save();

        return redirect()->back()->with('success', 'Qc Type record updated successfully');
    }

    public function configuration_index()
    {
        return view('layouts.qcmodule.configurations.index');
    }

    public function addQcStandard(Request $request)
    {
        $standard = Standards::find($request->standard_id) ?? new Standards();
        $standard->name = $request->name;
        $standard->code = $request->code;
        $standard->is_qc_standard = 1;
        $standard->qc_type_id = $request->qc_type_id;
        $standard->status = isset($request->is_active) ? 1 : 0;
        $standard->edited_by = auth()->user()->id;
        $standard->save();
        $standard->syncQcSchemes(is_array($request->qc_scheme_ids) ? $request->qc_scheme_ids : []);

        return redirect()->back()->with('success', 'Qc standard added successfully!');
    }

    public function deleteQcStandard(Request $request)
    {
        $standard = Standards::find($request->standard_id);
        $standard->status = 0;
        $standard->save();

        return redirect()->back()->with('success', 'Qc Standard deleted successfully!');
    }

    public function qcStandardShow($id)
    {
        $standard = Standards::find($id);

        return view('layouts.qcmodule.configurations.show', compact('standard'));
    }

    public function addQcStandardAnalyte(Request $request)
    {
        $analyte = StandardAnalytes::find($request->standard_analyte_id) ?? new StandardAnalytes();
        $analyte->standard_id = $request->standard_id;
        $analyte->analyte_id = $request->analyte_id;
        $analyte->absolute_tolerance = isset($request->use_absolute) ? 1 : 0;
        $analyte->tolerance_1 = $request->tolerance_1;
        $analyte->tolerance_2 = $request->tolerance_2;

        $expected = (float) ($request->expected_value ?? 0);
        $tolerance1 = (float) ($request->tolerance_1 ?? 0);
        $tolerance2 = $request->tolerance_2 !== null && $request->tolerance_2 !== ''
            ? (float) $request->tolerance_2
            : $tolerance1;

        $bands = $this->qcPassFailEvaluator->computeToleranceBands(
            $expected,
            $tolerance1,
            $tolerance2,
            isset($request->use_absolute)
        );

        $analyte->low = $bands['low'];
        $analyte->high = $bands['high'];
        $analyte->recommendations = $request->recomendation;
        $analyte->comments = $request->comment;
        $analyte->is_active = isset($request->is_active) ? 1 : 0;
        $analyte->expected_value = $request->expected_value;
        $analyte->standard_value_id = null;
        $analyte->standard_value_type = 'is_range';
        $analyte->save();

        return redirect()->back()->with('success', 'Standard analyte record updated successfully!');
    }

    public function deleteQcStandardAnalyte(Request $request)
    {
        $analyte = StandardAnalytes::find($request->standard_analyte_id);
        $analyte->is_active = 0;
        $analyte->save();

        return redirect()->back()->with('success', 'Standard analyte record deleted  successfully!');
    }

    public function MaintainQcSchemes(Request $request)
    {
        $scheme = QcSchemes::find($request->scheme_id) ?? new QcSchemes();
        $scheme->name = $request->name;
        $scheme->code = $request->code;
        $scheme->is_active = isset($request->is_active) ? 1 : 0;
        $scheme->save();

        return redirect()->back()->with('success', 'Qc Scheme records updated successfully!');
    }

    public function DeleteQcSchemes(Request $request)
    {
        $scheme = QcSchemes::find($request->scheme_id);
        $scheme->delete();

        return redirect()->back()->with('success', 'Qc scheme deleted successfully!');
    }

    public function qcWorkflowIndex()
    {
        return view('layouts.qcmodule.qchistory.index');
    }

    public function getQcStandardsAjax($qc_type_id)
    {
        $standards = Standards::where('is_qc_standard', 1)->where('status', 1)->where('qc_type_id', $qc_type_id)->get();

        return response()->json($standards);
    }

    public function getQcAnalysisTypesAjax($sample_type_id)
    {
        $analysis = AnalysisType::where('sample_type_id', $sample_type_id)->get();

        return response()->json($analysis);
    }

    public function generateQCReport(Request $request)
    {
        return redirect()->route('qcWorkflowIndex');
    }

    public function getQcTypeConfigAjax(Request $request, $id)
    {
        $qc_type = QcTypes::find($id);
        $res = [
            'data' => $qc_type,
            'samples' => SamplesCategory::where('sample_type_id', $request->sample_type_id)->where('workflow_stage', 'Completed')->selectRaw('id,sample_code')->get(),
        ];

        return response()->json($res);
    }

    public function addQcApprovvers(Request $request)
    {
        $approver = Approvers::where('personnel_id', $request->personnel_id)->first();
        if (! isset($approver->id)) {
            $approver = new Approvers();
            $approver->personnel_id = $request->personnel_id;
            $approver->created_by = auth()->user()->id;
            $approver->save();

            return redirect()->back()->with('success', 'Approver Added Successfully');
        }

        return redirect()->back()->with('error', 'Approver already exists');
    }

    public function editQcApprovers(Request $request)
    {
        Approvers::find($request->approver_id)->update(['personnel_id' => $request->personnel_id]);

        return redirect()->back()->with('success', 'Approver updated successfully!');
    }

    public function deleteQcApprovvers($id)
    {
        $approver = Approvers::find($id);
        $approver->delete();

        return redirect()->back()->with('success', 'Approver deleted Successfully');
    }

    public function getAnalysisElementsByTypeId($id)
    {
        $elements = AnalysisElements::where('analysis_type_id', $id)->get();

        return response()->json($elements);
    }

    public function showUnProcessed()
    {
        return view('layouts.qcmodule.qchistory.processing');
    }

    public function showQcReport()
    {
        return view('layouts.qcmodule.qchistory.reports');
    }

    public function showQcReportGraph($result_id)
    {
        $results = QCProcessedResults::find($result_id);

        return view('layouts.qcmodule.qchistory.reportshow', compact('results'));
    }

    public function processResults(Request $request)
    {
        $processed = $this->qcStatisticsService->processAllUnprocessed();

        if ($processed === 0) {
            return redirect()->back()->with('success', 'No unprocessed QC results were found.');
        }

        return redirect()->back()->with('success', 'All qc results have been processed');
    }
}
