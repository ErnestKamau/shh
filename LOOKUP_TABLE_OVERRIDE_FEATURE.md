# Lookup Table Override Feature

## Overview
This feature allows users to dynamically change the lookup table used in formula worksheets without modifying the underlying formula configuration. This is useful when you need to use a different set of reference values for specific samples while maintaining the same formula structure.

## How It Works

### Accessing the Feature
1. Navigate to a batch worksheet: **Sample Workflow → [Batch] → Worksheets**
2. Select a formula tab that contains lookup steps
3. Look for the swap icon (↔) button next to lookup step fields

### Changing a Lookup Table

1. **Click the swap button** next to any lookup field in the worksheet
2. A modal will appear showing:
   - **Formula Default**: The original lookup table configured in the formula
   - **Compatible Tables**: List of alternative lookup tables you can use
   
3. **Select a replacement** lookup table from the list
   - Only compatible tables are shown (same type and structure)
   - Tables must be active to appear in the list

4. **Click "Apply"** to use the selected lookup table
   - The formula will recalculate immediately
   - Results will update in real-time
   - Changes are auto-saved

### Visual Indicators
- **Warning icon (⚠)**: Appears when a lookup table has been overridden
- **Yellow text**: Shows the name of the currently active lookup table
- **Original formula**: Not modified (override is worksheet-specific)

### Resetting to Default
1. Click the swap button on an overridden lookup field
2. Click **"Reset to Default"** button in the modal
3. The formula will revert to using the original configured lookup table

## Compatibility Requirements

For a lookup table to be compatible, it must have:
- ✅ Same **lookup type** (Range-Based or Key-Value Comparison)
- ✅ Same **key structure** (for Key-Value tables)
- ✅ **Active** status
- ✅ Same **field names**

### Range-Based Tables
- Must be marked as `range_based` type
- Use numeric ranges (low/high values)
- Example: CFU count ranges → interpretation

### Key-Value Tables  
- Use exact key matching
- Key columns must match exactly (e.g., `organism`, `medium`)
- Example: organism + medium → result interpretation

## Use Cases

### 1. Different Standards
Use different reference standards for different samples in the same batch:
- Sample 1-5: Use "ISO Standard 2020"
- Sample 6-10: Use "ISO Standard 2023"

### 2. Client-Specific References
Apply client-specific interpretation tables:
- Client A samples: Use "Client A Reference Ranges"
- Client B samples: Use "Client B Reference Ranges"

### 3. Seasonal Variations
Switch between seasonal reference tables:
- Summer samples: Use "Summer Reference Values"
- Winter samples: Use "Winter Reference Values"

## Technical Details

### Database Storage
- Override stored in: `sample_worksheet_formular_step_data.overridden_lookup_table_id`
- Original formula: Unchanged in `formula_steps.lookup_config`
- Worksheet-specific: Each row can have different overrides

### Calculation Flow
1. User enters input values
2. System checks for lookup overrides
3. Uses overridden table if exists, otherwise uses formula default
4. Calculates and displays results
5. Auto-saves on blur

### Audit Trail
All lookup table changes are logged with:
- Captured result ID
- Formula step ID
- Original lookup table ID
- New lookup table ID
- Timestamp

## Important Notes

⚠️ **Worksheet-Only Changes**: Overrides only affect the current worksheet instance. The formula configuration remains unchanged.

⚠️ **Compatibility Check**: The system prevents selecting incompatible lookup tables. If no compatible tables exist, the swap button will inform you.

⚠️ **Auto-Save**: Changes are automatically saved when you click "Apply" and when the worksheet field loses focus.

## Troubleshooting

### "No compatible lookup tables found"
- Create a new lookup table with the same structure
- Ensure the lookup table is marked as active
- Verify key columns match exactly (for key-value tables)

### Override not persisting
- Check that auto-save is working (watch for success message)
- Verify database migration has run successfully
- Check browser console for JavaScript errors

### Calculation not updating
- Ensure all required input fields have values
- Check that the selected lookup table has matching entries
- Review application logs for calculation errors

## Related Files
- Component: `app/Livewire/Worksheets/FormulaWorksheet.php`
- View: `resources/views/livewire/worksheets/formula-worksheet.blade.php`
- Evaluator: `app/Services/Formulars/FormulaEvaluator.php`
- Lookup Service: `app/Services/Formulars/LookupService.php`
- Migration: `database/migrations/2025_10_18_201340_add_overridden_lookup_table_id_to_sample_worksheet_formular_step_data_table.php`

