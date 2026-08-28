<h2>What is an item?</h2>
<p>
	An <strong>item</strong> is one product on your inventory catalogue — the thing you request, quote, order, receive, and issue.
	Items live inside a <strong>category</strong> (for example Chemical Reagents or Consumables).
	Each item has a name, code, unit of measure, stock level, prices, and planning settings.
</p>
<p>
	Open <strong>Inventory → Categories</strong>, then click a category name to see its items.
	Click an item name or the green <strong>eye</strong> icon to open the full <strong>item view page</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/items.png',
	'caption' => 'Items in a category — the main list before you open an item.',
])

<h2>The items table — column by column</h2>
<p>
	When you open a category, you see a table of every item in that group.
	Below is what each column means and why it matters.
</p>

<h3>No</h3>
<p>Row number in the list. Useful when you are talking through a list with a colleague (“check row 3”).</p>

<h3>Actions (icons before the image)</h3>
<ul>
	<li><strong>Red bin (Delete)</strong> — removes the item from the catalogue. Only use when the item was created by mistake and has never been used.</li>
	<li><strong>Green eye (Show)</strong> — opens the full item view page (stock metrics, history, tabs).</li>
</ul>

<h3>Image</h3>
<p>
	A small picture of the product if one was uploaded.
	Helps requesters pick the right line when several items have similar names.
</p>

<h3>Name</h3>
<p>
	The everyday name of the product (for example <em>Hydrochloric Acid 37% ACS</em>).
	Click the name to open the item view page.
	Use clear, unique names so people do not order the wrong chemical or consumable.
</p>

<h3>Code</h3>
<p>
	The system’s own item code (for example <em>IM0002</em>).
	Assigned by Inventory; used on barcodes, reports, and internal references.
</p>

<h3>SAP Code</h3>
<p>
	Your organisation’s code in an external finance or ERP system (the label may say “SAP Code” on your screen).
	Leave blank if you do not use an external code; fill it in when finance needs the link.
</p>

<h3>Stock</h3>
<p>
	How much is on hand right now, shown as a badge (for example <em>0 In Stock</em> or <em>1 In Stock</em>).
</p>
<ul>
	<li><strong>In Stock</strong> — quantity available in store.</li>
	<li><strong>ReOrder</strong> (red link) — stock has fallen to the reorder level. Click it to start a Purchase Request for this item.</li>
</ul>

<h3>Classification</h3>
<p>How the system treats the item when planning and issuing stock:</p>
<ul>
	<li><strong>Stock</strong> — kept in store; quantity is tracked and replenished.</li>
	<li><strong>Non-Stock</strong> — bought when needed; not held as standing stock.</li>
	<li><strong>Service</strong> — a service line rather than a physical product.</li>
	<li><strong>Not-Set</strong> — classification not chosen yet; set it when creating or editing the item.</li>
</ul>

<h3>Maximum Order Quantity</h3>
<p>
	The largest amount someone can request in a single line (for example 100 vials or 960 grams).
	Stops accidental over-ordering on one Purchase Request.
</p>

<h3>UoM (Unit of Measure)</h3>
<p>
	How the item is counted when bought and stored (for example <em>Vials</em>, <em>g</em>, <em>Liters</em>).
	Always check this before entering a quantity on a request.
</p>

<h3>Secondary UoM</h3>
<p>
	<strong>Issuing unit of measure</strong> — how the item is counted when issued to a department, if that differs from how it is bought.
	For example you might buy in boxes but issue in individual units.
</p>

<h3>Cash Price / Credit Price</h3>
<p>
	Reference unit prices used on requests and reports:
</p>
<ul>
	<li><strong>Cash Price</strong> — price when paid immediately.</li>
	<li><strong>Credit Price</strong> — price when bought on account or credit terms.</li>
</ul>

<h3>Description</h3>
<p>
	Short text explaining what the item is for (grade, purity, pack size, or handling notes).
	Shown on the list so requesters can confirm they picked the right product.
</p>

<h2>Adding a new item</h2>
<p>
	On the category page, click <strong>+ Add Item</strong>.
	Fill in the form and <strong>Save</strong>.
	The same fields appear when you edit an item on the item view page.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-add-1.png',
	'caption' => 'Add Item — name, code, description, image, manufacturer, classification, and units.',
])

<p><strong>Name</strong> — what people will search for.<br>
<strong>SAP Code</strong> — external ERP code if you use one.<br>
<strong>Description</strong> — what it is and how it is used.<br>
<strong>Image</strong> — optional photo for the catalogue.<br>
<strong>Manufacturer</strong> — who makes it.<br>
<strong>Maximum Order Quantity</strong> — cap per request line.<br>
<strong>Item Classification</strong> — Stock, Non-Stock, or Service.<br>
<strong>Unit of Measure</strong> — how you buy and store it.<br>
<strong>Issuing Unit of Measure</strong> — how you issue it, if different.</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-add-2.png',
	'caption' => 'Add Item — prices, consumption, and lead times.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-add-3.png',
	'caption' => 'Add Item — lead times and material type.',
])

