<h2>Pricelist home</h2>
<p>
	Open <strong>Billing → Pricelists</strong>, then open a pricelist. Profile holds description, currency,
	and flags. Customers links who may use this price book. Items holds the catalogue rows.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-show.png',
	'caption' => 'Pricelist details — profile, currency, revision, and customer assignment.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-items.png',
	'caption' => 'Pricelist items — pending vs applied, Apply Price Changes, packages and tests.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-apply.png',
	'caption' => 'Pricelist items toolbar — Apply Price Changes, Import, and Add Item.',
])

<h2>Adding items: per test vs per package</h2>
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-item-modal.png',
	'caption' => 'Add item — sample type, Per test / Per package, cost and selling price, VAT.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-create.png',
	'caption' => 'Create Pricelist — set description, currency, and validity before adding items.',
])

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Per test</h4>
		<ul>
			<li>Tick each test to price.</li>
			<li>Enter cost and selling price per test.</li>
			<li>Tick VAT when that test is taxable (rate from Tax Regime).</li>
			<li>New items start as <strong>Active</strong>; turn off only if you mean to hide them.</li>
		</ul>
	</div>
	<div class="um-compare__card">
		<h4>Per package</h4>
		<ul>
			<li>Tick every test the package covers.</li>
			<li>Set one cost and one selling price for the whole package (price for one sample).</li>
			<li>On a quote: samples × package price — not per test inside.</li>
			<li>Turnaround is usually the longest among the ticked tests.</li>
		</ul>
	</div>
</div>

<h2>Pending prices vs Apply Price Changes</h2>
<p>
	Saving an item stores a proposed selling price. Rows can show as uncommitted until you press
	<strong>Apply Price Changes</strong>, which makes those prices live and bumps the revision.
	That is separate from the <strong>Active / Inactive</strong> badge.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/import-lines.png',
	'caption' => 'Import quotation lines (AmSpec Excel/PDF template) into a preparing quote — related workflow.',
])
@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/pricelist-customers.png',
	'caption' => 'Assign customers to the pricelist so the price book is available for their quotes.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>Healthy habit: update the pricelist → Apply Price Changes → then price requests or quotations so customers see the intended numbers.</p>
</div>
