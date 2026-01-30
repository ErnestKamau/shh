<div>
    @if(isset($batch->id) && $batch->hasSubmissionForm() && $relatedBatches->count() > 0)
        <div class="card border-0 mb-2" style="background-color: inherit !important">
            <div class="card-header- p-2 border-bottom" style="background-color: inherit !important">
                <h5 style="font-size: large"><i class="mdi mdi-link-variant"></i> Related Batches</h5>
            </div>
            <div class="card-body border-bottom bg-white">
                <div class="row no-gutters">
                    @foreach($relatedBatches as $relatedBatch)
                        <div class="col-sm-3 p-1">
                            <a href="{{ route('view-batch-details', ['batch' => $relatedBatch->id, 'client' => 0, 'portal' => 0, 'status' => $relatedBatch->status]) }}" 
                               style="color: rgb(68, 68, 68);font-size:11px; font-weight: bold; text-decoration: none;">
                                <i class="mdi mdi-flask-outline"></i> {{ $relatedBatch->batch_code }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
