# PHP 8.4 Enterprise Standards

## Purpose

This document defines the PHP coding standards for the project.

These standards apply to every PHP file including:

- Controllers
- Livewire Components
- Models
- Services
- Jobs
- Listeners
- Events
- Policies
- Form Requests
- Commands
- Traits
- Helpers

These rules exist to maximize maintainability, readability, correctness and long-term scalability.

---

# PHP Version

Current Version

PHP 8.4

Always use language features supported by the project's PHP version.

Do not generate code compatible with older PHP versions.

---


# Type Declarations

Always type:

Parameters

Return values

Properties

Constructor arguments

Never omit types when they can be expressed.

Good

public function approve(Sample $sample): void

Bad

public function approve($sample)

---

# Nullable Types

Use nullable types only when null is a valid business value.

Avoid unnecessary nullable types.

Bad

?string

when an empty string should be used.

---

# Return Types

Every public method must declare a return type.

Avoid mixed.

Prefer

Collection

array

bool

int

string

Model

DTO

Enum

Value Object

Never leave return types undocumented.

---

# Mixed Type

Avoid mixed.

Use only when absolutely unavoidable.

Prefer explicit unions.

Example

string|int

instead of

mixed

---

# Union Types

Prefer union types over PHPDoc where appropriate.

Example

User|Admin

instead of

@var mixed

---

# Constructor Property Promotion

Use constructor property promotion whenever appropriate.

Example

public function __construct(
    private readonly SampleService $sampleService
) {}

Avoid repetitive property assignment.

---

# Readonly

Use readonly for dependencies.

Dependencies should never change after construction.

Good

private readonly ReportService $reportService;

---

# Constants

Prefer constants over magic values.

Bad

$status = 5;

Good

$status = SampleStatus::APPROVED;

---

# Enums

Use PHP Enums instead of string constants whenever practical.

Good

SampleStatus::Pending

SampleStatus::Approved

SampleStatus::Rejected

Avoid

"PENDING"

"APPROVED"

"REJECTED"

scattered throughout the project.

---

# Match Expressions

Prefer match over long switch statements.

Good

match ($status) {

    ...

}

Avoid deeply nested switch blocks.

---

# Exceptions

Throw meaningful exceptions.

Never throw generic Exception.

Good

SampleNotFoundException

InvalidStatusTransitionException

DuplicateSampleException

Avoid

throw new Exception();

---

# Exception Messages

Messages should help developers diagnose problems.

Bad

Something went wrong.

Good

Sample cannot be approved because results are incomplete.

---

# Catching Exceptions

Catch exceptions only when recovery is possible.

Do not catch exceptions simply to ignore them.

Never use empty catch blocks.

---

# Dependency Injection

Always prefer constructor injection.

Avoid

new Service()

inside methods.

Laravel's container should resolve dependencies.

---

# Service Container

Resolve dependencies through the container.

Never manually instantiate services unless necessary.

---

# Static Methods

Avoid static methods for business logic.

Use services instead.

Allowed

Utility functions

Value Objects

Enums

Not allowed

Business workflows

---

# Traits

Traits should be small.

Traits should solve one reusable concern.

Avoid giant utility traits.

---

# Helper Functions

Avoid global helper functions.

Prefer:

Services

Value Objects

Small reusable classes

Only create helpers for genuinely universal functionality.

---

# SOLID Principles

Follow all SOLID principles.

Especially:

Single Responsibility

Dependency Inversion

Open Closed Principle

Avoid violating these for convenience.

---

# Method Length

Aim for

10–30 lines.

Methods exceeding approximately 50 lines should be reviewed for extraction.

Avoid extremely long methods.

---

# Class Size

Aim for

under 300 lines.

Classes approaching 500 lines should be evaluated for decomposition.

Avoid God Classes.

---

# Nesting

Avoid deep nesting.

Prefer early returns.

Bad

if (...)

{

    if (...)

    {

        if (...)

        {

        }

    }

}

Good

if (!$sample)

    return;

---

# Boolean Variables

Use meaningful names.

Good

$isApproved

$hasPermission

$canEdit

Avoid

$flag

$temp

$value

$data1

---

# Variable Names

Names should explain intent.

Avoid abbreviations.

Good

$approvedSamples

Bad

$as

---

# Temporary Variables

Minimize unnecessary temporary variables.

Write expressive code instead.

---

# Magic Numbers

Never use unexplained numeric values.

Replace with:

Enums

Constants

Configuration

---

# Configuration

Do not hardcode:

URLs

Emails

Limits

Timeouts

Feature flags

Move them into configuration files.

---

# Arrays

Use associative arrays only for simple data.

For complex business data prefer:

DTOs

Value Objects

Models

Collections

---

# Collections

Prefer Laravel Collections over complex array manipulation.

Avoid nested array logic.

---

# Value Objects

Use Value Objects for concepts like:

Money

Percentage

Weight

Quantity

Email

Phone Number

Measurement Units

Avoid primitive obsession.

---

# DTOs

Use DTOs when moving structured data between layers.

Avoid passing huge associative arrays.

---

# Comments

Write self-documenting code.

Comments should explain:

Why

not

What

Bad

Increment counter

Good

Counter is incremented to ensure unique sample numbering.

---

# PHPDoc

Use PHPDoc when:

Generics

Collections

Complex arrays

Templates

Inheritance

Avoid redundant PHPDoc.

---

# Formatting

One responsibility per method.

One logical block per section.

Avoid giant methods.

Whitespace should improve readability.

---

# Immutability

Prefer immutable objects.

Avoid changing object state unnecessarily.

---

# Loops

Prefer Collections when expressive.

Prefer foreach over complex for loops.

Avoid deeply nested loops.

---

# Performance

Avoid repeated:

Database queries

File reads

Expensive calculations

inside loops.

Cache where appropriate.

---

# Logging

Log useful context.

Never log passwords.

Never log secrets.

Never log access tokens.

---

# Security

Never trust user input.

Escape output.

Validate before processing.

Authorize before executing.

---

# Cursor Rules

When generating PHP:

Always use strict typing.

Always use constructor dependency injection.

Always declare return types.

Prefer Enums over strings.

Prefer Services over static classes.

Prefer readability over cleverness.

Avoid oversized classes.

Avoid oversized methods.

Avoid duplicated logic.

Generate enterprise-grade PHP using modern PHP 8.4 features.

Favor maintainability over brevity.

Every generated class should have a clear single responsibility.
