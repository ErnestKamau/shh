<h2>Pricelist home</h2>
<p>
	Open <strong>Billing → Pricelists</strong>. Create a profile first, then open the list to assign customers and manage items.
	New lists start as a profile record — add items and customers from the details screen after saving.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-create.png',
	'caption' => 'Create Pricelist — description, currency, valid till, status tag, Billing mode (Per package by default), Master and Active flags.',
])

<h2>Billing mode (header)</h2>
<p>
	<strong>Billing mode</strong> is set on the pricelist header when you create or edit the profile:
	<strong>Per package</strong> (default) or <strong>Per test</strong>.
	All catalogue items follow this mode — the Add Item screen shows the mode as locked from the header.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-show.png',
	'caption' => 'Pricelist Details — currency, revision, assigned customers, pending changes; profile and customer assignment.',
])

<h2>Assign customers</h2>
<p>
	On the <strong>Customer Assignment</strong> tab, search customers, stage chips, then assign.
	Only assigned customers see this list as eligible when preparing a quotation.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-customers.png',
	'caption' => 'Assigned customers — linked customers and Assign Selected Chips from the typeahead list.',
])

<h2>Pricelist items</h2>
<p>
	The <strong>Pricelist Items</strong> tab holds the catalogue. Use filters (All / Pending / Applied), search, and:
</p>
<ul>
	<li><strong>Apply Price Changes</strong> — makes pending selling prices live and bumps revision.</li>
	<li><strong>Clone Selected</strong> — copy selected items to a new list.</li>
	<li><strong>Import</strong> — AmSpec Excel/PDF into this catalogue (see Import chapter).</li>
	<li><strong>+ Add Item</strong> — add a catalogue row; pricing mode is already fixed on the pricelist header.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-items.png',
	'caption' => 'Pricelist Items — Apply Price Changes, Clone Selected, Import, Add Item; Pending vs Applied.',
])

<h2>Adding items (mode comes from the header)</h2>
<p>
	You do <strong>not</strong> pick per package vs per test on each item.
	<strong>Billing mode</strong> is set once on the pricelist profile (Create / Customer Assignment profile).
	On <strong>Add Pricelist Item</strong>, the mode is shown as read-only:
	<em>“Set on the pricelist header. All items follow this mode.”</em>
	To change mode, edit the pricelist header — not the item form.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-item-modal.png',
	'caption' => 'Add Pricelist Item — Pricing mode badge (e.g. Per package) is locked from the header; enter sample type and prices for that mode.',
])

<div class="um-compare">
	<div class="um-compare__card">
		<h4>When the header is Per package</h4>
		<ul>
			<li>Choose sample type, then tick every test in the package.</li>
			<li>Enter one <strong>package</strong> cost and one package selling price (price for a single sample).</li>
			<li>On a quotation: line total = number of samples × this package price — not once per test.</li>
			<li>Package TAT is usually the longest among the tests you tick.</li>
			<li>If VAT is ticked, the rate comes from Tax Regime Manager.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>When the header is Per test</h4>
		<ul>
			<li>Choose sample type, then tick each test to price.</li>
			<li>Enter cost and selling price <strong>per test</strong>.</li>
			<li>On a quotation: each selected test is typically its own line (samples × that test’s unit price).</li>
			<li>Tick VAT per test when taxable (rate from Tax Regime).</li>
		</ul>
	</div>
</div>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-item-flags.png',
	'caption' => 'Only ticked tests are saved. Prices stay pending until Apply Price Changes. Flags: Internal, External, Active.',
])

<h2>Pending prices vs Apply Price Changes</h2>
<p>
	Saving an item stores a proposed selling price. Rows can show as uncommitted until you press
	<strong>Apply Price Changes</strong>, which makes those prices live and bumps the revision.
	That is separate from the <strong>Active / Inactive</strong> badge.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>Healthy habit: update the pricelist → Apply Price Changes → then price requests or quotations so customers see the intended numbers.</p>
</div>

<h2>Import into this pricelist</h2>
<p>
	Use <strong>Import</strong> on the items toolbar to load AmSpec Excel or PDF package prices into the catalogue.
	Full steps, columns, and pricing modes are in the <strong>Import AmSpec template</strong> chapter.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-import-modal.png',
	'caption' => 'Import into pricelist — Amspec quotation-preparation template, Excel/PDF, Per package default.',
])
