<div class="table-responsive"><table class="table table-sm"><thead><tr><th>When</th><th>Action</th><th>User</th><th>Comment</th></tr></thead><tbody>
@foreach($actions as $a)<tr><td>{{ $a->performed_at?->format('Y-m-d H:i') }}</td><td>{{ $a->action_type }}</td><td>{{ $a->performer?->name }}</td><td>{{ $a->comment }}</td></tr>@endforeach
</tbody></table>{{ $actions->links() }}</div>
