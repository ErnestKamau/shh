<h2>Building the offer</h2>
<p>
	On a quotation in preparation you configure the header (client, contact, type),
	then add or review analysis lines. Each line is a test or a package with a sample count and a unit price.
	You can pull prices from a customer pricelist, enter them manually, or import an AmSpec template.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines.png',
	'caption' => 'Quotation preparation — header on the left; Import, Add line, and Save lines on the right.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines-2.png',
	'caption' => 'Line items — sample type, parameters, quantity required; Show LOQ / MU columns for the PDF.',
])

<h2>Using a pricelist</h2>
<p>
	Open <strong>Commercial</strong> (or the customer pricelist chooser) on the preparing quote.
	Eligible lists appear only when the customer is assigned under <strong>Billing → Pricelists</strong>.
	Selecting a list appends lines — existing rows are kept.
</p>
<ul>
	<li><strong>Package lists</strong> — one quotation line per package (samples × unit price once).</li>
	<li><strong>Per-test lists</strong> — one quotation line per test row from the list.</li>
	<li><strong>Binding</strong> — while a pricelist is bound, new lines follow the list billing mode.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-pricelist-chooser.png',
	'caption' => 'Customer pricelist chooser — list mode, selected list, and binding rules. If you see “No eligible pricelist,” assign that customer on a pricelist first.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>
		“No eligible pricelist” means this client is not linked to any price book yet.
		Fix it under Billing → Pricelists → Customer Assignment, then return here — or continue without a list (below).
	</p>
</div>

<h2>Quotation without a pricelist</h2>
<p>
	When there is no eligible list — or you prefer not to bind one — work manually:
</p>
<ol>
	<li>Press <strong>+ Add line</strong> and choose sample type and parameters.</li>
	<li>Enter unit price, quantity, and VAT as needed.</li>
	<li>Or use <strong>Import</strong> to load an AmSpec Excel/PDF into this quote (see the Import chapter).</li>
</ol>
<p>
	You do not need a pricelist to prepare, approve, or send a quotation.
</p>

<h2>Per package (default) vs per test</h2>
<div class="um-compare">
	<div class="um-compare__card">
		<h4>Per package (default)</h4>
		<p>
			One unit price covers the whole sample package.
			<strong>Total = number of samples × unit price (once)</strong> — not × number of tests inside the package.
			Tests are listed for scope.
		</p>
	</div>
	<div class="um-compare__card">
		<h4>Per test</h4>
		<p>
			Each analysis is billed at its own unit price.
			Use this when tests are priced individually instead of as one package.
		</p>
	</div>
</div>

<p>
	<strong>The simple math:</strong> line total ≈ number of samples × unit price (plus tax when VAT applies).
	The quotation total is the sum of the lines (as your PDF template shows).
</p>

<h2>VAT, LOQ, and MU% on the PDF</h2>
<p>
	When VAT is ticked, the <strong>rate comes from the active tax</strong> on Tax Regime Manager.
	<strong>Show LOQ column</strong> and <strong>Show MU column</strong> only control whether those columns print on the customer PDF —
	they do not change the money calculation.
</p>

<h2>Actions vs Move To workflow</h2>
<p>
	On the preparing quote, <strong>Actions</strong> covers day-to-day tools.
	<strong>Move To workflow</strong> starts the approval path (see the Approval chapter).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-actions.png',
	'caption' => 'Actions — Preview Quote, Process PDF, Commercial, Save As Draft, Delete Quotation. Use Move To workflow (not this menu) to send for approval.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
	<p>Wrong price? Check the pricelist (pending vs applied), then the quote’s bound list, then sample quantity — or edit the line manually if you are working without a list.</p>
</div>
