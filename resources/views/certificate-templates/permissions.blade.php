@extends('layouts.lab.layout.app')

@section('title2')
  <title>Manage Permissions - {{ $certificateTemplate->name }}</title>
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
          'link' => route('certificate-templates.index'),
          'name' => 'Certificate Templates',
          'icon' => null
        ),
        array(
          'link' => route('certificate-templates.show', $certificateTemplate),
          'name' => $certificateTemplate->name,
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Permissions',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h2>
          <i class="mdi mdi-account-key"></i> Manage Permissions
        </h2>
        <p class="text-muted mb-0">{{ $certificateTemplate->name }}</p>
      </div>
      <a href="{{ route('certificate-templates.show', $certificateTemplate) }}" class="btn btn-secondary">
        <i class="mdi mdi-arrow-left"></i> Back to Template
      </a>
    </div>

    <div class="row">
      <!-- Current Permissions -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-account-group"></i> Current Permissions
            </h5>
          </div>
          <div class="card-body">
            @if($certificateTemplate->permissions->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead>
                    <tr>
                      <th>User/Role</th>
                      <th>Type</th>
                      <th>Permission</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($certificateTemplate->permissions as $permission)
                      <tr>
                        <td>
                          @if($permission->user)
                            <i class="mdi mdi-account"></i> {{ $permission->user->name }}
                            <br><small class="text-muted">{{ $permission->user->email }}</small>
                          @elseif($permission->role)
                            <i class="mdi mdi-account-group"></i> {{ $permission->role->name }}
                            <br><small class="text-muted">Role-based permission</small>
                          @else
                            <span class="text-muted">Unknown</span>
                          @endif
                        </td>
                        <td>
                          @if($permission->user)
                            <span class="badge badge-primary">User</span>
                          @else
                            <span class="badge badge-info">Role</span>
                          @endif
                        </td>
                        <td>
                          @switch($permission->permission_type)
                            @case('view')
                              <span class="badge badge-secondary">View</span>
                              @break
                            @case('edit')
                              <span class="badge badge-warning">Edit</span>
                              @break
                            @case('publish')
                              <span class="badge badge-success">Publish</span>
                              @break
                            @case('generate')
                              <span class="badge badge-info">Generate</span>
                              @break
                            @default
                              <span class="badge badge-light">{{ ucfirst($permission->permission_type) }}</span>
                          @endswitch
                        </td>
                        <td>
                          <form method="POST" 
                                action="{{ route('certificate-templates.permissions.update', $certificateTemplate) }}" 
                                style="display: inline;"
                                onsubmit="return confirm('Are you sure you want to remove this permission?')">
                            @csrf
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                              <i class="mdi mdi-delete"></i>
                            </button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="text-center py-4">
                <i class="mdi mdi-account-key" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="text-muted mt-3">No specific permissions set</h5>
                <p class="text-muted">
                  Only the template creator and users with general template permissions can access this template.
                </p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Add New Permission -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-plus"></i> Add Permission
            </h5>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('certificate-templates.permissions.update', $certificateTemplate) }}">
              @csrf
              <input type="hidden" name="action" value="add">
              
              <div class="form-group">
                <label for="permission_type_select" class="form-label">Permission Type</label>
                <select class="form-control" id="permission_type_select" name="permission_type" required>
                  <option value="">Select Permission</option>
                  <option value="view">View - Can view template</option>
                  <option value="edit">Edit - Can modify template</option>
                  <option value="publish">Publish - Can publish/unpublish</option>
                  <option value="generate">Generate - Can create reports</option>
                </select>
              </div>

              <div class="form-group">
                <label for="assignment_type" class="form-label">Assign To</label>
                <select class="form-control" id="assignment_type" name="assignment_type" required>
                  <option value="">Select Type</option>
                  <option value="user">Specific User</option>
                  <option value="role">User Role</option>
                </select>
              </div>

              <!-- User Selection -->
              <div class="form-group" id="user_selection" style="display: none;">
                <label for="user_id" class="form-label">Select User</label>
                <select class="form-control" id="user_id" name="user_id">
                  <option value="">Choose User</option>
                  @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                  @endforeach
                </select>
              </div>

              <!-- Role Selection -->
              <div class="form-group" id="role_selection" style="display: none;">
                <label for="role_id" class="form-label">Select Role</label>
                <select class="form-control" id="role_id" name="role_id">
                  <option value="">Choose Role</option>
                  @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                  @endforeach
                </select>
              </div>

              <button type="submit" class="btn btn-primary btn-block">
                <i class="mdi mdi-plus"></i> Add Permission
              </button>
            </form>
          </div>
        </div>

        <!-- Permission Levels Info -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-information"></i> Permission Levels
            </h5>
          </div>
          <div class="card-body">
            <div class="permission-info">
              <div class="mb-2">
                <span class="badge badge-secondary">View</span>
                <small class="text-muted d-block">Can view template details and structure</small>
              </div>
              <div class="mb-2">
                <span class="badge badge-warning">Edit</span>
                <small class="text-muted d-block">Can modify template content and settings</small>
              </div>
              <div class="mb-2">
                <span class="badge badge-success">Publish</span>
                <small class="text-muted d-block">Can publish/unpublish templates</small>
              </div>
              <div class="mb-2">
                <span class="badge badge-info">Generate</span>
                <small class="text-muted d-block">Can generate reports from template</small>
              </div>
            </div>
            
            <hr>
            
            <small class="text-muted">
              <strong>Note:</strong> Template creators always have full access. 
              Users with general template permissions may also have access regardless of these settings.
            </small>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Success/Error Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif
@endsection

@section('scripts')
<script>
  // Show/hide user or role selection based on assignment type
  document.getElementById('assignment_type').addEventListener('change', function() {
    const userSelection = document.getElementById('user_selection');
    const roleSelection = document.getElementById('role_selection');
    const userSelect = document.getElementById('user_id');
    const roleSelect = document.getElementById('role_id');
    
    if (this.value === 'user') {
      userSelection.style.display = 'block';
      roleSelection.style.display = 'none';
      userSelect.required = true;
      roleSelect.required = false;
      roleSelect.value = '';
    } else if (this.value === 'role') {
      userSelection.style.display = 'none';
      roleSelection.style.display = 'block';
      userSelect.required = false;
      roleSelect.required = true;
      userSelect.value = '';
    } else {
      userSelection.style.display = 'none';
      roleSelection.style.display = 'none';
      userSelect.required = false;
      roleSelect.required = false;
      userSelect.value = '';
      roleSelect.value = '';
    }
  });

  // Auto-hide alerts after 5 seconds
  setTimeout(function() {
    $('.alert').fadeOut('slow');
  }, 5000);
</script>
@endsection