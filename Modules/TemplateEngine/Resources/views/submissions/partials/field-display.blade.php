@if($field->type === 'container')
    @php
        $columns = $field->meta['columns'] ?? 1;
        $colWidth = 12 / $columns;
    @endphp
    <div class="mb-3">
        @if($field->label && $field->label !== 'Container')
            <h5 class="mb-2">{{ $field->label }}</h5>
        @endif
        <div class="row">
            @foreach($field->children as $child)
                <div class="col-md-{{ $colWidth }} col-12 mb-3">
                    @include('template-engine::submissions.partials.field-display', ['field' => $child, 'data' => $data])
                </div>
            @endforeach
        </div>
    </div>
@elseif(in_array($field->type, ['heading', 'paragraph', 'blockquote', 'code_block', 'link', 'image']))
    {{-- Static Content --}}
    @if($field->type === 'heading')
        <{{ $field->meta['level'] ?? 'h2' }}>{{ $field->label }}</{{ $field->meta['level'] ?? 'h2' }}>
    @elseif($field->type === 'paragraph')
        <p>{{ nl2br(e($field->label)) }}</p>
    @elseif($field->type === 'blockquote')
        <blockquote class="blockquote border-left pl-3">
            <p class="mb-0">{{ $field->label }}</p>
        </blockquote>
    @elseif($field->type === 'code_block')
        <pre><code class="language-{{ $field->meta['language'] ?? 'text' }}">{{ $field->label }}</code></pre>
    @elseif($field->type === 'link')
        <a href="{{ $field->meta['url'] ?? '#' }}" target="{{ ($field->meta['new_tab'] ?? false) ? '_blank' : '_self' }}">{{ $field->label }}</a>
    @elseif($field->type === 'image')
        <div class="mb-3">
            <img src="{{ $field->meta['url'] ?? '' }}" alt="{{ $field->label }}" class="img-fluid">
            @if($field->label && $field->label !== 'Image') <div class="text-muted small mt-1">{{ $field->label }}</div> @endif
        </div>
    @endif
@elseif($field->type === 'image_upload')
    {{-- Display Uploaded Image --}}
    <div class="mb-3">
        <div class="form-group">
            <label class="font-weight-bold">{{ $field->label }}</label>
            @if(isset($data[$field->name]) && $data[$field->name])
                <div class="border rounded p-3 bg-light text-center">
                    <img src="{{ asset('storage/' . $data[$field->name]) }}" 
                         alt="{{ $field->label }}" 
                         class="img-fluid"
                         style="max-width: {{ $field->meta['display_width'] ?? '200px' }}; height: {{ $field->meta['display_height'] ?? 'auto' }};">
                </div>
            @else
                <div class="text-muted small">
                    <i class="fas fa-image"></i> No image uploaded
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Regular Input Fields --}}
    <div class="mb-3">
        <div class="form-group">
            <label class="font-weight-bold">{{ $field->label }}</label>
            <div class="form-control-plaintext bg-light px-3 py-2 border rounded">
                @if(isset($data[$field->name]) && $data[$field->name] !== '')
                    @if($field->type === 'checkbox')
                        @if(is_array($data[$field->name]))
                            {{ implode(', ', $data[$field->name]) }}
                        @else
                            {{ $data[$field->name] == 1 ? 'Yes' : 'No' }}
                        @endif
                    @else
                        {{ $data[$field->name] }}
                    @endif
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
        </div>
    </div>
@endif

