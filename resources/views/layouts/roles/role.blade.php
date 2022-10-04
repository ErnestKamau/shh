<form method="POST" action="{{ route('add-role-form') }}">
	@csrf
<h6>Roles </h6>
	<label>Name <input name="name" placeholder="Enter Name"/> </label><br>
	<label>Description <textarea name="description" placeholder="Enter Description..."></textarea> </label><br>
	<button type="submit">Save</button>
<table>
	@foreach ($roles as $role)
		<tr>
			<td>{{ $role->name }}</td>
			<td>{{ $role->description }}</td>
			<td>{{ $role->concat() }}</td>
		</tr>
	@endforeach
</table>

</form>