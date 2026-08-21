{{-- Quotation prep: import lines from Excel or Amspec PDF --}}
<div class="modal fade ls-quotation-workflow-modal" id="ls-quote-import-modal" tabindex="-1" role="dialog" aria-labelledby="ls-quote-import-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<span class="text-muted small text-uppercase">Import</span>
					<h5 class="modal-title mb-0" id="ls-quote-import-title">Import quotation lines</h5>
				</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="small text-muted mb-3">
					Import sample packages from Excel or the Amspec quotation-preparation PDF.
					<strong>Total = No. of samples × Unit price</strong> (once per package).
				</p>

				<div class="mb-3">
					<label class="d-block small font-weight-bold mb-1">File type</label>
					<div class="btn-group btn-group-sm" role="group" aria-label="Import format">
						<button type="button" class="btn btn-outline-secondary ls-quote-import-fmt is-active" data-format="excel">
							<i class="mdi mdi-file-excel"></i> Excel
						</button>
						<button type="button" class="btn btn-outline-secondary ls-quote-import-fmt" data-format="pdf" id="ls-quote-import-fmt-pdf">
							<i class="mdi mdi-file-pdf-box"></i> PDF
						</button>
					</div>
					<p class="small text-muted mt-2 mb-0" id="ls-quote-import-pdf-hint" hidden></p>
				</div>

				<div class="mb-3" id="ls-quote-import-excel-hint">
					<label class="d-block small font-weight-bold mb-1">Excel columns</label>
					<p class="small text-muted mb-0">
						<code>sample_type</code>, <code>parameters</code> (semicolon-separated names),
						<code>quantity_required</code>, <code>quantity</code>, <code>unit_price</code>,
						optional <code>pricing_mode</code> (<code>per_package</code> / <code>per_test</code>).
					</p>
				</div>

				<div class="mb-0">
					<label class="d-block small font-weight-bold mb-1" for="ls-quote-import-file">File <span class="text-danger">*</span></label>
					<input type="file" class="form-control form-control-sm" id="ls-quote-import-file"
						accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
					<div class="small text-danger mt-1" id="ls-quote-import-error" hidden></div>
					<div class="small text-success mt-1" id="ls-quote-import-success" hidden></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-quotation-primary btn-sm" id="ls-quote-import-submit">
					<i class="mdi mdi-upload"></i> Import
				</button>
			</div>
		</div>
	</div>
</div>
