@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Batch Report | CRM</title>
<style>
	.card {
		border-radius: 12px;
		transition: all 0.3s ease;
	}
	
	.card:hover {
		transform: translateY(-2px);
		box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
	}
	
	.card-header {
		border-radius: 12px 12px 0 0 !important;
		border-bottom: 1px solid rgba(0,0,0,0.05);
	}
	
	.btn {
		border-radius: 8px;
		font-weight: 500;
		transition: all 0.3s ease;
	}
	
	.btn:hover {
		transform: translateY(-1px);
	}
	
	.form-control {
		border-radius: 8px;
		border: 1px solid rgba(0,0,0,0.1);
		transition: all 0.3s ease;
	}
	
	.form-control:focus {
		border-color: #0d6efd;
		box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
	}
	
	.badge {
		border-radius: 6px;
		font-weight: 500;
	}
	
	.table th {
		border-top: none;
		font-weight: 600;
		color: #495057;
		background-color: #f8f9fa;
		white-space: nowrap;
	}
	
	.table td {
		vertical-align: middle;
	}
	
	.table tbody tr:hover {
		background-color: rgba(0,0,0,0.02);
	}
	
	.btn-group .btn {
		border-radius: 6px;
		margin-right: 2px;
	}
	
	.material-icons {
		vertical-align: middle;
	}
	

	
	.pagination .page-link {
		border-radius: 6px;
		margin: 0 2px;
	}
	
	.pagination .page-item.disabled .page-link {
		opacity: 0.5;
		cursor: not-allowed;
	}
	
	.form-select-sm {
		padding: 0.25rem 0.5rem;
		font-size: 0.875rem;
	}
	
	/* Responsive table */
	@media (max-width: 768px) {
		.table-responsive {
			border: 0;
		}
		
		.table-responsive .table {
			font-size: 0.875rem;
		}
		
		.btn-sm {
			padding: 0.25rem 0.5rem;
			font-size: 0.75rem;
		}
		
		.table th, .table td {
			padding: 0.5rem 0.25rem;
		}
		
		.badge {
			font-size: 0.75rem;
		}
		
		.card-body {
			padding: 1rem;
		}
		
		.d-flex.justify-content-between {
			flex-direction: column;
			gap: 1rem;
		}
		
		.d-flex.justify-content-between > div {
			text-align: center;
		}
	}
	
	@media (max-width: 576px) {
		.container-fluid {
			padding: 1rem 0.5rem;
		}
		
		.card {
			margin-bottom: 1rem;
		}
		
		.table-responsive {
			margin: 0 -0.5rem;
		}
		
		.btn {
			font-size: 0.875rem;
			padding: 0.375rem 0.75rem;
		}
		
		.form-label {
			font-size: 0.875rem;
		}
	}
	
	/* Enhanced table styling */
	.table {
		border-collapse: separate;
		border-spacing: 0;
	}
	
	.table th:first-child {
		border-top-left-radius: 8px;
	}
	
	.table th:last-child {
		border-top-right-radius: 8px;
	}
	
	.table tbody tr:last-child td:first-child {
		border-bottom-left-radius: 8px;
	}
	
	.table tbody tr:last-child td:last-child {
		border-bottom-right-radius: 8px;
	}
	
	/* Loading animation */
	.spinner-border {
		width: 3rem;
		height: 3rem;
	}
	
	/* Export buttons styling */
	.btn-success, .btn-info, .btn-danger {
		box-shadow: 0 2px 4px rgba(0,0,0,0.1);
	}
	
	.btn-success:hover, .btn-info:hover, .btn-danger:hover {
		box-shadow: 0 4px 8px rgba(0,0,0,0.15);
	}
	
	/* Pagination enhancements */
	.pagination .page-link {
		border: 1px solid #dee2e6;
		color: #0d6efd;
	}
	
	.pagination .page-link:hover {
		background-color: #e9ecef;
		border-color: #dee2e6;
		color: #0a58ca;
	}
	
	.pagination .page-item.active .page-link {
		background-color: #0d6efd;
		border-color: #0d6efd;
	}
	
	/* Filter section improvements */
	.form-label {
		font-weight: 500;
		color: #495057;
	}
	
	.form-control:focus {
		border-color: #0d6efd;
		box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
	}
	

