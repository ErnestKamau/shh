<div>
    @if($showInjectionModal)
        <div class="custom-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div class="custom-modal-dialog" style="background: white; border-radius: 5px; width: 100%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div class="custom-modal-content">
                    <div class="modal-header border-bottom p-3">
                        <h5 class="modal-title m-0">Required Parameters</h5>
                    </div>
                    <div class="modal-body p-3">
                        <p>This template requires additional data to generate the preview.</p>
                        @foreach($requiredInjections as $key => $inj)
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ $inj['label'] }}</label>
                                @if(!empty($injectionOptions[$key]))
                                    <select class="form-control" wire:model="injectionValues.{{ $key }}">
                                        <option value="">-- Select --</option>
                                        @foreach($injectionOptions[$key] as $opt)
                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" wire:model="injectionValues.{{ $key }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer border-top p-3 text-right">
                        <button type="button" class="btn btn-primary" wire:click="submitInjections">Preview with Data</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!$showInjectionModal)
    <form wire:submit.prevent="submit">
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif



        @foreach($template->sections as $section)
            <div class="mb-4 section-wrapper">
                {{-- Optional: If you want to show section title in preview/report, un-comment or style accordingly. For reports, often we want clean look --}}
                {{-- <h5 class="mb-3">{{ $section->title }}</h5> --}}
                
                <div class="row">
                    @foreach($section->fields->where('parent_field_id', null) as $field)
                        @include('template-engine::livewire.partials.render-field', [
                            'field' => $field, 
                            'resolvedVariables' => $resolvedVariables,
                            'dynamicOptions' => $dynamicOptions
                        ])
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($template->type !== 'report')
        <div class="text-right mt-3">
            <button type="submit" class="btn btn-success btn-lg">Submit Form</button>
        </div>
        @endif
    </form>
    @endif


</div>
