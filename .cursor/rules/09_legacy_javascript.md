# Legacy JavaScript Integration Standards

## Purpose

This project contains legacy JavaScript.

Legacy JavaScript exists to support mature functionality that cannot yet be migrated.

New development must integrate safely with existing JavaScript while avoiding additional technical debt.

The objective is gradual modernization, not unnecessary rewrites.

---

# Philosophy

Legacy JavaScript is supported.

Legacy JavaScript is not encouraged.

Whenever possible:

Laravel

↓

Livewire

↓

Alpine

should be preferred.

Only use legacy JavaScript when integration requires it.

---

# Ownership

Legacy JavaScript owns ONLY

Existing plugins

DataTables

Select2

CKEditor

TinyMCE

Flatpickr

Chart libraries

Barcode scanners

QR readers

Bootstrap JS plugins

Nothing else.

---

# Never Own

Legacy JavaScript must never own

Business Logic

Permissions

Validation

Database State

Authentication

Authorization

Livewire State

---

# DOM Ownership

Livewire owns

Rendered HTML

Forms

Validation

Lists

Components

Legacy JavaScript may enhance

already rendered HTML.

It must never replace it.

---

# DOM Manipulation

Never use

append()

prepend()

remove()

html()

replaceWith()

inside Livewire-managed components.

Use Livewire rendering.

---

# Event Binding

Never bind directly to elements that Livewire re-renders.

Bad

$(".save").click(...)

Good

Delegated Events

$(document).on("click",".save",...)

---

# Duplicate Event Listeners

Always remove listeners before reinitializing.

Avoid

document.ready()

running repeatedly.

---

# document.ready

Use only for

initial page load.

Do not assume the DOM remains unchanged.

Livewire continuously updates DOM.

---

# Livewire Lifecycle

Every plugin must support

Initialize

↓

Destroy

↓

Reinitialize

Never initialize plugins only once.

---

# Plugin Lifecycle

Standard Flow

Page Load

↓

Initialize Plugin

↓

Livewire Update

↓

Destroy Plugin

↓

Render

↓

Initialize Plugin

Every plugin must survive Livewire renders.

---

# wire:ignore

Use only when necessary.

Allowed

Select2

Charts

Editors

Maps

DataTables

Large third-party widgets

Avoid wrapping entire forms.

---

# DataTables

Allowed

Large datasets

Export functionality

Complex column searching

Server-side processing

Avoid DataTables for simple tables.

---

# Livewire + DataTables

Livewire owns data.

DataTables owns presentation.

Never allow DataTables to mutate Livewire state.

Destroy before Livewire updates.

Reinitialize afterwards.

---

# Select2

Select2 enhances UX.

Livewire owns selected value.

Synchronize using events.

Avoid duplicated state.

---

# Rich Text Editors

Examples

TinyMCE

CKEditor

Quill

Editor owns text editing.

Livewire owns saved content.

Synchronize explicitly.

---

# Date Pickers

Date picker owns calendar UI.

Livewire owns selected date.

Never duplicate values.

---

# Charts

Charts display data.

Laravel prepares data.

Livewire sends data.

Chart library renders visualization.

Never calculate business metrics inside JavaScript.

---

# AJAX

Avoid new jQuery AJAX.

Prefer

Livewire

Laravel HTTP

Fetch API

Only maintain existing AJAX where migration is impractical.

---

# Timers

Avoid unnecessary

setInterval()

Use polling only when justified.

Prefer Livewire polling.

---

# Global Variables

Avoid global variables.

Encapsulate feature-specific JavaScript.

---

# Inline JavaScript

Avoid inline scripts.

Keep JavaScript inside dedicated files.

---

# Namespaces

Group JavaScript by feature.

Example

Samples

Clients

Invoices

Reports

Avoid one massive app.js.

---

# Memory Leaks

Always destroy

Plugin instances

Observers

Intervals

Timeouts

before component removal.

---

# Bootstrap JavaScript

Bootstrap JS owns

Animation

Focus

Backdrop

Keyboard

Livewire owns

Data

Validation

Saving

Do not mix.

---

# Browser Events

Preferred

Livewire

↓

Browser Event

↓

Legacy Plugin

Avoid plugin-to-plugin communication.

---

# Custom Events

Use meaningful names.

Good

sample-approved

modal-open

datatable-refresh

Avoid

update

change

event1

---

# Error Handling

Do not silently ignore JavaScript errors.

Log meaningful information.

Fail gracefully.

---

# Performance

Avoid

Repeated initialization

Repeated DOM queries

Large selectors

Global event listeners

Nested callbacks

---

# Event Delegation

Always use delegated events for dynamic content.

Avoid binding directly to Livewire-generated elements.

---

# Debouncing

Debounce

Search

Resize

Scroll

Input

Avoid excessive browser events.

---

# Throttling

Throttle expensive events.

Examples

Window Resize

Mouse Move

Scroll

---

# Animation

Prefer CSS

or

Alpine

Avoid heavy jQuery animations.

---

# Accessibility

Maintain

Keyboard Support

Focus

Labels

ARIA

Never break Bootstrap accessibility.

---

# Logging

Development

Console logging acceptable.

Production

Remove debug logging.

Never expose sensitive information.

---

# Security

Never trust JavaScript.

Validation belongs to Laravel.

Authorization belongs to Laravel.

JavaScript is a convenience layer.

---

# Refactoring Strategy

Do not rewrite stable legacy JavaScript without business value.

When modifying legacy code:

Improve incrementally.

Reduce coupling.

Increase modularity.

Avoid introducing new technical debt.

---

# Migration Strategy

Preferred Direction

Legacy JS

↓

Alpine

↓

Livewire

↓

Laravel

Do not migrate simply because newer technology exists.

Migrate only when:

Adding features

Fixing bugs

Reducing complexity

Improving maintainability

---

# Decision Matrix

Need CRUD

↓

Livewire

Need Dropdown

↓

Alpine

Need DataTable

↓

Legacy Plugin

Need Rich Editor

↓

Legacy Plugin

Need Modal Animation

↓

Bootstrap

Need Business Logic

↓

Laravel

Need Validation

↓

Laravel / Livewire

Need Plugin

↓

Legacy JavaScript

---

# Anti-Patterns

Never

Duplicate Livewire state

Perform CRUD in jQuery

Perform validation in jQuery

Manipulate Livewire DOM

Initialize plugins twice

Leave destroyed listeners

Leak timers

Leak observers

Use AJAX instead of Livewire without reason

Create new jQuery-heavy features

---

# Cursor Rules

When generating JavaScript:

Assume Livewire owns application state.

Use legacy JavaScript only for existing plugin integration.

Always destroy and reinitialize plugins after Livewire updates.

Prefer delegated events.

Avoid direct DOM manipulation.

Avoid duplicate listeners.

Avoid global state.

Generate JavaScript that coexists safely with Livewire, Alpine, Bootstrap and Laravel.

Every new piece of legacy JavaScript should reduce technical debt rather than increase it.

