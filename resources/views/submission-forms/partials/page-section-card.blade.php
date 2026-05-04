{{--
  Partial: page-section-card
  Renders a single submission form card embedded in a page section.
  Variables:
    $form    - SubmissionForm model instance
    $context - string, slot context ('before_page_content' | 'after_page_content')
--}}
@php
    $isCollapsible = ($form->display_mode ?? 'expanded') === 'collapsible';
    $collapseId    = 'sf-card-collapse-' . $form->id;
@endphp

<div class="card sf-page-section-card mb-3 shadow-sm">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="mdi mdi-file-document-edit-outline mr-2 text-primary"></i>
            <strong class="small">{{ $form->name }}</strong>
        </div>
        @if($isCollapsible)
            <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                    type="button"
                    data-toggle="collapse"
                    data-target="#{{ $collapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $collapseId }}">
                <i class="mdi mdi-chevron-down"></i> Show
            </button>
        @endif
    </div>
    <div class="{{ $isCollapsible ? 'collapse' : '' }}" id="{{ $collapseId }}">
        <div class="card-body py-2">
            @if($form->description)
                <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($form->description, 150) }}</p>
            @endif
            {{--
            <a href="{{ route('submission-forms.instances.create', $form) }}"
               class="btn btn-sm btn-primary">
                <i class="mdi mdi-pencil-plus mr-1"></i>Fill Form
            </a>
            --}}
            <button type="button"
                    class="btn btn-sm btn-primary sf-open-inline-form"
                    data-form-name="{{ e($form->name) }}"
                    {{-- data-fill-url="{{ route('submission-forms.instances.create', $form) }}" --}}
                    data-launch-url="{{ route('submission-forms.instances.launch-inline', $form) }}">
                <i class="mdi mdi-pencil-plus mr-1"></i>Fill Form
            </button>
        </div>
    </div>
</div>

