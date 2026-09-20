<h2>Asset Types &amp; Locations</h2>
<p>
	Master data used when you assign equipment on the create/edit wizard (Assignment step).
	Open them from the Equipment sidebar: <strong>Asset Types</strong> and <strong>Asset Locations</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/asset-types-locations.png',
	'caption' => 'Asset Types — search, list, and + Add New Type.',
])

<h2>Asset Types</h2>
<p>
	An <strong>asset type</strong> classifies equipment (codes and descriptions used in reports and filters).
	On the Assignment step the field is labelled <strong>Asset Type</strong>; search and pick a type
	(display often shows code and description together).
</p>
<p>
	Open <strong>Equipment → Asset Types</strong>.
	Subtitle: <em>Manage asset categories and classifications.</em>
</p>
<ul>
	<li><strong>+ Add New Type</strong> — create a type.</li>
	<li><strong>Filter Options → Search</strong> — search by asset code or description.</li>
	<li>Columns: <strong>Actions</strong> (Edit / Delete), <strong>Asset Code</strong>, <strong>Description</strong>, <strong>Active Equipments</strong> (toggle), <strong>Status</strong>.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/asset-types-create.png',
	'caption' => 'Create Asset Type — Asset Code, Description, and Active Status.',
])

<ol>
	<li>Click <strong>+ Add New Type</strong>.</li>
	<li>Enter <strong>Asset Code *</strong> (for example <em>IT-HW</em>).</li>
	<li>Enter <strong>Description *</strong> (for example <em>IT Hardware</em>).</li>
	<li>Leave <strong>Active Status</strong> checked if the type should be available immediately.</li>
	<li>Click <strong>Save Changes</strong> (or <strong>Cancel</strong>).</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/asset-types-created.png',
	'caption' => 'After save — Asset Type created successfully; new row appears in the list.',
])

<p>
	Then return to Equipment List → Edit an asset → Assignment → select the new type.
</p>

<h2>Asset Locations</h2>
<p>
	An <strong>asset location</strong> is where the equipment is kept (room, wing, site).
	Open <strong>Equipment → Asset Locations</strong> from the same sidebar.
	On the Assignment step the field is labelled <strong>Asset Location</strong>.
</p>
<ol>
	<li>Open <strong>Equipment → Asset Locations</strong>.</li>
	<li>Add or edit locations by name (and related attributes on screen).</li>
	<li>Assign locations on each equipment profile so lists and reports can group by place.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		<strong>Lab</strong> on the Assignment step is separate from Asset Location.
		Lab drives monitoring templates and daily log visibility; Asset Location is the physical / organisational place of the asset.
	</p>
</div>
