# Lookup Step Type Implementation Guide

## Overview

This document explains how the lookup step type works in the Formula Step Editor, specifically focusing on the Key Configuration feature that was recently fixed.

---

## What is a Lookup Step?

A **lookup step** allows you to retrieve values from a predefined lookup table based on one or more key expressions. This is useful when you need to map input values to standardized outputs, reference tables, or conversion charts.

### Example Use Cases:
- Temperature conversion tables
- pH to acidity conversion
- Bacterial count to risk level mapping
- Equipment calibration correction factors

---

## How Lookup Steps Work

### 1. Backend Data Structure

The lookup table is stored in the database with the following structure:

```php
LookupTable {
    id: 1,
    name: "Temperature Conversion",
    key_columns: ['celsius'],           // Array of column names used as keys
    value_column: 'fahrenheit',         // Column name containing the result
    key_label: 'Temperature (°C)',      // Optional: Human-readable label for keys
    value_label: 'Temperature (°F)',    // Optional: Human-readable label for value
    is_active: true
}

LookupTableEntry {
    lookup_table_id: 1,
    key_values: {
        'celsius': '0'
    },
    result_value: '32'
}
```

### 2. Formula Step Configuration

When you create or edit a lookup step, the configuration is stored as:

```php
FormulaStep {
    step_type: 'lookup',
    variable_name: 'temp_f',
    label: 'Temperature in Fahrenheit',
    lookup_config: {
        lookup_table_id: 1,
        key_expressions: {
            'celsius': 'temp_c'  // Variable from previous step or direct value
        },
        key_values: []
    }
}
```

### 3. Execution Flow

When the formula executes:

1. **Expression Evaluation**: Each key expression is evaluated
   - `'temp_c'` → looks up the value from previous steps
   - `'25'` → uses direct value
   
2. **Table Lookup**: The evaluated keys are used to find a matching row in the lookup table

3. **Result Return**: The value from the `value_column` is returned and assigned to the step's variable

---

## Key Configuration Interface

### Components

The Key Configuration section appears when you:
1. Select "Lookup" as the step type
2. Choose a lookup table

For each key column in the selected table, you get:

#### Input Field (Left Column - 8/12 width)
- **Purpose**: Enter the expression that will provide the key value
- **Accepts**: 
  - Variable names from previous steps (e.g., `temperature`, `pressure`)
  - Direct values (e.g., `25`, `100`)
  - Global constants (e.g., `PI`, `STANDARD_TEMP`)
- **Wire Model**: `wire:model="lookupConfig.key_expressions.{column_name}"`
- **CSS Classes**: `form-control lookup-key-input`
- **Attributes**:
  - `id="lookup-key-{column_name}-{create|edit}"` - Unique identifier
  - `data-key="{column_name}"` - Stores the column name

#### Quick Select Dropdown (Right Column - 4/12 width)
- **Purpose**: Quickly insert variable names without typing
- **Populated With**:
  - Available variables from previous steps
  - Global constants
- **CSS Classes**: `form-select form-select-sm modern-select-sm quick-select-variable`
- **Attributes**:
  - `data-target-input="lookup-key-{column_name}-{create|edit}"` - Links to input field

---

## How Quick Select Works

### Old Implementation (❌ Broken)

The old code used inline event handlers:

```html
<select onchange="document.querySelector('input[wire\\:model=\"lookupConfig.key_expressions.{{ $key }}\"]').value = this.value">
```

**Problems:**
- Complex querySelector with escaped characters
- Blade interpolation (`{{ $key }}`) could contain special characters
- Browser popup controller conflicts
- Caused: `SyntaxError: Failed to execute 'setValueAndClosePopup'`

### New Implementation (✅ Fixed)

The new code uses data-attributes and event delegation:

```html
<!-- Input Field -->
<input type="text" 
       wire:model="lookupConfig.key_expressions.{{ $key }}" 
       class="form-control lookup-key-input" 
       id="lookup-key-{{ $key }}-create"
       data-key="{{ $key }}">

<!-- Quick Select -->
<select class="form-select form-select-sm modern-select-sm quick-select-variable" 
        data-target-input="lookup-key-{{ $key }}-create">
```

**JavaScript Handler:**

```javascript
document.addEventListener('change', function(e) {
    if (e.target.matches('.quick-select-variable')) {
        // Get the target input ID from data attribute
        const targetInputId = e.target.getAttribute('data-target-input');
        const input = document.getElementById(targetInputId);
        
        if (input && e.target.value) {
            // Set the value
            input.value = e.target.value;
            
            // Trigger Livewire update
            input.dispatchEvent(new Event('input', { bubbles: true }));
            
            // Add visual feedback (green flash)
            input.style.borderColor = '#10b981';
            input.style.backgroundColor = '#f0fdf4';
            input.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                input.style.borderColor = '';
                input.style.backgroundColor = '';
            }, 1000);
            
            // Reset the select dropdown
            e.target.value = '';
        }
    }
});
```

