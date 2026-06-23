@extends('layouts.lab.layout.app')

@section('title2')
  <title>{{ $budget->analyte_name }} - Uncertainty Budget</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('uncertainty-budgets.index'),
          'name' => 'Uncertainty Budgets',
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => $budget->analyte_name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-calculator"></i> {{ $budget->analyte_name }} - Uncertainty Budget
      <span class="text-muted small">| {{ $budget->method_name }}</span>
    </h2>
    
    <div class="container-fluid">
      <div class="row">
        <!-- Budget Information -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="mdi mdi-information"></i> Budget Information
              </h5>
            </div>
            <div class="card-body">
              <table class="table table-sm">
                <tr>
                  <td><strong>Analyte:</strong></td>
                  <td>{{ $budget->analyte_name }} ({{ $budget->analyte_code }})</td>
                </tr>
                <tr>
                  <td><strong>Method:</strong></td>
                  <td>{{ $budget->method_name }}</td>
                </tr>
                <tr>
                  <td><strong>Coverage Factor (k):</strong></td>
                  <td>{{ $budget->coverage_factor_k }}</td>
                </tr>
                <tr>
                  <td><strong>Confidence Level:</strong></td>
                  <td>{{ $budget->confidence_level }}%</td>
                </tr>
                <tr>
                  <td><strong>Combined Standard Uncertainty:</strong></td>
                  <td data-uncertainty="combined">{{ $budget->formatted_combined_uncertainty }}</td>
                </tr>
                <tr>
                  <td><strong>Expanded Uncertainty:</strong></td>
                  <td data-uncertainty="expanded">{{ $budget->formatted_expanded_uncertainty }}</td>
                </tr>
                <tr>
                  <td><strong>Version:</strong></td>
                  <td>{{ $budget->version_number }}</td>
                </tr>
                <tr>
                  <td><strong>Created By:</strong></td>
                  <td>{{ $budget->creator->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td><strong>Created:</strong></td>
                  <td>{{ $budget->created_at->format('M d, Y H:i') }}</td>
                </tr>
              </table>
              
              <div class="mt-3">
                <a href="{{ route('uncertainty-budgets.edit', $budget->id) }}" class="btn btn-sm btn-primary">
                  <i class="mdi mdi-pencil"></i> Edit Budget
                </a>
                <button type="button" class="btn btn-sm btn-info" onclick="recalculateUncertainties()">
                  <i class="mdi mdi-calculator"></i> Recalculate
                </button>
                <a href="{{ route('uncertainty-budgets.index') }}" class="btn btn-sm btn-secondary">
                  <i class="mdi mdi-arrow-left"></i> Back to List
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Uncertainty Sources -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="mdi mdi-table"></i> Uncertainty Sources
              </h5>
              <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addSourceModal">
                <i class="mdi mdi-plus"></i> Add Source
              </button>
            </div>
            <div class="card-body">
              @if($budget->sources->count() > 0)
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Source of Uncertainty</th>
                        <th>Type</th>
                        <th>Std. Uncertainty (u)</th>
                        <th>Sensitivity Coefficient (c)</th>
                        <th>Contribution (u·c)</th>
                        <th>Notes</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($budget->sources as $source)
                        <tr>
                          <td>{{ $source->source_name }}</td>
                          <td>
                            <span class="badge badge-{{ $source->type == 'A' ? 'primary' : 'info' }}">
                              {{ $source->type }}
                            </span>
                          </td>
                          <td>{{ $source->formatted_std_uncertainty }}</td>
                          <td>{{ $source->formatted_sensitivity_coefficient }}</td>
                          <td>{{ $source->formatted_contribution }}</td>
                          <td>{{ $source->notes ? \Illuminate\Support\Str::limit($source->notes, 50) : '-' }}</td>
                          <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="editSource({{ $source->id }})">
                              <i class="mdi mdi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteSource({{ $source->id }})">
                              <i class="mdi mdi-delete"></i>
                            </button>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="text-center py-4">
                  <i class="mdi mdi-table-large text-muted" style="font-size: 3rem;"></i>
                  <p class="text-muted mt-2">No uncertainty sources added yet.</p>
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSourceModal">
                    <i class="mdi mdi-plus"></i> Add First Source
                  </button>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

@endsection

@section('script2')
  <!-- Add Source Modal -->
  <div class="modal fade" id="addSourceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Uncertainty Source</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <form id="addSourceForm" action="{{ route('uncertainty-budgets.sources.store', $budget->id) }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="source_name">Source of Uncertainty <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="source_name" name="source_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="source_type">Type <span class="text-danger">*</span></label>
                  <select class="form-control no-select2" id="source_type" name="type" required>
                    <option value="">Select Type</option>
                    <option value="A">A (Statistical)</option>
                    <option value="B">B (Other)</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="std_uncertainty_value">Standard Uncertainty (u) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="std_uncertainty_value" name="std_uncertainty_value" 
                         step="0.000001" min="0" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sensitivity_coefficient">Sensitivity Coefficient (c) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="sensitivity_coefficient" name="sensitivity_coefficient" 
                         step="0.000001" min="0" value="1.000000" required>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="notes">Notes</label>
              <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Source</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Source Modal -->
  <div class="modal fade" id="editSourceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Uncertainty Source</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <form id="editSourceForm" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" id="edit_source_id" name="source_id">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_source_name">Source of Uncertainty <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_source_name" name="source_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_type">Type <span class="text-danger">*</span></label>
                  <select class="form-control no-select2" id="edit_type" name="type" required>
                    <option value="">Select Type</option>
                    <option value="A">A (Statistical)</option>
                    <option value="B">B (Other)</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_std_uncertainty_value">Standard Uncertainty (u) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="edit_std_uncertainty_value" name="std_uncertainty_value" 
                         step="0.000001" min="0" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_sensitivity_coefficient">Sensitivity Coefficient (c) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="edit_sensitivity_coefficient" name="sensitivity_coefficient" 
                         step="0.000001" min="0" required>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="edit_notes">Notes</label>
              <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Source</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Global function to update uncertainty display values
    function updateUncertaintyDisplay(budgetData) {
        // Update elements with data attributes
        const combinedElements = document.querySelectorAll('[data-uncertainty="combined"]');
        const expandedElements = document.querySelectorAll('[data-uncertainty="expanded"]');
        
        combinedElements.forEach(el => {
            el.textContent = budgetData.formatted_combined_uncertainty || 'N/A';
        });
        
        expandedElements.forEach(el => {
            el.textContent = budgetData.formatted_expanded_uncertainty || 'N/A';
        });
        
        // Show a success message with the updated values
        if (budgetData.formatted_combined_uncertainty && budgetData.formatted_expanded_uncertainty) {
            console.log('Uncertainty values updated:', {
                combined: budgetData.formatted_combined_uncertainty,
                expanded: budgetData.formatted_expanded_uncertainty
            });
        }
    }

    // Define global functions immediately to ensure they're available
    window.recalculateUncertainties = function() {
        console.log('recalculateUncertainties function called');
        if (typeof $ === 'undefined') {
            alert('jQuery is not available. Please refresh the page.');
            return;
        }
        
        // Create a form and submit it for session messages
        var form = $('<form>', {
            'method': 'POST',
            'action': `/lab-uncertainty/{{ $budget->id }}/recalculate`
        });
        
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        }));
        
        $('body').append(form);
        form.submit();
    };

    window.editSource = function(sourceId) {
        $.ajax({
            url: `/lab-uncertainty/api/sources/${sourceId}`,
            method: 'GET',
            success: function(data) {
                $('#edit_source_id').val(data.id);
                $('#edit_source_name').val(data.source_name);
                $('#edit_type').val(data.type);
                $('#edit_std_uncertainty_value').val(data.std_uncertainty_value);
                $('#edit_sensitivity_coefficient').val(data.sensitivity_coefficient);
                $('#edit_notes').val(data.notes || '');
                
                // Set the form action dynamically
                $('#editSourceForm').attr('action', `/lab-uncertainty/sources/${sourceId}`);
                
                $('#editSourceModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('Error loading source data:', error);
                alert('Error loading source data');
            }
        });
    };

    window.deleteSource = function(sourceId) {
        if (confirm('Are you sure you want to delete this uncertainty source?')) {
            // Create a form and submit it for session messages
            var form = $('<form>', {
                'method': 'POST',
                'action': `/lab-uncertainty/sources/${sourceId}`
            });
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content')
            }));
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_method',
                'value': 'DELETE'
            }));
            
            $('body').append(form);
            form.submit();
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Reset forms when modals are hidden
        $('#addSourceModal').on('hidden.bs.modal', function() {
            $('#addSourceForm')[0].reset();
        });
        
        $('#editSourceModal').on('hidden.bs.modal', function() {
            $('#editSourceForm')[0].reset();
        });

        // Add Source Form - Use regular form submission for session messages
        $('#addSourceForm').on('submit', function(e) {
            // Let the form submit normally to get session messages
            // No need to prevent default or use AJAX
        });

        // Edit Source Form - Use regular form submission for session messages
        $('#editSourceForm').on('submit', function(e) {
            // Let the form submit normally to get session messages
            // No need to prevent default or use AJAX
        });
    });

    // Verify function is defined
    console.log('recalculateUncertainties function defined:', typeof window.recalculateUncertainties);
  </script>
@endsection