</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content2')
<main>
	<?php
	$items = array(
		array(
			'link' => route('customers-list'),
			'name' => 'CRM',
			'icon' => null
		),
		array(
			'link' => route('customers-list'),
			'name' => 'Batch Report',
			'icon' => null
		)
	);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	
	<div class="container-fluid p-4">
		<!-- Page Header -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card shadow-sm border-0">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<i class="mdi mdi-file-chart-outline text-primary me-4" style="font-size: 2.5rem;"></i>
							<div class="ml-3">
								<h2 class="mb-1 text-dark">Batch Report</h2>
								<p class="text-muted mb-0">Generate comprehensive batch reports with advanced filtering (Completed batches only)</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Filters Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card shadow-sm border-0">
					<div class="card-header bg-light border-0">
						<div class="d-flex align-items-center">
							<i class="mdi mdi-filter-variant text-primary me-2" style="font-size: 1.5rem;"></i>
							<h5 class="mb-0 text-dark ml-3">Filters</h5>
						</div>
					</div>
					<div class="card-body">
						<form id="batchReportForm">
							@csrf
							<div class="row g-3">
								<div class="col-md-3">
									<label for="date_from" class="form-label text-muted">
										Date From (Receipt Date)
									</label>
									<input type="date" class="form-control" id="date_from" name="date_from">
								</div>
								<div class="col-md-3">
									<label for="date_to" class="form-label text-muted">
										Date To (Receipt Date)
									</label>
									<input type="date" class="form-control" id="date_to" name="date_to">
								</div>
								<div class="col-md-3">
									<label for="customer_filter" class="form-label text-muted">
										Customer
									</label>
									<select class="form-control select2" id="customer_filter" name="customer_filter[]" multiple>
										@foreach($customers as $customer)
											<option value="{{ $customer->id }}">{{ $customer->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-3">
									<label for="sample_type_filter" class="form-label text-muted">
										Sample Type
									</label>
									<select class="form-control select2" id="sample_type_filter" name="sample_type_filter[]" multiple>
										@foreach($sample_types as $sample_type)
											<option value="{{ $sample_type->id }}">{{ $sample_type->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-3">
									<label for="company_unit_filter" class="form-label text-muted">
										Company Unit
									</label>
									<select class="form-control select2" id="company_unit_filter" name="company_unit_filter[]" multiple disabled>
										<!-- Options will be populated dynamically based on selected customers -->
									</select>
								</div>
								<div class="col-md-3">
									<label for="sample_point_filter" class="form-label text-muted">
										Sampling Location
									</label>
									<select class="form-control select2" id="sample_point_filter" name="sample_point_filter[]" multiple disabled>
										<!-- Options will be populated dynamically based on selected company units -->
									</select>
								</div>
								<div class="col-md-6">
									<label for="sample_number_filter" class="form-label text-muted">
										Sample Number
									</label>
									<input type="text" class="form-control" id="sample_number_filter" name="sample_number_filter" placeholder="Enter sample number or batch code">
								</div>
								<div class="col-md-6 mt-3 d-flex align-items-end">
									<button type="submit" class="btn btn-primary mr-3">
										Apply Filters
									</button>
									<button type="button" class="btn btn-outline-secondary" id="clearFilters">
										Clear
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<!-- Results Section -->
		<div class="row">
			<div class="col-12">
				<div class="card shadow-sm border-0">
					<div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
						<div class="d-flex align-items-center">
							<i class="mdi mdi-table-chart text-primary me-2" style="font-size: 1.5rem;"></i>
							<h5 class="mb-0 text-dark ml-3">Results</h5>
						</div>
						<div class="d-flex gap-2">
							<button type="button" class="btn btn-sm btn-outline-success" id="exportExcel" style="display: none;">
								<i class="mdi mdi-download me-1"></i>
								Export to Excel
							</button>
							<button type="button" class="btn btn-sm btn-outline-info" id="exportCsv" style="display: none;">
								<i class="mdi mdi-file-document-outline me-1"></i>
								Export to CSV
							</button>
							{{-- <button type="button" class="btn btn-outline-danger" id="exportPdf" style="display: none;">
								<i class="mdi mdi-file-pdf-outline me-1"></i>
								Export to PDF
							</button> --}}
						</div>
					</div>
					<div class="card-body">
						<div id="loadingSpinner" class="text-center py-5" style="display: none;">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
							<p class="text-muted mt-2">Loading results...</p>
						</div>
						
						<div id="resultsTable" style="display: none;">
							<!-- Pagination Controls -->
							<div class="d-flex justify-content-between align-items-center mb-3">
								<div class="d-flex align-items-center">
									<label for="perPage" class="form-label me-2 mb-0">Show:</label>
									<select class="form-select form-select-sm" id="perPage" style="width: auto;">
										<option value="10">10</option>
										<option value="25" selected>25</option>
										<option value="50">50</option>
										<option value="100">100</option>
									</select>
								</div>
								<div class="text-muted">
									Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalResults">0</span> results
								</div>
							</div>

							<div class="table-responsive">
								<table class="table table-hover" id="batchReportTable">
									<thead class="table-light">
										<tr>
											<th>Report Number</th>
											<th>COA Report</th>
											<th>Lab No</th>
											<th>Sample Type</th>
											<th>Customer</th>
											<th>Company Unit</th>
											<th>Sampling Location</th>
											<th>Receipt Date</th>
											<th>Date Collected</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody id="tableBody">
									</tbody>
								</table>
							</div>

							<!-- Pagination -->
							<div class="d-flex justify-content-between align-items-center mt-3">
								<div class="text-muted">
									Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
								</div>
								<nav aria-label="Batch report pagination">
									<ul class="pagination pagination-sm mb-0">
										<li class="page-item" id="prevPage">
											<a class="page-link" href="#" aria-label="Previous">
												<i class="mdi mdi-chevron-left"></i>
											</a>
										</li>
										<li class="page-item" id="nextPage">
											<a class="page-link" href="#" aria-label="Next">
												<i class="mdi mdi-chevron-right"></i>
											</a>
										</li>
									</ul>
								</nav>
							</div>
						</div>
						
						<div id="noResults" class="text-center py-5" style="display: none;">
							<i class="mdi mdi-cube-off-outline text-muted" style="font-size: 4rem;"></i>
							<h5 class="text-muted mt-3">No results found</h5>
							<p class="text-muted">Try adjusting your filters to see more results.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection

@section('script2')
<script>
$(document).ready(function() {
	// Initialize Select2
	$('.select2').select2({
		placeholder: 'Select options',
		allowClear: true
	});

	// Function to refresh CSRF token
	function refreshCsrfToken() {
		$.get('{{ route("home") }}').done(function() {
			// Update the meta tag and form token
			const newToken = $('meta[name="csrf-token"]').attr('content');
			$('input[name="_token"]').val(newToken);
		});
	}

	// Set default dates (last 30 days)
	const today = new Date();
	const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
	$('#date_from').val(thirtyDaysAgo.toISOString().split('T')[0]);
	$('#date_to').val(today.toISOString().split('T')[0]);

	// Pagination variables
	let currentPage = 1;
	let perPage = 25;
	let totalResults = 0;
	let allData = [];

	// Store original data for cascading dropdowns
	const allCompanyUnits = @json($company_units);
	const allSamplePoints = @json($sample_points);

	// Customer change handler for cascading dropdowns
	$('#customer_filter').on('change', function() {
		const selectedCustomers = $(this).val() || [];
		const companyUnitSelect = $('#company_unit_filter');
		const samplePointSelect = $('#sample_point_filter');
		
		// Clear and disable dependent dropdowns
		companyUnitSelect.empty().prop('disabled', selectedCustomers.length === 0).trigger('change');
		samplePointSelect.empty().prop('disabled', true).trigger('change');
		
		if (selectedCustomers.length > 0) {
			// Filter company units based on selected customers
			const filteredUnits = allCompanyUnits.filter(unit => 
				selectedCustomers.includes(unit.crm_customer_id.toString())
			);
			
			// Populate company units dropdown
			filteredUnits.forEach(unit => {
				companyUnitSelect.append(new Option(unit.name, unit.id));
			});
			
			companyUnitSelect.prop('disabled', false);
		}
	});

	// Company unit change handler for sample points
	$('#company_unit_filter').on('change', function() {
		const selectedUnits = $(this).val() || [];
		const samplePointSelect = $('#sample_point_filter');
		
		// Clear sample points dropdown
		samplePointSelect.empty().prop('disabled', selectedUnits.length === 0).trigger('change');
		
		if (selectedUnits.length > 0) {
			// Filter sample points based on selected company units
			const filteredPoints = allSamplePoints.filter(point => 
				selectedUnits.includes(point.crm_company_unit_id.toString())
			);
			
			// Populate sample points dropdown
			filteredPoints.forEach(point => {
				const label = point.display_name || point.name || ('#' + point.id);
				samplePointSelect.append(new Option(label, point.id));
			});
			
			samplePointSelect.prop('disabled', false);
		}
	});

	// Form submission
	$('#batchReportForm').on('submit', function(e) {
		e.preventDefault();
		currentPage = 1;
		loadBatchReport();
	});

	// Clear filters
	$('#clearFilters').on('click', function() {
		$('#batchReportForm')[0].reset();
		$('.select2').val(null).trigger('change');
		$('#date_from').val(thirtyDaysAgo.toISOString().split('T')[0]);
		$('#date_to').val(today.toISOString().split('T')[0]);
		
		// Reset cascading dropdowns
		$('#company_unit_filter').empty().prop('disabled', true);
		$('#sample_point_filter').empty().prop('disabled', true);
		
		$('#resultsTable').hide();
		$('#noResults').hide();
		$('#exportExcel').hide();
		$('#exportCsv').hide();
		$('#exportPdf').hide();
	});

	// Per page change
	$('#perPage').on('change', function() {
		perPage = parseInt($(this).val());
		currentPage = 1;
		displayResults(allData);
	});

	// Pagination navigation
	$('#prevPage').on('click', function(e) {
		e.preventDefault();
		if (currentPage > 1) {
			currentPage--;
			displayResults(allData);
		}
	});

	$('#nextPage').on('click', function(e) {
		e.preventDefault();
		if (currentPage < Math.ceil(totalResults / perPage)) {
			currentPage++;
			displayResults(allData);
		}
	});

	// Export buttons
	$('#exportExcel').on('click', function() {
		exportToExcel();
	});

	$('#exportCsv').on('click', function() {
		exportToCsv();
	});

	$('#exportPdf').on('click', function() {
		exportToPdf();
	});

	// Load batch report data
	function loadBatchReport() {
		const formData = $('#batchReportForm').serialize();
		const csrfToken = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content');
		
		if (!csrfToken) {
			alert('CSRF token not found. Please refresh the page and try again.');
			return;
		}
		
		$('#loadingSpinner').show();
		$('#resultsTable').hide();
		$('#noResults').hide();
		$('#exportExcel').hide();
		$('#exportCsv').hide();
		$('#exportPdf').hide();

		$.ajax({
			url: '{{ route("crm.batch-report.data") }}',
			method: 'POST',
			data: formData,
			headers: {
				'X-CSRF-TOKEN': $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				$('#loadingSpinner').hide();
				
				if (response.data && response.data.length > 0) {
					allData = response.data;
					totalResults = allData.length;
					displayResults(allData);
					$('#resultsTable').show();
					$('#exportExcel').show();
					$('#exportCsv').show();
					$('#exportPdf').show();
				} else {
					$('#noResults').show();
				}
			},
			error: function(xhr) {
				$('#loadingSpinner').hide();
				$('#noResults').show();
				
				if (xhr.status === 419) {
					// CSRF token mismatch
					if (confirm('Session expired. Would you like to refresh the token and try again?')) {
						refreshCsrfToken();
						setTimeout(function() {
							loadBatchReport();
						}, 1000);
					} else {
						alert('Please refresh the page manually and try again.');
					}
				} else if (xhr.status === 500) {
					// Server error
					alert('Server error occurred. Please try again later.');
				} else {
					// Other errors
					alert('An error occurred while loading data. Please try again.');
				}
			}
		});
	}

	// Display results in table with pagination
	function displayResults(data) {
		const tbody = $('#tableBody');
		tbody.empty();

		// Calculate pagination
		const startIndex = (currentPage - 1) * perPage;
		const endIndex = startIndex + perPage;
		const pageData = data.slice(startIndex, endIndex);

		// Update pagination info
		$('#showingFrom').text(startIndex + 1);
		$('#showingTo').text(Math.min(endIndex, totalResults));
		$('#totalResults').text(totalResults);
		$('#currentPage').text(currentPage);
		$('#totalPages').text(Math.ceil(totalResults / perPage));

		// Update pagination buttons
		$('#prevPage').toggleClass('disabled', currentPage === 1);
		$('#nextPage').toggleClass('disabled', currentPage >= Math.ceil(totalResults / perPage));

		pageData.forEach(function(batch) {
			const row = `
				<tr class="hover:bg-gray-50">
					<td>
						<strong class="text-primary">${batch.batch_code}</strong>
					</td>
					<td>
						${batch.batch_report_url ? 
							`<a href="/storage${batch.batch_report_url}" target="_blank" class="btn btn-sm btn-outline-success" title="View COA Report">
								<i class="mdi mdi-file-pdf-outline me-1"></i>
								COA
							</a>` : 
							`<span>-</span>`
						}
					</td>
					<td>
						<span>${batch.sample_codes || 'N/A'}</span>
					</td>
					<td>
						<span>${batch.sample_type_name}</span>
					</td>
					<td>
						<div>
							<div class="fw-bold">${batch.customer_name}</div>
						</div>
					</td>
					<td>
						<span>${batch.crm_unit_name}</span>
					</td>
					<td>
						<span>${batch.sample_points || 'N/A'}</span>
					</td>
					<td>
						<span>${batch.receipt_date}</span>
					</td>
					<td>
						<span>${batch.date_collected || 'N/A'}</span>
					</td>
					<td>
						<span>${batch.status}</span>
					</td>
				</tr>
			`;
			tbody.append(row);
		});
	}

	// Export to Excel
	function exportToExcel() {
		const formData = $('#batchReportForm').serialize();
		const csrfToken = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content');
		
		if (!csrfToken) {
			alert('CSRF token not found. Please refresh the page and try again.');
			return;
		}
		
		$.ajax({
			url: '{{ route("crm.batch-report.export") }}',
			method: 'POST',
			data: formData,
			headers: {
				'X-CSRF-TOKEN': $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				if (response.download_url) {
					const link = document.createElement('a');
					link.href = response.download_url;
					link.download = 'batch_report.xlsx';
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				}
			},
			error: function(xhr) {
				if (xhr.status === 419) {
					// CSRF token mismatch
					alert('Session expired. Please refresh the page and try again.');
				} else if (xhr.status === 500) {
					// Server error
					alert('Server error occurred. Please try again later.');
				} else {
					// Other errors
					alert('Error exporting data. Please try again.');
				}
			}
		});
	}

	// Export to CSV
	function exportToCsv() {
		const headers = [
			'Report Number',
			'COA Report',
			'Lab No',
			'Sample Type',
			'Customer',
			'Customer Company Unit',
			'Sampling Location',
			'Receipt Date',
			'Date Collected',
			'Status'
		];

		let csvContent = headers.join(',') + '\n';

		allData.forEach(function(batch) {
			const row = [
				batch.batch_code,
				batch.batch_report_url || '',
				batch.sample_codes || 'N/A',
				batch.sample_type_name,
				batch.customer_name,
				batch.crm_unit_name || 'N/A',
				batch.sample_points || 'N/A',
				batch.receipt_date,
				batch.date_collected || 'N/A',
				batch.status
			];
			csvContent += row.map(field => `"${field}"`).join(',') + '\n';
		});

		const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
		const link = document.createElement('a');
		link.href = URL.createObjectURL(blob);
		link.download = 'batch_report.csv';
		link.click();
	}

	// Export to PDF
	function exportToPdf() {
		// This would typically use a library like jsPDF or make a server request
		alert('PDF export functionality would be implemented here. Consider using jsPDF or a server-side PDF generation library.');
	}

	// View batch details
	window.viewBatchDetails = function(batchId) {
		window.open(`/crm/batch/${batchId}/details`, '_blank');
	};
});
</script>

@endsection