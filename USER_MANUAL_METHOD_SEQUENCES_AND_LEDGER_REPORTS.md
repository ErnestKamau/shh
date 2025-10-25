# Laboratory Information Management System (LIMS) - User Manual

## Method Sequences, Test Stages, Solutions Management & Ledger Reports

**Version:** 2.0  
**Date:** October 23, 2025  
**Application:** QPLUS LIMS

---

## Table of Contents

1. [Introduction](#introduction)
2. [Solutions Management](#solutions-management)
3. [Solutions Monitoring & Test Stages](#solutions-monitoring--test-stages)
4. [Method Sequence Management](#method-sequence-management)
5. [Ledger Reports](#ledger-reports)
6. [Troubleshooting](#troubleshooting)
7. [Best Practices](#best-practices)

---

## Introduction

This manual covers the advanced testing workflow features in QPLUS LIMS, including:
- **Solutions Management**: Inventory management for laboratory media, reagents, and buffers
- **Solution Preparation**: Tracking and documenting solution preparation processes
- **Test Stages & Method Sequences**: Sequential, time-based laboratory test procedures
- **Solutions Monitoring**: Tracking media, controls, and equipment usage during test execution
- **Ledger Reports**: Comprehensive reporting of all test stage activities
- **Consumption Analytics**: Monitoring and forecasting solution usage patterns

These features enable laboratory staff to manage complex, multi-day testing procedures with automated timing, quality control tracking, inventory management, and comprehensive audit trails.

---

## Solutions Management

### Overview

The Solutions Management module provides comprehensive tools for managing laboratory consumables including culture media, reagents, buffers, and controls. This module ensures:
- **Inventory tracking** of all laboratory solutions
- **Batch management** with expiry tracking
- **Consumption monitoring** and analytics
- **Cost analysis** per solution and test
- **Predictive analytics** for stock reordering

### Navigation

```
Main Menu → Lab → Stock Monitoring → [Select Module]
```

Available modules:
1. Categories
2. Stock Management
3. Stock Movement
4. Solution Preparation
5. Batch Management
6. Consumption Analytics
7. Cost Analysis
8. Predictive Analytics

---

### Module 1: Categories Management

**Path:** `Lab → Stock Monitoring → Categories`

#### Purpose
Organize solutions into logical groups for better inventory management and reporting.

#### Features

**Category Attributes:**
- Name
- Description
- Image
- Minimum stock level
- Unit type (ml, L, plates, tubes, etc.)

#### Managing Categories

##### Adding a Category

1. **Navigate** to Categories page
2. **Click** "Add" button (top-right)
3. **Complete** form:
   ```
   Fields:
   - Name: Category name (e.g., "Culture Media", "Stains")
   - Description: Purpose and usage notes
   - Image: Visual identifier (upload file)
   ```
4. **Submit** form

##### Viewing Category Status

**Dashboard Display:**
```
┌─────────────────────────────────────────┐
│ No │ Image │ Name │ Description │ Available │
├─────────────────────────────────────────┤
│  1 │ [img] │ Media│ Culture...  │ 250 ml    │
│    │       │      │             │ (⚠️ if low)│
└─────────────────────────────────────────┘
```

**Stock Level Indicators:**
- ⚠️ **Alert**: Available stock below minimum level
- **Available**: Current stock amount
- **Pending**: Stock in process (preparing/ordering)

##### Editing Categories

1. **Locate** category in list
2. **Click** "Edit" button
3. **Modify** fields as needed
4. **Save** changes

##### Deleting Categories

1. **Click** "Delete" button for category
2. **Confirm** deletion in modal
3. **Note**: Cannot delete categories with active stock items

---

### Module 2: Stock Management

**Path:** `Lab → Stock Monitoring → Stock Management`

#### Purpose
Manage individual solution items within categories, including specifications, rates, and tracking information.

#### Interface Overview

**Enhanced Features:**
- **Advanced filtering** with real-time search
- **Multi-select category** filtering
- **Status indicators** (Active/Inactive)
- **Date range** filtering
- **Loading states** and empty state handling
- **Bulk operations** support

#### Stock Item Attributes

```
Item Properties:
├── Basic Information
│   ├── Name
│   ├── Category
│   ├── Description
│   └── Image
├── Measurement
│   ├── Unit of Measure
│   └── Production Rate
├── Status
│   ├── Active/Inactive
│   └── Created Date
└── Management
    ├── Show URL
    └── Related Batches
```

#### Adding Stock Items

1. **Click** "Add Item" button
2. **Fill** in details:

```
Form Fields:

Name: [Text] *Required
  Example: "Nutrient Agar"

Category: [Dropdown] *Required
  Options: From categories list or "Non Specific"

Description: [Textarea] *Required
  Example: "General purpose culture medium for bacteria"

Image: [File Upload]
  Accepted: image/*

Unit of Measure: [Dropdown] *Required
  Options: ml, L, g, kg, plates, tubes, etc.

Rate: [Number] *Required
  Production/consumption rate
```

3. **Submit** to save

#### Filtering and Search

**Filter Panel:**
```
┌────────────────────────────────────────────────┐
│ 🔍 Filter Options                              │
├────────────────────────────────────────────────┤
│ Search: [____________________________]         │
│ Status: [All Status ▼] Date: [From] [To]     │
│ Category: [Multi-select ▼]                    │
│ Unit: [All Units ▼]                           │
│ [Apply Filters (2)] [Clear Filters]          │
└────────────────────────────────────────────────┘
```

**Real-Time Filtering:**
- **Search**: Instant filter on name/description
- **Debounced**: 300ms delay for performance
- **Multi-criteria**: All filters combine (AND logic)
- **Filter count**: Shows active filter count

**Using Filters:**

1. **Text Search**:
   - Type in search box
   - Filters name and description fields
   - Case-insensitive matching

2. **Status Filter**:
   - All Status (default)
   - Active only
   - Inactive only

3. **Date Range**:
   - From date: Start of range
   - To date: End of range
   - Filters by creation date

4. **Category Filter**:
   - Multi-select dropdown (Select2)
   - Can select multiple categories
   - Shows only items in selected categories

5. **Unit Filter**:
   - Single select dropdown
   - Filter by unit of measure

**Clear Filters:**
- Resets all filters to default
- Shows all items
- Resets page to 1

#### Table Display

**Column Structure:**
```
┌──┬────────┬───────────┬──────────┬──────┬──────┬────────┬────────────┬─────────┐
│No│ Image  │ Name      │ Category │ Rate │ Unit │ Status │Description │ Actions │
├──┼────────┼───────────┼──────────┼──────┼──────┼────────┼────────────┼─────────┤
│1 │ [img]  │ Agar      │ Media    │ 2.5  │ g/L  │🟢Active│ General... │ [Btns]  │
└──┴────────┴───────────┴──────────┴──────┴──────┴────────┴────────────┴─────────┘
```

**Status Badges:**
- 🟢 **Active**: Green badge with checkmark
- 🔴 **Inactive**: Red badge with X

**Action Buttons:**
- **Edit**: Modify item details
- **View**: See item details and batch history
- **Delete**: Remove item (with confirmation)
- **Clone**: Create copy of item

#### Managing Stock Items

##### Editing Items

1. **Click** "Edit" button
2. **Modal** opens with current values
3. **Modify** fields
4. **Submit** to update
5. **Note**: Image optional (keeps existing if blank)

##### Viewing Item Details

1. **Click** "View" button
2. **Redirects** to detail page showing:
   - Full item information
   - Associated batches
   - Stock levels
   - Usage history
   - Consumption metrics

##### Deleting Items

1. **Click** "Delete" button
2. **Confirmation** modal appears
3. **Review** warning message
4. **Confirm** to delete permanently
5. **Note**: Cannot undo this action

##### Cloning Items

**Purpose**: Quickly create similar items

1. **Click** "Clone" button
2. **Confirmation** modal
3. **System** creates copy with:
   - Same name (with "Copy" suffix)
   - Same category, description, rate, unit
   - New ID and creation date

---

### Module 3: Stock Movement

**Path:** `Lab → Stock Monitoring → Stock Movement`

#### Purpose
Track all stock transactions including receipts, issuances, transfers, and adjustments.

#### Movement Types

1. **Receipt**: New stock received
2. **Issue**: Stock dispensed for use
3. **Transfer**: Move between locations
4. **Adjustment**: Inventory corrections
5. **Preparation**: Stock used in solution prep
6. **Testing**: Stock consumed in tests

#### Recording Movements

**Movement Record Contains:**
```
Transaction Data:
├── Type: Receipt/Issue/etc.
├── Item: Which solution
├── Quantity: Amount moved
├── From Location
├── To Location
├── Date/Time: When occurred
├── User: Who recorded
├── Reference: Related document
├── Notes: Additional info
└── Batch Info: Which batch affected
```

#### Viewing Movement History

**Table Columns:**
- Date/Time
- Movement Type
- Item Name
- Quantity
- From → To
- Performed By
- Reference #
- Status

**Filtering Options:**
- Date range
- Movement type
- Item/Category
- Location
- User

---

### Module 4: Solution Preparation Tracking

**Path:** `Lab → Stock Monitoring → Solution Preparation`

#### Purpose
Document and track the step-by-step preparation of laboratory solutions, ensuring consistency and traceability.

#### Preparation Workflow

```
Create → Assign Steps → Execute Steps → Complete → Generate Batch
```

#### Creating New Preparation

1. **Click** "New Preparation" button
2. **Select** solution to prepare
3. **Enter** details:

```
Preparation Form:

Solution: [Dropdown] *Required
  Select from configured solutions

Quantity: [Number] *Required
  Amount to prepare

Unit of Measure: [Dropdown] *Required
  ml, L, g, etc.

Notes: [Textarea]
  Special instructions or observations
```

4. **Submit** to create
5. **System** generates:
   - Unique batch number
   - Preparation record
   - Step checklist (if configured)

#### Preparation Dashboard

**Table View:**
```
┌──┬────────────┬──────────┬──────────┬──────┬─────────┬────────┬─────────┬─────────┐
│No│Batch Number│ Solution │Prepared  │ Date │Quantity │ Status │Progress │ Actions │
├──┼────────────┼──────────┼──────────┼──────┼─────────┼────────┼─────────┼─────────┤
│1 │B-20251023-1│Agar Med. │John Doe  │Oct23 │500 ml   │Preparing│ 60%   │ [Btns]  │
└──┴────────────┴──────────┴──────────┴──────┴─────────┴────────┴─────────┴─────────┘
```

**Status Types:**
- **Preparing** (Yellow): In progress
- **Completed** (Green): Finished successfully
- **Cancelled** (Gray): Preparation stopped
- **Failed** (Red): Unsuccessful preparation

**Progress Indicator:**
- Visual progress bar
- Percentage complete
- "X/Y steps" completed

#### Executing Preparation Steps

1. **Click** "View" on preparation record
2. **Step-by-step checklist** displays
3. **For each step**:
   - Read instructions
   - Perform action
   - Check off when complete
   - Add notes if needed
   - Record actual measurements

**Step Information:**
```
Step Card:
├── Order: Step number
├── Name: Action description
├── Instructions: Detailed procedure
├── Expected Values: Target measurements
├── Actual Values: [Input field]
├── Notes: [Text area]
├── Completed By: User name
└── Timestamp: When completed
```

#### Completing Preparation

1. **All steps** must be checked off
2. **Click** "Complete" button
3. **Enter** final details:

```
Completion Form:

Final Quantity: [Number]
  Actual amount prepared (may differ from planned)

Final Notes: [Textarea]
  Summary, observations, deviations
```

4. **Submit** to finalize
5. **System** actions:
   - Marks preparation complete
   - Generates solution batch
   - Updates stock levels
   - Creates audit trail

---

### Module 5: Batch Management

**Path:** `Lab → Stock Monitoring → Batch Management`

#### Purpose
Track individual batches of prepared solutions with expiry dates, quality control, and consumption monitoring.

#### Batch Attributes

```
Batch Record:
├── Identification
│   ├── Batch Number: Unique ID
│   ├── Solution Name
│   └── Category
├── Dates
│   ├── Preparation Date
│   ├── Expiry Date
│   └── Last Used Date
├── Quantity
│   ├── Initial Amount
│   ├── Current Amount
│   └── Consumed Amount
├── Quality
│   ├── QC Status
│   ├── QC Date
│   └── QC By
└── Status
    ├── Active
    ├── Expired
    ├── Recalled
    └── Consumed
```

#### Adding New Batches

**Manual Entry:**
1. **Click** "New Batch" button
2. **Fill** batch information:

```
Batch Form:

Solution: [Dropdown] *Required
Batch Number: [Text] Auto-generated or manual
Prepared Date: [Date] *Required
Expiry Date: [Date] *Required
Initial Quantity: [Number] *Required
Unit: [Dropdown] *Required
Location: [Dropdown] Storage location
Notes: [Textarea] Preparation notes
```

3. **Submit** to create

**From Preparation:**
- Automatically created when preparation completed
- Pre-filled with preparation data
- Batch number from preparation

#### Batch Dashboard

**Table Structure:**
```
┌──┬─────────────┬──────────┬────────┬────────┬─────────┬────────┬─────────┐
│No│Batch Number │ Solution │Prepared│ Expiry │Quantity │ Status │ Actions │
├──┼─────────────┼──────────┼────────┼────────┼─────────┼────────┼─────────┤
│1 │BATCH-2025001│Agar      │Oct 1   │Nov 1   │450/500ml│🟢Active│ [Btns]  │
│2 │BATCH-2025002│Buffer    │Sep 15  │Oct 15  │0/1000ml │⏸️Consumed│ [Btns] │
│3 │BATCH-2025003│Media     │Aug 20  │Sep 20  │200/500ml│🔴Expired│ [Btns]  │
└──┴─────────────┴──────────┴────────┴────────┴─────────┴────────┴─────────┘
```

**Status Indicators:**
- 🟢 **Active**: In use, not expired
- 🔴 **Expired**: Past expiry date
- ⚠️ **Recalled**: Quality issue, do not use
- ⏸️ **Consumed**: Fully used up

**Expiry Warnings:**
- 🟡 **Warning** (7 days): Yellow highlight
- 🔴 **Danger** (3 days): Red highlight
- **Expired**: Strikethrough, red badge

#### Filtering Batches

**Available Filters:**
```
Filters:
├── Solution: Select specific solution
├── Status: Active/Expired/Consumed/Recalled
├── Date Range: Prepared/Expiry date
├── Location: Storage location
└── Search: Batch number, solution name
```

#### Recording Batch Usage

1. **Locate** batch in table
2. **Click** "Record Usage" button
3. **Enter** consumption:

```
Usage Form:

Quantity Used: [Number] *Required
Date Used: [Date] Auto-filled (today)
Used For: [Dropdown] Test/Batch/Sample
Reference: [Text] Batch code or sample ID
Notes: [Textarea] Usage notes
```

4. **Submit**
5. **System** updates:
   - Current quantity decreased
   - Consumed amount increased
   - Last used date updated
   - Status changed if fully consumed

#### Batch Recall

**When to Recall:**
- Quality control failure
- Contamination detected
- Preparation error discovered
- Regulatory requirement

**Recall Process:**
1. **Click** "Recall" button on batch
2. **Enter** recall details:

```
Recall Form:

Reason: [Dropdown] *Required
  - QC Failure
  - Contamination
  - Preparation Error
  - Regulatory
  - Other

Description: [Textarea] *Required
  Detailed explanation

Recalled By: [Auto-filled] Current user
Date: [Auto-filled] Current date

Affected Tests: [Multi-select]
  List of tests using this batch
```

3. **Submit** recall
4. **System** actions:
   - Marks batch as recalled
   - Flags related tests
   - Notifies relevant users
   - Generates incident report

---

### Module 6: Consumption Analytics

**Path:** `Lab → Stock Monitoring → Consumption Analytics`

#### Purpose
Monitor solution usage patterns, identify trends, and support data-driven decision making for inventory management.

#### Dashboard Overview

**Key Metrics Display:**
```
┌─────────────────────────────────────────────────────────┐
│  Total        Average        Top          Waste        │
│  Consumption  Daily Use      Consumer     Rate         │
│  ───────      ────────       ─────────    ──────       │
│  2,500 ml     83.3 ml/day    Agar (45%)   2.3%        │
└─────────────────────────────────────────────────────────┘
```

**Trend Indicators:**
- 📈 **Increasing**: Up arrow, green
- 📉 **Decreasing**: Down arrow, red  
- ➡️ **Stable**: Right arrow, yellow

#### Analytics Charts

**1. Consumption Over Time**
- **Type**: Line chart
- **X-Axis**: Date
- **Y-Axis**: Quantity consumed
- **Features**:
  - Multiple solutions on same chart
  - Zoom and pan
  - Tooltip with details
  - Export to image

**2. Solution Breakdown**
- **Type**: Pie/Donut chart
- **Shows**: Percentage by solution
- **Interactive**: Click to filter

**3. Daily Average**
- **Type**: Bar chart
- **Shows**: Average daily consumption
- **Grouping**: By solution or category

**4. Waste Analysis**
- **Type**: Stacked bar chart
- **Categories**:
  - Used (green)
  - Expired (red)
  - Recalled (orange)
  - Other waste (gray)

#### Generating Metrics

**On-Demand Generation:**
1. **Click** "Generate Metrics" button
2. **Select** parameters:

```
Metrics Parameters:

Period: [Dropdown] *Required
  - Last 7 days
  - Last 30 days
  - Last 90 days
  - Last 365 days
  - Custom range

Solutions: [Multi-select]
  All or specific solutions

Include: [Checkboxes]
  ☑ Consumed in testing
  ☑ Used in preparations
  ☑ Expired/Waste
  ☑ Pending consumption
```

3. **Submit** to generate
4. **System** calculates and displays

#### Solution-Specific Analytics

**Drill-Down View:**
1. **Click** on solution name/card
2. **Detailed analytics** display:

```
Solution: Nutrient Agar

Overall Statistics:
├── Total Prepared: 5,000 ml
├── Total Consumed: 4,200 ml (84%)
├── Currently Available: 600 ml
├── Expired/Wasted: 200 ml (4%)
└── Average Daily: 46.7 ml

Usage Breakdown:
├── Test Stage Usage: 3,500 ml (83%)
├── QC Testing: 400 ml (10%)
├── R&D: 300 ml (7%)
└── Other: 0 ml

Batch Information:
├── Active Batches: 2
├── Average Batch Size: 500 ml
├── Average Consumption Time: 12 days
└── Next Expiry: Oct 28, 2025

Trends:
├── Week-over-Week: +12% 📈
├── Month-over-Month: -5% 📉
└── Forecast Next Month: 1,400 ml
```

#### Exporting Analytics

**Export Options:**
1. **Click** "Export" button
2. **Select** format:
   - **Excel**: Detailed data tables
   - **PDF**: Report with charts
   - **CSV**: Raw data
   - **Image**: Charts only (PNG/SVG)

3. **Configure** export:
   - Date range
   - Solutions to include
   - Metrics to show
   - Chart types

4. **Download** file

---

### Module 7: Cost Analysis

**Path:** `Lab → Stock Monitoring → Cost Analysis`

#### Purpose
Track and analyze costs associated with solution usage, helping with budgeting and cost optimization.

#### Cost Tracking

**Cost Components:**
```
Total Cost =
  Raw Materials Cost +
  Preparation Labor +
  Equipment Usage +
  Storage Cost +
  Waste Cost
```

**Per-Solution Costing:**
- Purchase/preparation cost per unit
- Allocation to tests/batches
- Waste cost calculation
- Cost trends over time

#### Reports

1. **Cost per Test**: Solution costs by test type
2. **Cost per Batch**: Total solution costs per sample batch
3. **Cost Trends**: Monthly/quarterly cost analysis
4. **Waste Costs**: Financial impact of expired solutions
5. **Budget vs Actual**: Comparison reports

---

### Module 8: Predictive Analytics

**Path:** `Lab → Stock Monitoring → Predictive Analytics`

#### Purpose
Forecast future solution needs based on historical usage patterns to prevent stockouts and reduce waste.

#### Prediction Models

**1. Consumption Forecast**
- Based on historical usage
- Accounts for seasonality
- Adjusts for trends
- Confidence intervals provided

**2. Reorder Point Calculation**
```
Reorder Point = (Average Daily Use × Lead Time) + Safety Stock

Example:
  Average Daily Use: 50 ml/day
  Lead Time: 7 days
  Safety Stock: 100 ml
  Reorder Point: (50 × 7) + 100 = 450 ml
```

**3. Optimal Batch Size**
- Minimizes preparation frequency
- Reduces expiry waste
- Considers shelf life
- Balances cost vs convenience

#### Alerts and Notifications

**System Alerts:**
- 🔔 **Low Stock**: Below reorder point
- ⏰ **Expiry Warning**: Batch expiring soon
- 📊 **Usage Spike**: Unusual consumption detected
- 💰 **Cost Alert**: Budget threshold exceeded

**Notification Methods:**
- In-app notifications
- Email alerts
- SMS (if configured)
- Dashboard widgets

---

## Solutions Monitoring & Test Stages

### Overview

Test Stages represent individual steps in a laboratory testing method. Each stage can require specific:
- **Duration** (in hours)
- **Media** (culture media, reagents)
- **Controls** (positive/negative controls)
- **Equipment** (incubators, instruments)
- **Results** (observations or measurements)

### Key Concepts

#### Stage Header
A **Stage Header** defines a complete testing method for a specific analyte, method, and sample type. It contains:
- Method name (e.g., "Microbiology Testing")
- Analyte (e.g., "Total Coliform")
- Sample type (e.g., "Water")
- Total test duration in days
- Sequence of test stages

#### Test Stage
An individual step within a Stage Header, containing:
- **Order**: Sequential position (Day 1, Day 2, etc.)
- **Name**: Stage description (e.g., "Incubation", "Reading")
- **Duration**: Time required in hours
- **Media Required**: List of culture media/reagents
- **Controls Required**: Quality control materials
- **Equipment Required**: Instruments/equipment needed
- **Result Type**: Expected result format (positive/negative, numeric, etc.)
- **End Conditions**: Rules for proceeding to next stage

#### Stage Types

1. **Standard Stage**: Regular sequential step
2. **Result Stage**: Requires observation/measurement recording
3. **Conditional Stage**: Next stage depends on result (positive/negative)
4. **End Stage**: Final step in the sequence

---

## Method Sequence Management

### Accessing Method Sequences

#### From Sample Workflow (Batch View)

1. **Navigate to Batch**
   - Go to: **Lab → Sample Workflow → Select Batch**
   - The batch must be past "Samples Reception" and "Samples Request Review" stages

2. **Access Method Sequences**
   - **Option A**: Click the **"Method Sequences"** tab in the batch detail page
   - **Option B**: Click the **"Method Sequences"** button (top-right of batch header)

3. **Initial Load**
   - First click on "Method Sequences" tab loads available sequences
   - System displays all methods configured with stage headers for this batch

#### Method Sequences Page Layout

The dedicated Method Sequences page (`/sample-workflow/batch/{batch_id}/method-sequences`) displays:

**Header Section:**
- Batch code and information
- Back navigation to batch
- Method sequence overview

**Tabs:**
- One tab per Stage Header/Method
- Shows method name, analyte, and sample type

**Content Area:**
- Run management section
- Active and historical runs
- Stage timeline visualization

---

### Creating a Method Sequence Run

A **Run** is an execution instance of a Stage Header for specific samples.

#### Step 1: Create New Run

1. **Select Method Tab**
   - Click on the desired method tab (e.g., "Microbiology - Total Coliform")

2. **Click "Create New Run"**
   - Button located at top of tab content
   - Opens "Create Run" modal dialog

3. **Configure Run**
   ```
   Modal Fields:
   - Run Name: Descriptive name (e.g., "Batch 2025-001 - Run 1")
   - Start Date: When the run begins (defaults to today)
   - Notes: Optional description or special instructions
   - Samples: List of applicable samples (auto-populated)
   ```

4. **Review Sample Selection**
   - System automatically includes samples with:
     * Captured results for this method
     * Analysis elements linked to the stage header
   - Sample codes displayed in the modal

5. **Submit**
   - Click **"Create Run"** button
   - System creates tracking records for all stages × samples

#### What Happens on Run Creation

The system automatically:
- Creates `StageHeaderRun` record
- Generates `SampleCapturedTestStagesTrack` for each:
  * Sample in the batch
  * Stage in the sequence
  * Combination (if 5 samples × 3 stages = 15 track records)
- Sets all tracks to **"pending"** status
- Links tracks to captured results

---

### Managing Test Stage Execution

#### Run Display

Each run shows:

**Run Header (Collapsible):**
- Run name and date
- Total samples count
- Overall progress (X/Y stages completed)
- Status indicator

**Run Body:**
Contains stage timeline with visual progress indicators

#### Stage Timeline View

**Stage Cards Display:**
- Stage order/day number
- Stage name
- Duration requirement
- Status badge (pending/running/completed/overtime)
- Start/Stop buttons
- Elapsed time timer

**Stage Details (Expandable):**
Click stage name or arrow to expand:
- Required media (with quantities)
- Required controls
- Required equipment
- Expected duration
- Per-sample execution status
- Reading/result entry fields (if result stage)

---

### Starting a Test Stage

#### Prerequisites
- Run must be created
- Previous stage completed (for sequential stages)
- Stage status must be "pending"

#### Procedure

1. **Locate Stage**
   - Find the stage card in the run timeline
   - Ensure stage shows "Pending" status

2. **Review Requirements**
   - Expand stage details
   - Verify all required materials available:
     * Media prepared
     * Controls ready
     * Equipment available

3. **Select Solutions**
   When prompted:
   ```
   Media Selection:
   - Select specific batch of each required media
   - System shows available batches from lab inventory
   - Record batch numbers used
   
   Controls Selection:
   - Select positive control batch
   - Select negative control batch
   - Record lot numbers
   
   Equipment Selection:
   - Select specific instrument/equipment
   - System filters by equipment type required
   - Records equipment ID
   ```

4. **Click "Start Test Stage"**
   - System records:
     * Start timestamp
     * User ID (who started)
     * Selected media batches
     * Selected control batches
     * Selected equipment
   - Stage status changes to **"Running"**
   - Timer begins counting

5. **Automatic Monitoring**
   - System calculates expected end time
   - Monitors for overtime (when duration exceeded)
   - Updates visual timer every 30 seconds

#### During Stage Execution

**Timer Display:**
- **Green**: Within expected duration
- **Orange**: Approaching end time (last 10%)
- **Red**: Overtime (exceeded expected duration)

**Status Updates:**
- Elapsed time displayed in human-readable format
- Remaining time shown (or overtime amount)
- Stage cannot be started twice

---

### Ending a Test Stage

#### When to End

End a stage when:
- Required incubation/reaction time complete
- Observations recorded
- Results captured (for result stages)
- Ready to proceed to next stage

#### Procedure

1. **Recording Results (if Result Stage)**
   Before ending:
   - Expand stage details
   - Locate sample rows
   - Enter observations/measurements:
     * Positive/Negative results
     * Colony counts
     * Numeric readings
     * Visual observations

2. **Click "End Test Stage"**
   - System records:
     * End timestamp
     * Total elapsed time
     * Overtime status (if applicable)
   - Stage status changes to **"Completed"**
   - Timer stops

3. **Automatic Next Stage Preparation**
   - System identifies next stage in sequence
   - After 30 minutes, automatically sets next stage to "ready"
   - Notification may be sent to assigned users

4. **Conditional Branching (if applicable)**
   For stages with result-based routing:
   - System evaluates result (positive/negative)
   - Routes to appropriate next stage:
     * Positive result → Next stage for positive path
     * Negative result → Next stage for negative path
     * End if configured (e.g., negative = test complete)

---

### Reading and Recording Results

#### Result Stages

Stages marked as "Result Stage" require data entry.

**Field Types:**
- **Observation**: Visual assessment (present/absent, color, clarity)
- **Count**: Colony counts, cell counts
- **Measurement**: Numeric values with units
- **Pass/Fail**: Binary outcome

#### Recording Process

1. **Expand Stage Details**
   - Click on result stage name
   - Sample-by-sample grid appears

2. **For Each Sample:**
   ```
   Sample Grid Columns:
   - Sample Code: Identifier
   - Reading Date: When observed
   - Result: Observation/measurement
   - Units: Measurement unit
   - Remarks: Additional notes
   - Read By: User recording result
   ```

3. **Enter Data**
   - Click in result field for sample
   - Enter observation/measurement
   - Select appropriate units (if applicable)
   - Add remarks if needed
   - System auto-saves on blur

4. **Review**
   - Verify all samples have results
   - Check for anomalies
   - Confirm control results acceptable

5. **Post Results**
   - Click **"Post Results"** button
   - Results transferred to captured results
   - Timestamp and user recorded
   - Status updated to "Results Posted"

---

### Solutions and Quality Control Tracking

#### Media Tracking

**Purpose:** Ensure correct media used and track batch performance

**Captured Information:**
- Media/reagent name
- Batch number
- Lot number
- Expiry date
- Quantity used
- Preparation date

**Workflow:**
1. When starting stage, system prompts for media selection
2. User selects from available inventory
3. System records media_data JSON:
   ```json
   {
     "media_ids": [123, 456],
     "media_names": ["Nutrient Agar", "Peptone Water"],
     "batch_numbers": ["NA-2025-001", "PW-2025-045"],
     "quantities_used": [10, 20],
     "units": ["plates", "ml"]
   }
   ```
4. Inventory automatically updated (if configured)

#### Control Tracking

**Purpose:** Verify test method performance and validity

**Captured Information:**
- Control type (positive/negative)
- Control material name
- Batch/lot number
- Expected result
- Actual result
- Acceptance criteria

**Workflow:**
1. During stage start, select controls
2. Record control results alongside sample results
3. System validates against expected outcomes
4. Alerts if control fails acceptance criteria

#### Equipment Tracking

**Purpose:** Link results to specific instruments for calibration and maintenance tracking

**Captured Information:**
- Equipment ID and name
- Calibration status
- Last maintenance date
- Operator qualification status

**Validation:**
- System checks equipment calibration current
- Verifies operator qualified on equipment
- Records usage for maintenance scheduling

---

### Progress Monitoring

#### Run-Level Progress

**Run Header Shows:**
- Total stages in sequence
- Completed stages count
- Percentage complete
- Estimated completion date

**Example:**
```
Run: Batch-2025-001-R1
Progress: 4/6 stages completed (67%)
Estimated completion: Oct 25, 2025
Status: In Progress
```

#### Sample-Level Progress

Each sample tracked independently:
- Can view individual sample progress
- Filter stages by sample
- Identify delayed samples

**Sample Status:**
- **On Track**: All stages on schedule
- **Delayed**: One or more stages behind
- **Completed**: All stages finished

#### Visual Indicators

**Status Badges:**
- 🔘 **Pending** (Gray): Not started
- 🔵 **Running** (Blue): Currently active
- ✅ **Completed** (Green): Finished
- ⏰ **Overtime** (Red): Exceeded expected duration
- ⚠️ **Alert** (Yellow): Attention needed

---

## Navigating Between Views

### From Batch Workflow → Method Sequences

**Path:**
1. Lab → Sample Workflow → Select Batch
2. Click **"Method Sequences"** button or tab
3. Redirects to: `/sample-workflow/batch/{id}/method-sequences`

### Method Sequences Page → Batch Workflow

**Path:**
1. Click **"Back to Batch"** button (top-left)
2. Returns to batch detail page
3. All data saved (no confirmation needed)

### Accessing Ledger Reports

**Path:**
1. Lab → Reports → Ledger Reports
2. Or direct URL: `/reports/ledger-reports`

---

## Ledger Reports

### Overview

Ledger Reports provide comprehensive, filterable views of all test stage activities across batches, samples, and time periods. Essential for:
- Quality audits
- Productivity analysis
- Equipment utilization tracking
- Compliance documentation
- Trend analysis

### Accessing Ledger Reports

**Navigation:**
```
Main Menu → Lab → Reports → Ledger Reports
```

**URL:**
```
/reports/ledger-reports
```

**Permissions Required:**
- View Ledger Reports (permission)
- Lab staff or higher role

---

### Report Interface

#### Filter Section

Located at top of page in highlighted panel.

**Available Filters:**

1. **Date Range**
   - **Start Date**: Beginning of date range
   - **End Date**: End of date range
   - Filters by stage start date

2. **Analyte Filter**
   - Dropdown of all analytes in system
   - Shows only analytes with test stage activity
   - Option: "All Analytes"

3. **Sample Filter**
   - Dropdown of sample codes
   - Shows only samples with test stage records
   - Option: "All Samples"

4. **Search Box**
   - Free text search across:
     * Analyte names
     * Sample codes
     * Media names
     * Control names
     * User names

5. **Reset Button**
   - Clears all filters
   - Returns to full dataset view

**Filter Behavior:**
- **Real-Time**: Filters apply instantly (wire:model.live)
- **Cumulative**: Multiple filters combine (AND logic)
- **Persistent**: Filter state saved in URL (shareable links)

#### Results Table

**Table Structure:**

| Column | Description | Data Source |
|--------|-------------|-------------|
| Date | Stage start date | started_at |
| Media & Controls | Materials used | media_data, controls_data |
| Samples | Sample code | sample_detail |
| Analyte | Test analyte | captured_result → analyte |
| Start Date | Stage start timestamp | started_at |
| End Date | Stage end timestamp | ended_at |
| Equipment columns | Dynamic based on data | equipment_data |
| Done By | User who performed | user or readBy |
| Checked By | Batch verifier | batch → verifier |

**Dynamic Equipment Columns:**
- System identifies all equipment used in filtered results
- Creates one column per unique equipment
- Shows "Yes" if equipment used for that record
- Shows "-" if not used

**Table Features:**
- **Sortable**: Click column headers to sort
- **Responsive**: Horizontal scroll for many equipment columns
- **Hover Effects**: Row highlighting on mouse over
- **Clean Design**: Light gray header, white rows

#### Pagination & Display Options

**Show Dropdown:**
- Located in table header (top-right)
- Options: 10, 25, 50, 100 records per page
- Selection applies instantly
- Default: 25 records

**Pagination Controls:**
- Bottom of table
- Shows: "Showing X to Y of Z records"
- Navigation: First, Previous, Page numbers, Next, Last
- Updates without page reload (Livewire)

---

### Exporting Data

#### Excel Export

**Purpose:** Download filtered data for offline analysis, archival, or external reporting.

**Button Location:**
- Table header, next to "Show" dropdown
- Green button with Excel icon: **"Export to Excel"**

#### Export Process

1. **Apply Filters** (optional)
   - Set desired date range
   - Select analyte
   - Select sample
   - Enter search terms
   - Export will include ONLY filtered records

2. **Click "Export to Excel"**
   - System generates Excel file
   - Includes all filtered records (not just current page)
   - Applies same column structure as table

3. **Download**
   - File downloads automatically
   - Filename format: `ledger-reports-YYYY-MM-DD-HHMMSS.xlsx`
   - Example: `ledger-reports-2025-10-23-143522.xlsx`

#### Excel File Structure

**Worksheet Layout:**

**Row 1 (Headers):**
- Bold formatting
- Same columns as on-screen table
- Equipment columns included dynamically

**Data Rows:**
- Date formatted: "Oct 23, 2025"
- Timestamps: "Oct 23, 2025 14:35"
- Media & Controls: "Media: Nutrient Agar | Controls: Positive Control"
- Equipment: "Yes" or "-"

**Excel Benefits:**
- Pivot tables
- Charts and graphs
- Additional calculations
- Data archival
- Compliance documentation
- Share with non-LIMS users

---

### Understanding Ledger Data

#### Record Sources

Each ledger row represents one `SampleCapturedTestStagesTrack` record, created when:
1. Method sequence run initiated
2. Stage started for a sample
3. Stage completed
4. Results recorded

#### Data Relationships

```
Ledger Record
├── Sample Detail (sample code)
├── Captured Result
│   ├── Analyte
│   └── Sample Header (Batch)
│       └── Verifier
├── Test Stage
│   ├── Stage Header
│   └── Duration info
├── Media Data (JSON)
├── Controls Data (JSON)
├── Equipment Data (JSON)
├── User (started by)
└── Read By User (results recorded by)
```

#### Media & Controls Column

**Format:**
```
Media: [List of media names]
Controls: [List of control names]
```

**Example:**
```
Media: Nutrient Agar, MacConkey Agar
Controls: E. coli ATCC 25922 (Positive), Sterile Water (Negative)
```

**Empty Values:**
- "-" if no media or controls used for that stage

#### Equipment Columns

**Dynamic Generation:**
- System scans all records in filtered results
- Identifies unique equipment IDs
- Creates column for each with equipment name as header

**Example:**
```
| Incubator-01 | Microscope-A | Autoclave-02 |
|--------------|--------------|--------------|
| Yes          | -            | Yes          |
| -            | Yes          | -            |
```

**Use Cases:**
- Equipment utilization reports
- Identify which samples used which equipment
- Equipment qualification tracking
- Maintenance scheduling based on usage

---

### Common Report Scenarios

#### Scenario 1: Daily Activity Report

**Goal:** View all test stage activities for today

**Steps:**
1. Set Start Date = Today
2. Set End Date = Today
3. Leave other filters blank
4. Review table
5. Export to Excel for daily log

**Use:** Daily management, shift handovers

---

#### Scenario 2: Analyte-Specific Audit

**Goal:** All activities for "Total Coliform" testing in October

**Steps:**
1. Set Start Date = Oct 1, 2025
2. Set End Date = Oct 31, 2025
3. Select Analyte = "Total Coliform"
4. Click Export to Excel

**Use:** Method validation, quality audits

---

#### Scenario 3: Equipment Utilization

**Goal:** When and how often was "Incubator-01" used?

**Steps:**
1. Set desired date range
2. Leave other filters blank
3. Export to Excel
4. In Excel, filter "Incubator-01" column for "Yes"
5. Count rows for usage frequency

**Use:** Maintenance scheduling, capacity planning

---

#### Scenario 4: User Performance

**Goal:** All work done by specific user

**Steps:**
1. Enter user name in Search box
2. System searches "Done By" and "Checked By" columns
3. Review results showing that user's activities

**Use:** Training verification, productivity analysis

---

#### Scenario 5: Sample Traceability

**Goal:** Complete history for sample "S-2025-0123"

**Steps:**
1. Select Sample = "S-2025-0123" (or search)
2. View all stages performed
3. See all media, controls, equipment used
4. Identify who performed each step
5. Export for quality documentation

**Use:** Customer inquiries, technical complaints, accreditation

---

### Report Interpretation

#### Reading Timestamps

**Start Date Column:**
- When stage was initiated
- Includes date and time
- Format: "Oct 23, 2025 09:30"

**End Date Column:**
- When stage was completed
- Duration = End - Start
- "-" if not yet ended (still running)

**Overtime Detection:**
- Compare end-start to expected duration
- Not directly shown in ledger (use Method Sequences page)

#### Quality Control Data

**Media Information:**
- Confirms correct media used
- Batch traceability
- Critical for method validation

**Controls Information:**
- Ensures test method performed correctly
- Both positive and negative should be present
- Review periodically for control failures

**Missing Data:**
- "-" indicates not applicable or not recorded
- Investigate if expected but missing

#### Equipment Lineage

**Why Track Equipment:**
- Results linked to specific instruments
- Calibration verification
- Out-of-calibration instrument identification
- Equipment qualification

**Identifying Issues:**
- If equipment shows problems after a date
- Filter by that equipment and date range
- Identify affected samples
- Potential retesting needed

---

## Troubleshooting

### Method Sequences Issues

#### Issue: Method Sequences Tab is Empty

**Possible Causes:**
1. Batch not yet in appropriate workflow stage
2. No analysis elements configured with stage headers
3. No captured results for this batch

**Solutions:**
1. Verify batch status (must be past "Samples Reception")
2. Check Analysis Type → Analysis Elements → confirm stage_header_id set
3. Ensure samples have captured results
4. Verify analyte/method/sample type match stage header configuration

---

#### Issue: Cannot Create Run

**Error:** "No samples available for this method"

**Solution:**
1. Verify samples in batch have captured results for this analyte
2. Check analysis_elements table for stage_header_id linkage
3. Confirm captured_results have stage_header_id populated
4. Run observer may need to populate stage_header_id (CapturedObserver)

---

#### Issue: Start Button Disabled

**Possible Reasons:**
1. Stage already started
2. Previous stage not completed
3. Run not properly created
4. Status not "pending"

**Solutions:**
1. Check stage status badge
2. Verify previous stage shows "Completed"
3. Refresh page to reload status
4. Check SampleCapturedTestStagesTrack table directly

---

#### Issue: Timer Not Updating

**Symptoms:**
- Elapsed time frozen
- Overtime not showing

**Solutions:**
1. Refresh page (timers update every 30 seconds)
2. Check browser console for JavaScript errors
3. Verify started_at timestamp in database
4. Clear browser cache
5. Ensure method-sequences.js loaded correctly

---

### Ledger Reports Issues

#### Issue: No Data Showing

**Possible Causes:**
1. No test stages executed yet
2. Filters too restrictive
3. Date range excluding all data

**Solutions:**
1. Click "Reset Filters" button
2. Expand date range
3. Verify SampleCapturedTestStagesTrack table has records
4. Check Livewire errors in browser console

---

#### Issue: Export Downloads Empty File

**Cause:** Filters exclude all records

**Solution:**
1. Review applied filters
2. Note record count at bottom ("Showing 0 to 0 of 0")
3. Adjust or clear filters
4. Retry export

---

#### Issue: Missing Equipment Columns

**Cause:** No equipment data in filtered results

**Explanation:**
- Equipment columns dynamically generated
- Only equipment actually used in results appears
- If no equipment used, no columns shown

**Solution:** This is expected behavior if no equipment logged

---

#### Issue: Verifier Shows "-"

**Possible Causes:**
1. Batch not yet verified
2. No verifier_id or verified_by in sample_headers table
3. Batch relationship not loading

**Solutions:**
1. Verify batch verification status
2. Check sample_headers table for verifier fields
3. Ensure SampleDetails.batch() relationship working
4. May need to complete batch verification workflow

---

### Performance Issues

#### Slow Loading

**Method Sequences:**
1. Reduce number of samples in batch
2. Limit stages per stage header
3. Index database tables (stage_header_id, status)
4. Check for N+1 query issues

**Ledger Reports:**
1. Narrow date range
2. Use filters to reduce result set
3. Database indexes on:
   - started_at
   - sample_detail_id
   - captured_result_id
   - stage_header_id
4. Consider pagination (default 25 records)

---

## Best Practices

### For Laboratory Staff

#### Test Execution

1. **Review Requirements First**
   - Always expand stage details before starting
   - Verify all media, controls, equipment available
   - Check expected duration to plan timing

2. **Record Accurately**
   - Select exact media batches used (not just similar)
   - Record control lot numbers
   - Enter results immediately after observation

3. **Monitor Progress**
   - Check timer regularly
   - Address overtime stages promptly
   - Don't let stages sit in "Running" status indefinitely

4. **Complete Stages**
   - Always click "End" when stage complete
   - Don't leave stages running overnight unless intentional
   - Record result before ending if result stage

#### Quality Control

1. **Control Documentation**
   - Always include positive and negative controls
   - Record control results separately from samples
   - Investigate any control failures immediately

2. **Media Traceability**
   - Select correct media batch
   - Verify expiry dates before use
   - Report media quality issues

3. **Equipment Verification**
   - Check calibration status before starting
   - Report equipment issues immediately
   - Use correct equipment (don't substitute without approval)

---

### For Supervisors

#### Workflow Monitoring

1. **Daily Review**
   - Check ledger reports daily
   - Identify overtime stages
   - Follow up on incomplete runs

2. **Progress Tracking**
   - Review run progress weekly
   - Identify bottlenecks
   - Allocate resources to delayed samples

3. **Quality Audits**
   - Use ledger reports for monthly QC review
   - Export equipment utilization reports
   - Verify control usage patterns

#### Training

1. **New Staff**
   - Practice on test batch first
   - Shadow experienced user
   - Review this manual section by section

2. **Competency Verification**
   - Use ledger reports to verify proper execution
   - Check media/control selection accuracy
   - Review timing compliance

---

### For Quality Assurance

#### Audit Trail

1. **Complete Documentation**
   - Every test stage recorded
   - User identification captured
   - Timestamps automatic and tamper-proof

2. **Traceability**
   - Sample → Stages → Media/Controls → Equipment → Results
   - Full chain documented
   - Exportable for auditors

3. **Non-Conformance Investigation**
   - Ledger reports identify all related tests
   - Equipment usage tracked
   - Media batch traceability

#### Compliance

1. **ISO 17025 Requirements**
   - Method validation documentation
   - Equipment usage records
   - Quality control evidence
   - Traceability maintained

2. **Regulatory Reporting**
   - Export filtered data sets
   - Include all required data elements
   - Verifiable timestamps

---

### For System Administrators

#### Configuration

1. **Stage Header Setup**
   - Link to correct analysis elements
   - Set appropriate durations
   - Configure conditional routing
   - Test with pilot samples

2. **Permissions**
   - Limit "Start Stage" to qualified users
   - Restrict "Post Results" appropriately
   - Control export access

3. **Data Integrity**
   - Regular database backups
   - Monitor for orphaned records
   - Verify observer functioning (CapturedObserver)

#### Maintenance

1. **Performance**
   - Monitor query performance
   - Add database indexes as needed
   - Archive old track records (after retention period)

2. **Monitoring**
   - Check job queue (CheckStageOvertime, AutoStartNextStage)
   - Verify email notifications working
   - Review error logs regularly

---

## Glossary

**Analyte**: The specific substance or organism being tested (e.g., E. coli, pH, Total Coliform)

**Batch**: A group of samples received and processed together (also called Sample Header)

**Captured Result**: A placeholder record for a test result, created when sample submitted

**Conditional Stage**: A test stage where the next step depends on the current result

**Control**: Reference material with known properties used to verify test method performance

**End Stage**: The final stage in a method sequence, indicating test completion

**Equipment Data**: JSON record of instruments/equipment used during a stage

**Incubation**: Period where samples are maintained at specific conditions (temperature, humidity)

**Ledger Report**: Comprehensive report of all test stage activities with filtering capabilities

**Media**: Culture media or reagents required for microbial growth or chemical reactions

**Method**: A defined procedure for performing a laboratory test

**Method Sequence**: The complete series of test stages for a specific analyte/method combination

**Overtime**: When a stage exceeds its expected duration

**Result Stage**: A test stage requiring observation, measurement, or data entry

**Run**: A single execution instance of a method sequence for selected samples

**Sample Detail**: An individual sample within a batch

**Stage**: A single step within a method sequence (also called Test Stage)

**Stage Header**: Container defining a complete method with its sequence of stages

**Track Record**: Database record tracking one sample through one stage (SampleCapturedTestStagesTrack)

**Wire:model.live**: Livewire directive for real-time data binding without page reload

---

## Appendix: Database Schema

### Key Tables

**stage_headers**
- Defines complete test methods
- Links to method, analyte, sample type

**test_stages**
- Individual stages within a stage header
- Ordered sequence (order column)
- Contains duration, requirements, conditions

**stage_header_runs**
- Execution instances of stage headers
- Links to batch and user

**sample_captured_test_stages_track**
- Tracks each sample × stage combination
- Stores status, timestamps, solutions used
- Main source for ledger reports

**captured_results**
- Links samples to test methods
- Receives final results after method completion

---

## Support & Feedback

For questions, issues, or suggestions:

1. **Technical Issues**: Contact IT Support
2. **Training Requests**: Contact Lab Manager
3. **Feature Requests**: Contact LIMS Administrator
4. **Quality Questions**: Contact QA Department

---

**Document Control**

- **Author**: LIMS Development Team
- **Reviewed**: Laboratory Management
- **Approved**: Quality Assurance
- **Next Review**: January 2026
- **Version History**:
  - v1.0 (Oct 23, 2025): Initial release

---

*End of Manual*

