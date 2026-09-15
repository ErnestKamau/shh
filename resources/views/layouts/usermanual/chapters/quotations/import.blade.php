<h2>Two imports, one AmSpec template family</h2>
<p>
	AmSpec import uses the same quotation-preparation template style in two places.
	They look similar but land in different destinations.
</p>

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Quotation import</h4>
		<ul>
			<li>Fills <strong>one preparing quote</strong> with sample packages / lines.</li>
			<li>Open from quotation prep → <strong>Import</strong>.</li>
			<li>After: review lines on that quote; then approval / send.</li>
			<li>Does <strong>not</strong> update the lasting price book.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>Pricelist import</h4>
		<ul>
			<li>Fills the <strong>price book catalogue</strong> for future quotes.</li>
			<li>Open from Billing → Pricelist → <strong>Import</strong>.</li>
			<li>After: review items, then <strong>Apply Price Changes</strong>.</li>
			<li>Does <strong>not</strong> create or complete a quotation by itself.</li>
		</ul>
	</div>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-database-import-outline"></i></span>
	<p>System Bulk Import can also load many pricelists from a spreadsheet for admins — that is separate from the AmSpec Import button on a single list or quote.</p>
</div>

<h2>Shared AmSpec quotation-preparation template</h2>
<p>
	Both modals point at the <strong>Amspec quotation-preparation template</strong> (Excel and PDF downloads).
	Layout columns: Sample / Test / Method / Quantity Required / TAT / No. of Samples / Unit Price / Total.
	Excel templates use one <strong>Test</strong> (+ <strong>Method</strong>) per row under a shared Sample package block.
</p>
<ul>
	<li><strong>Package math (default):</strong> Package total = <strong>No. of samples × Unit price (once)</strong> — not × number of tests.</li>
	<li><strong>Naming:</strong> Sample type and parameter names must match LIMS. Skipped rows appear as warnings.</li>
	<li>Download the Excel or PDF template from the import modal before you fill and upload.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/import-quote-modal.png',
	'caption' => 'Import quotation lines — template guide, Excel/PDF, Per package (default) vs Per test, file upload.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-import-modal.png',
	'caption' => 'Import into pricelist — same template family; Excel uses Sample / Test / Method with package prices on the first row.',
])

<h2>Quotation import — step by step</h2>
<ol>
	<li>Open a quotation that is <strong>In Preparation</strong>.</li>
	<li>Press <strong>Import</strong> to open <strong>Import quotation lines</strong>.</li>
	<li>Choose <strong>File type</strong>: Excel or PDF.
		<ul>
			<li>Excel columns: <code>Sample</code>, <code>Test</code>, <code>Method</code>, <code>quantity_required</code>, <code>quantity</code>, <code>unit_price</code> — one Test row per line under a shared Sample package; Method picks the existing LIMS analysis element.</li>
			<li>PDF: upload the filled Amspec quotation-preparation PDF; packages and prices are read from the layout.</li>
		</ul>
	</li>
	<li>Choose <strong>Pricing mode</strong> (default <strong>Per package</strong>):
		<ul>
			<li><strong>Per package</strong> — one unit price covers the whole sample package; total = samples × unit price once.</li>
			<li><strong>Per test</strong> — each analysis billed at its own unit price.</li>
		</ul>
	</li>
	<li>Upload the file and press <strong>Import</strong>.</li>
	<li>Review created lines. Fix warnings (unknown sample type, no matching parameters) by aligning names in LIMS or the file, then re-import or edit lines.</li>
	<li>Check customer, currency, quantities, and VAT; then continue preparation or <strong>Move To workflow</strong>.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-file-upload-outline"></i></span>
	<p>Wrong pricing mode mis-maps package vs test rows. Import is a head start — always review before approval.</p>
</div>

<h2>Pricelist import — step by step</h2>
<ol>
	<li>Open <strong>Billing → Pricelists</strong>, then open a list (or start Import from the list when offered).</li>
	<li>Press <strong>Import</strong> to open <strong>Import into pricelist</strong>.</li>
	<li>Choose <strong>File type</strong>: Excel or PDF.
		<ul>
			<li>Excel columns: <code>Sample</code>, <code>Test</code>, <code>Method</code>, <code>cost_price</code>, <code>selling_price</code>, and optional <code>tax</code> — one Test row per line under a shared Sample package.</li>
			<li>PDF: package rows are imported from the Amspec quotation-preparation PDF.</li>
		</ul>
	</li>
	<li>Choose <strong>Pricing mode</strong> (default <strong>Per package</strong>) to match how the file was priced.</li>
	<li>Upload and press <strong>Import</strong>. Note created / updated counts and any warnings.</li>
	<li>Open the <strong>Pricelist Items</strong> tab, review pending vs applied rows, then press <strong>Apply Price Changes</strong> so quotations and Process enquiry see live selling prices.</li>
	<li>Assign customers so the list becomes eligible on their quotes.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-items.png',
	'caption' => 'After pricelist import — review items, then Apply Price Changes so prices become live.',
])

<h2>Quick checklist</h2>
<ul>
	<li>Same template family; different destination (quote lines vs price book).</li>
	<li>Both default to <strong>per package</strong>.</li>
	<li>Both require LIMS-matching sample types and parameters.</li>
	<li>Only pricelist import needs <strong>Apply Price Changes</strong> afterward.</li>
	<li>Only quotation import puts money on <strong>this</strong> customer offer immediately.</li>
</ul>
