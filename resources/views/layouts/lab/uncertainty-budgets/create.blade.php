@extends('layouts.lab.layout.app')

@section('title2')
  <title>Create Uncertainty Budget</title>
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
          'name' => 'Create',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-calculator"></i> Create Uncertainty Budget
      <span class="text-muted small">- Add new measurement uncertainty budget</span>
    </h2>
    
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="mdi mdi-plus-circle"></i> New Uncertainty Budget
              </h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('uncertainty-budgets.store') }}">
                @csrf
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="analyte_id" class="form-label">Analyte <span class="text-danger">*</span></label>
                      <select class="form-control select2 @error('analyte_id') is-invalid @enderror" id="analyte_id" name="analyte_id" required>
                        <option value="">Select Analyte</option>
                        @foreach($analytes as $analyte)
                          <option value="{{ $analyte->id }}" {{ old('analyte_id') == $analyte->id ? 'selected' : '' }}>
                            {{ $analyte->name }} ({{ $analyte->code }})
                          </option>
                        @endforeach
                      </select>
                      @error('analyte_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="method_ids" class="form-label">Methods <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('method_ids') is-invalid @enderror" id="method_ids" name="method_ids[]" multiple required>
                            @foreach($methods as $method)
                                <option value="{{ $method->id }}" {{ in_array($method->id, old('method_ids', [])) ? 'selected' : '' }}>
                                    {{ $method->name }} ({{ $method->code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select methods for this analyte. Methods will be pre-selected based on the analyte.</small>
                        @error('method_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="coverage_factor_k" class="form-label">Coverage Factor (k) <span class="text-danger">*</span></label>
                      <input type="number" class="form-control @error('coverage_factor_k') is-invalid @enderror" 
                             id="coverage_factor_k" name="coverage_factor_k" 
                             value="{{ old('coverage_factor_k', 2.00) }}" 
                             step="0.01" min="1" max="10" required>
                      <small class="form-text text-muted">Usually 2 for 95% confidence level</small>
                      @error('coverage_factor_k')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="confidence_level" class="form-label">Confidence Level (%) <span class="text-danger">*</span></label>
                      <input type="number" class="form-control @error('confidence_level') is-invalid @enderror" 
                             id="confidence_level" name="confidence_level" 
                             value="{{ old('confidence_level', 95.00) }}" 
                             step="0.01" min="50" max="99.99" required>
                      <small class="form-text text-muted">Usually 95% for most applications</small>
                      @error('confidence_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="form-group mt-4">
                  <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-content-save"></i> Create Uncertainty Budget
                  </button>
                  <a href="{{ route('uncertainty-budgets.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="mdi mdi-information"></i> Information
              </h5>
            </div>
            <div class="card-body">
              <p class="text-muted">
                <strong>Coverage Factor (k):</strong> A numerical factor used to expand the combined standard uncertainty to obtain the expanded uncertainty.
              </p>
              <p class="text-muted">
                <strong>Confidence Level:</strong> The percentage of the distribution that is expected to fall within the expanded uncertainty interval.
              </p>
              <hr>
              <p class="text-muted small">
                <strong>Note:</strong> After creating the budget, you can add uncertainty sources and the system will automatically calculate the combined standard uncertainty and expanded uncertainty.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <style>
    #method_ids {
      min-height: 120px;
      border: 2px solid #ced4da;
      border-radius: 0.375rem;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    #method_ids:focus {
      border-color: #80bdff;
      outline: 0;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    #method_ids option {
      padding: 8px 12px;
      font-size: 14px;
    }
    
    #method_ids option:checked {
      background-color: #007bff;
      color: white;
      font-weight: 500;
    }
    
    #method_ids option:hover {
      background-color: #f8f9fa;
    }
    
    #method_ids option:checked:hover {
      background-color: #0056b3;
    }
    
    .form-group label {
      font-weight: 600;
      margin-bottom: 8px;
    }
    
    .form-text {
      font-size: 12px;
      color: #6c757d;
      margin-top: 4px;
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const analyteSelect = document.getElementById('analyte_id');
      const methodSelect = document.getElementById('method_ids');
      
      // Handle analyte selection change
      analyteSelect.addEventListener('change', function() {
        const analyteId = this.value;
        
        if (analyteId) {
          // Clear current selections
          $(methodSelect).val(null).trigger('change');
          
          // Fetch methods for the selected analyte
          fetch(`/lab-uncertainty/api/methods/${analyteId}`)
            .then(response => response.json())
            .then(data => {
              if (data.length > 0) {
                // Pre-select the methods that belong to this analyte
                const methodIds = data.map(method => method.id.toString());
                $(methodSelect).val(methodIds).trigger('change');
              }
            })
            .catch(error => {
              console.error('Error loading methods:', error);
            });
        } else {
          // Clear selections when no analyte is selected
          $(methodSelect).val(null).trigger('change');
        }
      });
      
      // Form validation
      document.querySelector('form').addEventListener('submit', function(e) {
        const selectedMethods = Array.from(methodSelect.selectedOptions);
        
        if (selectedMethods.length === 0) {
          e.preventDefault();
          alert('Please select at least one method for this analyte.');
          return false;
        }
      });
    });
  </script>

@endsection
