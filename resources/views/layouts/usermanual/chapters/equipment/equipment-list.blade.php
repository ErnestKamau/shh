<h2>Equipment List</h2>
<p>
	Open <strong>Equipment → Equipment List</strong>.
	This is the main register of every piece of equipment.
	From here you search, filter, import, add, edit, view, and request disposal.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/equipment-list.png',
	'caption' => 'Equipment List — filters, action buttons, and the equipment table.',
])

<h2>Page header buttons</h2>
<ul>
	<li><strong>Import Equipment</strong> — opens <strong>Bulk Import Equipment</strong>. Download the Excel template, fill rows, then <strong>Upload &amp; Import</strong> (or <strong>Cancel</strong>).</li>
	<li><strong>Add Equipment</strong> — opens the <strong>Create Equipment</strong> wizard (five steps). Full field guide is in the Create &amp; edit chapter.</li>
</ul>

<h2>Filter Options</h2>
<ul>
	<li><strong>Search</strong> — type part of the name, equipment number, make, or model.</li>
	<li><strong>Status</strong> — choose a status (for example Active, Obsolete, Out Of Service) or leave <strong>All Status</strong>.</li>
	<li><strong>Show</strong> — how many rows per page.</li>
	<li><strong>Clear</strong> — resets filters.</li>
</ul>

<h2>Table columns</h2>
<ul>
	<li><strong>Actions</strong> — row icons (see below).</li>
	<li><strong>Photo</strong> — thumbnail if a picture was uploaded.</li>
	<li><strong>Name</strong> — equipment name.</li>
	<li><strong>Equipment Number</strong> — unique ID / asset number.</li>
	<li><strong>Make</strong> / <strong>Model</strong> / <strong>Serial Number</strong> / <strong>Manufacturer</strong>.</li>
	<li><strong>Department</strong> / <strong>Employee</strong> — assignment.</li>
	<li><strong>Purchased On</strong>.</li>
	<li><strong>Calibration Date</strong> / <strong>Maintenance Date</strong> — next or last schedule dates shown on the list (colour may warn when due).</li>
	<li><strong>Status</strong> — operational status badge.</li>
</ul>

<h2>Row actions</h2>
<ul>
	<li><strong>View</strong> — opens the equipment detail page.</li>
	<li><strong>Edit</strong> — opens the <strong>Edit Equipment</strong> wizard with existing values.</li>
	<li><strong>Request Disposal</strong> — starts a disposal request for that asset (see the Disposal chapter).</li>
</ul>

<h2>Schedule alerts on the list</h2>
<p>
	Some rows or banners may show messages such as <strong>Schedule Calibration</strong> or
	<strong>Calibration Required</strong> when an interval is due or overdue.
	Open the asset and add a calibration log (or maintenance log) to bring the schedule up to date.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Prefer <strong>Import Equipment</strong> with the official Excel template when onboarding many assets at once.
		Always download a fresh template so columns match the current import.
	</p>
</div>
