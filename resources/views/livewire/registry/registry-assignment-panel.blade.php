<div class="rr-tab-pane-content">
        <form wire:submit="assign" class="rr-assign-form mb-3">
            <div class="form-group mb-2">
                <label class="rr-meta-label d-block mb-1">Assign to</label>
                <select wire:model.live="assigned_to" class="form-control no-select2">
                    <option value="">Select user…</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group mb-2">
                <label class="rr-meta-label d-block mb-1">Role context</label>
                <input wire:model="role_context" class="form-control" placeholder="e.g. Reviewer, Registrar">
            </div>
            <button type="submit" class="btn btn-primary rr-btn-assign" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="assign"><i class="mdi mdi-account-plus-outline"></i> Assign</span>
                <span wire:loading wire:target="assign">Assigning…</span>
            </button>
        </form>

        @if($request->assignments->isNotEmpty())
            <ul class="rr-assign-list">
                @foreach($request->assignments as $a)
                    <li class="rr-assign-list__item">
                        <div class="rr-assign-list__avatar">
                            {{ strtoupper(substr($a->assignee?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="rr-assign-list__info">
                            <span class="rr-assign-list__name">{{ $a->assignee?->name ?? 'Unknown' }}</span>
                            @if($a->role_context)
                                <span class="rr-assign-list__role">{{ $a->role_context }}</span>
                            @endif
                        </div>
                        @if($a->is_active)
                            <span class="rr-badge rr-badge--priority-low">Active</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="rr-assign-empty mb-0">No assignments yet.</p>
        @endif
</div>
