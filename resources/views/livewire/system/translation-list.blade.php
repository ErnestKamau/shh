<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-format-list-bulleted text-primary"></i> Translation Keys</h5>
            <small class="text-muted">Manage translation groups and keys.</small>
        </div>
        @if(auth()->user()->can('System.components.Translations.Add') || auth()->user()->can('System.permission'))
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" wire:click="$dispatch('open-translation-create')">
                <i class="mdi mdi-plus"></i> Add Translation
            </button>
        @endif
    </div>
    <div class="card-body">
        @if(session()->has('success') || session()->has('error'))
            <div class="alert {{ session()->has('success') ? 'alert-success' : 'alert-danger' }}">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @if(auth()->user()->can('System.components.Translations.Add') || auth()->user()->can('System.components.Translations.Edit') || auth()->user()->can('System.permission'))
            @livewire('system.translation-form')
        @endif

        <div class="form-row mb-3 align-items-end">
            <div class="col-md-4 mb-2">
                <label class="text-muted font-weight-bold">Search</label>
                <input type="text" class="form-control" wire:model.live.debounce.300ms="keySearch" placeholder="Search keys...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="text-muted font-weight-bold">Group</label>
                <select class="form-control" wire:model.live="groupFilter">
                    <option value="">All Groups</option>
                    @foreach($this->groups as $group)
                        <option value="{{ $group }}">{{ $group }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="text-muted font-weight-bold">Per Page</label>
                <select class="form-control" wire:model.live="perPage">
                    <option value="15">15 records</option>
                    <option value="25">25 records</option>
                    <option value="50">50 records</option>
                    <option value="100">100 records</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary mr-2" wire:click="exportCsv">
                    <i class="mdi mdi-file-delimited"></i> Export CSV
                </button>
                <button type="button" class="btn btn-outline-secondary" wire:click="exportJson">
                    <i class="mdi mdi-code-json"></i> Export JSON
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Key</th>
                        <th>Missing</th>
                        <th>Preview</th>
                        <th class="text-right">Actions</th>
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
                                    <span class="badge badge-warning">{{ $missing }} missing</span>
                                @else
                                    <span class="badge badge-success">Complete</span>
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
                                    <button type="button" class="btn btn-sm btn-success" wire:click="saveInlineEdit">Save</button>
                                    <button type="button" class="btn btn-sm btn-light" wire:click="cancelInlineEdit">Cancel</button>
                                @else
                                    @if(auth()->user()->can('System.components.Translations.Edit') || auth()->user()->can('System.permission'))
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="startInlineEdit({{ $line->id }})" title="Inline Edit"><i class="mdi mdi-table-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-info" wire:click="$dispatch('open-translation-edit', { id: {{ $line->id }} })" title="Edit"><i class="mdi mdi-pencil"></i></button>
                                    @endif
                                @endif
                                @if(auth()->user()->can('System.components.Translations.Delete') || auth()->user()->can('System.permission'))
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="askDelete({{ $line->id }})" title="Delete"><i class="mdi mdi-trash-can"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state-container text-muted">
                                    <i class="mdi mdi-translate-off text-light d-block mb-3" style="font-size: 4rem; opacity: 0.6;"></i>
                                    <h5 class="text-dark font-weight-bold">No Translations Found</h5>
                                    <p class="text-muted mb-4">We couldn't find any matching translation keys for your search criteria.</p>
                                    @if(auth()->user()->can('System.components.Translations.Add') || auth()->user()->can('System.permission'))
                                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="$dispatch('open-translation-create')">
                                            <i class="mdi mdi-plus mr-1"></i> Add Your First Translation
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
                <span>Confirm delete of this translation key?</span>
                <div>
                    <button type="button" class="btn btn-sm btn-danger" wire:click="deleteTranslation">Yes, Delete</button>
                    <button type="button" class="btn btn-sm btn-light" wire:click="cancelDelete">Cancel</button>
                </div>
            </div>
        @endif
    </div>
</div>
