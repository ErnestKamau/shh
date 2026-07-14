# Bootstrap 5 Enterprise Standards

## Purpose

This document defines how Bootstrap should be used throughout the application.

Bootstrap is responsible for presentation.

Bootstrap is NOT responsible for:

- Business Logic
- Validation
- CRUD
- State Management
- Database interaction

Bootstrap provides visual consistency.

Laravel and Livewire provide application behavior.

---

# Bootstrap Philosophy

Bootstrap should be used as intended.

Avoid fighting Bootstrap.

Prefer Bootstrap utilities over custom CSS.

Only write custom CSS when Bootstrap cannot solve the problem cleanly.

---

# Responsibilities

Bootstrap owns

✓ Layout

✓ Grid

✓ Typography

✓ Colors

✓ Spacing

✓ Cards

✓ Tables

✓ Buttons

✓ Forms

✓ Responsive Design

✓ Modals

✓ Alerts

✓ Navigation

Bootstrap does not own

Business Rules

Server State

Permissions

Validation Logic

Application State

---

# Grid System

Always use Bootstrap Grid.

Preferred

container

↓

row

↓

col

Avoid deeply nested rows.

Avoid unnecessary wrappers.

---

# Responsive Design

Every page should support

Desktop

Laptop

Tablet

Mobile

Avoid desktop-only layouts.

---

# Utility Classes

Prefer Bootstrap utility classes.

Examples

mt-3

mb-2

px-4

d-flex

justify-content-between

align-items-center

text-end

Avoid unnecessary custom CSS.

---

# Spacing Standards

Prefer utilities.

Avoid inline margins.

Good

mb-3

py-4

gx-3

Avoid

style="margin-top:15px"

---

# Colors

Use Bootstrap theme colors.

Examples

primary

secondary

success

danger

warning

info

dark

Avoid random hex values.

---

# Typography

Use Bootstrap typography.

Avoid manually setting font sizes.

Prefer

h1-h6

lead

small

fw-bold

text-muted

---

# Cards

Use cards consistently.

Cards should represent one logical section.

Avoid placing unrelated information inside one card.

---

# Tables

Tables should remain readable.

Prefer

table

table-hover

table-striped

table-responsive

Avoid excessive borders.

Large datasets belong to Livewire pagination or DataTables.

---

# Forms

Use Bootstrap form controls consistently.

Every field should include

Label

Input

Validation Feedback

Help Text (if needed)

Avoid unlabeled inputs.

---

# Form Groups

Keep form layout consistent.

Preferred

Label

↓

Input

↓

Validation

↓

Help Text

Avoid inconsistent spacing.

---

# Buttons

Buttons should clearly indicate intent.

Primary

Main action

Secondary

Alternative action

Danger

Destructive action

Warning

Potentially risky action

Success

Positive confirmation

Avoid multiple primary buttons competing for attention.

---

# Button Sizes

Use consistent sizing.

Avoid random button heights.

---

# Icons

Use one icon library.

Avoid mixing:

Bootstrap Icons

Font Awesome

Heroicons

Material Icons

Choose one.

---

# Alerts

Use Bootstrap alerts.

Keep messages concise.

Avoid exposing technical errors.

---

# Badges

Badges indicate status.

Examples

Pending

Approved

Rejected

Cancelled

Avoid using badges for actions.

---

# Navigation

Navigation should remain predictable.

Highlight active routes.

Keep menus grouped logically.

Avoid clutter.

---

# Breadcrumbs

Use breadcrumbs for deep navigation.

Avoid breadcrumbs on simple pages.

---

# Tabs

Bootstrap tabs

or

Alpine tabs

Do not mix implementations.

---

# Accordions

Prefer Bootstrap accordion unless custom interaction is required.

Avoid creating custom accordion JavaScript.

---

# Modals

Bootstrap owns

Animation

Transitions

Focus

Keyboard Support

Backdrop

Livewire owns

Modal Data

Form State

Validation

Saving

Never duplicate modal ownership.

---

# Modal Rules

One modal

↓

One responsibility

Avoid gigantic multi-purpose modals.

---

# Dropdowns

Simple

↓

Bootstrap

Complex Interactive

↓

Alpine

Legacy Plugin

↓

jQuery

Choose one owner.

---

# Toasts

Use Bootstrap toast UI.

Livewire dispatches events.

Bootstrap displays notification.

Avoid multiple notification systems.

---

# Offcanvas

Use Bootstrap Offcanvas for

Navigation

Filters

Small Panels

Avoid replacing pages with Offcanvas.

---

# Collapse

Prefer Bootstrap Collapse.

Use Alpine only when more control is needed.

---

# Lists

Use Bootstrap list groups.

Avoid manually recreating list styling.

---

# Images

Images should be

Responsive

Optimized

Lazy Loaded when appropriate

Avoid oversized images.

---

# Responsive Utilities

Use Bootstrap responsive utilities.

Examples

d-none d-lg-block

d-md-flex

Avoid custom media queries when Bootstrap utilities suffice.

---

# CSS

Prefer Bootstrap utilities.

Then project CSS.

Avoid inline styles.

Avoid !important.

---

# Custom CSS

Only write CSS when:

Bootstrap cannot solve the problem.

The design system requires it.

Custom CSS should remain reusable.

Avoid page-specific CSS.

---

# JavaScript

Bootstrap JavaScript should only manage UI behavior.

Never use Bootstrap JS for business workflows.

---

# Accessibility

Maintain:

Keyboard Navigation

Focus States

Labels

ARIA

Color Contrast

Bootstrap components should remain accessible.

---

# Performance

Avoid:

Unused CSS

Duplicate Components

Large DOM Trees

Excessive Nesting

Repeated Layouts

---

# Anti-Patterns

Never

Inline CSS

Random utility combinations

Hardcoded colors

Duplicate modal markup

Custom grid systems

Competing UI frameworks

Manual spacing

Inconsistent form layouts

---

# UI Consistency

Every screen should feel like part of the same application.

Users should recognize:

Buttons

Forms

Cards

Tables

Navigation

without relearning each page.

---

# Cursor Rules

When generating Bootstrap code:

Prefer Bootstrap utilities.

Keep layouts responsive.

Generate accessible markup.

Use semantic HTML.

Avoid inline styles.

Keep spacing consistent.

Use Bootstrap components before custom CSS.

Maintain visual consistency across the application.

Generate enterprise-quality user interfaces.
