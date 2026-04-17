@extends('layouts.lab.layout.app')

@section('title2')
  <title>Edit Uncertainty Budget</title>
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
          'link' => route('uncertainty-budgets.show', $budget->id),
          'name' => $budget->analyte_name,
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Edit',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-calculator"></i> Edit Uncertainty Budget
      <span class="text-muted small">- {{ $budget->analyte_name }}</span>
    </h2>
    
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="mdi mdi-pencil"></i> Edit Uncertainty Budget
              </h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('uncertainty-budgets.update', $budget->id) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="analyte_id" class="form-label">Analyte <span class="text-danger">*</span></label>
                      <select class="form-control select2 @error('analyte_id') is-invalid @enderror" id="analyte_id" name="analyte_id" required>
                        <option value="">Select Analyte</option>
                        @foreach($analytes as $analyte)
                          <option value="{{ $analyte->id }}" {{ old('analyte_id', $budget->analyte_id) == $analyte->id ? 'selected' : '' }}>
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
                      <label class="form-label">Method</label>
                      <div class="form-control-plaintext">
                        <strong>{{ $budget->method_name }}</strong>
                        <span class="text-muted">({{ $budget->method->code ?? 'N/A' }})</span>
                        <small class="text-muted d-block">Method cannot be changed after creation</small>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="coverage_factor_k" class="form-label">Coverage Factor (k) <span class="text-danger">*</span></label>
                      <input type="number" class="form-control @error('coverage_factor_k') is-invalid @enderror" 
                             id="coverage_factor_k" name="coverage_factor_k" 
                             value="{{ old('coverage_factor_k', $budget->coverage_factor_k) }}" 
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
                             value="{{ old('confidence_level', $budget->confidence_level) }}" 
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
                    <i class="mdi mdi-content-save"></i> Update Uncertainty Budget
                  </button>
                  <a href="{{ route('uncertainty-budgets.show', $budget->id) }}" class="btn btn-secondary">
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
                <i class="mdi mdi-information"></i> Current Information
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
                  <td><strong>Version:</strong></td>
                  <td>{{ $budget->version_number }}</td>
                </tr>
                <tr>
                  <td><strong>Sources:</strong></td>
                  <td>{{ $budget->sources->count() }}</td>
                </tr>
                <tr>
                  <td><strong>Combined Uncertainty:</strong></td>
                  <td>{{ $budget->formatted_combined_uncertainty }}</td>
                </tr>
                <tr>
                  <td><strong>Expanded Uncertainty:</strong></td>
                  <td>{{ $budget->formatted_expanded_uncertainty }}</td>
                </tr>
              </table>
              
              <div class="mt-3">
                <a href="{{ route('uncertainty-budgets.show', $budget->id) }}" class="btn btn-sm btn-outline-primary">
                  <i class="mdi mdi-eye"></i> View Budget
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>


@endsection
