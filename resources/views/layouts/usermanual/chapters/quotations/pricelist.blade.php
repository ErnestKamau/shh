<h2>Who uses this?</h2>
<p>
	<strong>Commercial / finance staff</strong> maintain pricelists — the price book the lab quotes from.
	<strong>Lab managers</strong> may review margins and approve catalogue changes.
	Once a list is live and assigned to customers, anyone preparing quotations can pull prices from it.
</p>
<p>
	Open <strong>Billing → Pricelists</strong>. Create a profile first, then open the list to assign customers and manage items.
</p>

<h2>Step 1 — Create a pricelist</h2>
<ol>
	<li>On the Pricelists list, press <strong>+ Add Pricelist</strong>.</li>
	<li>Fill in <strong>Description</strong> (required), <strong>Currency</strong> (required), and optional <strong>Valid till</strong>.</li>
	<li>Choose <strong>Billing mode</strong> — this is fixed for every item on the list:
		<ul>
			<li><strong>Per package</strong> (default) — one unit price covers a bundle of tests on one sample type.</li>
			<li><strong>Per test</strong> — each parameter is priced individually.</li>
		</ul>
	</li>
	<li>Set <strong>Master</strong> only when this list should be flagged as the organisation’s master catalogue (still requires customer assignment before use on a quote).</li>
	<li>Leave <strong>Active</strong> ticked so the list can be assigned and used.</li>
	<li>Press <strong>Save Pricelist</strong>. You land on the details screen to add items and customers.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-create.png',
	'caption' => 'Create Pricelist — description, currency, valid till, status tag, Billing mode (Per package by default), Master and Active flags.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-create-billing-mode.png',
	'caption' => 'Billing mode on create — Per package vs Per test. All catalogue items follow the header mode.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>New lists start as a profile record. Add items and assign customers from the details screen after saving — the list is not eligible on quotations until both are done.</p>
</div>

<h2>Step 2 — Import into a pricelist (optional)</h2>
<p>
	To load many rows at once, use <strong>Import</strong> on the <strong>Pricelist Items</strong> tab (or from the list when offered).
	This uses the AmSpec quotation-preparation template family (Excel or PDF).
</p>
<ol>
	<li>Download the Excel or PDF template from the import modal.</li>
	<li>Fill sample types, parameters, and unit prices. Names must match LIMS exactly.</li>
	<li>Choose <strong>File type</strong> (Excel or PDF) and <strong>Pricing mode</strong> (default <strong>Per package</strong>).</li>
	<li>Upload and press <strong>Import</strong>. Review created / updated counts and any warnings.</li>
	<li>On the items tab, review pending rows, then press <strong>Apply Price Changes</strong> so selling prices go live.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-import-modal.png',
	'caption' => 'Import into pricelist — template guide, Excel/PDF, Per package default, file upload.',
])

<p>
	Full column lists and quotation-side import are in the <strong>Import AmSpec template</strong> chapter.
</p>

<h2>Step 3 — Assign customers</h2>
<p>
	Only customers explicitly linked to a pricelist see it as <strong>eligible</strong> when preparing a quotation.
	Master lists do <em>not</em> apply globally — assign the customer even for a master catalogue.
</p>
<ol>
	<li>Open the pricelist → <strong>Customer Assignment</strong> tab.</li>
	<li>Search customers in the right-hand panel (typeahead by name, code, or email).</li>
	<li>Press <strong>+ Add</strong> on each customer — they appear as blue chips above the list.</li>
	<li>Press <strong>Assign Selected Chips</strong> to link them. A success message confirms the count.</li>
	<li>Assigned customers appear under <strong>Assigned Customers</strong> with contact details and a remove action if needed.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-show.png',
	'caption' => 'Pricelist Details — profile on the left; search and stage customers on the right.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-customers-staging.png',
	'caption' => 'Staging customers — chips above the list before Assign Selected Chips.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-customers-empty.png',
	'caption' => 'Assigned Customers tab — empty until you assign chips from the panel.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-customers.png',
	'caption' => 'After assignment — linked customer with Active badge and remove option.',
])

<h2>Step 4 — Create pricelist items</h2>
<p>
	On the <strong>Pricelist Items</strong> tab, build the catalogue. Toolbar actions:
</p>
<ul>
	<li><strong>Apply Price Changes</strong> — commits pending selling prices and bumps revision.</li>
	<li><strong>Clone Selected</strong> — copy selected rows to another list.</li>
	<li><strong>Import</strong> — AmSpec bulk load (see above).</li>
	<li><strong>+ Add Item</strong> — add one row; billing mode is read-only from the header.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-items.png',
	'caption' => 'Pricelist Items — package row with Cost, Selling, Profit, Margin, VAT, Active; package tests are shown under the row (click to collapse).',
])

