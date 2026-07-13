# Blade Enterprise Standards

## Purpose

Blade is the presentation layer of the application.

Its responsibility is to display information.

Blade should never become an application layer.

Blade should never contain business logic.

Blade should remain declarative, predictable and reusable.

---

# Responsibilities

Blade is responsible for:

- HTML
- Layouts
- Components
- Slots
- Rendering data
- Conditional display
- Loops
- Forms
- Accessibility

Blade is NOT responsible for:

Business Logic

Database Queries

Authorization Decisions

Complex Calculations

Application Workflows

API Calls

---

# Blade Philosophy

Blade describes

what the user should see

not

how the application works.

---

# Source of Data

Blade should receive prepared data.

Bad

Blade

↓

Database Query

Good

Controller

↓

Service

↓

Livewire

↓

Blade

---

# Keep Blade Thin

Views should remain simple.

Avoid nested conditions.

Avoid deeply nested loops.

Avoid inline PHP.

---

# Components

Prefer Blade Components whenever UI is reused.

Examples

Input

Card

Modal

Badge

Alert

Table

Pagination

Button

Tooltip

Avatar

Breadcrumb

---

# Component Design

Components should:

Have one responsibility.

Remain configurable.

Avoid becoming mini frameworks.

---

# Anonymous Components

Prefer anonymous components when logic is unnecessary.

---

# Class Components

Use class components when:

Formatting data

Preparing attributes

Computing display values

Avoid placing business logic inside them.

---

# Slots

Prefer slots over duplicated layouts.

Use named slots for complex layouts.

---

# Layouts

Every page should extend a layout.

Avoid duplicated HTML structure.

---

# Sections

Keep sections organized.

Example

Title

Toolbar

Content

Scripts

Avoid mixing responsibilities.

---

# Partial Views

Extract repeated markup.

Examples

Filters

Search Bars

Headers

Footers

Action Buttons

Status Badges

---

# View Organization

Group views by feature.

Example

samples/

clients/

reports/

billing/

inventory/

Avoid huge flat directories.

---

# HTML

Use semantic HTML.

Prefer

header

main

section

article

nav

footer

Avoid excessive div nesting.

---

# Accessibility

Always include:

Labels

Alt Text

ARIA when appropriate

Keyboard navigation

Visible focus states

Never sacrifice accessibility for convenience.

---

# Forms

Blade displays forms.

Livewire owns data.

Never duplicate form state.

---

# Validation

Display validation errors.

Do not perform validation logic.

---

# Loops

Keep loops simple.

Avoid calculations inside loops.

Prepare data beforehand.

---

# Conditionals

Keep conditionals readable.

Avoid multiple nested @if blocks.

Extract complex display logic.

---

# Inline PHP

Avoid @php blocks.

Avoid raw PHP.

Prefer View Models or computed data.

---

# Database Access

Never query inside Blade.

Never call models directly.

Never use DB:: inside Blade.

---

# Business Logic

Never place workflows inside Blade.

Examples

Pricing

Approval Rules

Status Logic

Permissions

Move these into Services or Livewire.

---

# Authorization

Use:

@can

@cannot

@canany

Avoid manual permission checks.

---

# Escaping

Always escape output.

Use raw HTML only when absolutely trusted.

Avoid unnecessary {!! !!}

---

# JavaScript

Avoid inline JavaScript.

Keep JavaScript inside dedicated files.

Only use inline scripts when integration requires it.

---

# CSS

Avoid inline styles.

Prefer Bootstrap utilities.

Use project CSS for reusable styling.

---

# Livewire

Blade displays Livewire.

Do not duplicate Livewire logic.

Avoid manipulating Livewire DOM manually.

---

# Alpine

Blade may initialize Alpine.

Business data remains in Livewire.

---

# Bootstrap

Blade applies Bootstrap classes.

Bootstrap controls appearance.

Blade controls structure.

---

# jQuery

Blade should never depend on jQuery.

Legacy widgets should remain isolated.

---

# Icons

Use one icon library consistently.

Avoid mixing icon packs.

---

# Naming

Use meaningful names.

Good

sample-table

client-card

approval-modal

Avoid

table1

box2

widget

---

# Tables

Keep tables readable.

Extract reusable table rows when necessary.

Avoid giant Blade files.

---

# Buttons

Use reusable button components.

Avoid repeated Bootstrap markup.

---

# Modals

Prefer reusable modal components.

Modal content belongs to Blade.

Modal data belongs to Livewire.

Modal animation belongs to Bootstrap.

---

# Empty States

Every list should handle:

No Results

No Permissions

Loading

Errors

Do not leave blank pages.

---

# Loading States

Use Livewire loading directives.

Avoid custom loading implementations.

---

# Error Messages

Display friendly messages.

Avoid exposing internal exception details.

---

# Formatting

Prepare formatting before Blade whenever possible.

Avoid:

Date calculations

Currency calculations

Business formatting

inside templates.

---

# Performance

Avoid:

Nested loops

Repeated rendering

Large components

Duplicate markup

Prepare data before rendering.

---

# Maintainability

If Blade exceeds several hundred lines because it contains multiple unrelated sections, extract components or partials.

One Blade file should represent one screen or one reusable UI element.

---

# Anti-Patterns

Never:

Query the database

Call external APIs

Instantiate services

Write SQL

Perform business workflows

Manipulate Livewire state directly

Perform heavy calculations

Use excessive inline PHP

---

# Cursor Rules

When generating Blade:

Keep templates declarative.

Generate reusable components.

Avoid business logic.

Prefer semantic HTML.

Prefer Bootstrap utilities.

Keep views readable.

Extract repeated markup.

Prepare data before rendering.

Generate maintainable enterprise Blade templates.

Blade should describe the interface—not implement application logic.
