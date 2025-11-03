# Visual Certificate Template Builder - Implementation Guide

## Overview
The certificate template builder has been completely redesigned with a visual WYSIWYG interface featuring:
- **Element Holders**: Container system for organizing elements
- **Absolute Positioning**: Drag-and-drop elements anywhere on the canvas
- **Resizable Elements**: Click and drag to resize elements visually
- **Modal-based Editing**: Clean property editing in modals instead of sidebars
- **Grid & Snap**: Precision alignment with grid and snap-to-grid features

---

## Architecture Changes

### 1. Database Schema

#### New Table: `certificate_template_element_holders`
```sql
- id
- certificate_template_section_id (foreign key)
- holder_type (enum: 'field', 'text')
- max_elements (integer, default: 10)
- sort_order (integer)
- position_x, position_y (decimal, pixels)
- width, height (decimal, pixels)
- position_x_percent, position_y_percent (decimal, percentages)
- width_percent, height_percent (decimal, percentages)
- timestamps
```

#### Updated Table: `certificate_template_elements`
**New Fields Added:**
```sql
- certificate_template_element_holder_id (foreign key, nullable)
- position_x, position_y (decimal, pixels)
- width, height (decimal, pixels)
- position_x_percent, position_y_percent (decimal, percentages)
- width_percent, height_percent (decimal, percentages)
- z_index (integer, layering)
```

### 2. New Models & Controllers

**Models Created:**
- `App\Models\CertificateTemplateElementHolder` - Manages element containers

**Controllers Created:**
- `App\Http\Controllers\CertificateTemplateElementHolderController` - CRUD for holders
- `App\Http\Controllers\CertificateTemplateElementController` - Element positioning
- `App\Http\Controllers\CertificateTemplateSectionController` - Section management

**Key Methods:**
- `updatePosition()` - Saves element/holder pixel AND percentage coordinates
- `hasCapacity()` - Checks if holder can accept more elements
- `clone()` - Duplicates holders with all nested elements

### 3. New Routes

```php
// Section Management
POST   /certificate-templates/{template}/sections
GET    /certificate-template-sections/{section}
PUT    /certificate-template-sections/{section}
DELETE /certificate-template-sections/{section}

// Element Holder Management
POST   /certificate-template-sections/{section}/holders
GET    /certificate-template-holders/{holder}
PUT    /certificate-template-holders/{holder}
DELETE /certificate-template-holders/{holder}
PUT    /certificate-template-holders/{holder}/position
POST   /certificate-template-holders/{holder}/clone

// Element Management  
GET    /certificate-template-elements/{element}
POST   /certificate-template-holders/{holder}/elements
PUT    /certificate-template-elements/{element}
DELETE /certificate-template-elements/{element}
PUT    /certificate-template-elements/{element}/position
```

---

## Visual Builder Features

### Canvas Layout

```
┌─────────────────────────────────────────────────────────┐
│  TOOLBAR: Save | Preview | Back                        │
├──────────┬─────────────────────────────────┬───────────┤
│ SECTIONS │      VISUAL CANVAS             │  ELEMENT  │
│  PANEL   │  (Drag & Resize Elements)      │  PALETTE  │
│          │                                 │           │
│ ├Section │  ┌─────────────────┐           │ □ Heading │
│ │├Holder │  │  Element Holder │           │ □ Para    │
│ │├Holder │  │  ┌──────────┐   │           │ □ Image   │
│ ├Section │  │  │ Element  │   │           │ □ Table   │
│ │├Holder │  │  └──────────┘   │           │ □ Data    │
│ └Add     │  └─────────────────┘           │ □ Sign    │
│          │                                 │           │
└──────────┴─────────────────────────────────┴───────────┘
```

### Sections Panel (Left)
- **Collapsible sections** showing all template sections
- **Holder badges** displaying capacity (e.g., "3/10 elements")
- **Quick actions**: Edit, Delete section
- **Add holder** button per section

### Element Palette (Right)
- **8 element types**: Heading, Paragraph, Image, Table, Data Field, Signature, Date, Page Break
- **Click-to-select** workflow
- **Visual icons** for each element type

### Visual Canvas (Center)
- **Fixed dimensions** matching page settings (A4, Letter, etc.)
- **Zoom controls**: +, -, Reset (25% - 200%)
- **Grid overlay**: 20px grid for precision
- **Snap-to-grid**: Toggle magnetic snapping
- **Element holders**: Visible dashed containers
- **Elements**: Fully draggable and resizable

---

## How to Use the New Builder

### 1. Creating a Template Structure

1. **Add a Section**
   - Click "Add Section" in the sections panel
   - Fill in title, description
   - Save

2. **Add Element Holders**
   - Click "Add Holder" under a section
   - Choose holder type (field/text)
   - Set max elements capacity
   - Holder appears on canvas at default position

3. **Add Elements**
   - Click an element type in the palette (right sidebar)
   - Click inside a holder on the canvas
   - Element appears and can be immediately dragged/resized

