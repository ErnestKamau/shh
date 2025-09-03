# Design Document

## Overview

The Certificate of Analysis Template Engine is designed as a comprehensive reporting system that extends the existing submission form infrastructure. The system follows a hierarchical structure similar to submission forms but optimized for document generation and rich content creation. The architecture leverages Laravel's MVC pattern with dedicated models for templates, sections, and content elements, while providing a modern drag-and-drop interface for template design.

## Architecture

### High-Level Architecture

The system follows a layered architecture approach:

1. **Presentation Layer**: jQuery/JavaScript frontend with drag-and-drop capabilities
2. **Application Layer**: Laravel controllers handling template management and report generation
3. **Domain Layer**: Eloquent models representing template structure and business logic
4. **Infrastructure Layer**: Database storage, file management, and PDF generation services

### Core Components

- **Template Management System**: CRUD operations for certificate templates
- **Template Builder Interface**: Drag-and-drop UI for template design
- **Content Element System**: Pluggable content types (text, images, data fields, etc.)
- **Data Injection Engine**: Dynamic content population from submission forms
- **Report Generation Service**: PDF creation with template rendering
- **Preview System**: Real-time template preview with sample data

## Components and Interfaces

### Database Schema

#### Certificate Templates
```sql
certificate_templates:
- id (primary key)
- name (string, required)
- description (text, nullable)
- is_published (boolean, default false)
- is_active (boolean, default true)
- version (string, default '1.0')
- page_settings (json) // size, orientation, margins
- header_settings (json) // logo, header content
- footer_settings (json) // footer content, page numbers
- created_by (foreign key to users)
- timestamps
```

#### Template Sections
```sql
certificate_template_sections:
- id (primary key)
- certificate_template_id (foreign key)
- parent_section_id (foreign key, nullable) // for subsections
- title (string, required)
- description (text, nullable)
- sort_order (integer)
- is_collapsible (boolean, default false)
- styling_options (json) // background, borders, spacing
- timestamps
```

#### Template Elements
```sql
certificate_template_elements:
- id (primary key)
- certificate_template_section_id (foreign key)
- element_type (enum: heading, paragraph, text, strong_text, image, data_field, table, spacer)
- content (text) // static content or rich text
- properties (json) // element-specific properties
- styling (json) // CSS-like styling options
- sort_order (integer)
- is_conditional (boolean, default false)
- conditional_logic (json) // show/hide conditions
- timestamps
```

#### Template Permissions
```sql
certificate_template_permissions:
- id (primary key)
- certificate_template_id (foreign key)
- user_id (foreign key, nullable)
- role_id (foreign key, nullable)
- permission_type (enum: view, edit, publish, generate)
- timestamps
```

### Model Relationships

```php
CertificateTemplate:
- hasMany(CertificateTemplateSection::class)
- hasMany(CertificateTemplatePermission::class)
- belongsTo(User::class, 'created_by')

CertificateTemplateSection:
- belongsTo(CertificateTemplate::class)
- belongsTo(CertificateTemplateSection::class, 'parent_section_id') // parent
- hasMany(CertificateTemplateSection::class, 'parent_section_id') // children
- hasMany(CertificateTemplateElement::class)

CertificateTemplateElement:
- belongsTo(CertificateTemplateSection::class)
```

### Content Element Types

#### Heading Element
```json
{
  "element_type": "heading",
  "properties": {
    "level": "h1|h2|h3|h4|h5|h6",
    "text": "Heading text with {{placeholders}}",
    "alignment": "left|center|right|justify"
  },
  "styling": {
    "font_size": "24px",
    "font_weight": "bold",
    "color": "#000000",
    "margin_bottom": "16px"
  }
}
```

#### Paragraph Element (Rich Text)
```json
{
  "element_type": "paragraph",
  "content": "<p>Rich HTML content with <strong>formatting</strong> and {{field_placeholders}}</p>",
  "properties": {
    "editor_type": "tinymce"
  },
  "styling": {
    "line_height": "1.5",
    "text_align": "justify"
  }
}
```

#### Data Field Element
```json
{
  "element_type": "data_field",
  "properties": {
    "field_source": "submission_form",
    "field_name": "client_name",
    "display_label": "Client Name:",
    "default_value": "N/A",
    "format_type": "text|number|date|currency"
  }
}
```

#### Image Element
```json
{
  "element_type": "image",
  "properties": {
    "image_path": "/storage/templates/logo.png",
    "alt_text": "Company Logo",
    "width": "200px",
    "height": "auto",
    "alignment": "center"
  }
}
```

