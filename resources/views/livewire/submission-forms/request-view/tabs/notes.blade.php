<div class="rv-notes-tab workflow-board-panel-body flush-top px-0">
    <div class="rv-tab-panel-header">
        <div>
            <h5 class="rv-sample-collection-tab__title mb-1">
                <span class="ls-icon-tile__glyph ls-icon--burgundy rv-tab-title-icon" aria-hidden="true">
                    <i class="mdi mdi-comment-text-outline"></i>
                </span>
                Notes
            </h5>
            <p class="rv-sample-collection-tab__hint mb-0">
                Internal or public notes for this request.
            </p>
        </div>
    </div>

    <div class="request-notes-composer">
        <h6 class="ls-type-label mb-2">
            <i class="mdi mdi-comment-plus-outline" aria-hidden="true"></i>
            Add note
        </h6>
        <form wire:submit="addNote">
            <div class="ls-field mb-3">
                <label class="ls-field__label" for="noteBody">Message</label>
                <textarea
                    wire:model="noteBody"
                    id="noteBody"
                    class="ls-textarea @error('noteBody') is-invalid @enderror"
                    rows="3"
                    placeholder="Write a note for this request…"
                ></textarea>
                @error('noteBody')
                    <p class="ls-field__msg ls-field__msg--error"><i class="mdi mdi-alert-circle-outline"></i> {{ $message }}</p>
                @enderror
            </div>
            <div class="form-row align-items-end">
                <div class="form-group col-md-4 mb-md-0">
                    <div class="ls-field">
                        <label class="ls-field__label" for="noteVisibility">Visibility</label>
                        <div class="ls-field__control">
                            <select wire:model="noteVisibility" id="noteVisibility" class="ls-field__input">
                                <option value="internal">Internal (lab only)</option>
                                <option value="public">Public (notify customer)</option>
                            </select>
                        </div>
                    </div>
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
        <p class="rv-tab-empty text-muted mb-0 py-2 px-3">No notes yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover workflow-table rv-tab-table mb-0">
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
