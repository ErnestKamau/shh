# Custom Form Elements

This document describes the new custom form elements added to the submission form builder.

## New Custom Elements

### 1. Client Select
- **Type**: `client_select`
- **Description**: Dropdown that lists all active CRM customers
- **Data Source**: `crm_customers` table where `active = 1`
- **Icon**: Account Group
- **Usage**: Use this to allow users to select a client/customer

### 2. Sample Type Select
- **Type**: `sample_type_select`
- **Description**: Dropdown that lists all available sample types
- **Data Source**: `sample_types` table
- **Icon**: Test Tube
- **Usage**: Use this to allow users to select a sample type

### 3. Client Unit Select
- **Type**: `client_unit_select`
- **Description**: Dropdown that lists units belonging to the selected client
- **Data Source**: `crm_company_units` table filtered by `crm_customer_id`
- **Icon**: Office Building
- **Dependencies**: Requires a Client Select element in the same form
- **Usage**: Use this to allow users to select a specific unit within a client organization

### 4. Client Contact Select
- **Type**: `client_contact_select`
- **Description**: Dropdown that lists contacts belonging to the selected client
- **Data Source**: `crm_customer_contacts` table filtered by `crm_customer_id`
- **Icon**: Account Multiple
- **Dependencies**: Requires a Client Select element in the same form
- **Usage**: Use this to allow users to select a specific contact person for a client

## How It Works

### Dynamic Loading
- Client Select and Sample Type Select load their options immediately when the form loads
- Client Unit Select and Client Contact Select load their options when a client is selected
- All options are loaded via AJAX calls to `/submission-forms/dynamic-options`

### Dependencies
- Client Unit Select and Client Contact Select automatically detect Client Select elements in the same form
- When the client selection changes, dependent dropdowns are updated automatically
- If no client is selected, dependent dropdowns show only a placeholder option

### Form Builder Integration
- All custom elements appear in the element types panel in the form builder
- They can be dragged and configured like standard elements
- Options are not manually configurable (they're loaded dynamically from the database)
- Standard properties like label, placeholder, required, readonly, and help text are supported

### API Endpoint
The dynamic options are loaded from:
```
GET /submission-forms/dynamic-options?element_type={type}&client_id={id}
```

Parameters:
- `element_type`: The type of element (client_select, sample_type_select, etc.)
- `client_id`: Required for client_unit_select and client_contact_select

Response format:
```json
{
  "options": [
    {
      "value": "1",
      "label": "Client Name"
    }
  ]
}
```

## Usage Examples

### Basic Client Selection Form
1. Add a Client Select element
2. Add a Sample Type Select element
3. Users can select both independently

### Complete Client Information Form
1. Add a Client Select element (required)
2. Add a Client Unit Select element
3. Add a Client Contact Select element
4. Users select client first, then unit and contact options become available

## Technical Implementation

### Models Updated
- `SubmissionFormElement`: Added support for new element types in validation and option handling
- Added dynamic option loading methods

### Controllers Updated
- `SubmissionFormController`: Added `getDynamicOptions()` method

### Views Updated
- `builder.blade.php`: Added new element types to the builder interface
- `form-element.blade.php`: Added rendering support for custom elements with AJAX loading

### Routes Added
- `GET /submission-forms/dynamic-options`: Endpoint for loading dynamic options

### JavaScript Features
- Automatic detection of client select elements
- Dynamic loading of dependent options
- Error handling for failed AJAX requests
- Loading states for better user experience