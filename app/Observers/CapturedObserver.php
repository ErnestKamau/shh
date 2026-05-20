<?php

namespace App\Observers;

use App\AnalysisElements;
use App\CapturedResult;
use App\Models\Lab\TatCaptured;
use App\SampleAnalysisDates;
use App\SampleDate;
use App\SampleHeader;

class CapturedObserver
{
    /**
     * Handle the captured "created" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function created(CapturedResult $captured): void
    {
        // Query for the matching analysis element using analysis_type_id and analyte_id
        $analysisElement = AnalysisElements::where('analysis_type_id', $captured->analysis_type_id)
            ->where('analyte_id', $captured->analyte_id)
            ->first();

        if ($analysisElement) {
            // Always set the analysis_element_id
            $captured->analysis_element_id = $analysisElement->id;

            // Set formular_id if result_is_calculated is true and formular_id exists
            if ($analysisElement->result_is_calculated && $analysisElement->formular_id) {
                $captured->formular_id = $analysisElement->formular_id;
            }

            // Set method_sequence_id if has_method_sequence is true and method_sequence_id exists
            if ($analysisElement->has_method_sequence && $analysisElement->method_sequence_id) {
                $captured->method_sequence_id = $analysisElement->method_sequence_id;
            }

            // Set procedure_worksheet_id if it exists
            if ($analysisElement->procedure_worksheet_id) {
                $captured->procedure_worksheet_id = $analysisElement->procedure_worksheet_id;
                $captured->has_procedure_worksheet = true;

                // If no explicit result was set during creation and the analyte
                // expects an attachment-based worksheet, mark it as having
                // no attachment yet. This will later be flipped to "as attached"
                // once a batch attachment is linked.
                if (is_null($captured->result) || $captured->result === '') {
                    $captured->result = 'No attachment';
                }
            }

            // Save the updated captured result (without triggering observers again)
            $captured->saveQuietly();
        }
    }

    /**
     * Handle the captured "updated" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function updated(CapturedResult $captured)
    {
        $remarks = [
            "1"=>"Excelent",
            "2"=>"Satisfactory",
            "3"=>"Good",
            "4"=>"NEED IMPROVEMENT",
            "5"=>"UNSATISFACTORY",
        ];
        
        $tat_exist = TatCaptured::where('captured_result_id',$captured->id)->where('is_complete',0)->first();
        $tat_complete = TatCaptured::where('captured_result_id',$captured->id)->where('is_complete',1)->first();
        $analysis_date = SampleAnalysisDates::where('sample_detail_id',$captured->sample_detail_id)->where('sample_header_id',$captured->sample_header_id)->first();
        // return response()->json($analysis_date);
        if(isset($tat_complete->id) && $tat_complete->result == $captured->result){
            return "done";
        }
        if(isset($tat_exist->id) && $tat_exist->result == $captured->result){
            return "done";
        }

        if(isset($tat_exist->id) && $tat_exist->result != $captured->result){
           $tat_exist->result = $captured->result;
           $tat_exist->analyst_id = $captured->analystIdForTat();
           $tat_exist->start_date_analysis = $analysis_date->start_analysis_date;

           $batch = SampleHeader::find($captured->sample_header_id);
           $sample_date = SampleDate::where('sample_header_id',$batch->id)->where('name','Target Date')->first();
           $date_sample = \Carbon\Carbon::parse($sample_date->date);
           $now = \Carbon\Carbon::now();
           $diff = (int) $date_sample->diffInDays($now);
           $tat_remark_counter = $date_sample > $now ? 1 : 0;

           $tat_exist->finished_date = date('Y-m-d H:i:s');
            $tat_exist->tat_overdue_days = $diff;

            if($tat_remark_counter ==1){
                if($diff >= 2){
                    $tat_exist->tat_remark = 1;
                }elseif($diff == 1){
                    $tat_exist->tat_remark = 2;
                }else{
                    $tat_exist->tat_remark = 3; 
                }
            }else{
                if($diff >= 2){
                    $tat_exist->tat_remark = 5;
                }elseif($diff == 1){
                    $tat_exist->tat_remark = 4;
                }else{
                    $tat_exist->tat_remark = 4;
                }
            }

           $tat_exist->save();
           return "done";
        }
        if(!isset($tat_complete->id) && !isset($tat_exist->id)){
            $batch = SampleHeader::find($captured->sample_header_id);
            $sample_date = SampleDate::where('sample_header_id',$batch->id)->where('name','Target Date')->first();
            $date_sample = \Carbon\Carbon::parse($sample_date->date);
            $now = \Carbon\Carbon::now();
            $diff = (int) $date_sample->diffInDays($now);
            $tat_remark_counter = $date_sample > $now ? 1 : 0;

            $tat = new TatCaptured();
            $tat->captured_result_id = $captured->id;
            $tat->analysis_type_id = $captured->analysis_type_id;
            $tat->analyte_id = $captured->analyte_id;
            $tat->sample_type_id = $batch->sample_type_id;
            $tat->sample_detail_id = $captured->sample_detail_id;
            $tat->result = $captured->result;
            $tat->analyst_id = $captured->analystIdForTat();
            $tat->tat_date = $sample_date->date;
            $tat->sample_header_id = $batch->id;
            $tat->finished_date = date('Y-m-d H:i:s');
            $tat->tat_overdue_days = $diff;
            $tat->start_date_analysis = $analysis_date->start_analysis_date ?? '';

            if($tat_remark_counter ==1){
                if($diff >= 2){
                    $tat->tat_remark = 1;
                }elseif($diff == 1){
                    $tat->tat_remark = 2;
                }else{
                    $tat->tat_remark = 3; 
                }
            }else{
                if($diff >= 2){
                    $tat->tat_remark = 5;
                }elseif($diff == 1){
                    $tat->tat_remark = 4;
                }else{
                    $tat->tat_remark = 4;
                }
            }
            $tat->save();
            return "done";
        }
        if(isset($tat_complete->id) && $tat_complete->result == $captured->result && !isset($tat_exist->id)){
            $batch = SampleHeader::find($captured->sample_header_id);
            $sample_date = SampleDate::where('sample_header_id',$batch->id)->where('name','Target Date')->first();
            $date_sample = \Carbon\Carbon::parse($sample_date->date);
            $now = \Carbon\Carbon::now();
            $diff = (int) $date_sample->diffInDays($now);
            $tat_remark_counter = $date_sample > $now ? 1 : 0;

            $tat = new TatCaptured();
            $tat->captured_result_id = $captured->id;
            $tat->analysis_type_id = $captured->analysis_type_id;
            $tat->analyte_id = $captured->analyte_id;
            $tat->sample_type_id = $batch->sample_type_id;
            $tat->sample_detail_id = $captured->sample_detail_id;
            $tat->result = $captured->result;
            $tat->analyst_id = $captured->analystIdForTat();
            $tat->tat_date = $sample_date->date;
            $tat->sample_header_id = $batch->id;
            $tat->finished_date = date('Y-m-d H:i:s');
            $tat->tat_overdue_days = $diff;
            $tat->start_date_analysis = $analysis_date->start_analysis_date ?? '';

            if($tat_remark_counter ==1){
                if($diff >= 2){
                    $tat->tat_remark = 1;
                }elseif($diff == 1){
                    $tat->tat_remark = 2;
                }else{
                    $tat->tat_remark = 3; 
                }
            }else{
                if($diff >= 2){
                    $tat->tat_remark = 5;
                }elseif($diff == 1){
                    $tat->tat_remark = 4;
                }else{
                    $tat->tat_remark = 4;
                }
            }
            $tat->save();
            return "done";
        }
        return "Done";
    }

    /**
     * Handle the captured "deleted" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function deleted(CapturedResult $captured)
    {
        //
    }

    /**
     * Handle the captured "restored" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function restored(CapturedResult $captured)
    {
        //
    }

    /**
     * Handle the captured "force deleted" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function forceDeleted(CapturedResult $captured)
    {
        //
    }
}
