@if(($drafts ?? collect())->count() > 0)
    <div class="dropdown">
        <button type="button"
                class="btn btn-outline-secondary btn-sm dropdown-toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
            <i class="mdi mdi-file-edit-outline"></i>
            Drafts
            <span class="badge badge-pill badge-primary ml-1">{{ $drafts->count() }}</span>
        </button>
        <div class="dropdown-menu dropdown-menu-right quotation-drafts-menu p-2">
            @foreach($drafts as $draft)
                <a class="dropdown-item quotation-draft-item"
                   href="{{ route('add-qoute-details-view', ['id' => $draft->id]) }}">
                    <strong>{{ $draft->quote_number }}</strong>
                    <small class="text-muted d-block">{{ $draft->created_at }}</small>
                </a>
            @endforeach
        </div>
    </div>
@endif
