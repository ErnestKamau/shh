# Design Document

## Overview

The Submission Form Creator is a template-based form generation system integrated into the existing Laravel-based lab management application. The system creates **submission form templates** that define the structure and fields, which are then instantiated when users fill them out. Each submission generates a unique form number based on configurable naming conventions (e.g., SF/ML/001).

The system follows a four-tier template architecture: Form Templates → Sections → Element Holders → Form Elements, with separate tables for storing actual submission instances and their field values in a normalized structure.

The design leverages Laravel's Eloquent ORM for database relationships, Blade templating for dynamic form rendering, and follows the existing application's patterns for consistency with the current lab management interface.

## Architecture

### System Architecture

The Submission Form Creator follows a modular architecture that integrates seamlessly with the existing lab management system:

```
┌─────────────────────────────────────────────────────────────┐
│                    Lab Management Module                     │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │   Form Builder  │  │  Form Renderer  │  │ Data Manager │ │
│  │   (Admin UI)    │  │   (User UI)     │  │  (Reports)   │ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                    Form Management Layer                     │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │ Form Controller │  │Section Controller│  │Element Ctrl  │ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                      Data Access Layer                       │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │  Form Models    │  │ Section Models  │  │Element Models│ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                       Database Layer                         │
│           MySQL Database with Eloquent ORM                   │
└─────────────────────────────────────────────────────────────┘
```

### Integration Points

The system integrates with existing lab management components:
- **Authentication**: Uses existing user authentication and role management
- **UI Framework**: Follows existing Bootstrap/Material Design patterns
- **Navigation**: Adds new menu items to the lab management sidebar
- **Permissions**: Leverages existing role-based access control system

## Components and Interfaces

### Database Schema

#### submission_forms Table
```sql
CREATE TABLE submission_forms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    naming_convention_prefix VARCHAR(50) DEFAULT 'SF',
    naming_convention_format VARCHAR(100) DEFAULT '{prefix}/{year}/{sequence}',
    is_published BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    version VARCHAR(10) DEFAULT '1.0',
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_name (name),
    INDEX idx_published_active (is_published, is_active)
);
```

#### submission_form_sections Table
```sql
CREATE TABLE submission_form_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_id) REFERENCES submission_forms(id) ON DELETE CASCADE
);
```

#### submission_form_element_holders Table
```sql
CREATE TABLE submission_form_element_holders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_section_id BIGINT UNSIGNED NOT NULL,
    holder_type ENUM('field', 'text') NOT NULL,
    max_elements INT DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_section_id) REFERENCES submission_form_sections(id) ON DELETE CASCADE
);
```

#### submission_form_elements Table
```sql
CREATE TABLE submission_form_elements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_element_holder_id BIGINT UNSIGNED NOT NULL,
    element_type ENUM('text', 'number', 'email', 'date', 'datetime', 'textarea', 'select', 'radio', 'checkbox', 'file', 'signature', 'calculation') NOT NULL,
    label VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    placeholder VARCHAR(255),
    help_text TEXT,
    is_required BOOLEAN DEFAULT FALSE,
    is_readonly BOOLEAN DEFAULT FALSE,
    default_value TEXT,
    validation_rules JSON,
    options JSON, -- For select, radio, checkbox options
    calculation_formula TEXT, -- For calculated fields
    conditional_logic JSON, -- Show/hide based on other fields
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_element_holder_id) REFERENCES submission_form_element_holders(id) ON DELETE CASCADE,
    INDEX idx_holder_order (submission_form_element_holder_id, sort_order)
);
```

#### submission_form_instances Table
```sql
CREATE TABLE submission_form_instances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_id BIGINT UNSIGNED NOT NULL,
    form_number VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255), -- User-defined title for the instance
    submitted_by BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'submitted', 'in_review', 'approved', 'rejected', 'cancelled') DEFAULT 'draft',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    due_date DATE NULL,
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    review_notes TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_id) REFERENCES submission_forms(id),
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_form_number (form_number),
    INDEX idx_status (status),
    INDEX idx_submitted_by (submitted_by),
    INDEX idx_form_status (submission_form_id, status)
);
```

