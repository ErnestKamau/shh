# Laravel 12 Enterprise Standards

## Purpose

This document defines the Laravel development standards for the project.

All Laravel code must follow these rules.

The goal is to produce code that is:

- Consistent
- Predictable
- Testable
- Secure
- Performant
- Easy to maintain

Always prefer Laravel's native conventions over custom implementations unless there is a compelling architectural reason.

---

# Laravel Philosophy

Laravel provides solutions for most common application problems.

Prefer Laravel's built-in features before introducing custom abstractions or third-party packages.

Examples:

Use Policies instead of custom authorization checks.

Use Form Requests instead of manual validation.

Use Events for decoupled communication.

Use Queues for long-running tasks.

Use Notifications instead of custom messaging systems.

Avoid reinventing framework features.

---

# Project Structure

Respect Laravel's default directory structure.

Do not relocate framework directories.

New folders should only be introduced when they improve clarity.

Avoid deeply nested directories.

---

# Controllers

Controllers coordinate requests.

Controllers should:

- Validate requests (via Form Requests)
- Authorize actions
- Call services
- Return responses

Controllers should never contain business logic.

Controllers should never contain SQL queries.

Controllers should remain small.

Target size:

Less than 200 lines.

---

# Form Requests

Always use Form Requests for validation.

Do not validate large forms directly inside controllers or Livewire components unless Livewire validation is required.

Validation belongs close to the incoming request.

Rules should remain readable.

Extract reusable validation logic when appropriate.

---

# Services

Business workflows belong in Services.

Examples:

Create Sample

Approve Results

Generate Invoice

Issue Certificate

Receive Shipment

Calculate Charges

Do not create Services that simply wrap Eloquent methods.

---

# Models

Models represent data.

Models may contain:

Relationships

Scopes

Accessors

Mutators

Attribute casting

Small helper methods

Avoid placing business workflows inside Models.

Avoid making Models service locators.

---

# Eloquent

Always prefer Eloquent over raw SQL.

Only use raw SQL when:

Performance requires it.

Database-specific features are needed.

Complex reporting cannot reasonably be expressed.

Document why raw SQL was necessary.

---

# Relationships

Always define relationships.

Prefer relationships over manual joins.

Always eager load relationships when appropriate.

Avoid N+1 queries.

---

# Route Model Binding

Prefer Route Model Binding.

Good

Sample $sample

Bad

$id

Sample::find($id)

---

# Authorization

Never authorize in Blade.

Never rely on hidden buttons.

Authorization belongs in:

Policies

Gates

Middleware

Livewire authorize() calls

Every protected action must be authorized.

---

# Policies

Prefer Policies.

Every major model should have one.

Examples:

SamplePolicy

InvoicePolicy

ClientPolicy

ReportPolicy

CertificatePolicy

---

# Middleware

Middleware should handle:

Authentication

Permissions

Localization

Rate limiting

Request preprocessing

Avoid business logic inside middleware.

---

# Events

Use Events when multiple systems react to one action.

Examples:

SampleApproved

InvoicePaid

CertificateGenerated

ClientRegistered

Avoid directly calling unrelated services.

---

# Listeners

Listeners should perform one responsibility.

Good

Send notification

Generate PDF

Audit action

Sync API

Avoid listeners performing unrelated work.

---

# Jobs

Long-running tasks must use queues.

Examples:

Large exports

PDF generation

Email sending

External API synchronization

Report generation

Never block user requests unnecessarily.

---

# Notifications

Use Laravel Notifications.

Avoid manually sending emails throughout the codebase.

Centralize notification behavior.

---

# Mail

Mailables should remain presentation-focused.

Do not calculate business data inside Mailables.

---

# Database Transactions

Use transactions whenever multiple writes depend on each other.

Example:

Create Sample

↓

Create Analysis

↓

Create Audit

↓

Create Barcode

↓

Commit

Never leave partial writes.

---

# Validation

Validate all user input.

Never trust client-side validation.

Prefer Rule objects for complex validation.

---

# Configuration

Never hardcode:

URLs

Emails

Timeouts

Feature flags

Credentials

Move configuration into config files.

---

# Environment Variables

Only access env() inside configuration files.

Never call env() directly in application code.

Always use config().

---

# Dependency Injection

Always prefer constructor injection.

Avoid resolving services manually.

Bad

app(Service::class)

Good

Constructor Injection

---

# Facades

Facades are acceptable for Laravel services.

Examples:

Cache

DB

Storage

Log

Auth

Avoid using Facades as service locators.

---

# Database Queries

Never query inside loops.

Always eager load relationships.

Prefer chunking for large datasets.

Use cursor() when processing massive data.

---

# Pagination

Always paginate large datasets.

Avoid returning thousands of records to Blade.

---

# Caching

Cache expensive operations.

Examples:

Configuration

Reference tables

Reports

Statistics

Avoid caching frequently changing transactional data unless invalidation is well-defined.

---

# Logging

Log meaningful events.

Include:

User

Action

Context

Avoid excessive logging.

Never log secrets.

---

# Error Handling

Fail gracefully.

Provide useful log messages.

Return user-friendly responses.

Avoid exposing stack traces.

---

# Scheduling

Use Laravel Scheduler.

Avoid cron scripts scattered across servers.

Centralize scheduled tasks.

---

# File Storage

Always use Laravel Storage.

Never hardcode filesystem paths.

Support local and cloud storage transparently.

---

# APIs

Always return Resources for API responses.

Avoid returning raw Models.

Version public APIs when appropriate.

---

# Resources

Use API Resources to shape responses.

Avoid exposing unnecessary database columns.

---

# Migrations

Migrations must be:

Reversible

Atomic

Descriptive

Avoid editing old migrations after deployment.

Always create new migrations.

---

# Seeders

Seeders should create predictable development data.

Avoid embedding business workflows inside seeders.

---

# Factories

Use Factories for:

Testing

Development

Seeding

Factories should generate realistic data.

---

# Commands

Artisan Commands should orchestrate work.

Avoid placing business logic inside Commands.

Delegate to Services.

---

# Helpers

Prefer dependency injection over helper-heavy code.

Avoid large collections of global helper functions.

---

# Package Usage

Before installing a package ask:

Can Laravel already do this?

Does this package have active maintenance?

Will this increase long-term complexity?

Prefer fewer high-quality dependencies.

---

# Backward Compatibility

When modifying existing modules:

Preserve behavior.

Avoid unnecessary rewrites.

Maintain API compatibility unless intentionally changing it.

---

# Cursor Rules

When generating Laravel code:

Follow Laravel conventions.

Prefer framework features over custom implementations.

Generate thin Controllers.

Generate thin Models.

Generate reusable Services.

Always use Policies.

Always use Form Requests where applicable.

Always eager load relationships.

Avoid N+1 queries.

Use dependency injection.

Use transactions for multi-step writes.

Generate production-ready Laravel code.

Do not introduce architectural patterns inconsistent with the rest of the project.

When multiple Laravel approaches exist, choose the one that is the most maintainable and most aligned with Laravel 12 best practices.
