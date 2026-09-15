<h2>Asset Types &amp; Locations</h2>
<p>
	Master data used when you assign equipment on the create/edit wizard (Assignment step).
	Open them from the Equipment sidebar: <strong>Asset Types</strong> and <strong>Asset Locations</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/asset-types-locations.png',
	'caption' => 'Asset Types and Asset Locations managers in the Equipment module.',
])

<h2>Asset Types</h2>
<p>
	An <strong>asset type</strong> classifies equipment (codes and descriptions used in reports and filters).
	On the Assignment step the field is labelled <strong>Asset Type</strong>; search and pick a type
	(display often shows code and description together).
</p>
<ol>
	<li>Open <strong>Equipment → Asset Types</strong>.</li>
	<li>Add or edit types (code, description, and any other fields shown).</li>
	<li>Use import/export templates if your organisation loads many types at once.</li>
	<li>Return to Equipment List → Edit an asset → Assignment → select the new type.</li>
</ol>

<h2>Asset Locations</h2>
<p>
	An <strong>asset location</strong> is where the equipment is kept (room, wing, site).
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