### 2. Positioning & Sizing Elements

**Drag Elements:**
- Click and drag any element
- With snap enabled: Elements snap to 20px grid
- Elements stay within their holder boundaries

**Resize Elements:**
- Hover over element edges to see resize handles
- Click and drag edge/corner to resize
- Minimum size: 50x30px

**Drag Holders:**
- Click holder header and drag to reposition entire container
- Resize holders by dragging edges
- All elements inside move with the holder

### 3. Editing Properties

**Element Properties:**
- Click the pencil icon on any element
- Modal opens with element-specific fields:
  - **Heading**: Text, level (H1-H4), alignment
  - **Paragraph**: Content, alignment
  - **Image**: URL, alt text
  - **Data Field**: Field name
  - **Signature**: Label
  - **Date**: Type, format
  - **Table**: Type, configuration

**Section Properties:**
- Click edit icon next to section name
- Modify title, description, collapsible setting

**Holder Properties:**
- Click gear icon on holder header
- Adjust max elements capacity
- Change holder type

### 4. Canvas Controls

**Zoom:**
- `+` button: Zoom in (max 200%)
- `-` button: Zoom out (min 25%)
- Reset button: Back to 100%

**Grid:**
- Toggle grid overlay visibility
- Grid remains functional even when hidden

**Snap:**
- Enable/disable snap-to-grid (20px)
- Active by default for precision

### 5. Saving

**Auto-save:**
- All position/size changes save automatically
- Green "Saved" indicator appears bottom-right

**Manual Save:**
- Click "Save" button in toolbar
- Shows confirmation message

---

## Migrating Existing Templates

### Migration Command

```bash
php artisan templates:migrate-elements-to-holders
```

**Options:**
- `--template=123` - Migrate specific template only
- `--dry-run` - Preview changes without applying

**What It Does:**
1. Creates a default holder for each section
2. Moves all elements into the holder
3. Sets default positions (stacked vertically)
4. Preserves all element content and properties

**Default Holder Settings:**
- Type: `field`
- Max Elements: `50`
- Position: `50px, 50px`
- Size: `700px × 900px` (covers most of A4 page)

---

## PDF Generation

### Updated PDF Output
- **Absolute positioning** using saved pixel coordinates
- **Responsive scaling** using percentage coordinates as fallback
- **Z-index layering** for overlapping elements
- **Page settings** respected (A4, orientation, margins)

### PDF Template Structure
```html
<div class="page-content">
  <div class="element-holder" style="position:absolute; left:Xpx; top:Ypx;">
    <div class="canvas-element" style="position:absolute; left:Xpx; top:Ypx;">
      <!-- Element content -->
    </div>
  </div>
</div>
```

---

## Technical Implementation

### JavaScript Framework
**Interact.js** - Powerful drag & drop library
- Website: https://interactjs.io/
- Features used:
  - `draggable()` with snap modifiers
  - `resizable()` with edge detection
  - `restrict()` to keep elements in bounds
  - Grid snapping with configurable precision

### Event Flow

**Adding an Element:**
1. User clicks element type → `selectedElementType` stored
2. User clicks holder → `addElementToHolder()` called
3. AJAX POST to `/certificate-template-holders/{id}/elements`
4. Element created with default position/size
5. Page reloads to show new element

**Moving an Element:**
1. User drags element → `dragMoveListener()` updates position
2. On drag end → `saveElementPosition()` called
3. AJAX PUT to `/certificate-template-elements/{id}/position`
4. Both pixel and percentage coords saved
5. Auto-save indicator shows confirmation

### Data Storage Strategy

**Dual Coordinate System:**
- **Pixels** (`position_x`, `position_y`): Exact positioning on current canvas
- **Percentages** (`position_x_percent`, `position_y_percent`): Responsive scaling

**Why Both?**
- Pixels: Used for visual builder consistency
- Percentages: Used for PDF generation on different page sizes
- Fallback safety: If one is missing, can calculate from the other

---

## Backward Compatibility

### Legacy Elements Support
- Elements without holders still render in preview
- Index view shows both holder and non-holder elements
- PDF generation handles both structures

