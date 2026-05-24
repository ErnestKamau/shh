<div>
    @if($actions->isEmpty())
        <div class="rr-empty-state">
            <div class="rr-empty-state__icon"><i class="mdi mdi-history"></i></div>
            <p class="rr-empty-state__text">No audit entries for this request.</p>
        </div>
    @else
        <div class="table-responsive rr-audit-table-wrap">
            <table class="table rr-audit-table mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actions as $a)
                        <tr>
                            <td class="rr-audit-table__when">{{ $a->performed_at?->format('M j, Y H:i') }}</td>
                            <td>
                                <span class="rr-audit-action">{{ ucwords(str_replace('_', ' ', $a->action_type)) }}</span>
                            </td>
                            <td>{{ $a->performer?->name ?? '—' }}</td>
                            <td class="rr-audit-table__comment">{{ $a->comment ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($actions->hasPages())
            <div class="rr-audit-pagination px-3 py-2 border-top">
                {{ $actions->links() }}
            </div>
        @endif
    @endif
</div>
