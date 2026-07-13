# Enterprise Architecture Standards

## Purpose

This document defines the architectural rules for the entire application.

Every feature, component, controller, service, model, and view must follow these principles.

Architecture consistency always takes precedence over individual coding preferences.

---

# Architectural Goals

The system should be:

- Modular
- Predictable
- Easy to debug
- Easy to extend
- Easy to test
- Easy to maintain

Every new feature should fit naturally into the existing architecture.

Avoid introducing competing architectural styles.

---

# Architectural Layers

The application follows a layered architecture.

Presentation Layer

↓

Application Layer

↓

Business Layer

↓

Persistence Layer

↓

Database

Each layer has clearly defined responsibilities.

---

# Presentation Layer

Responsible for:

- Blade Views
- Livewire Components
- Alpine State
- Bootstrap UI
- User interaction

The presentation layer should never contain business logic.

Allowed:

✔ displaying data

✔ formatting values

✔ showing validation errors

✔ conditional rendering

✔ emitting events

Not allowed:

✘ database queries

✘ authorization decisions

✘ complex calculations

✘ business workflows

✘ direct SQL

---

# Application Layer

Responsible for coordinating workflows.

Examples:

Create Sample

Approve Result

Generate Invoice

Register Client

Receive Sample

Generate Certificate

The application layer coordinates work.

It should not contain presentation code.

---

# Business Layer

Contains business rules.

Examples:

Sample numbering

Laboratory workflow

Approval chains

Pricing calculations

Certificate generation

Status transitions

Business rules should exist in dedicated services.

Never inside Blade.

Never inside JavaScript.

Avoid placing large amounts of business logic inside Livewire components.

---

# Persistence Layer

Responsible for:

Eloquent Models

Relationships

Scopes

Database interaction

Persistence should not contain UI logic.

---

# Single Responsibility Principle

Every class should have one responsibility.

Good

SampleService

InvoiceService

CertificateGenerator

ReportExporter

Bad

UtilityService

GeneralService

HelperService

EverythingManager

Avoid "God Classes."

---

# Feature-Based Organization

Keep related code together.

Example

Feature

    Livewire
    Blade
    Requests
    Services
    Policies
    Events
    Tests

Avoid scattering feature logic throughout the project.

---

# Separation of Concerns

Keep responsibilities separate.

Livewire

↓

Service

↓

Model

↓

Database

Never skip layers unnecessarily.

---

# State Ownership

Only one technology should own a piece of state.

Server data

↓

Livewire

UI state

↓

Alpine

Legacy widgets

↓

jQuery

Database

↓

Laravel

Never duplicate ownership.

---

# Source of Truth

Every value should have one source of truth.

Bad

Livewire property

+

Alpine property

+

Hidden input

+

jQuery variable

Good

Livewire owns server data.

Alpine reflects UI state.

---

# Data Flow

Preferred flow

User

↓

Livewire

↓

Service

↓

Model

↓

Database

↓

Livewire

↓

Blade

↓

Browser

Avoid bypassing this flow.

---

# Dependency Direction

Dependencies should flow downward.

Blade

↓

Livewire

↓

Services

↓

Models

↓

Database

Lower layers should never depend on higher layers.

---

# Business Logic Rules

Business rules belong in Services.

Examples

Pricing

Approval logic

Status transitions

Notifications

Barcode generation

Certificate validation

Avoid placing business logic inside:

Blade

JavaScript

Alpine

Bootstrap

---

# Service Classes

Use services when:

multiple models are involved

complex business rules exist

external APIs are used

transactions are required

workflows span multiple steps

Do not create services that simply wrap Eloquent.

---

# Controllers

Controllers should remain thin.

Responsibilities

Validate request

Authorize request

Call service

Return response

Avoid large controllers.

---

# Livewire Components

Livewire components are presentation controllers.

Responsibilities

Load data

Validate forms

Handle interaction

Emit events

Call services

Render views

Avoid placing large business workflows inside Livewire.

---

# Models

Models should represent data.

Allowed

Relationships

Scopes

Accessors

Mutators

Small helper methods

Avoid

Large workflows

External API calls

Rendering HTML

Notifications

---

# Blade Views

Blade should display data.

Avoid placing business logic inside Blade.

Keep Blade declarative.

---

# JavaScript

JavaScript enhances the interface.

It should not replace Laravel.

Do not duplicate backend validation.

---

# Events

Use events for loose coupling.

Examples

SampleApproved

InvoiceGenerated

CertificateIssued

Avoid tightly coupling unrelated modules.

---

# Transactions

Whenever multiple writes must succeed together:

Use database transactions.

Never leave partial writes.

---

# Error Handling

Fail gracefully.

Log useful information.

Never expose sensitive errors to users.

Avoid swallowing exceptions.

---

# Reuse

Prefer:

Services

Blade Components

Traits (sparingly)

Reusable Livewire Components

Avoid copy-paste programming.

---

# Coupling

Aim for:

Low coupling

High cohesion

Every module should know as little as possible about others.

---

# Scalability

Every feature should be written assuming:

More users

More laboratories

More records

More reports

More integrations

Avoid shortcuts that block future growth.

---

# Performance

Avoid:

N+1 queries

Duplicate requests

Repeated calculations

Rendering unnecessary components

Always eager load relationships when appropriate.

---

# Security

Every action requires:

Validation

Authorization

Auditability where applicable

Never trust browser data.

---

# Refactoring

Improve architecture incrementally.

Do not rewrite stable modules simply because a newer Laravel feature exists.

Respect existing patterns unless they are demonstrably harmful.

---

# Architectural Decision Priority

When multiple solutions exist, prioritize in this order:

1. Maintainability
2. Consistency
3. Simplicity
4. Performance
5. Developer convenience

---

# Definition of Good Code

Good code is:

Readable

Predictable

Modular

Testable

Secure

Maintainable

Easy to extend

Easy to debug

---

# Cursor Rules

When generating code:

Follow existing project architecture.

Do not introduce new architectural styles.

Do not duplicate business logic.

Do not mix frontend responsibilities.

Keep responsibilities separated.

Generate production-ready enterprise code.

When uncertain, choose the solution that minimizes long-term maintenance cost rather than the one with the fewest lines of code.

