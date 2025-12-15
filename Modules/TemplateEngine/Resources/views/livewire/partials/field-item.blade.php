<div class="field-item {{ $field->type === 'container' ? 'element-holder-container' : '' }}">
    <!-- Actions Toolbar -->
    <div class="field-item-actions">
        @if($field->type === 'container')
             <button class="btn btn-xs text-success" title="Add Child" wire:click="addField({{ $field->section_id }}, {{ $field->id }})">
                <i class="fas fa-plus"></i>
             </button>
        @endif
        <button class="btn btn-xs text-primary" title="Edit" wire:click="editField({{ $field->id }})">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-xs text-danger" title="Delete" wire:click="deleteField({{ $field->id }})" onclick="confirm('Are you sure you want to delete this field?') || event.stopImmediatePropagation()">
            <i class="fas fa-trash"></i>
        </button>
    </div>

    @if($field->type === 'container')
        <div style="min-height: 50px;">
            <div class="holder-header">
                <span class="holder-title">
                    <i class="fas fa-box-open mr-1 text-muted"></i> {{ $field->label }}
                </span>
                <span class="badge badge-light border">{{ $field->children->count() }} items</span>
            </div>
            
            <div class="holder-content p-2">
                @if($field->children->count() > 0)
                    @foreach($field->children as $child)
                        @include('template-engine::livewire.partials.field-item', ['field' => $child])
                    @endforeach
                @else
                    <div class="text-center text-muted py-3 small" wire:click="addField({{ $field->section_id }}, {{ $field->id }})" style="cursor: pointer; border: 1px dashed #ddd; border-radius: 4px;">
                        <i class="fas fa-plus mr-1"></i> Add content here
                    </div>
                @endif
            </div>
        </div>
    @elseif(in_array($field->type, ['heading', 'paragraph', 'blockquote', 'code_block', 'link']))
        <!-- Static Content Preview -->
        <div>
            @if($field->type === 'heading')
                <div class="p-2 border rounded bg-white">
                    <span class="badge badge-light mb-1">H{{ $field->meta['level'] ?? '1' }}</span>
                    <h5 class="mb-0 text-dark">{{ $field->label }}</h5>
                </div>
            @elseif($field->type === 'paragraph')
                <div class="p-2 border rounded bg-white text-muted">
                    {{ Str::limit($field->label, 100) }}
                </div>
            @elseif($field->type === 'link')
                <div class="p-2 border rounded bg-white">
                    <a href="#" class="text-primary"><i class="fas fa-link mr-1"></i> {{ $field->label }}</a>
                </div>
            @else
                 <div class="p-2 border rounded bg-white">
                    <span class="badge badge-secondary">{{ $field->type }}</span> {{ Str::limit($field->label, 50) }}
                </div>
            @endif
        </div>
    @elseif($field->type === 'image')
         <div class="p-2 border rounded bg-white text-center">
            <i class="fas fa-image text-muted fa-2x mb-2"></i>
            <div class="small">{{ $field->label }}</div>
         </div>
    
    @else
        <!-- Form Fields Preview -->
        <div>
            <label class="field-label small text-uppercase text-muted">
                {{ $field->label }}
                @if($field->required) <span class="text-danger">*</span> @endif
            </label>
            <div class="field-preview">
                @if($field->type === 'dynamic')
                    <i class="fas fa-database text-warning mr-2"></i> Bound to Dataset
                @elseif($field->type === 'select')
                     Select Option <i class="fas fa-chevron-down float-right small mt-1"></i>
                @elseif($field->type === 'date')
                     YYYY-MM-DD <i class="far fa-calendar float-right small mt-1"></i>
                @else
                     Placeholder input...
                @endif
            </div>
        </div>
    @endif
</div>
