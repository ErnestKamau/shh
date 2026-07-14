# Livewire 3 Enterprise Standards

## Purpose

This document defines how Livewire should be used throughout the application.

Livewire is the primary technology responsible for server-driven user interfaces.

Livewire is not a replacement for Laravel architecture.

Livewire coordinates user interaction.

Business logic belongs elsewhere.

---

# Livewire Responsibilities

Livewire components should:

- Load data
- Display data
- Validate user input
- Handle user interactions
- Call Services
- Emit events
- Dispatch browser events
- Coordinate workflows

Livewire should NOT become the application's business layer.

---

# One Component = One Responsibility

Each Livewire component should solve one business problem.

Good

Create Sample

Approve Result

Edit Client

Generate Invoice

Assign Analyst

Bad

LaboratoryDashboardManager

EverythingComponent

MasterComponent

DashboardControllerComponent

Avoid components that perform unrelated responsibilities.

---

# Component Size

Recommended

200–300 lines

Review

300–500 lines

Avoid

500+ lines

Large components should be decomposed.

---

# Component Structure

Organize properties logically.

Example order

Properties

Computed Properties

Lifecycle Methods

Listeners

Actions

Private Helpers

Render

Keep a consistent structure.

---

# Public Properties

Only expose data that the frontend actually needs.

Avoid unnecessary public properties.

Never expose sensitive values.

---

# Protected and Private Properties

Use protected/private for implementation details.

Not everything belongs as a public property.

---

# Typed Properties

Always type properties.

Good

public string $name;

public int $sampleId;

Avoid

public $name;

---

# Validation

Use Livewire validation only for Livewire forms.

Keep validation rules organized.

Complex validation belongs in Rule classes.

Avoid repeating validation logic.

---

# Lifecycle Hooks

Use lifecycle hooks intentionally.

Examples

mount()

boot()

hydrate()

dehydrate()

updated()

rendered()

Do not place business workflows inside lifecycle methods.

---

# mount()

Use mount() for:

Loading initial data

Authorization

Initializing component state

Avoid expensive operations.

---

# boot()

Use only for component initialization.

Avoid loading business data.

---

# render()

render() should only prepare data for display.

Never:

Create records

Update records

Delete records

Call APIs

Send emails

Generate PDFs

Render must remain pure.

---

# Database Queries

Avoid database queries inside render() whenever possible.

Prefer:

mount()

Computed Properties

Dedicated methods

Services

Repeated render queries reduce performance.

---

# Computed Properties

Use computed properties for derived data.

Avoid recalculating values repeatedly.

Do not perform expensive work unnecessarily.

---

# Actions

Actions should:

Validate

Authorize

Call Services

Refresh state

Return

Avoid embedding business logic.

---

# Business Logic

Business workflows belong in Services.

Bad

Approve sample

↓

Complex calculations

↓

Notifications

↓

Audit logging

↓

Inside Livewire

Good

Approve button

↓

Service

↓

Refresh UI

---

# Authorization

Authorize actions.

Do not rely on hidden buttons.

Every protected action must verify permissions.

---

# Events

Prefer Livewire events for component communication.

Avoid direct coupling between unrelated components.

Events should represent business actions.

Examples

SampleApproved

AnalysisCompleted

InvoicePaid

ClientCreated

Avoid generic event names.

---

# Browser Events

Dispatch browser events only for UI behaviour.

Examples

Open Modal

Close Modal

Show Toast

Scroll To Section

Do not use browser events for business workflows.

---

# State Ownership

Livewire owns server state.

Examples

Form data

Selected sample

Status

Database records

Permissions

Alpine owns temporary UI state.

Never duplicate ownership.

---

# Forms

One component should own one form.

Avoid giant multi-purpose forms.

Split large workflows into smaller components.

---

# File Uploads

Use Livewire uploads.

Validate:

Type

Size

Security

Store using Laravel Storage.

Never trust uploaded filenames.

---

# Pagination

Always use Livewire pagination.

Avoid loading thousands of records.

---

# Search

Debounce search fields.

Avoid querying on every keystroke.

Prefer

wire:model.live.debounce

over aggressive updates.

---

# Polling

Use polling sparingly.

Prefer events when possible.

Avoid unnecessary server requests.

---

# Loading Indicators

Every long-running action should display loading feedback.

Use:

wire:loading

wire:target

Prevent duplicate submissions.

---

# Optimistic UI

Use optimistic updates only when failure is unlikely.

Otherwise wait for server confirmation.

---

# Modals

Livewire controls modal data.

Bootstrap controls modal animation.

Avoid duplicated modal state.

---

# Flash Messages

Prefer browser events or dedicated toast components.

Avoid repeating alert markup.

---

# Component Communication

Prefer

Events

Parent-child communication

Properties

Avoid reaching into other components directly.

---

# JavaScript

Do not write unnecessary JavaScript.

If Livewire can solve it cleanly, use Livewire.

Do not recreate Livewire behaviour using jQuery.

---

# Alpine Integration

Use Alpine only for:

Dropdowns

Tabs

Accordion

Visibility

Animations

Temporary UI state

Do not duplicate Livewire properties.

---

# jQuery Integration

Only use jQuery for:

Legacy plugins

Bootstrap plugins

DataTables

Select2

Datepickers

Never let jQuery own Livewire state.

---

# Bootstrap

Bootstrap is responsible for presentation.

Livewire is responsible for data.

Do not mix responsibilities.

---

# Error Handling

Show user-friendly validation messages.

Log unexpected exceptions.

Do not expose stack traces.

---

# Performance

Avoid unnecessary renders.

Avoid repeated queries.

Use eager loading.

Avoid huge payloads.

Lazy load where appropriate.

---

# Security

Validate every action.

Authorize every action.

Escape output.

Never trust browser state.

---

# Testing

Every complex component should have tests covering:

Validation

Authorization

State changes

Events

Rendering

Edge cases

---

# Anti-Patterns

Never:

Query inside loops

Call APIs from render()

Perform large calculations in Blade

Duplicate Alpine state

Duplicate jQuery state

Create 1000-line components

Embed SQL

Perform business workflows inside render()

Mix unrelated responsibilities

---

# Cursor Rules

When generating Livewire code:

Treat Livewire as a presentation orchestration layer.

Keep components focused.

Use typed properties.

Validate user input.

Authorize protected actions.

Call Services for business workflows.

Keep render() pure.

Avoid duplicated state.

Prefer events over tight coupling.

Generate reusable, maintainable enterprise Livewire components that integrate cleanly with Laravel architecture.

