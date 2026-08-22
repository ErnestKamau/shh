<h2>Bring lines in from an AmSpec template</h2>
<p>
	When you already have pricing laid out in the AmSpec quotation template (Excel or PDF),
	you can import it into a quotation that is still in preparation instead of retyping every line.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/import-lines.png',
	'caption' => 'Import into a preparing quotation — choose format and pricing mode.',
])

<h3>Typical steps</h3>
<ol>
	<li>Open the quotation in preparation (or start import from Quotation Overview where offered).</li>
	<li>Choose <strong>Excel</strong> or <strong>PDF</strong> to match your file.</li>
	<li>Choose <strong>per test</strong> or <strong>per package</strong> so the importer knows how to map rows.</li>
	<li>Upload the AmSpec template file and confirm.</li>
	<li>Review the lines on screen — fix anything that did not map cleanly — then save.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-file-upload-outline"></i></span>
	<p>Import is a head start, not a substitute for review. Always check customer, currency, quantities, and VAT after import.</p>
</div>

<h2>Pricelist import (related)</h2>
<p>
	Separately, on a <strong>pricelist</strong> you can import catalogue prices (including AmSpec-oriented files)
	so the price book itself stays current. That feeds future quotes; it does not replace importing into one preparing quote.
	See the Pricelist chapter.
</p>
