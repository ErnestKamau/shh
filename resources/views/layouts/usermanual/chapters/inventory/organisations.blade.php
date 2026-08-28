<h2>What are organisations (departments)?</h2>
<p>
	<strong>Departments</strong> are the teams that request, use, and account for stock —
	for example Finance, Store Keeping, or Laboratory.
	When someone raises a Purchase Request or Request to Store, their department is recorded on the request.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/organisations.png',
	'caption' => 'Departments — active teams linked to Inventory.',
])

<h2>The list page</h2>
<p>Open <strong>Inventory → Departments</strong>.</p>
<ul>
	<li><strong>Add</strong> — create a department with a name.</li>
	<li><strong>Edit</strong> — change the name or tick <strong>Active</strong> on or off.</li>
	<li><strong>Show</strong> (green eye) — open the department detail page.</li>
</ul>
<p>Columns on the list:</p>
<ul>
	<li><strong>Name</strong> — department label shown on requests and reports.</li>
	<li><strong>Active</strong> — green tick means the department can be selected; a red cross means it is hidden from new activity.</li>
</ul>

<h2>Department view page</h2>
<p>
	Click <strong>Show</strong> to open <strong>Inventory Items Activity for [department name]</strong>.
	This is a movement history for that department — not a place to edit the department itself.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/organisations-view.png',
	'caption' => 'Department view — stock in and stock out activity for the department.',
])

<p>The table shows each stock movement line:</p>
<ul>
	<li><strong>Category</strong> and <strong>Sub Category</strong> — what moved.</li>
	<li><strong>Manufacturer</strong> — item manufacturer where recorded.</li>
	<li><strong>Stock In</strong> / <strong>Stock Out</strong> — quantity and unit for that movement.</li>
	<li><strong>Checked By</strong> — who recorded it.</li>
	<li><strong>Date</strong> — when it happened.</li>
</ul>
<p>
	If no movements exist yet, the table shows <em>No data available</em>.
	Use this page to review what a department has received or consumed over time.
	To change the department name or active status, go back to the list and use <strong>Edit</strong>.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-home-group"></i></span>
	<p>
		Keep department names aligned with your organisation chart.
		Deactivate departments that no longer exist so they do not appear on new requests.
	</p>
</div>