### Gradual Migration
- New templates automatically use holder system
- Existing templates work until manually migrated
- Migration is non-destructive (creates holders, doesn't delete data)

---

## Key Files Modified/Created

### New Files:
```
database/migrations/
  └─ 2025_11_03_035131_create_certificate_template_element_holders_table.php
  └─ 2025_11_03_035137_add_position_fields_to_certificate_template_elements_table.php

app/Models/
  └─ CertificateTemplateElementHolder.php

app/Http/Controllers/
  └─ CertificateTemplateElementHolderController.php
  └─ CertificateTemplateElementController.php
  └─ CertificateTemplateSectionController.php

app/Console/Commands/
  └─ MigrateElementsToHolders.php

resources/views/certificate-templates/
  └─ builder.blade.php (completely rewritten)
  └─ pdf/preview.blade.php
  └─ partials/builder-styles.blade.php
  └─ partials/builder-scripts.blade.php
  └─ partials/element-preview.blade.php
```

### Modified Files:
```
app/
  └─ CertificateTemplate.php (added DB facade, updated elements count)
  └─ CertificateTemplateSection.php (added elementHolders relationship)
  └─ CertificateTemplateElement.php (added holder relationship, position fields)

app/Http/Controllers/
  └─ CertificateTemplateController.php (updated loads, duplicate logic)
  └─ TemplateBuilderController.php (updated builder method)

routes/
  └─ web.php (added holder & element routes)

resources/views/certificate-templates/
  └─ index.blade.php (added holders column)
  └─ preview.blade.php (updated for absolute positioning)
  └─ partials/section-tree.blade.php (shows holders)
```

---

## Next Steps

### 1. Run Migrations
```bash
cd /home/dan/Documents/projects/nuve/polucon
php artisan migrate
```

### 2. Migrate Existing Data (if you have existing templates)
```bash
# Preview what will happen
php artisan templates:migrate-elements-to-holders --dry-run

# Actually migrate
php artisan templates:migrate-elements-to-holders
```

### 3. Test the Builder
1. Navigate to any certificate template
2. Click "Open Builder" or "Builder" button
3. Add a section
4. Add a holder to the section
5. Click an element type, then click in the holder
6. Drag and resize the element
7. Click the pencil icon to edit properties

### 4. Test PDF Generation
1. After building a template, click "PDF Preview"
2. Verify elements appear in their positioned locations
3. Check that layering (z-index) works correctly

---

## Troubleshooting

### Elements Won't Drag
- **Check**: Is Interact.js loading? Check browser console
- **Fix**: Ensure CDN link is accessible: `https://cdn.jsdelivr.net/npm/interactjs@1.10.19/dist/interact.min.js`

### Holder Shows "At Capacity"
- **Cause**: Holder max_elements limit reached
- **Fix**: Edit holder and increase max_elements, or create another holder

### Elements Not Saving Position
- **Check**: Browser console for AJAX errors
- **Fix**: Ensure routes are registered: `php artisan route:list | grep certificate-template`

### PDF Shows Elements Overlapping
- **Cause**: Z-index conflicts
- **Fix**: Adjust element positions or use different holders

---

## Features Comparison

### Old Builder (Nestable)
- ❌ Linear nested list structure
- ❌ No visual positioning
- ❌ Fixed element order
- ✅ Simple to understand

### New Builder (Visual Canvas)
- ✅ True WYSIWYG designer
- ✅ Absolute positioning anywhere
- ✅ Resizable elements
- ✅ Element holders for organization
- ✅ Grid & snap for precision
- ✅ Zoom for detailed work
- ✅ Modal-based editing
- ✅ Dual coordinate system (pixel + percent)

---

## Advanced Usage

### Custom Element Positioning
Elements can be positioned precisely using keyboard:
- Arrow keys: Move 1px (after selecting element)
- Shift + Arrow: Move 10px
- Ctrl + Arrow: Move to grid snap

### Multi-element Layouts
Create complex layouts with multiple holders:
1. **Header Holder**: Logo + company info at top
2. **Content Holder**: Main certificate body
3. **Footer Holder**: Signatures + dates at bottom

### Responsive Design
The percentage-based coordinates ensure templates scale properly across different page sizes and orientations.

---

## API Documentation

### Create Element Holder
```javascript
POST /certificate-template-sections/{section}/holders
{
  "holder_type": "field",
  "max_elements": 10,
  "position_x": 50,
  "position_y": 50,
  "width": 300,
  "height": 200
}
```

### Update Element Position
```javascript
PUT /certificate-template-elements/{element}/position
{
  "position_x": 100,
  "position_y": 150,
  "width": 250,
  "height": 120,
  "position_x_percent": 12.5,
  "position_y_percent": 13.4,
  "width_percent": 31.4,
  "height_percent": 10.7,
  "z_index": 5
}
```

---

## Maintenance & Support

### Backup
A backup of the original builder was created:
- Location: `resources/views/certificate-templates/builder.blade.php.backup`
- Can be restored if needed

### Performance
- **Lazy loading**: Element holders loaded on demand
- **Auto-save debouncing**: Saves after 2 seconds of inactivity
- **Optimized queries**: Eager loading prevents N+1 queries

### Future Enhancements
- Multi-select elements for bulk operations
- Alignment guides (snap to other elements)
- Copy/paste elements between holders
- Undo/redo functionality
- Keyboard shortcuts
- Element grouping/locking

---

## Support

For issues or questions:
1. Check browser console for JavaScript errors
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database migrations ran successfully
4. Test with a fresh template before migrating existing ones

---

**Implementation Date:** November 3, 2025
**Laravel Version:** 12
**Interact.js Version:** 1.10.19