**Advantages:**
- ✅ No complex selectors
- ✅ No escaping issues
- ✅ Works with any key column name
- ✅ Clean separation of concerns
- ✅ Visual feedback on selection

---

## User Experience Flow

### Creating a Lookup Step

1. **Click "Add Step"** button
2. **Select Step Type**: Choose "Lookup" from dropdown
3. **Choose Lookup Table**: Select from available tables
4. **Configure Keys**: For each key column:
   - Either type directly in the input field, OR
   - Use Quick Select dropdown to choose a variable
   - When selected, the variable name appears with green flash feedback
5. **Add Label & Description**
6. **Save**: The step is created with the configuration

### Using the Step

When the formula executes:
- The key expressions are evaluated with current variable values
- The lookup table is queried with these keys
- The result is assigned to the step's variable name
- Subsequent steps can use this variable

---

## Visual Styling

### Modern Quick Select Dropdown

The quick select uses a modern, polished design:

```css
.modern-select-sm {
    /* Gradient background */
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    
    /* Custom dropdown arrow */
    background-image: url("data:image/svg+xml,...");
    
    /* Smooth transitions */
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    
    /* Subtle shadow */
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.modern-select-sm:hover {
    /* Blue border on hover */
    border-color: #3b82f6;
    
    /* Lift effect */
    transform: translateY(-1px);
    
    /* Enhanced shadow */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
```

### Visual Feedback

When a variable is selected:
1. **Green border** (`#10b981`) appears
2. **Light green background** (`#f0fdf4`) fills the input
3. **Smooth transition** over 300ms
4. **Revert to normal** after 1 second

---

## Backend Methods

### Key Livewire Methods

#### `updatedLookupTableId()`
```php
public function updatedLookupTableId()
{
    if ($this->lookupTableId) {
        $lookupTable = LookupTable::find($this->lookupTableId);
        if ($lookupTable) {
            // Initialize empty expressions for each key column
            $this->lookupConfig = [
                'key_expressions' => array_fill_keys($lookupTable->key_columns, ''),
                'key_values' => array_fill_keys($lookupTable->key_columns, ''),
            ];
        }
    }
}
```

**Purpose**: Automatically creates empty slots for each key column when a table is selected.

#### `createStep()` / `updateStep()`
```php
// Prepare lookup config for lookup steps
$lookupConfig = [];
if ($this->stepType === 'lookup' && $this->lookupTableId) {
    $lookupConfig = [
        'lookup_table_id' => $this->lookupTableId,
        'key_expressions' => $this->lookupConfig['key_expressions'] ?? [],
        'key_values' => $this->lookupConfig['key_values'] ?? [],
    ];
}

FormulaStep::create([
    // ...
    'lookup_config' => $lookupConfig,
]);
```

**Purpose**: Saves the lookup configuration to the database.

---

## Troubleshooting

### Common Issues

#### 1. "No variables available"
**Cause**: No previous steps exist, or current step number is 1
**Solution**: Create input steps first, then use lookup steps

#### 2. "Lookup returns null"
**Cause**: No matching entry in lookup table for given keys
**Solution**: Verify lookup table has entries matching your key values

#### 3. "Expression not evaluating"
**Cause**: Variable name doesn't exist or typo
**Solution**: Use Quick Select to ensure correct variable names

---

## Best Practices

1. **Name Variables Clearly**: Use descriptive names like `temp_celsius` not `t1`
2. **Create Input Steps First**: Lookup steps need variables to reference
3. **Test Lookup Tables**: Ensure your lookup tables have complete data
4. **Use Quick Select**: Reduces typos and shows available variables
5. **Add Descriptions**: Help others understand what each step does

---

## Technical Notes

- Lookup config is stored as JSON in the database
- Key expressions support any valid expression syntax
- Multiple lookup steps can reference the same table
- Lookup tables can have multiple key columns for complex lookups
- The Quick Select dropdown auto-resets after selection
- Visual feedback provides confirmation without being intrusive

---

## Files Modified

1. **View**: `resources/views/livewire/formulars/formula-step-editor.blade.php`
   - Lines 523-546: Create modal lookup configuration
   - Lines 814-837: Edit modal lookup configuration
   - Lines 1390-1450: Modern styling for quick select
   - Lines 1743-1769: JavaScript event handler

2. **Component**: `app/Livewire/Formulars/FormulaStepEditor.php`
   - Lines 440-451: `updatedLookupTableId()` method
   - Lines 194-201: Create step lookup config
   - Lines 240-248: Update step lookup config

---

## Version History

- **v2.0 (Current)**: Data-attribute based Quick Select with modern styling
- **v1.0**: Inline handler implementation (had JavaScript errors)

---

Last Updated: October 13, 2025

