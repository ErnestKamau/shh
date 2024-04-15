<?php

namespace Modules\QualityControl\Jobs\QcData;

use App\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\QualityControl\Entities\Data\QcResults;

class CreateQcResultsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $sample_header_id;
    public function __construct($id)
    {
        $this->sample_header_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $results = Result::where('sample_header_id',$this->sample_header_id)->get();
        foreach($results as $r){
            $qc_res = QcResults::where('result_id',$r->id)->first() ?? new QcResults();
            $qc_res->captured_result_id = $r->captured_result_id;
            $qc_res->sample_detail_code = $r->sample_detail_code;
            $qc_res->sample_detail_id = $r->sample_detail_id;
            $qc_res->sample_header_id = $r->sample_header_id;
            $qc_res->analyte_id = $r->analyte_id;
            $qc_res->analyte_code = $r->analyte_code;
            $qc_res->result = $r->result;
            $qc_res->guide = $r->guide;
            $qc_res->comments = $r->comments;
            $qc_res->recheck = $r->recheck;
            $qc_res->guide_low = $r->guide_low;
            $qc_res->guide_high = $r->guide_high;
            $qc_res->unit_code = $r->unit_code;
            $qc_res->status_code = $r->status_code;
            $qc_res->reporting_symbol = $r->reporting_symbol;
            $qc_res->correct_target = $r->correct_target;
            $qc_res->standard_target = $r->standard_target;
            $qc_res->recommendations = $r->recommendations;
            $qc_res->initial_result = $r->initial_result;
            $qc_res->initial_reporting_symbol = $r->initial_reporting_symbol;
            $qc_res->very_low_guide = $r->very_low_guide;
            $qc_res->very_high_guide = $r->very_high_guide;
            $qc_res->analysis_type_id = $r->analysis_type_id;
            $qc_res->seond_guide = $r->seond_guide;
            $qc_res->remark =$r->remark;
            $qc_res->analyte_status_contracted = $r->analyte_status_contracted;
            $qc_res->analyte_accreditted = $r->analyte_accredited;
            $qc_res->save();

        }
    }
}
