@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-{{ $flashType === 'warning' ? 'warning' : 'success' }} alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Evaluation Approvals',
    'subtitle' => 'Review supervisor evaluations before capability updates',
    'icon' => 'mdi-clipboard-check-outline',
])
@endcomponent

@include('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search by attendee, evaluator, or notes...'])

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Pending Evaluations',
])
    @slot('body')
        @if($pending->count() > 0)
            @component('livewire.skills-matrix.partials.list-table')
                @slot('head')
                    <th>Attendee</th>
                    <th>Evaluator</th>
                    <th>Proposed Level</th>
                    <th>Notes</th>
                    <th>Actions</th>
                @endslot
                @slot('body')
                    @foreach($pending as $ev)
                        <tr wire:key="ev-{{ $ev->id }}">
                            <td><strong>{{ $ev->attendee->name ?? '—' }}</strong></td>
                            <td>{{ $ev->evaluator->name ?? '—' }}</td>
                            <td>{{ $ev->proposedProficiency->description ?? '—' }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($ev->score_notes ?? '', 50) }}</td>
                            <td>
                                <div class="sm-table-actions">
                                    <button type="button" class="btn btn-sm btn-success" wire:click="approve('{{ $ev->id }}')">Approve</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="reject('{{ $ev->id }}')">Reject</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endslot
            @endcomponent
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $pending])
        @else
            <p class="text-muted mb-0 text-center py-4">No pending evaluations.</p>
        @endif
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-page-close')
