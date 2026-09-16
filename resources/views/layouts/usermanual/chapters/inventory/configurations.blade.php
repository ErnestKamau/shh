<h2>Configurations (sidebar)</h2>
<p>
	<strong>Configurations</strong> is a section in the Inventory sidebar.
	It holds lookup lists and conversion rules that other parts of Inventory use —
	material types, currencies, and how to convert between them.
</p>
<p>
	Most day-to-day users only <em>pick</em> from these lists (for example choosing a <strong>Material Type</strong> when adding an item).
	Store or admin staff maintain the lists here when your organisation’s setup changes.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/configurations-sidebar.png',
	'caption' => 'Inventory sidebar — Configurations menu expanded.',
])

<p>Open the menu by clicking <strong>Configurations</strong> in the sidebar. It contains:</p>
<ol>
	<li><strong>Material Type</strong></li>
	<li><strong>Currency</strong></li>
	<li><strong>Currency Conversion</strong></li>
	<li><strong>UoM Conversion</strong></li>
</ol>

<p>
	<strong>Unit of Measure</strong> sits just above Configurations in the sidebar as its own menu item.
	Use it to define the units your catalogue can use (grams, litres, vials, and so on).
	UoM Conversion then links two of those units with a rate.
</p>

<h2>Material Type</h2>
<p>
	Material types group items by the kind of material they are — for example chemicals, consumables, or precious metals.
	When you add or edit an item, you can assign a <strong>Material Type</strong> from this list.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/config-material-type.png',
	'caption' => 'Material Type — list of types for this location.',
])

<h3>List columns</h3>
<ul>
	<li><strong>No</strong> — row number.</li>
	<li><strong>Name</strong> — label shown on item forms (for example <em>Gold</em>).</li>
	<li><strong>Description</strong> — optional notes about when to use this type.</li>
	<li><strong>Edit</strong> (pencil) — change name or description.</li>
	<li><strong>Show</strong> (green eye) — open the material type detail page (conversions and states for that type).</li>
</ul>

<h3>Adding or editing</h3>
<ul>
	<li><strong>+ Add</strong> — create a new material type (name and description).</li>
	<li><strong>Edit</strong> on a row — update an existing type.</li>
</ul>

<h3>Material type view page</h3>
<p>
	Click <strong>Show</strong> on a material type to open its detail page.
	The left side lets you edit <strong>Name</strong> and <strong>Description</strong>.
	On the right, two tabs appear:
</p>
<ul>
	<li><strong>Conversions</strong> — unit conversions specific to this material type.</li>
	<li><strong>States</strong> — material states (for example solid, liquid) used when handling stock of this type.</li>
</ul>

<h2>Currency</h2>
<p>
	Currencies you use when buying and reporting — for example EUR and USD.
	Suppliers, purchase orders, and RFQs can reference these codes.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/config-currency.png',
	'caption' => 'Currency — currencies available in Inventory.',
])

<h3>List columns</h3>
<ul>
	<li><strong>No</strong> — row number.</li>
	<li><strong>Name</strong> — currency code or label (for example EUR, USD).</li>
	<li><strong>Description</strong> — optional longer text (often the same as the name).</li>
	<li><strong>Edit</strong> (pencil) — change the currency entry.</li>
</ul>

<h3>Adding or editing</h3>
<ul>
	<li><strong>+ Add</strong> — register a new currency.</li>
	<li><strong>Edit</strong> — update name or description.</li>
</ul>
<p>
	Currency is shared across locations, so the same list appears wherever you work in Inventory.
</p>

<h2>Currency Conversion</h2>
<p>
	Exchange rates between two currencies at your location.
	Used when amounts need to be shown or calculated in another currency (for example converting EUR quotes to USD).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/config-currency-conversion.png',
	'caption' => 'Currency Conversion — rates between currency pairs.',
])

<h3>List columns</h3>
<ul>
	<li><strong>No</strong> — row number.</li>
	<li><strong>Currency 1</strong> — the “from” currency.</li>
	<li><strong>Currency 2</strong> — the “to” currency.</li>
	<li><strong>Conversion Rate</strong> — how many units of Currency 2 equal one unit of Currency 1 (for example 1 EUR = 14 USD would be rate 14).</li>
	<li><strong>Edit</strong> (pencil) — change the rate or currencies.</li>
</ul>

<h3>Adding a conversion</h3>
<p>Click <strong>+ Add</strong>, then fill in the form:</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/config-currency-conversion-add.png',
	'caption' => 'Add Currency Conversion — pick two currencies and enter the rate.',
])

<ul>
	<li><strong>Currency 1</strong> — select from your currency list.</li>
	<li><strong>Currency 2</strong> — select the other currency.</li>
	<li><strong>Conversion Rate</strong> — enter the numeric rate, then <strong>Save</strong>.</li>
</ul>

<h2>UoM Conversion</h2>
<p>
	<strong>Unit of measure conversion</strong> — how to convert between two units when you buy in one unit and issue in another.
	For example <em>1 kg = 1000 g</em> appears as UoM 1: <strong>g</strong>, UoM 2: <strong>kg</strong>, rate <strong>1000</strong>.
</p>
<p>
	If an item uses two different units and no conversion exists, the system warns you when saving the item.
	Add the conversion here first, then save the item again.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/config-uom-conversion.png',
	'caption' => 'UoM Conversion — rates between units of measure.',
])

<h3>List columns</h3>
<ul>
	<li><strong>No</strong> — row number.</li>
	<li><strong>UoM 1</strong> — first unit (for example g).</li>
	<li><strong>UoM 2</strong> — second unit (for example kg).</li>
	<li><strong>Conversion Rate</strong> — how many of UoM 1 equal one of UoM 2 (example above: 1000).</li>
	<li><strong>Edit</strong> (pencil) — update the conversion.</li>
</ul>

<h3>Adding a conversion</h3>
<p>Click <strong>+ Add</strong>, then:</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/config-uom-conversion-add.png',
	'caption' => 'Add UoM Conversion — pick two units and enter the rate.',
])

<ul>
	<li><strong>UoM 1</strong> — select the first unit from your unit list.</li>
	<li><strong>UoM 2</strong> — select the second unit.</li>
	<li><strong>Conversion Rate</strong> — enter the number, then <strong>Save</strong>.</li>
</ul>

<h2>Approval Configuration (separate from this menu)</h2>
<p>
	Approver lists for Purchase Request, RFQ, Purchase Order, and Request to Store are <strong>not</strong> under Configurations in the sidebar.
	They live on each workflow screen under the <strong>Approval Configuration</strong> tab
	(see <a href="{{ route('usermanual.show', ['manual' => 'inventory', 'chapter' => 'request-to-store']) }}">Request to Store</a> for the store issuance approvers).
	Update those when approvers change role or leave.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-cog-outline"></i></span>
	<p>
		Set up <strong>Currency</strong> and <strong>Unit of Measure</strong> before heavy buying season.
		Add <strong>UoM Conversion</strong> and <strong>Currency Conversion</strong> before items or suppliers need them.
	</p>
</div>
