# Project Overview

## Application Type

This project is an enterprise Laboratory Information Management System (LIMS).

The application manages laboratory workflows, sample lifecycles, customer records, quotations, invoicing, reporting, laboratory analysis, approvals, authentication, auditing, notifications and administrative processes.

The codebase is mature and continuously evolving.

This is NOT a greenfield application.

Changes should integrate cleanly with the existing architecture instead of introducing competing patterns.

---

# Technology Stack

Backend

- PHP 8.4
- Laravel 12
- Livewire 3

Frontend

- Blade
- Alpine.js
- Bootstrap
- jQuery (legacy support only)

Authentication

- Laravel Sanctum

Authorization

- Spatie Laravel Permission

Documentation

- Swagger

---

# Development Philosophy

Always prioritize:

1. Maintainability
2. Readability
3. Predictability
4. Scalability
5. Performance
6. Security

Never optimize for writing fewer lines of code.

Optimize for code another developer can understand in six months.

---

# Architecture Philosophy

The project follows a layered architecture.

Presentation Layer

↓

Application Layer

↓

Business Logic

↓

Database

Business logic must never live inside Blade templates.

---

# General Principles

Always improve code quality.

Never increase technical debt.

Leave files cleaner than you found them.

Prefer extending existing architecture over introducing new architectural styles.

Avoid unnecessary abstractions.

Avoid overengineering.

Every class should have a single responsibility.

Every method should solve one problem.

Every component should own one feature.

---

# Existing Code

This is an existing enterprise application.

Respect existing naming conventions.

Respect existing folder structures.

Avoid unnecessary rewrites.

Avoid changing working code unless required.

Refactor incrementally.

---

# Consistency

Consistency is more important than personal preference.

If the project already has a standard, follow it.

Do not introduce multiple ways of solving the same problem.

---

# User Experience

Users are laboratory personnel.

Speed matters.

Accuracy matters.

Reliability matters.

Avoid unnecessary page reloads.

Avoid UI flickering.

Avoid duplicate requests.

Avoid losing user input.

---

# Performance Goals

Minimize:

- database queries
- page reloads
- Livewire requests
- DOM updates
- JavaScript execution

Prefer efficient rendering.

---

# Security

Always assume user input is malicious.

Validate all requests.

Authorize every protected action.

Escape output.

Never trust client-side validation.

---

# Code Generation Rules

When generating code:

Follow project conventions.

Generate production-ready code.

Avoid placeholder implementations.

Avoid TODO comments.

Avoid pseudo code.

Generate complete solutions.

---

# Refactoring Rules

Refactoring should:

reduce duplication

improve readability

reduce complexity

preserve behaviour

avoid unnecessary architectural changes

---

# Preferred Laravel Features

Prefer:

Dependency Injection

Policies

Form Requests

Events

Listeners

Queues

Services

DTOs where appropriate

Configuration over hardcoded values

---

# Avoid

Do not introduce unnecessary packages.

Do not introduce new frontend frameworks.

Do not replace Bootstrap.

Do not replace Livewire.

Do not replace Blade.

Do not replace existing architecture without strong justification.

---


# Cursor Behaviour

When multiple solutions exist:

Choose the solution that best fits the existing architecture rather than the newest Laravel feature.

Avoid introducing patterns inconsistent with the project.

Always explain significant architectural decisions.