#### submission_form_instance_values Table
```sql
CREATE TABLE submission_form_instance_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_instance_id BIGINT UNSIGNED NOT NULL,
    submission_form_element_id BIGINT UNSIGNED NOT NULL,
    value TEXT,
    file_path VARCHAR(500), -- For file uploads
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_instance_id) REFERENCES submission_form_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (submission_form_element_id) REFERENCES submission_form_elements(id),
    UNIQUE KEY unique_instance_element (submission_form_instance_id, submission_form_element_id),
    INDEX idx_instance_element (submission_form_instance_id, submission_form_element_id)
);
```

#### submission_form_permissions Table
```sql
CREATE TABLE submission_form_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    permission_type ENUM('view', 'create', 'edit', 'review', 'approve') NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_id) REFERENCES submission_forms(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_form_permissions (submission_form_id, permission_type)
);
```

#### submission_form_audit_log Table
```sql
CREATE TABLE submission_form_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_form_instance_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action ENUM('created', 'updated', 'submitted', 'reviewed', 'approved', 'rejected', 'cancelled') NOT NULL,
    field_changes JSON, -- Track what fields were changed
    notes TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (submission_form_instance_id) REFERENCES submission_form_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_instance_action (submission_form_instance_id, action),
    INDEX idx_created_at (created_at)
);
```

### Model Relationships

#### SubmissionForm Model
```php
class SubmissionForm extends Model
{
    protected $fillable = ['name', 'description', 'naming_convention_prefix', 'naming_convention_format', 'is_published', 'is_active', 'version', 'created_by'];
    protected $casts = ['is_published' => 'boolean', 'is_active' => 'boolean'];

    public function sections() {
        return $this->hasMany(SubmissionFormSection::class)->orderBy('sort_order');
    }
    
    public function instances() {
        return $this->hasMany(SubmissionFormInstance::class);
    }
    
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function permissions() {
        return $this->hasMany(SubmissionFormPermission::class);
    }
    
    public function canUserAccess($user, $permissionType = 'view') {
        return $this->permissions()
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('role_id', $user->roles->pluck('id'));
            })
            ->where('permission_type', $permissionType)
            ->exists();
    }
    
    public function isPublishedAndActive() {
        return $this->is_published && $this->is_active;
    }
}
```

#### SubmissionFormSection Model
```php
class SubmissionFormSection extends Model
{
    protected $fillable = ['submission_form_id', 'title', 'description', 'sort_order'];

    public function submissionForm() {
        return $this->belongsTo(SubmissionForm::class);
    }
    
    public function elementHolders() {
        return $this->hasMany(SubmissionFormElementHolder::class, 'submission_form_section_id')->orderBy('sort_order');
    }
}
```

#### SubmissionFormElementHolder Model
```php
class SubmissionFormElementHolder extends Model
{
    protected $fillable = ['submission_form_section_id', 'holder_type', 'max_elements', 'sort_order'];

    public function section() {
        return $this->belongsTo(SubmissionFormSection::class, 'submission_form_section_id');
    }
    
    public function elements() {
        return $this->hasMany(SubmissionFormElement::class, 'submission_form_element_holder_id')->orderBy('sort_order');
    }
}
```

#### SubmissionFormElement Model
```php
class SubmissionFormElement extends Model
{
    protected $fillable = ['submission_form_element_holder_id', 'element_type', 'label', 'name', 'placeholder', 'help_text', 'is_required', 'validation_rules', 'options', 'sort_order'];
    protected $casts = ['is_required' => 'boolean', 'validation_rules' => 'array', 'options' => 'array'];

    public function holder() {
        return $this->belongsTo(SubmissionFormElementHolder::class, 'submission_form_element_holder_id');
    }
}
```

### Controller Architecture

#### SubmissionFormController
- `index()`: List all forms with pagination and search
- `create()`: Show form creation interface
- `store()`: Save new form
- `show()`: Display form details with sections and elements
- `edit()`: Show form editing interface
- `update()`: Update existing form
- `destroy()`: Delete form (with cascade protection)
- `preview()`: Render form for preview
- `publish()`: Toggle form published status

#### FormBuilderController
- `buildForm()`: Main form builder interface
- `addSection()`: Add new section to form
- `updateSection()`: Update section details
- `deleteSection()`: Remove section
- `addElementHolder()`: Add element holder to section
- `updateElementHolder()`: Update holder configuration
- `addElement()`: Add form element to holder
- `updateElement()`: Update element properties

