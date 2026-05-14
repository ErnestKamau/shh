<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-format-list-bulleted text-primary"></i> {{ __('system.translation_keys') }}</h5>
            <small class="text-muted">{{ __('system.manage_translation_keys') }}</small>
        </div>
        @if(auth()->user()->can('system.translations.keys.add'))
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" wire:click="$dispatch('open-translation-create')">
                <i class="mdi mdi-plus"></i> {{ __('system.add_translation') }}
            </button>
        @endif
    </div>
    <div class="card-body">
        @if(session()->has('success') || session()->has('error'))
            <div class="alert {{ session()->has('success') ? 'alert-success' : 'alert-danger' }}">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @if(auth()->user()->can('system.translations.keys.add') || auth()->user()->can('system.translations.keys.edit'))
            @livewire('system.translation-form')
        @endif

        <div class="form-row mb-3 align-items-end">
            <div class="col-md-4 mb-2">
                <label class="text-muted font-weight-bold">{{ __('system.search') }}</label>
                <input type="text" class="form-control" wire:model.live.debounce.300ms="keySearch" placeholder="{{ __('system.search_keys') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label class="text-muted font-weight-bold">{{ __('system.group') }}</label>
                <select class="form-control" wire:model.live="groupFilter">
                    <option value="">{{ __('system.all_groups') }}</option>
                    @foreach($this->groups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="text-muted font-weight-bold">{{ __('system.per_page') }}</label>
                <select class="form-control" wire:model.live="perPage">
                    <option value="15">15 {{ __('system.records') }}</option>
                    <option value="25">25 {{ __('system.records') }}</option>
                    <option value="50">50 {{ __('system.records') }}</option>
                    <option value="100">100 {{ __('system.records') }}</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary mr-2" wire:click="exportCsv">
                    <i class="mdi mdi-file-delimited"></i> {{ __('system.export_csv') }}
                </button>
                <button type="button" class="btn btn-outline-secondary" wire:click="exportJson">
                    <i class="mdi mdi-code-json"></i> {{ __('system.export_json') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>{{ __('system.group') }}</th>
                        <th>{{ __('system.key') }}</th>
                        <th>{{ __('system.missing') }}</th>
                        <th>{{ __('system.preview') }}</th>
                        <th class="text-right">{{ __('system.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->translations as $line)
                        @php
                            $text = is_array($line->text) ? $line->text : [];
                            $missing = $this->missingCount($line);
                        @endphp
                        <tr>
                            <td><span class="badge badge-light">{{ $line->group }}</span></td>
                            <td>{{ $line->key }}</td>
                            <td>
                                @if($missing > 0)
                                    <span class="badge badge-warning">{{ $missing }} {{ __('system.missing') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('system.complete') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($inlineEditId === $line->id)
                                    @foreach($this->activeLanguageCodes as $code)
                                        <div class="mb-1">
                                            <label class="small text-muted mb-0">{{ strtoupper($code) }}</label>
                                            <input type="text" class="form-control form-control-sm" wire:model.defer="inlineText.{{ $code }}">
                                        </div>
                                    @endforeach
                                    @error('inlineText')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                @else
                                    @foreach($this->activeLanguageCodes as $code)
                                        @php $value = trim((string) ($text[$code] ?? '')); @endphp
                                        <div class="small {{ $value === '' ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                            <strong>{{ strtoupper($code) }}:</strong> {{ $value === '' ? 'missing' : \Illuminate\Support\Str::limit($value, 50) }}
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-right">
                                @if($inlineEditId === $line->id)
                                    <button type="button" class="btn btn-sm btn-success" wire:click="saveInlineEdit">{{ __('system.save') }}</button>
                                    <button type="button" class="btn btn-sm btn-light" wire:click="cancelInlineEdit">{{ __('system.cancel') }}</button>
                                @else
                                    @if(auth()->user()->can('system.translations.keys.edit'))
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="startInlineEdit('{{ $line->id }}')" title="Inline Edit"><i class="mdi mdi-table-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-info" wire:click="$dispatch('open-translation-edit', { id: '{{ $line->id }}' })" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                    @endif
                                @endif
                                @if(auth()->user()->can('system.translations.keys.delete'))
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="askDelete('{{ $line->id }}')" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state-container text-muted">
                                    <i class="mdi mdi-translate-off text-light d-block mb-3" style="font-size: 4rem; opacity: 0.6;"></i>
                                    <h5 class="text-dark font-weight-bold">{{ __('system.no_translations_found') }}</h5>
                                    <p class="text-muted mb-4">{{ __('system.no_translations_criteria') }}</p>
                                    @if(auth()->user()->can('system.translations.keys.add'))
                                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="$dispatch('open-translation-create')">
                                            <i class="mdi mdi-plus mr-1"></i> {{ __('system.add_your_first_translation') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $this->translations->links() }}</div>

        @if($deleteLineId)
            <div class="alert alert-warning mt-3 mb-0 d-flex justify-content-between align-items-center">
                <span>{{ __('system.confirm_delete_translation_key') }}</span>
                <div>
                    <button type="button" class="btn btn-sm btn-danger" wire:click="deleteTranslation">{{ __('system.yes_delete') }}</button>
                    <button type="button" class="btn btn-sm btn-light" wire:click="cancelDelete">{{ __('system.cancel') }}</button>
                </div>
            </div>
        @endif
    </div>
</div>
