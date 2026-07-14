# Alpine.js Enterprise Standards

## Purpose

This document defines how Alpine.js should be used throughout the project.

Alpine exists to enhance the user interface.

It is **not** the application's business layer.

It is **not** a replacement for Livewire.

Its primary responsibility is managing temporary client-side UI state.

---

# Core Philosophy

Laravel owns business logic.

↓

Livewire owns server state.

↓

Blade renders HTML.

↓

Alpine owns temporary UI behavior.

↓

Bootstrap styles the interface.

---

# Responsibilities

Alpine should be used for:

- Dropdowns
- Accordions
- Tabs
- Expand / Collapse
- Show / Hide
- Modals (visual state only)
- Toast visibility
- Small animations
- Keyboard shortcuts
- Temporary UI preferences

Alpine should **not** perform:

- CRUD
- Validation
- Authorization
- Database updates
- Business workflows
- API requests (unless explicitly required)

---

# State Ownership

Livewire owns:

- Database values
- Forms
- Records
- Search
- Filters
- Pagination
- Validation
- Permissions

Alpine owns:

- Menu open
- Dropdown visibility
- Active tab
- Sidebar collapsed
- Toast visible
- Loading animation
- Hover state

Never duplicate ownership.

---

# One Component One Purpose

Each Alpine component should solve one UI problem.

Good

Dropdown

Accordion

Sidebar

Tooltip

Tabs

Modal Toggle

Bad

Entire Dashboard

Massive Form Logic

Business Workflow

---

# Component Size

Recommended

Under 100 lines

Review

100–200 lines

Avoid

200+ line Alpine components

Large Alpine components usually indicate misplaced responsibilities.

---

# x-data

Keep x-data minimal.

Good

```
x-data="{
    open:false
}"
```

Avoid storing application data inside x-data.

---

# Application Data

Never copy Livewire properties into Alpine.

Bad

```
x-data="{
    users:[]
}"
```

Good

```
@foreach(...)
```

or

```
$wire.users
```

The server owns server data.

---

# entangle()

Use entangle only when shared state is truly required.

Good

Modal Open

Selected Tab

Current Wizard Step

Avoid entangling:

Large Objects

Collections

Database Records

Search Results

---

# Local State

Prefer local Alpine state.

Avoid global stores unless necessary.

---

# Alpine Stores

Use stores only for application-wide UI state.

Examples

Dark Mode

Sidebar

Language Selector

Notification Center

Avoid storing business data.

---

# Methods

Methods should be small.

Good

toggle()

close()

open()

selectTab()

Avoid:

saveInvoice()

approveSample()

deleteClient()

Business actions belong in Livewire.

---

# Computed Values

Use getters for simple derived UI values.

Avoid expensive calculations.

---

# Watchers

Use `$watch()` sparingly.

Avoid cascading watchers.

Never use watchers for business workflows.

---

# Initialization

Use `x-init` only for UI initialization.

Avoid AJAX.

Avoid database logic.

Avoid complex setup.

---

# Events

Use Alpine events only for UI interaction.

Examples

Open Sidebar

Close Dropdown

Show Tooltip

Avoid using Alpine events for business events.

---

# Browser Events

Listen for Livewire browser events when updating UI.

Example use cases

Show Toast

Close Modal

Focus Input

Scroll Into View

Do not duplicate server-side actions.

---

# Livewire Integration

Preferred flow

User

↓

Livewire Action

↓

Database

↓

Browser Event

↓

Alpine Updates UI

Avoid bypassing Livewire.

---

# DOM Manipulation

Avoid direct DOM manipulation.

Use Alpine directives instead.

Prefer

x-show

x-bind

x-transition

Avoid

document.querySelector()

element.style.display

innerHTML

---

# Conditional Display

Use

x-show

instead of manually hiding elements.

---

# Visibility

Use

x-cloak

to prevent flashing content.

Always include

```
[x-cloak]{
display:none!important;
}
```

---

# Loops

Use Blade loops for server data.

Use x-for only for small UI collections.

Avoid rendering large datasets with Alpine.

---

# Forms

Livewire owns forms.

Alpine enhances UX only.

Examples

Password Toggle

Wizard Navigation

Step Indicators

Character Counter

Preview

Avoid submitting forms with Alpine when Livewire owns the form.

---

# Validation

Never duplicate validation rules.

Livewire validates.

Alpine displays.

---

# Modals

Bootstrap

↓

Animation

Livewire

↓

Data

Alpine

↓

Visibility Helpers

Do not duplicate modal state.

---

# Animations

Prefer Alpine transitions.

Avoid jQuery animations.

Use

x-transition

instead of manual CSS when practical.

---

# Performance

Avoid:

Large Stores

Large Arrays

Nested Watchers

Repeated DOM Queries

Heavy Loops

---

# Accessibility

Maintain keyboard navigation.

Respect focus.

Close dialogs using Escape.

Maintain ARIA attributes.

Do not hide important content from screen readers.

---

# JavaScript

Keep Alpine declarative.

Avoid mixing imperative JavaScript.

---

# Naming

Use meaningful names.

Good

open

expanded

selectedTab

isVisible

Avoid

flag

temp

x

value

---

# Error Handling

UI failures should never break the page.

Gracefully recover.

---

# Anti-Patterns

Never

Store database records

Run business workflows

Perform CRUD

Call APIs unnecessarily

Duplicate Livewire state

Write jQuery inside Alpine

Create giant stores

Use Alpine as an application framework

---

# Cursor Rules

When generating Alpine code:

Use Alpine only for temporary UI state.

Keep x-data small.

Avoid duplicated server state.

Use entangle sparingly.

Prefer Livewire for application data.

Use x-show instead of manual DOM manipulation.

Prefer Alpine transitions.

Generate readable, declarative Alpine components.

Keep Alpine focused exclusively on user interface behavior.
