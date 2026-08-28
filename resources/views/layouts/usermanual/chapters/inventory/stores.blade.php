<h2>What is a store?</h2>
<p>
	A <strong>store</strong> is a physical place where stock is kept — main store, lab store, or bench-kit storage.
	Each store can have <strong>slots</strong> (shelves, bins, or positions) so you know exactly where an item sits.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/stores.png',
	'caption' => 'Stores — list with Stores and Contents tabs.',
])

<h2>The list page</h2>
<p>Open <strong>Inventory → Store</strong>.</p>
<p>Two tabs sit at the top of the page:</p>

<h3>Stores tab</h3>
<p>Your register of storage areas.</p>
<ul>
	<li><strong>Add</strong> — create a store (name; optionally mark as lab store).</li>
	<li><strong>Edit</strong> / <strong>Delete</strong> — maintain existing stores.</li>
	<li><strong>Show</strong> (green eye) — open store details (slots, contacts, and more).</li>
</ul>
<p>List columns:</p>
<ul>
	<li><strong>Name</strong> — store label used when receiving or issuing.</li>
	<li><strong>Type</strong> — Inventory Store, Lab Store, or Bench-Kit Storage.</li>
	<li><strong>Slots</strong> — how many positions are defined in that store.</li>
	<li><strong>Status</strong> — <em>Operational</em> or frozen for stock taking.</li>
</ul>

<h3>Contents tab</h3>
<p>
	A single view of <strong>everything currently on hand</strong> across all stores.
	Use it to answer “what do we have, and where?” without opening each store one by one.
</p>
<p>Columns include store, slot, category, item code, item name, quantity, unit, and material state.
	Only lines with quantity greater than zero are shown.
</p>

<h2>Store view page</h2>
<p>
	From the <strong>Stores</strong> tab, click <strong>Show</strong> on a row to open <strong>Store Details</strong>.
	Four tabs organise the detail. You can drill down further: <strong>Store → Slots → View slot</strong>.
</p>

<h3>Slots tab</h3>
<p>
	Positions inside this store — for example <em>Cold Storage Shelf C-1</em> or <em>Shelf 2</em>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/stores-view-slots.png',
	'caption' => 'Store Details — Slots tab: add, edit, delete, or open a slot.',
])

<ul>
	<li><strong>Add</strong> — create a new slot.</li>
	<li><strong>Edit</strong> / <strong>Delete</strong> — maintain slot names.</li>
	<li><strong>Show</strong> (green eye) — open <strong>Slot Contents</strong> for that position only.</li>
</ul>

<h3>View slot (Slot Contents)</h3>
<p>
	From the Slots tab, click <strong>Show</strong> on a slot.
	The breadcrumb shows the path: <em>Store → Main Store → [slot name]</em>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/stores-slot-contents.png',
	'caption' => 'Slot Contents — items and quantities in one shelf or position.',
])

<p>The table lists what is physically in that slot:</p>
<ul>
	<li><strong>Category</strong>, <strong>Item Code</strong>, <strong>Item</strong></li>
	<li><strong>Quantity</strong> and <strong>UoM</strong> (unit of measure)</li>
	<li><strong>Material State</strong> — condition or storage type where recorded</li>
</ul>

<h3>Contacts tab</h3>
<p>
	People linked to the store (for example store keeper or supervisor).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/stores-view-contacts.png',
	'caption' => 'Store Details — Contacts tab: people responsible for this store.',
])

<ul>
	<li><strong>Add</strong> — pick a user from the system.</li>
	<li><strong>Delete</strong> (red bin) — remove someone from this store’s contact list.</li>
</ul>

<h3>Contents tab</h3>
<p>
	Everything currently held in <em>this</em> store across all slots — same columns as Slot Contents, plus <strong>Store</strong> and <strong>Slot</strong> so you can see where each line sits.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/stores-view-contents.png',
	'caption' => 'Store Details — Contents tab: all stock in this store.',
])

<h3>Cost Centers tab</h3>
<p>
	Finance codes tied to the store for charging or reporting.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/stores-view-cost-centers.png',
	'caption' => 'Store Details — Cost Centers tab: link finance codes to the store.',
])

<ul>
	<li><strong>Add</strong> — select one or more cost centers.</li>
	<li><strong>Remove</strong> — unlink a cost center from this store.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-package-variant-closed"></i></span>
	<p>
		Define slots before heavy receiving seasons.
		When goods arrive, always pick store and slot so Contents and Slot Contents stay accurate.
	</p>
</div>
