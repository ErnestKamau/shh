<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-translate text-primary"></i> {{ __('system.languages') }}</h5>
            <small class="text-muted">{{ __('system.manage_available_languages') }}</small>
        </div>
        @if(auth()->user()->can('system.translation.language.add'))
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" wire:click="$dispatch('open-language-create')">
                <i class="mdi mdi-plus"></i> {{ __('system.add_language') }}
            </button>
        @endif
    </div>

    <div class="card-body">
        @if(session()->has('success') || session()->has('error'))
            <div class="alert {{ session()->has('success') ? 'alert-success' : 'alert-danger' }}">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @if(auth()->user()->can('system.translation.language.add') || auth()->user()->can('system.translation.language.edit'))
            @livewire('system.language-form')
        @endif

        <div class="form-row mb-3 align-items-end">
            <div class="col-md-9 mb-2">
                <label class="text-muted font-weight-bold">{{ __('system.search') }}</label>
                <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('system.search_by_language_or_code') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label class="text-muted font-weight-bold">{{ __('system.per_page') }}</label>
                <select class="form-control" wire:model.live="perPage">
                    <option value="10">10 {{ __('system.records') }}</option>
                    <option value="25">25 {{ __('system.records') }}</option>
                    <option value="50">50 {{ __('system.records') }}</option>
                    <option value="100">100 {{ __('system.records') }}</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>{{ __('system.name') }}</th>
                        <th>{{ __('system.code') }}</th>
                        <th>{{ __('system.status') }}</th>
                        <th>{{ __('system.default') }}</th>
                        <th class="text-right">{{ __('system.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->languages as $language)
                        <tr>
                            <td>{{ $language->name }}</td>
                            <td><span class="badge badge-light text-uppercase">{{ $language->code }}</span></td>
                            <td>
                                @if(auth()->user()->can('system.translation.language.edit'))
                                    <button type="button" class="btn btn-sm {{ $language->is_active ? 'btn-success' : 'btn-outline-secondary' }}" wire:click="toggleActive('{{ $language->id }}')">
                                        {{ $language->is_active ? __('system.active') : __('system.inactive') }}
                                    </button>
                                @else
                                    <span class="badge {{ $language->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $language->is_active ? __('system.active') : __('system.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($language->is_default)
                                    <span class="badge badge-primary">{{ __('system.default') }}</span>
                                @elseif(auth()->user()->can('system.translation.language.edit'))
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="setDefault('{{ $language->id }}')" title="Set Default"><i class="mdi mdi-star"></i></button>
                                @else
                                    <span class="badge badge-light">-</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if(auth()->user()->can('system.translation.language.edit'))
                                    <button type="button" class="btn btn-sm btn-outline-info" wire:click="$dispatch('open-language-edit', { id: '{{ $language->id }}' })" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                @endif
                                @if(auth()->user()->can('system.translation.language.delete'))
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="askDelete('{{ $language->id }}')" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('system.no_languages_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $this->languages->links() }}</div>

        @if($deleteLanguageId)
            <div class="alert alert-warning mt-3 mb-0 d-flex justify-content-between align-items-center">
                <span>{{ __('system.confirm_delete_language') }}</span>
                <div>
                    <button type="button" class="btn btn-sm btn-danger" wire:click="deleteLanguage">{{ __('system.yes_delete') }}</button>
                    <button type="button" class="btn btn-sm btn-light" wire:click="cancelDelete">{{ __('system.cancel') }}</button>
                </div>
            </div>
        @endif
    </div>
</div>
