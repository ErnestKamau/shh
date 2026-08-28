<h2>Welcome to Inventory</h2>
<p>
	<strong>Inventory</strong> is where your organisation keeps track of stock:
	what you have, where it is kept, who needs it, and how it is bought and received.
	It supports day-to-day work such as raising requests, getting approvals, ordering from suppliers,
	receiving goods into store, and issuing items to departments.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/overview.png',
	'caption' => 'Inventory dashboard — summary cards, activity chart, and sidebar navigation.',
])

<p>
	From the dashboard you can see counts for <strong>categories</strong>, <strong>suppliers</strong>, and <strong>departments</strong>,
	<strong>restock notifications</strong>, and recent <strong>stock in / stock out</strong> activity.
	Use the sidebar to reach master data (Organizational Structure, Departments, Store, Categories, Suppliers)
	or day-to-day workflows (Request to Order, Request to Store, Approval Requests).
</p>

<h2>How this guide is organised</h2>

<h3>1. Master data (set up once, keep updated)</h3>
<p>
	Before buying or issuing stock, your organisation needs a clear picture of
	<strong>where</strong> things live, <strong>who</strong> requests them, and <strong>what</strong> can be bought.
</p>
<ol>
	<li><strong>Locations</strong> — sites and organisational structure</li>
	<li><strong>Organisations (departments)</strong> — teams that request and own stock</li>
	<li><strong>Stores</strong> — physical places where items are kept</li>
	<li><strong>Suppliers &amp; categories</strong> — vendors and how items are grouped</li>
	<li><strong>Items</strong> — the products on the catalogue</li>
</ol>
<p>
	Administrators also use <strong>Configurations</strong> in the sidebar (material types, currencies, and conversions)
	and <strong>Unit of Measure</strong> so lists and calculations work correctly.
</p>

<h3>2. Request to order (day-to-day buying)</h3>
<p>
	When something must be <strong>bought from a supplier</strong>, you work through:
	Purchase Request → Request for Quotation → Purchase Order → Goods Receipt.
	Each chapter in that section walks you through what you see on screen and what to do next.
</p>

<h3>3. Request to Store</h3>
<p>
	When stock is <strong>already in the store</strong> and a department needs it issued,
	you use Request to Store (and related store steps). That chapter will grow as the process is documented further.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-page-variant"></i></span>
	<p>
		Start with master data if you are setting Inventory up for the first time.
		If you already have locations, stores, and items, jump to <strong>Request to order</strong>.
	</p>
</div>
