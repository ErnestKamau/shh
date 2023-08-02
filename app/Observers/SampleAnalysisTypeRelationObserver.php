<?php

namespace App\Observers;

use App\SampleAnalysisTypeRelation;

class SampleAnalysisTypeRelationObserver
{
    /**
     * Handle the sample analysis type relation "created" event.
     *
     * @param  \App\SampleAnalysisTypeRelation  $sampleAnalysisTypeRelation
     * @return void
     */
    public function created(SampleAnalysisTypeRelation $sampleAnalysisTypeRelation)
    {
        //
    }

    /**
     * Handle the sample analysis type relation "updated" event.
     *
     * @param  \App\SampleAnalysisTypeRelation  $sampleAnalysisTypeRelation
     * @return void
     */
    public function updated(SampleAnalysisTypeRelation $sampleAnalysisTypeRelation)
    {
        //
    }

    /**
     * Handle the sample analysis type relation "deleted" event.
     *
     * @param  \App\SampleAnalysisTypeRelation  $sampleAnalysisTypeRelation
     * @return void
     */
    public function deleted(SampleAnalysisTypeRelation $sampleAnalysisTypeRelation)
    {
        //
    }

    /**
     * Handle the sample analysis type relation "restored" event.
     *
     * @param  \App\SampleAnalysisTypeRelation  $sampleAnalysisTypeRelation
     * @return void
     */
    public function restored(SampleAnalysisTypeRelation $sampleAnalysisTypeRelation)
    {
        //
    }

    /**
     * Handle the sample analysis type relation "force deleted" event.
     *
     * @param  \App\SampleAnalysisTypeRelation  $sampleAnalysisTypeRelation
     * @return void
     */
    public function forceDeleted(SampleAnalysisTypeRelation $sampleAnalysisTypeRelation)
    {
        //
    }
}
