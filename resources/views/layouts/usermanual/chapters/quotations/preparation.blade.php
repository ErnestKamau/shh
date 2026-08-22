<h2>Building the offer</h2>
<p>
	On a quotation in preparation you choose the customer’s pricelist (when needed),
	then add or review analysis lines. Each line is a test or a package with a sample count and a unit price.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines.png',
	'caption' => 'Quotation preparation — header and line area.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines-2.png',
	'caption' => 'Lines with unit prices, sample counts, and totals.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines-3.png',
	'caption' => 'Continue editing until the offer matches the job.',
])

<h2>The simple math</h2>
<p>
	<strong>Line total ≈ number of samples × unit price</strong> (plus tax when VAT applies on that line).
	The quotation total is the sum of the lines (as your PDF template shows).
</p>

<h3>Where the unit price comes from</h3>
<ol>
	<li>Select a pricelist allowed for the customer.</li>
	<li>The system suggests the selling price from that catalogue.</li>
	<li>Adjust only where your policy allows.</li>
</ol>

<h2>Per test vs per package</h2>
<div class="um-compare">
	<div class="um-compare__card">
		<h4>Per test</h4>
		<p>Each test has its own price. On the quote, each selected test is usually its own line: samples × that test’s unit price.</p>
	</div>
	<div class="um-compare__card">
		<h4>Per package</h4>
		<p>One package price covers a set of tests for a sample. Bill <strong>samples × package price</strong> — not once per test inside the package.</p>
	</div>
</div>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-params.png',
	'caption' => 'Parameter / test picker — tick what belongs on the quote or package.',
])

<h2>VAT, LOQ, and MU% on the PDF</h2>
<p>
	When VAT is ticked, the <strong>rate comes from the active tax</strong> on Tax Regime Manager.
	<strong>LOQ</strong> and <strong>MU%</strong> toggles only control whether those columns print on the customer PDF — they do not change the money calculation.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-pdf.png',
	'caption' => 'Customer PDF layout — lines and totals as the customer sees them.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
	<p>Wrong price? Check the pricelist (pending vs applied), then the quote’s pricelist, then sample quantity.</p>
</div>