#### FormInstanceController
- `create()`: Display form template for user to fill out
- `store()`: Save new form instance with generated form number
- `show()`: Display completed form instance
- `edit()`: Edit draft form instance
- `update()`: Update form instance values
- `draft()`: Save as draft
- `instances()`: List user's form instances
- `review()`: Admin review interface
- `export()`: Export instance data

## Data Models

### Form Configuration Data Structure

Forms are stored with hierarchical JSON structure for efficient rendering:

```json
{
  "form": {
    "id": 1,
    "name": "Water Quality Testing Form",
    "description": "Standard water quality testing submission",
    "sections": [
      {
        "id": 1,
        "title": "Sample Information",
        "description": "Basic sample details",
        "element_holders": [
          {
            "id": 1,
            "type": "field",
            "max_elements": 2,
            "elements": [
              {
                "id": 1,
                "type": "text",
                "label": "Sample ID",
                "name": "sample_id",
                "required": true,
                "validation": ["required", "string", "max:50"]
              },
              {
                "id": 2,
                "type": "date",
                "label": "Collection Date",
                "name": "collection_date",
                "required": true
              }
            ]
          }
        ]
      }
    ]
  }
}
```

### Form Instance Data Structure

Form instances are stored with normalized field values:

**Form Instance Record:**
```json
{
  "id": 1,
  "submission_form_id": 1,
  "form_number": "SF/ML/001",
  "submitted_by": 123,
  "status": "submitted",
  "submitted_at": "2025-01-15T10:30:00Z"
}
```

**Instance Values Records:**
```json
[
  {
    "id": 1,
    "submission_form_instance_id": 1,
    "submission_form_element_id": 5,
    "value": "WQ-2025-001"
  },
  {
    "id": 2,
    "submission_form_instance_id": 1,
    "submission_form_element_id": 6,
    "value": "2025-01-15"
  },
  {
    "id": 3,
    "submission_form_instance_id": 1,
    "submission_form_element_id": 7,
    "value": "7.2"
  }
]
```

## Error Handling

### Validation Strategy

1. **Client-side Validation**: JavaScript validation for immediate feedback
2. **Server-side Validation**: Laravel validation rules for security
3. **Database Constraints**: Foreign key constraints and data integrity
4. **Business Logic Validation**: Custom validation for form structure rules

