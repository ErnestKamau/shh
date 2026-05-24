@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => $plan->name,
    'subtitle' => 'Manage each training session — attendance, materials, and evaluations are recorded per session',
    'icon' => 'mdi-calendar-check',
])
@endcomponent

<div class="sm-tp-hint card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-4 d-flex align-items-start">
        <i class="mdi mdi-information-outline text-primary mr-3" style="font-size: 22px;"></i>
        <div>
            <strong class="d-block mb-1" style="font-size: 13px;">How this plan works</strong>
            <span class="text-muted" style="font-size: 12px;">
                Select a training on the left. Attendance invites, course materials, and competency evaluations are tracked separately for that session — not for the whole plan.
            </span>
        </div>
    </div>
</div>

<div class="sm-training-plan-layout">
    {{-- Session catalog --}}
    <aside class="sm-tp-sidebar card shadow-sm border-0">
        <div class="sm-tp-sidebar-head">
            <div>
                <h6 class="mb-0 font-weight-bold">Training sessions</h6>
                <span class="text-muted" style="font-size: 11px;">{{ $sessionCount }} in this plan</span>
            </div>
        </div>
        <div class="sm-tp-sidebar-body">
            <div class="sm-tp-section-label">From training needs</div>
            @forelse($competencySessionGroups as $competencyId => $group)
                @php $lead = $group->first(); @endphp
                @include('livewire.skills-matrix.partials.training-plan-session-picker', [
                    'session' => $lead,
                    'staffGapCount' => $group->count(),
                ])
            @empty
                <p class="sm-tp-empty-note">No competency sessions yet.</p>
            @endforelse

            <div class="sm-tp-section-label mt-3">Additional trainings</div>
            @forelse($otherSessions as $session)
                @include('livewire.skills-matrix.partials.training-plan-session-picker', ['session' => $session])
            @empty
                <p class="sm-tp-empty-note">No additional trainings yet.</p>
            @endforelse
        </div>
    </aside>

    {{-- Per-session workspace --}}
    <section class="sm-tp-workspace card shadow-sm border-0">
        @if($selectedSession)
            @php
                $status = $this->trainingStatusMeta($selectedSession->status);
            @endphp
            <div class="sm-tp-workspace-head">
                <div class="sm-tp-workspace-head-main">
                    <span class="sm-tp-workspace-week">Week {{ $selectedSession->week_no ?? '—' }}</span>
                    <h5 class="sm-tp-workspace-title mb-1">{{ $this->sessionTitle($selectedSession) }}</h5>
                    <div class="sm-tp-workspace-sub text-muted">
                        <span>{{ $this->sessionArea($selectedSession) }}</span>
                        @if(filled($selectedSession->organizer_trainer))
                            <span class="mx-2">·</span>
                            <span><i class="mdi mdi-account-tie-outline"></i> {{ $selectedSession->organizer_trainer }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <span class="sm-training-status {{ $status['class'] }}">{{ $status['label'] }}</span>
                    <button type="button" class="btn btn-sm btn-light border" wire:click="clearSelectedSession" title="Close session">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
            </div>

            <div class="sm-tp-session-tabs">
                <button
                    type="button"
                    class="sm-tp-session-tab {{ $activeTab === 'attendance' ? 'is-active' : '' }}"
                    wire:click="selectSession('{{ $selectedSession->id }}', 'attendance')"
                >
                    <i class="mdi mdi-account-group-outline"></i> Attendance
                </button>
                <button
                    type="button"
                    class="sm-tp-session-tab {{ $activeTab === 'materials' ? 'is-active' : '' }}"
                    wire:click="selectSession('{{ $selectedSession->id }}', 'materials')"
                >
                    <i class="mdi mdi-file-document-outline"></i> Materials
                </button>
                <button
                    type="button"
                    class="sm-tp-session-tab {{ $activeTab === 'evaluation' ? 'is-active' : '' }}"
                    wire:click="selectSession('{{ $selectedSession->id }}', 'evaluation')"
                >
                    <i class="mdi mdi-clipboard-check-outline"></i> Evaluation
                </button>
            </div>

            <div class="sm-tp-workspace-body">
                @if($activeTab === 'attendance')
                    <div class="sm-tp-panel">
                        <div class="sm-tp-panel-head">
                            <div>
                                <h6 class="mb-0 font-weight-bold">Session attendance</h6>
                                <p class="text-muted small mb-0">Invite staff and confirm who attended this training</p>
                            </div>
                        </div>
                        <div class="sm-tp-invite-bar">
                            <div class="flex-grow-1" style="min-width: 200px;">
                                <label class="form-label fw-bold small mb-1">Invite attendee</label>
                                <select class="form-control form-control-sm" wire:model="selectedAttendeeId">
                                    <option value="">Choose staff member</option>
                                    @foreach($staffOptions as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @can('skills-matrix.components.training-plan.attend.manage')
                                <button type="button" class="btn btn-sm btn-primary sm-tp-invite-btn" wire:click="inviteAttendee">
                                    <i class="mdi mdi-email-plus-outline"></i> Send invite
                                </button>
                            @endcan
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm sm-modern-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Attendee</th>
                                        <th>Confirmed</th>
                                        <th>Present</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendance as $row)
                                        <tr wire:key="att-{{ $row->id }}">
                                            <td class="font-weight-medium">{{ $row->user->name ?? '—' }}</td>
                                            <td>
                                                @if($row->confirmed_at)
                                                    <span class="sm-tp-pill sm-tp-pill--ok">{{ $row->confirmed_at->format('M j, Y H:i') }}</span>
                                                @else
                                                    <span class="sm-tp-pill sm-tp-pill--muted">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($row->marked_present_at)
                                                    <span class="sm-tp-pill sm-tp-pill--ok">{{ $row->marked_present_at->format('M j, Y H:i') }}</span>
                                                @else
                                                    <span class="sm-tp-pill sm-tp-pill--muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="sm-table-actions justify-content-end">
                                                    @can('skills-matrix.components.training-plan.attend.confirm')
                                                        @if(!$row->confirmed_at && (string) $row->user_id === (string) auth()->id())
                                                            <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--view" wire:click="confirmAttendance('{{ $row->id }}')">Confirm</button>
                                                        @endif
                                                    @endcan
                                                    @can('skills-matrix.components.training-plan.attend.manage')
                                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="markPresent('{{ $row->id }}')">Mark present</button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">
                                                <div class="sm-tp-inline-empty">
                                                    <i class="mdi mdi-account-off-outline"></i>
                                                    No attendees yet — invite staff above.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($activeTab === 'materials')
                    <div class="sm-tp-panel">
                        <div class="sm-tp-panel-head">
                            <div>
                                <h6 class="mb-0 font-weight-bold">Training materials</h6>
                                <p class="text-muted small mb-0">Files and resources for this session only</p>
                            </div>
                        </div>
                        @can('skills-matrix.components.training-plan.materials.manage')
                            <div class="sm-tp-upload-zone">
                                <label class="form-label fw-bold small mb-2">Upload file</label>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 12px;">
                                    <input type="file" wire:model="uploadMaterial" class="form-control-file">
                                    <button type="button" class="btn btn-sm btn-primary" wire:click="uploadSessionMaterial" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="uploadSessionMaterial,uploadMaterial"><i class="mdi mdi-upload"></i> Upload</span>
                                        <span wire:loading wire:target="uploadSessionMaterial,uploadMaterial">Uploading…</span>
                                    </button>
                                </div>
                            </div>
                        @endcan
                        <div class="sm-tp-materials-list">
                            @forelse($materials as $m)
                                <div class="sm-tp-material-row" wire:key="mat-{{ $m->id }}">
                                    <div class="sm-tp-material-icon"><i class="mdi mdi-file-outline"></i></div>
                                    <span class="sm-tp-material-name">{{ $m->original_name }}</span>
                                    <a href="{{ route('matrix.training.material.download', $m->id) }}" class="btn btn-sm rm-act-btn rm-act-btn--view">
                                        <i class="mdi mdi-download"></i> Download
                                    </a>
                                </div>
                            @empty
                                <div class="sm-tp-inline-empty">
                                    <i class="mdi mdi-folder-open-outline"></i>
                                    No materials uploaded for this session.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                @if($activeTab === 'evaluation')
                    <div class="sm-tp-panel">
                        <div class="sm-tp-panel-head">
                            <div>
                                <h6 class="mb-0 font-weight-bold">Training evaluation</h6>
                                <p class="text-muted small mb-0">Record proposed competency level after this session</p>
                            </div>
                        </div>
                        @can('skills-matrix.components.training-plan.evaluation.submit')
                            <div class="sm-tp-eval-form">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Attendee</label>
                                        <select class="form-control" wire:model="evaluationUserId">
                                            <option value="">Select attendee</option>
                                            @foreach($staffOptions as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Proposed proficiency level</label>
                                        <select class="form-control" wire:model="evaluationProficiencyId">
                                            <option value="">Select level</option>
                                            @foreach($proficiencies as $p)
                                                <option value="{{ $p->id }}">{{ $p->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Evaluator notes</label>
                                    <textarea class="form-control" wire:model="evaluationNotes" rows="4" placeholder="Observations, outcomes, and recommendations for this training session…"></textarea>
                                </div>
                                <div class="sm-table-actions">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="saveEvaluation(false)">Save draft</button>
                                    <button type="button" class="btn btn-primary btn-sm" wire:click="saveEvaluation(true)">Submit for approval</button>
                                </div>
                            </div>
                        @else
                            <p class="text-muted small mb-0">You do not have permission to submit evaluations.</p>
                        @endcan
                    </div>
                @endif
            </div>
        @else
            <div class="sm-tp-workspace-empty">
                <div class="sm-tp-workspace-empty-icon">
                    <i class="mdi mdi-gesture-tap-button"></i>
                </div>
                <h5 class="font-weight-bold mb-2">Select a training session</h5>
                <p class="text-muted mb-0" style="max-width: 360px;">
                    Choose a session from the list to manage <strong>attendance</strong>, <strong>materials</strong>, and <strong>evaluations</strong> for that specific training.
                </p>
            </div>
        @endif
    </section>
</div>

@include('livewire.skills-matrix.partials.list-page-close')