<p><strong>Cash Price / Credit Price</strong> — reference prices per unit.<br>
<strong>Annual Consumption</strong> — how much you expect to use in a year (helps planning).<br>
<strong>Working Days</strong> — operating days per year used in demand calculations.<br>
<strong>Estimated variation in demand</strong> — safety buffer as a % above average use.<br>
<strong>Internal Lead Time</strong> — days for internal steps (request, approval, processing) before the order goes to the supplier.<br>
<strong>External Lead Time</strong> — days for the supplier to deliver after the order is placed.<br>
<strong>Material Type</strong> — optional grouping (for example hazardous, cold chain). Choose <em>Non Specific</em> if none apply.</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-information-outline"></i></span>
	<p>
		If you pick two different units (buy vs issue) and see a warning about missing conversion,
		ask an administrator to set up the unit conversion before saving.
	</p>
</div>

<h2>Item view page — overview</h2>
<p>
	Open an item from the list to see everything about that product in one place.
	The page has four main areas:
</p>
<ol>
	<li><strong>Title and barcode</strong> at the top</li>
	<li><strong>Edit Inventory Item</strong> form on the left</li>
	<li><strong>Stock metrics</strong> grid on the right (eight boxes)</li>
	<li><strong>Tabs</strong> below for history and related data</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-view.png',
	'caption' => 'Item view — barcode, edit form, stock metrics, and Overview tab.',
])

<h3>Barcode (top of page)</h3>
<p>
	A scannable barcode for the item code.
	Click the <strong>printer</strong> icon next to it to print a label for shelves or containers.
</p>

<h3>Restock Required badge</h3>
<p>
	If stock is below the reorder level, a red <strong>Restock Required</strong> badge appears in the title.
	That is your cue to raise a Purchase Request or check the store.
</p>

<h2>Edit Inventory Item (left panel)</h2>
<p>
	Same fields as <strong>Add Item</strong> — category, name, SAP code, description, image, manufacturer, classification, units, prices, consumption, lead times, and material type.
	Change what you need and click <strong>Save</strong>.
	Moving <strong>Item Category</strong> to another group re-files the item under a different catalogue heading.
</p>

<h2>Stock metrics (eight boxes)</h2>
<p>
	These numbers summarise stock health and ordering. They update from the item settings and movement history.
</p>

<h3>Available</h3>
<p>Quantity physically in store right now, in the item’s unit of measure.</p>

<h3>Daily Demand</h3>
<p>
	Estimated average use per day, calculated from annual consumption and working days.
	Helps predict how fast stock will run down.
</p>

<h3>Total Lead Time</h3>
<p>
	Combined internal + external lead time in <strong>days</strong> — how long from deciding to order until goods typically arrive.
</p>

<h3>Lead Time Consumption</h3>
<p>
	How much stock you are likely to use during that lead time (daily demand × total lead time).
</p>

<h3>Safety Stock</h3>
<p>
	Extra buffer kept to cover unexpected use or late deliveries.
</p>

<h3>Reorder Level</h3>
<p>
	When available stock drops to this level, the item shows <strong>ReOrder</strong> on the list and <strong>Restock Required</strong> on the view page.
</p>

<h3>Standard Order Quantity</h3>
<p>
	Typical amount to order in one go (often aligned with pack size or contract).</p>

<h3>Max Standard Inventory</h3>
<p>
	Upper target — you generally should not hold more than this in store.</p>

<h2>Tabs on the item view page</h2>
<p>
	Use the tabs to see history and linked records.
	Each tab is explained below with its table columns.
</p>

<h3>Overview</h3>
<p>
	<strong>Stock by store and slot</strong> — grey badges at the top (for example <em>Main Store - Shelf 2: 5 Vials</em>).
	Quick answer to “where is this item?”
</p>
<p>
	<strong>Movement table</strong> below lists every stock movement for this item:
</p>
<ul>
	<li><strong>Name</strong> — item (with small image)</li>
	<li><strong>Stock In</strong> — quantity received or returned to store</li>
	<li><strong>Stock Out</strong> — quantity issued or removed</li>
	<li><strong>Department</strong> — which team the movement relates to</li>
	<li><strong>Created By</strong> — who recorded it</li>
	<li><strong>Date</strong> — when it happened</li>
	<li><strong>Status</strong> — current state of that movement line</li>
</ul>

<h3>Brands</h3>
<p>
	Alternative brands or trade names for the same catalogue item (for example different manufacturers of the same reagent grade).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-brands.png',
	'caption' => 'Brands tab — add or remove brand variants for this item.',
])

<ul>
	<li><strong>+ Add</strong> — link a new brand (name and optional image).</li>
	<li>Each brand card can be updated or deleted.</li>
	<li>If none are set, you see <em>No brands configured for this item</em>.</li>
</ul>

<h3>Suppliers</h3>
<p>
	Which vendors can supply this item — used when receiving goods and building RFQs.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-suppliers.png',
	'caption' => 'Suppliers tab — vendors linked to this item.',
])

