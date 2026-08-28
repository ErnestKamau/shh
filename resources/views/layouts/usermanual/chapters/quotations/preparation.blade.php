<h2>Who prepares quotations?</h2>
<p>
	<strong>Commercial staff</strong> and <strong>lab coordinators</strong> build quotations under
	<strong>Billing → Quotations</strong> (or via <strong>Process enquiry</strong> on a request).
	This chapter covers line items, billing modes, and using a customer pricelist.
</p>

<h2>Quotation without a pricelist</h2>
<p>
	You do <strong>not</strong> need a pricelist to prepare, approve, or send a quotation.
	When no list is bound, you work in <strong>independent quotation</strong> mode — enter prices yourself.
</p>

<h3>Default: per package (one price for the sample package)</h3>
<ol>
	<li>Open a quotation <strong>In Preparation</strong> (or create one with <strong>+ Add Quotation</strong>).</li>
	<li>Set the header: <strong>Client</strong>, <strong>Client contact</strong>, <strong>Quotation type</strong>, currency, and dates.</li>
	<li>Press <strong>+ Add line</strong> and choose a <strong>Sample type</strong>.</li>
	<li>Leave <strong>Per parameter</strong> <em>unchecked</em> — this is the default package mode.</li>
	<li>Open the <strong>Parameters</strong> picker (Quotation Parameters modal) and tick the tests in the package.</li>
	<li>Enter <strong>Qty</strong>, <strong>Unit price</strong>, and VAT. Help text reads:
		<em>“Independent quotation (package): enter unit price.”</em>
	</li>
	<li>Press <strong>Save lines</strong>.</li>
</ol>

<p>
	<strong>Package math:</strong> line total = <strong>number of samples × unit price (once)</strong> — not × number of tests in the package.
	Tests define scope on the PDF; money is per package.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines.png',
	'caption' => 'Quote In Preparation — default per package line; Per parameter unchecked; Add line and Save lines.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-parameters-modal.png',
	'caption' => 'Quotation Parameters — tick tests for the package. Billing is qty × unit price once, not per test.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines-2.png',
	'caption' => 'After saving parameters — tests listed on the line; still in per package mode (Per parameter off).',
])

<h3>Switch to per parameter (per test billing)</h3>
<p>
	Tick <strong>Per parameter</strong> on the line when each analysis should be billed at its own unit price instead of one package price.
</p>
<ol>
	<li>On an existing line, tick <strong>Per parameter</strong>. The badge changes to e.g. <strong>PER PARAMETER · 2 TESTS</strong>.</li>
	<li>Each selected test can have its own unit price and quantity.</li>
	<li>Line total ≈ sum of (samples × unit price) for each parameter row.</li>
	<li>Unchecking <strong>Per parameter</strong> merges sibling rows back into one package line.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-per-parameter-mode.png',
	'caption' => 'Per parameter mode — each test billed separately; badge shows test count.',
])

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Per package (default)</h4>
		<p>One unit price covers the whole sample package. Total = samples × unit price once. Use when the customer buys a fixed bundle.</p>
	</div>
	<div class="um-compare__card">
		<h4>Per parameter</h4>
		<p>Each analysis has its own unit price. Use when tests are priced individually. Same UI label as “per test” on pricelists.</p>
	</div>
</div>

<h3>Other ways to add lines without a pricelist</h3>
<ul>
	<li><strong>Import</strong> — load AmSpec Excel/PDF into this quote (see <strong>Import AmSpec template</strong> chapter).</li>
	<li><strong>Manual entry</strong> — type unit prices on each line after choosing parameters.</li>
</ul>

<h2>Using a customer pricelist</h2>
<p>
	When the client is assigned to one or more active pricelists, you can append catalogue lines instead of typing prices.
</p>

<h3>Conditions that must be met</h3>
<ul>
	<li>The quotation has a <strong>client</strong> selected.</li>
	<li>That client is <strong>assigned</strong> to at least one <strong>active</strong> pricelist under Billing → Pricelists → Customer Assignment.</li>
	<li>The pricelist has <strong>active items</strong> with applied selling prices (<strong>Apply Price Changes</strong> run after edits).</li>
	<li>Master pricelists only appear when the customer is explicitly assigned — there is no automatic global fallback.</li>
</ul>

<h3>Append lines from a pricelist</h3>
<ol>
	<li>On the preparing quote, open <strong>Commercial</strong> (list icon) — tooltip: <em>Append lines from customer pricelist</em>.</li>
	<li>In the <strong>Customer pricelist</strong> modal, pick an eligible list tile.</li>
	<li>Lines are <strong>appended</strong> — existing rows are kept.</li>
	<li>The quotation <strong>binds</strong> to that list: list mode, code, and line count show in the summary.</li>
	<li>New lines follow the list’s billing mode:
		<ul>
			<li><strong>Package lists</strong> — one quotation line per package (samples × unit price once).</li>
			<li><strong>Per-test lists</strong> — one quotation line per test row from the catalogue.</li>
		</ul>
	</li>
	<li>Use <strong>Detach pricelist (manual pricing)</strong> to unbind and edit prices freely again.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-pricelist-chooser.png',
	'caption' => 'Commercial button — append lines from customer pricelist (hover tooltip on list icon).',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-pricelist-no-eligible.png',
	'caption' => 'No eligible pricelist — assign the customer under Billing → Pricelists first.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>
		“No eligible pricelist” means this client is not linked to any active price book yet.
		Fix it under Billing → Pricelists → Customer Assignment, then return — or continue without a list (above).
	</p>
</div>

<h2>VAT, LOQ, and MU% on the PDF</h2>
<ul>
	<li><strong>VAT</strong> — when ticked on a line, the rate comes from the active tax on Tax Regime Manager.</li>
	<li><strong>Show LOQ column</strong> and <strong>Show MU column</strong> — control whether those columns print on the customer PDF; they do not change the money calculation.</li>
</ul>

<h2>Actions vs Move To workflow</h2>
<p>
	On the preparing quote:
</p>
<ul>
	<li><strong>Actions</strong> — Preview Quote, Process PDF, Commercial (pricelist), Save As Draft, Delete Quotation.</li>
	<li><strong>Move To workflow</strong> — starts approval (see <strong>Approval &amp; notifications</strong> chapter). This is <em>not</em> in the Actions menu.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
	<p>Wrong price? Check: (1) pricelist pending vs applied prices, (2) bound list on the quote, (3) sample quantity, (4) per package vs per parameter on the line — or edit manually if working without a list.</p>
</div>

<p>
	After approval and completion, use <strong>Billing quotation workflow</strong> to create an enquiry from the quote.
</p>