<h3>Add Pricelist Item — per package header</h3>
<ol>
	<li>Press <strong>+ Add Item</strong>.</li>
	<li>Choose <strong>Sample type</strong>.</li>
	<li>Confirm the badge shows <strong>Per package</strong> (locked from the header).</li>
	<li>Enter <strong>Package cost price</strong> and <strong>Package selling price</strong>.</li>
	<li>Tick <strong>Has VAT</strong> when taxable (rate from Tax Regime Manager).</li>
	<li>Tick every test that belongs in the package (use <strong>Select all</strong> or pick individually).</li>
	<li>Set <strong>Internal</strong>, <strong>External</strong>, and <strong>Active</strong> flags (see below).</li>
	<li>Press <strong>Save Item</strong>, then <strong>Apply Price Changes</strong> when ready to publish prices.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-item-modal.png',
	'caption' => 'Add Pricelist Item (Per package) — package cost/selling price, VAT, and test selection instructions.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-item-test-selection.png',
	'caption' => 'Tick tests for the package — e.g. HALAL microbiology parameters with method and TAT columns.',
])

<div class="um-compare">
	<div class="um-compare__card">
		<h4>When the header is Per package</h4>
		<ul>
			<li>One cost and one selling price for the whole package (per single sample).</li>
			<li>On a quotation: line total = <strong>number of samples × package unit price</strong> — not once per test inside the package.</li>
			<li>Package TAT is the longest among the tests you tick.</li>
			<li>Expand a saved package row to see <strong>Package parameters</strong> (tests, methods, TAT).</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>When the header is Per test</h4>
		<ul>
			<li>Choose sample type, then tick each test to price.</li>
			<li>Enter <strong>cost price</strong> and <strong>selling price</strong> per test row.</li>
			<li>On a quotation: each selected test is typically its own line (samples × that test’s unit price).</li>
			<li>Tick VAT per test when taxable.</li>
		</ul>
	</div>
</div>

<h2>Cost price, selling price, profit, and margin</h2>
<p>
	Every pricelist item stores two money fields. Together they show whether the catalogue is commercially healthy.
</p>
<ul>
	<li><strong>Cost price</strong> — what performing the work costs the lab (reagents, subcontract, labour allocation, etc.). Used internally for margin reporting; customers do not see this on the quotation PDF.</li>
	<li><strong>Selling price</strong> — what you charge the customer per package or per test (depending on billing mode). This is what quotations and Process enquiry pull when a pricelist is bound.</li>
	<li><strong>Profit</strong> — <em>Selling price − Cost price</em> for that row (shown on the items table).</li>
	<li><strong>Profit margin</strong> — <em>Profit ÷ Selling price × 100</em> (percentage). A 0% margin means you are breaking even on that row; negative margin means you are quoting below cost.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
	<p>Cost price does not change what the customer pays — it helps commercial teams spot under-priced packages before they go live. Always review profit and margin after import or bulk edits.</p>
</div>

<h2>Internal vs External flags</h2>
<p>
	Each item has three independent switches at the bottom of the Add/Edit Item modal:
</p>
<ul>
	<li><strong>Internal</strong> — marks the row as <em>internal use</em>. Use for internal costing references or items you want to filter as “internal only” on the catalogue. Default: off.</li>
	<li><strong>External</strong> — marks the row as <em>visible externally</em> when you share or publish customer-facing pricelist documents. Turn off to hide a row from external views while keeping it in the catalogue for internal pricing. Default: on.</li>
	<li><strong>Active</strong> — when off, the item is excluded from new quotation lines appended from this list. Default: on for new items.</li>
</ul>
<p>
	Use <strong>More filters</strong> on the items tab to filter by Internal use or External view when auditing a large catalogue.
</p>

<h2>Pending prices vs Apply Price Changes</h2>
<p>
	Saving or editing an item can store a <strong>proposed</strong> selling price. Rows with uncommitted changes are highlighted until you press
	<strong>Apply Price Changes</strong>, which:
</p>
<ul>
	<li>Makes pending selling prices live for quotations and Process enquiry.</li>
	<li>Bumps the pricelist revision / status tag (e.g. from <em>no-changes</em> to <em>has-changes</em>, then back after apply).</li>
</ul>
<p>
	<strong>Active / Inactive</strong> is separate — an inactive item stays in the catalogue but is not appended to new quotes.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>Healthy habit: update the pricelist → <strong>Apply Price Changes</strong> → then price requests or quotations so customers see the intended numbers.</p>
</div>

<h2>Quick checklist</h2>
<ul>
	<li>Create list with correct <strong>billing mode</strong> and <strong>currency</strong>.</li>
	<li>Import and/or add items; tick the right tests for each package.</li>
	<li>Enter <strong>cost</strong> and <strong>selling</strong> prices; check profit margin.</li>
	<li><strong>Apply Price Changes</strong> when prices should go live.</li>
	<li><strong>Assign customers</strong> so the list appears on their quotations.</li>
</ul>

<p>
	Next: <strong>Quote preparation &amp; math</strong> for building quotations from this catalogue (or without it).
</p>