#### Table Element
```json
{
  "element_type": "table",
  "properties": {
    "headers": ["Test Parameter", "Result", "Unit", "Specification"],
    "data_source": "submission_form_elements",
    "filter_criteria": {"element_type": "test_result"},
    "show_borders": true,
    "alternate_rows": true
  }
}
```

### Template Builder Interface

#### JavaScript Structure
```
TemplateBuilder/
├── js/
│   ├── template-builder.js (main builder logic)
│   ├── element-manager.js (element CRUD operations)
│   ├── section-manager.js (section management)
│   ├── data-injection.js (placeholder handling)
│   └── preview-manager.js (real-time preview)
├── Blade Templates/
│   ├── builder.blade.php (main builder view)
│   ├── partials/section-builder.blade.php
│   ├── partials/element-builder.blade.php
│   └── modals/ (various modal templates)
```

#### Drag and Drop Implementation
- Use SortableJS for section and element reordering
- jQuery event handlers for element interactions
- AJAX calls for real-time saving
- Bootstrap modals for element configuration
- Visual feedback during drag operations
- Constraint validation (e.g., max nesting levels)

### Data Injection System

#### Placeholder Format
```
Standard Format: {{field_name}}
Nested Format: {{submission.field_name}}
Conditional Format: {{field_name|default:"N/A"}}
Formatted: {{date_field|format:"Y-m-d"}}
```

#### Injection Engine (PHP 7.4 Compatible)
```php
class DataInjectionEngine
{
    public function injectData($content, SubmissionFormInstance $submission)
    {
        // Parse placeholders
        // Retrieve submission data
        // Apply formatting and defaults
        // Return processed content
    }
    
    public function getAvailableFields(SubmissionForm $form)
    {
        // Return list of available fields for picker
    }
}
```

## Data Models

### Template Configuration
```php
class CertificateTemplate extends Model
{
    protected $fillable = [
        'name', 'description', 'is_published', 'is_active', 
        'version', 'page_settings', 'header_settings', 
        'footer_settings', 'created_by'
    ];
    
    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'page_settings' => 'array',
        'header_settings' => 'array',
        'footer_settings' => 'array'
    ];
}
```

### Page Settings Structure
```json
{
  "page_size": "A4|Letter|Legal|Custom",
  "orientation": "portrait|landscape",
  "margins": {
    "top": "20mm",
    "right": "15mm",
    "bottom": "20mm",
    "left": "15mm"
  },
  "custom_dimensions": {
    "width": "210mm",
    "height": "297mm"
  }
}
```

### Header/Footer Settings
```json
{
  "enabled": true,
  "content": "<p>Company Name - Certificate of Analysis</p>",
  "height": "30mm",
  "show_logo": true,
  "logo_position": "left|center|right",
  "show_page_numbers": true,
  "page_number_format": "Page {current} of {total}"
}
```

## Error Handling

### Template Validation
- Section hierarchy validation (max 3 levels)
- Element type validation
- Required field validation
- Circular reference detection in conditional logic

### Data Injection Errors
- Missing field handling with default values
- Invalid placeholder format detection
- Type conversion errors with fallbacks
- Conditional logic evaluation errors

### Report Generation Errors
- PDF generation failures with retry logic
- Template rendering errors with detailed logging
- File storage errors with alternative storage options
- Memory limit handling for large reports

## Testing Strategy

### Unit Tests
- Model relationships and business logic
- Data injection engine functionality
- Template validation rules
- Permission checking logic

### Integration Tests
- Template CRUD operations
- Report generation workflow
- File upload and storage
- PDF generation with various templates

### Frontend Tests
- jQuery-based drag and drop functionality
- Element property editing with Bootstrap modals
- Real-time preview updates via AJAX
- Form validation and error handling

### End-to-End Tests
- Complete template creation workflow
- Report generation from template to PDF
- Permission-based access control
- Multi-user template collaboration

### Performance Tests
- Large template rendering performance
- Concurrent report generation
- Memory usage during PDF creation
- Database query optimization validation

## Security Considerations

### Access Control
- Role-based permissions for template operations
- Template ownership validation
- Secure file upload with type validation
- XSS prevention in rich text content

### Data Protection
- Sanitization of user input in templates
- Secure handling of submission form data
- Audit logging for template modifications
- Backup and recovery procedures

### File Security
- Secure storage of uploaded images
- Path traversal prevention
- File type validation and scanning
- Access control for generated PDFs