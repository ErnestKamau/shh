# Frontend Architecture Standards

## Purpose

This document defines how every frontend technology in this project interacts.

The project uses multiple frontend technologies.

These technologies complement one another.

They must never compete for ownership of the same responsibility.

The primary objective is predictability.

Every piece of UI should have exactly one owner.

---

# Frontend Stack

Server Rendering

Laravel Blade

↓

Server Reactivity

Livewire

↓

UI State

Alpine.js

↓

Presentation

Bootstrap

↓

Legacy Plugins

jQuery

---

# Ownership Principle

One responsibility.

One owner.

Never duplicate ownership.

If multiple technologies control the same thing,
the architecture is wrong.

---

# Responsibility Matrix

Laravel

owns

✓ Business Logic

✓ Authorization

✓ Validation

✓ Database

✓ Services

✓ Policies

✓ Events

---

Livewire

owns

✓ Server State

✓ Forms

✓ CRUD

✓ Loading Data

✓ Saving Data

✓ Pagination

✓ Filtering

✓ Searching

✓ Validation

✓ Component Communication

---

Blade

owns

✓ HTML

✓ Layouts

✓ Components

✓ Rendering

✓ Presentation

---

Alpine

owns

✓ Dropdowns

✓ Accordions

✓ Collapse

✓ Tabs

✓ Local Toggles

✓ Temporary UI State

✓ Animations

---

Bootstrap

owns

✓ Layout

✓ Grid

✓ Styling

✓ Modal Animation

✓ Utility Classes

---

jQuery

owns ONLY

✓ Legacy Plugins

✓ DataTables

✓ Select2

✓ Existing Widgets

Nothing else.

---

# State Ownership

Server Data

↓

Livewire

Temporary UI

↓

Alpine

DOM Styling

↓

Bootstrap

Legacy Plugin State

↓

jQuery

Never duplicate state.

---

# Source of Truth

Every value has one source.

Bad

Livewire Property

+

Alpine Variable

+

Hidden Input

+

jQuery Variable

Good

Livewire Property

↓

Alpine reads it

↓

Display

---

# DOM Ownership

Livewire owns

Rendered HTML

Blade Components

Form Elements

Dynamic Lists

Validation Messages

Never manipulate these with jQuery.

---

Alpine owns

Visibility

Animation

Open

Closed

Expanded

Collapsed

Nothing more.

---

Bootstrap owns

Appearance

Spacing

Grid

Utilities

Responsive Layout

Never use Bootstrap JS to replace Livewire.

---

jQuery owns

Only plugin initialization.

Never use

.html()

.append()

.remove()

inside Livewire components.

---

# Form Ownership

Livewire owns

Form Values

Validation

Submission

Saving

Errors

Alpine may control

Visibility

Wizard Navigation

Animations

Never duplicate form values.

---

# Modal Ownership

Bootstrap

owns animation.

Livewire

owns modal data.

Alpine

may assist with small UI interactions.

jQuery

must never control modal business logic.

---

# Tables

Livewire

owns

Filtering

Searching

Pagination

Selection

Bulk Actions

DataTables may be used only when necessary.

---

# Dropdowns

Simple dropdowns

↓

Alpine

Searchable dropdowns

↓

Legacy plugin if already in project

Otherwise

Prefer Alpine.

---

# Validation

Server Validation

↓

Livewire

Client Validation

↓

Optional UX enhancement

Never replace server validation.

---

# Notifications

Livewire

dispatches event

↓

Toast Component

↓

Bootstrap UI

Avoid duplicated notification systems.

---

# Loading States

Every server request

↓

Livewire Loading

Never create custom loading logic if Livewire provides it.

---

# Search

Search belongs to Livewire.

Use

debounce

Avoid jQuery AJAX.

---

# Pagination

Pagination belongs to Livewire.

Avoid client-side pagination for server datasets.

---

# File Uploads

Uploads belong to Livewire.

Progress indicators may use Alpine.

Storage belongs to Laravel.

---

# Event Flow

Preferred

User

↓

Livewire

↓

Service

↓

Database

↓

Livewire Event

↓

Browser Event

↓

Alpine UI

Avoid

jQuery

↓

AJAX

↓

DOM

↓

Livewire

↓

DOM

---

# Browser Events

Use browser events for

Toast

Open Modal

Close Modal

Scroll

Focus

Animation

Never use browser events for business workflows.

---

# Livewire Events

Use Livewire events for

Refreshing Components

Updating Parent

Updating Child

Cross Component Communication

Avoid generic names.

---

# Alpine Events

Use Alpine only for local UI interactions.

Avoid turning Alpine into an application framework.

---

# wire:ignore

Only use wire:ignore when integrating

Select2

DataTables

Charts

Rich Editors

Maps

Complex third-party widgets

Never use wire:ignore to hide architectural problems.

---

# Third Party Plugins

Plugins must

Initialize

↓

Destroy

↓

Reinitialize

after Livewire updates.

Never assume DOM remains unchanged.

---

# Lifecycle Integration

Plugin

↓

Livewire Render

↓

Destroy Plugin

↓

Render

↓

Initialize Plugin

Always respect Livewire lifecycle.

---

# JavaScript Files

Never scatter JavaScript.

Keep feature-specific scripts together.

Avoid inline scripts.

---

# CSS

Bootstrap first.

Custom CSS second.

Avoid inline styles.

---

# Progressive Enhancement

The application should remain functional without heavy JavaScript whenever practical.

Do not create unnecessary SPA behavior.

---

# Performance

Avoid

Duplicate Requests

Duplicate Rendering

Duplicate State

Repeated Plugin Initialization

Repeated DOM Queries

---

# Accessibility

Bootstrap components must remain accessible.

Do not remove keyboard support.

Maintain focus management.

Provide labels.

Maintain ARIA attributes where applicable.

---

# Debugging Principle

When a frontend bug occurs ask:

Who owns this?

If more than one answer exists,

the architecture is incorrect.

---

# Decision Tree

Need CRUD?

↓

Livewire

Need Toggle?

↓

Alpine

Need Styling?

↓

Bootstrap

Need Plugin?

↓

jQuery

Need Business Logic?

↓

Laravel

Need Validation?

↓

Livewire

Need Animation?

↓

Alpine

Need Authorization?

↓

Laravel

---

# Cursor Rules

Never duplicate ownership.

Never duplicate state.

Never manipulate Livewire DOM with jQuery.

Never store server state in Alpine.

Never perform CRUD in Alpine.

Never perform business logic in JavaScript.

Bootstrap is presentation.

Livewire is interaction.

Laravel is business logic.

Alpine is temporary UI.

jQuery exists only for legacy plugins.

Whenever multiple solutions exist,

choose the one with the clearest ownership.