<ul>
	<li><strong>+ Add Supplier</strong> — link an existing supplier from your directory.</li>
	<li><strong>Name</strong>, <strong>Phone</strong>, <strong>Email</strong> — contact details.</li>
	<li><strong>Delete</strong> — unlink a supplier from this item (does not delete the supplier company).</li>
</ul>

<h3>Received</h3>
<p>
	Every time this item was received into store — full delivery history.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-received.png',
	'caption' => 'Received tab — goods receipt lines for this item.',
])

<ul>
	<li><strong>Batch Code</strong> — lot or batch identifier for traceability.</li>
	<li><strong>Barcode</strong> — barcode on that received batch, if scanned.</li>
	<li><strong>P.O. Number</strong> — purchase order the delivery came from.</li>
	<li><strong>Quantity</strong> — amount received.</li>
	<li><strong>Est Price</strong> — expected value (quantity × catalogue price).</li>
	<li><strong>Actual Price</strong> — price recorded on receipt.</li>
	<li><strong>Supplier</strong> — who delivered.</li>
	<li><strong>Date</strong> — when received.</li>
	<li><strong>Received_by</strong> — store staff who recorded it.</li>
	<li><strong>Expiry</strong> — expiry date if applicable (dash if not tracked).</li>
	<li><strong>Status</strong> — for example accepted or awaiting approval.</li>
	<li><strong>Slot</strong> — where in the store it was put.</li>
	<li><strong>Rating</strong> — rate the supplier for this delivery.</li>
	<li><strong>Documentation</strong> — view attached notes or files.</li>
</ul>

<h3>Transfer</h3>
<p>
	Items <strong>issued out</strong> from store to a department (Material Issuance and similar).
	Titled <em>Items Issued Out</em> on screen.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-transfer.png',
	'caption' => 'Transfer tab — stock issued to departments.',
])

<ul>
	<li><strong>Quantity</strong> — amount issued.</li>
	<li><strong>Department</strong> — team that received it.</li>
	<li><strong>Issued By</strong> — store person who processed the issue.</li>
	<li><strong>Received By</strong> — person who took the goods.</li>
	<li><strong>Date</strong> — when issued.</li>
	<li><strong>Return</strong> action — start returning unused quantity to store from this line.</li>
</ul>

<h3>Return</h3>
<p>
	Stock <strong>returned to store</strong> from a department (unused material brought back).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-return.png',
	'caption' => 'Return tab — items returned to store.',
])

<ul>
	<li><strong>Code</strong> — batch or line code.</li>
	<li><strong>Quantity</strong> — amount returned.</li>
	<li><strong>Returned By</strong> — who brought it back.</li>
	<li><strong>Received By</strong> — store staff who accepted the return.</li>
	<li><strong>Date</strong> — when returned.</li>
	<li><strong>Expiry</strong> — expiry on returned stock if tracked.</li>
	<li><strong>Slot</strong> — where it was placed back in store.</li>
	<li><strong>Notes</strong> — view comments on the return.</li>
</ul>

<h3>Stock Adjustment</h3>
<p>
	Manual corrections after a stock count or when physical stock does not match the system.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-stock-adjustment.png',
	'caption' => 'Stock Adjustment tab — corrections from stock taking.',
])

<ul>
	<li><strong>Make Adjustment</strong> — record a new correction.</li>
	<li><strong>Batch Code</strong> — which batch was adjusted.</li>
	<li><strong>Adjustment</strong> — amount added (green, up arrow) or removed (red, down arrow).</li>
	<li><strong>Stock Taker</strong> — who performed the count or adjustment.</li>
	<li><strong>Date</strong> — when adjusted.</li>
	<li><strong>Slot</strong> — location affected.</li>
	<li><strong>Notes</strong> — reason or comments.</li>
</ul>

<h3>Disposal</h3>
<p>
	Stock written off — expired, damaged, or discarded material.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/item-disposal.png',
	'caption' => 'Disposal tab — stock disposed of and who oversaw it.',
])

<ul>
	<li><strong>Batch Code</strong> — batch disposed.</li>
	<li><strong>Quantity</strong> — amount removed from inventory.</li>
	<li><strong>Overseer</strong> — person who authorised or witnessed disposal.</li>
	<li><strong>Date</strong> — when disposed.</li>
	<li><strong>Notes</strong> — reason (expiry, breakage, and so on).</li>
</ul>

<h2>Before raising a request</h2>
<ul>
	<li>Confirm the item exists and shows the right <strong>unit</strong> and <strong>classification</strong>.</li>
	<li>Check <strong>Overview</strong> or <strong>Stock</strong> on the list to see if store stock is available (Request to Store) or you need to buy (Purchase Request).</li>
	<li>Link <strong>Suppliers</strong> on the item before procurement sends RFQs.</li>
	<li>If the item is missing from the catalogue, use <strong>+ Add Item</strong> or ask an Inventory administrator.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-barcode"></i></span>
	<p>
		Use one catalogue entry per real product.
		Duplicate names make stock counts, reorder alerts, and buying history unreliable.
	</p>
</div>
