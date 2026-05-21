<div class="workflow-board-panel-body flush-top px-0">
    <div class="request-notes-composer">
        <h6><i class="mdi mdi-comment-plus-outline"></i> Add note</h6>
        <form wire:submit="addNote">
            <div class="form-group mb-3">
                <label for="noteBody" class="small font-weight-bold text-muted">Message</label>
                <textarea wire:model="noteBody" id="noteBody" class="form-control @error('noteBody') is-invalid @enderror" rows="3" placeholder="Write a note for this request…"></textarea>
                @error('noteBody')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-row align-items-end">
                <div class="form-group col-md-4 mb-md-0">
                    <label for="noteVisibility" class="small font-weight-bold text-muted">Visibility</label>
                    <select wire:model="noteVisibility" id="noteVisibility" class="form-control form-control-sm">
                        <option value="internal">Internal (lab only)</option>
                        <option value="public">Public (notify customer)</option>
                    </select>
                </div>
                <div class="form-group col-md-8 text-md-right mb-0">
                    <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addNote"><i class="mdi mdi-content-save-outline"></i> Save note</span>
                        <span wire:loading wire:target="addNote">Saving…</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($instance->notes->isEmpty())
        <p class="text-muted mb-0 py-2">No notes yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table mb-0">
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Visibility</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instance->notes as $note)
                        <tr>
                            <td class="text-nowrap">{{ $note->author?->name ?? '—' }}</td>
                            <td class="text-nowrap">{{ $note->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($note->isPublic())
                                    <span class="badge badge-info">Public</span>
                                @else
                                    <span class="badge badge-secondary">Internal</span>
                                @endif
                            </td>
                            <td>{{ $note->body }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