### Error Response Format

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "sample_id": ["The sample ID field is required."],
    "collection_date": ["The collection date must be a valid date."]
  },
  "error_code": "VALIDATION_ERROR"
}
```

### Exception Handling

- **FormNotFoundException**: When accessing non-existent forms
- **FormNotPublishedException**: When users try to access unpublished forms
- **ElementCapacityExceededException**: When adding elements beyond holder capacity
- **InvalidFormStructureException**: When form structure validation fails

## Testing Strategy

### Unit Testing

1. **Model Tests**: Test all Eloquent relationships and methods
2. **Validation Tests**: Test all form validation rules
3. **Business Logic Tests**: Test form building and submission logic

### Integration Testing

1. **Controller Tests**: Test all controller methods with various scenarios
2. **Database Tests**: Test database operations and constraints
3. **Form Rendering Tests**: Test dynamic form generation

### Feature Testing

1. **Form Builder Workflow**: End-to-end form creation process
2. **Form Submission Workflow**: Complete user submission process
3. **Permission Tests**: Role-based access control testing

### Test Data Strategy

- **Factory Classes**: Create test data for all models
- **Seeder Classes**: Populate test database with realistic data
- **Mock Data**: Use consistent test data across all tests

## Performance Considerations

### Database Optimization

1. **Indexing Strategy**:
   - Index on `submission_forms.name` for search
   - Index on `submission_form_sections.submission_form_id` for joins
   - Index on `submission_form_instances.submitted_by` for user queries
   - Index on `submission_form_instances.form_number` for unique lookups
   - Composite index on `submission_form_instances(submission_form_id, status)`
   - Composite index on `submission_form_instance_values(submission_form_instance_id, submission_form_element_id)`

2. **Query Optimization**:
   - Use eager loading for form structure queries
   - Implement pagination for large datasets
   - Cache frequently accessed forms

### Caching Strategy

1. **Form Template Caching**: Cache complete form template structures for rendering
2. **User Permission Caching**: Cache user permissions for form access
3. **Instance Statistics**: Cache form instance counts and statistics
4. **Form Number Generation**: Cache sequence numbers for efficient form number generation

### Frontend Performance

1. **Lazy Loading**: Load form sections progressively
2. **Client-side Validation**: Reduce server requests
3. **Asset Optimization**: Minify CSS/JS for form builder interface

#### SubmissionFormInstance Model
```php
class SubmissionFormInstance extends Model
{
    protected $fillable = ['submission_form_id', 'form_number', 'title', 'submitted_by', 'status', 'priority', 'due_date', 'submitted_at', 'reviewed_at', 'reviewed_by', 'review_notes'];
    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'due_date' => 'date'];

    public function submissionForm() {
        return $this->belongsTo(SubmissionForm::class);
    }
    
    public function submittedBy() {
        return $this->belongsTo(User::class, 'submitted_by');
    }
    
    public function reviewedBy() {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    public function values() {
        return $this->hasMany(SubmissionFormInstanceValue::class);
    }
    
    public function auditLogs() {
        return $this->hasMany(SubmissionFormAuditLog::class);
    }
    
    public function generateFormNumber() {
        $form = $this->submissionForm;
        $prefix = $form->naming_convention_prefix ?? 'SF';
        $format = $form->naming_convention_format ?? '{prefix}/{year}/{sequence}';
        
        // Get next sequence number for this form and year
        $year = date('Y');
        $lastInstance = self::where('submission_form_id', $this->submission_form_id)
            ->where('form_number', 'LIKE', "{$prefix}%/{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
            
        $sequence = 1;
        if ($lastInstance) {
            $parts = explode('/', $lastInstance->form_number);
            $sequence = intval(end($parts)) + 1;
        }
        
        return str_replace(
            ['{prefix}', '{year}', '{sequence}'],
            [$prefix, $year, str_pad($sequence, 3, '0', STR_PAD_LEFT)],
            $format
        );
    }
    
    public function getValueByElementName($elementName) {
        return $this->values()
            ->whereHas('element', function($query) use ($elementName) {
                $query->where('name', $elementName);
            })
            ->first()?->value;
    }
    
    public function isOverdue() {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['approved', 'rejected', 'cancelled']);
    }
    
    public function logAction($action, $user, $fieldChanges = null, $notes = null) {
        $this->auditLogs()->create([
            'user_id' => $user->id,
            'action' => $action,
            'field_changes' => $fieldChanges,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
```

#### SubmissionFormInstanceValue Model
```php
class SubmissionFormInstanceValue extends Model
{
    protected $fillable = ['submission_form_instance_id', 'submission_form_element_id', 'value', 'file_path'];

    public function instance() {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }
    
    public function element() {
        return $this->belongsTo(SubmissionFormElement::class, 'submission_form_element_id');
    }
    
    public function getFormattedValue() {
        $element = $this->element;
        
        switch ($element->element_type) {
            case 'date':
                return $this->value ? Carbon::parse($this->value)->format('Y-m-d') : null;
            case 'datetime':
                return $this->value ? Carbon::parse($this->value)->format('Y-m-d H:i:s') : null;
            case 'file':
                return $this->file_path;
            case 'checkbox':
                return $this->value === '1' ? 'Yes' : 'No';
            default:
                return $this->value;
        }
    }
}
```

#### SubmissionFormPermission Model
```php
class SubmissionFormPermission extends Model
{
    protected $fillable = ['submission_form_id', 'role_id', 'user_id', 'permission_type'];

    public function submissionForm() {
        return $this->belongsTo(SubmissionForm::class);
    }
    
    public function role() {
        return $this->belongsTo(Role::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

#### SubmissionFormAuditLog Model
```php
class SubmissionFormAuditLog extends Model
{
    protected $fillable = ['submission_form_instance_id', 'user_id', 'action', 'field_changes', 'notes', 'ip_address', 'user_agent'];
    protected $casts = ['field_changes' => 'array'];

    public function instance() {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```