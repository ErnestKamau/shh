<div class="move-to-intray-modal-body">
    @if ($selectedFormInstanceIds === [])
        <div class="alert alert-light border mb-0">
            No requests selected.
        </div>
    @else
        <div class="move-to-intray-info-card mb-4">
            <div class="d-flex align-items-start">
                <span class="move-to-intray-info-icon mr-3">
                    <i class="mdi mdi-inbox-arrow-down"></i>
                </span>
                <div>
                    <h6 class="mb-2">What moving to an intray does</h6>
                    <ul class="mb-0 pl-3 small text-muted">
                        <li>Hands this submission request to another lab user for review or action.</li>
                        <li>Records every handoff in a movement log so you can see where the document has been.</li>
                        <li>Adds the request to the assignee’s <strong>My intray</strong> pending tasks until they mark it complete.</li>
                        <li>If the request is already in someone’s intray (pending), assigning again moves it to the new user and clears the previous pending task.</li>
                        <li>Does <strong>not</strong> change the request’s workflow status (submitted, received, etc.).</li>
                    </ul>
                </div>
            </div>
        </div>

        @if ($pendingIntrayAssignments !== [])
            <div class="alert alert-warning border-0 py-2 px-3 mb-4 small">
                <i class="mdi mdi-information-outline mr-1"></i>
                <strong>Reassignment:</strong>
                @if (count($pendingIntrayAssignments) === 1)
                    @php $pending = $pendingIntrayAssignments[0]; @endphp
                    <strong>{{ $pending['label'] ?? 'Request' }}</strong> is currently pending with
                    <strong>{{ $pending['assignee_name'] ?: 'another user' }}</strong>.
                    Choosing a new assignee will move it to their intray.
                @else
                    {{ count($pendingIntrayAssignments) }} selected request(s) already have a pending intray assignee.
                    Choosing a new assignee will reassign each one.
                @endif
            </div>
        @endif

        <div class="receive-sample-summary mb-4">
            <div class="receive-sample-summary-label">Selected requests</div>
            <div class="receive-sample-chip-list">
                @foreach ($selectedFormSummaries as $summary)
                    <span class="receive-sample-chip">
                        <strong>{{ $summary['label'] ?? 'Request' }}</strong>
                        @if (!empty($summary['customer']))
                            <span class="text-muted">· {{ $summary['customer'] }}</span>
                        @endif
                    </span>
                @endforeach
                @if ($selectedFormSummaries === [] && $selectedFormInstanceIds !== [])
                    <span class="receive-sample-chip text-muted">{{ count($selectedFormInstanceIds) }} request(s)</span>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label class="receive-sample-field-label" for="intray-assignee">Assign to <span class="text-danger">*</span></label>
            <select id="intray-assignee"
                wire:model="assigneeUserId"
                class="form-control form-control-sm @error('assigneeUserId') is-invalid @enderror">
                <option value="">Select a user</option>
                @foreach ($assignableUsers as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </select>
            @error('assigneeUserId')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-0">
            <label class="receive-sample-field-label" for="intray-comment">Comment</label>
            <textarea id="intray-comment"
                wire:model="comment"
                rows="3"
                class="form-control form-control-sm @error('comment') is-invalid @enderror"
                placeholder="Optional note for the assignee (reason for handoff, urgency, etc.)"></textarea>
            @error('comment')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    @endif

    @error('selection')
        <div class="alert alert-danger border-0 mt-3 mb-0">{{ $message }}</div>
    @enderror

    <div class="receive-sample-modal-footer d-flex justify-content-end align-items-center mt-4 pt-3">
        <button type="button" class="btn btn-light btn-sm mr-2" data-dismiss="modal">
            Cancel
        </button>
        <button type="button"
            class="btn btn-outline-primary btn-sm receive-sample-submit-btn"
            wire:click="confirmMove"
            wire:loading.attr="disabled"
            @if ($selectedFormInstanceIds === [] || $assignableUsers === []) disabled @endif>
            <span wire:loading.remove wire:target="confirmMove">
                <i class="mdi mdi-inbox-arrow-down mr-1"></i> Move to tray
            </span>
            <span wire:loading wire:target="confirmMove">
                <span class="spinner-border spinner-border-sm mr-1" role="status"></span>
                Moving…
            </span>
        </button>
    </div>
</div>
