<div class="col-md-12 mb-3">
    @if($field->type === 'container')
        <div class="card card-custom gutter-b" style="border: 1px dashed #e2e5ec; background-color: #f8f9fa;">
           @if($field->label && $field->label !== 'Container')
               <div class="card-header p-3 min-h-auto">
                   <h6 class="card-title m-0">{{ $field->label }}</h6>
               </div>
           @endif
           <div class="card-body p-3">
               <div class="row">
                   @foreach($field->children as $child)
                       @include('template-engine::livewire.partials.render-field', ['field' => $child])
                   @endforeach
               </div>
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
            <img src="{{ $field->meta['url'] ?? '' }}" alt="{{ $field->label }}" class="img-fluid">
            @if($field->label && $field->label !== 'Image') <div class="text-muted small">{{ $field->label }}</div> @endif
        @endif
    @else
        {{-- Input Fields --}}
        <label>{{ $field->label }} @if($field->required) <span class="text-danger">*</span> @endif</label>
        
        @if($field->type === 'text')
            <input type="text" class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
        @elseif($field->type === 'textarea')
            <textarea class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}></textarea>
        @elseif($field->type === 'date')
            <input type="date" class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
        @elseif($field->type === 'number')
            <input type="number" class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
        @elseif($field->type === 'select')
            <select class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
                <option value="">Select...</option>
                @foreach($field->options as $opt)
                    <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                @endforeach
            </select>
        @elseif($field->type === 'radio')
            <div>
                @foreach($field->options as $opt)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="formData.{{ $field->name }}" value="{{ $opt->value }}">
                        <label class="form-check-label">{{ $opt->label }}</label>
                    </div>
                @endforeach
            </div>
        @elseif($field->type === 'checkbox')
            @if($field->options->count() > 0)
                <div>
                    @foreach($field->options as $opt)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="formData.{{ $field->name }}" value="{{ $opt->value }}">
                            <label class="form-check-label">{{ $opt->label }}</label>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" wire:model="formData.{{ $field->name }}" value="1">
                    <label class="form-check-label">{{ $field->label }}</label>
                </div>
            @endif
        @elseif($field->type === 'dynamic')
            <select class="form-control" wire:model="formData.{{ $field->name }}" {{ $field->required ? 'required' : '' }}>
                <option value="">Select...</option>
                @if(isset($dynamicOptions[$field->id]))
                    @foreach($dynamicOptions[$field->id] as $opt)
                        <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                    @endforeach
                @endif
            </select>
        @endif
    @endif
</div>
