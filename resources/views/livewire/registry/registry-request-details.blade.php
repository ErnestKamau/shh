<div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
    <div class="card-body p-4">
        <h4 class="mb-2">
            <i class="mdi mdi-file-document text-primary"></i>
            {{ $request->reference_no }} — {{ $request->subject }}
        </h4>
        <p class="text-muted mb-2">
            Category: {{ $request->category?->name }}
            | Status: <span class="badge badge-info">{{ $request->status }}</span>
            | Stage: {{ $request->current_stage }}
        </p>
        @if($request->description)
            <p class="mb-0">{{ $request->description }}</p>
        @endif
    </div>
</div>
