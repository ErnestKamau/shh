<?php

namespace App\Http\Controllers\QcModule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\QualityControl\Entities\Data\QcReportHeader;
use Modules\QualityControl\Entities\QCResultsView;

class ReleaseQCReportController extends Controller
{

    public function releaseQCReport(Request $request){
        $results = QCResultsView::whereIn('id',explode(',',$request->release_ids))->get();
        $header = new QcReportHeader();
        $header->created_by = auth()->user()->id;
        $header->qc_results_ids = $request->release_ids;
        $header->from_date = $request->from_date;
        $header->to_date = $request->to_date;
        $header->analyte_id = $request->analyte_id;
        $header->sample_type_id = $request->sample_type_id;
        $header->analysis_type_id = $request->analysis_type_id;
        $header->qc_type_id = $request->qc_type_id;
        $header->qc_scheme_id = $request->qc_scheme_id;
        $header->standard_id = $request->standard_id;
        $header->remark = $request->remark;
        $header->save();

        return redirect()->back()->with('success','QC Report scheduled for analysis successfully!');
    }
    
   

}
