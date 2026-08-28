<h2>What is a location?</h2>
<p>
	A <strong>location</strong> is a site or unit in your organisational structure —
	for example a headquarters, a lab, or a store that sits under a parent site.
	Locations define <strong>where</strong> inventory is managed and which currency applies there.
</p>
<p>
	In the menu this area is labelled <strong>Organizational Structure</strong>.
	The breadcrumb at the top of Inventory (for example <em>Location &gt; Dubai Lab</em>) shows which location you are working in.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/locations.png',
	'caption' => 'Organizational Structure — list of locations with level and parent.',
])

<h2>The list page</h2>
<p>Open <strong>Inventory → Organizational Structure</strong>.</p>
<ul>
	<li><strong>Add</strong> — create a new location (name, currency, parent location).</li>
	<li><strong>Edit</strong> — change name, currency, or parent.</li>
	<li><strong>Delete</strong> — remove a location that is no longer used.</li>
	<li><strong>Show</strong> (green eye) — open the location detail page.</li>
</ul>
<p>Columns on the list:</p>
<ul>
	<li><strong>Name</strong> — how the site appears across Inventory.</li>
	<li><strong>Unit</strong> — number of child units under this location.</li>
	<li><strong>Level</strong> — depth in the hierarchy (top level or nested).</li>
	<li><strong>Parent Unit</strong> — the location above this one, or <em>Top Level</em>.</li>
</ul>

<h2>Location view page</h2>
<p>
	Click <strong>Show</strong> on a row to open that location.
	The page lists <strong>users who have access</strong> to work in this location.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/locations-view.png',
	'caption' => 'Location view — users with access to this site (name and email).',
])

<ul>
	<li>Each row shows the user’s <strong>Name</strong> and <strong>Email</strong>.</li>
	<li><strong>Delete</strong> (red bin) on a row removes that user’s access to this location.</li>
	<li>Use the table search or export buttons (Copy, CSV, Excel, PDF, Print) to work with the list.</li>
</ul>

<h3>Adding users</h3>
<p>
	Click <strong>User Access</strong> at the top right to open the <strong>Add Users</strong> window.
	Select one or more people from the list, then <strong>Save</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/locations-add-users.png',
	'caption' => 'Add Users — grant access to this location.',
])

<p>
	Only people with access see and act on inventory for that site.
	Add store managers, procurement staff, and other key users here when a new location goes live.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-map-marker"></i></span>
	<p>
		Set up parent locations first (for example HQ), then add child units (for example a lab or store under HQ).
		Match names to how staff refer to sites day to day.
	</p>
</div>
