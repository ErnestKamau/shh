# Encrypted & Hashed Data Reference

This document provides a comprehensive reference to every field in the application that is stored in an **encrypted** or **hashed** form. All encryption is handled transparently by Laravel's `encrypted` cast (using `APP_KEY` via AES-256-CBC), and all password/token hashing uses **bcrypt** via `Hash::make()` / `bcrypt()` (unless noted otherwise).

---

## Table of Contents

1. [Overview](#overview)
2. [Encryption Technology](#encryption-technology)
3. [User Model](#1-user--appuserphp)
4. [CRM Customer](#2-crm-customer--appmodelscrm-crmcustomerphp)
5. [CRM Customer Contact](#3-crm-customer-contact--appmodelescrm-customercontactphp)
6. [Sample Header (Batch)](#4-sample-header--appsampleheaderphp)
7. [Sample Details](#5-sample-details--appsampledetailsphp)
8. [Captured Result](#6-captured-result--appcapturedresultphp)
9. [Result (Published)](#7-result-published--appresultphp)
10. [Worksheet Formula Record](#8-worksheet-formula-record--appmodelesworksheets-samplecapturedworksheetformulaphp)
11. [Worksheet Formula Step Data](#9-worksheet-formula-step-data--appmodelsworksheets-sampleworksheetformularstepdataphp)
12. [Worksheet Formula Mandatory Data](#10-worksheet-formula-mandatory-data--appmodelsworksheets-sampleworksheetformularmandatorydataphp)
13. [Method Sequence Stage Sample Result](#11-method-sequence-stage-sample-result--appmodelsworksheets-methodsequencestagesampleresultphp)
14. [Captured Procedure Value](#12-captured-procedure-value--appmodelsprocedures-capturedprocedurevaluephp)
15. [Procedure Test Kit Value](#13-procedure-test-kit-value--appmodelsprocedures-proceduretestkitvaluephp)
16. [Captured Procedure Config Value](#14-captured-procedure-config-value--appmodelsprocedures-capturedprocedureconfigvaluephp)
17. [Submission Form Instance Value](#15-submission-form-instance-value--appmodels-submissionforminstancevaluephp)
18. [Hashed Data Summary](#hashed-data-summary)
19. [WorkorderApproval — user_key](#16-workorder-approval--appmodelsworkorder-workorderapprovalphp)
20. [EntityApproval — link_key](#17-entity-approval--appentityapprovalphp)
21. [User Password](#18-user-password--appuserphp)
22. [Two-Factor Recovery Codes](#19-two-factor-recovery-codes--appservicesauth-totpservicephp)
23. [Security Notes & Recommendations](#security-notes--recommendations)

---

## Overview

The application protects sensitive data at the **database column level** using two distinct mechanisms:

| Mechanism | Algorithm | Reversible | Used For |
|---|---|---|---|
| Laravel `encrypted` cast | AES-256-CBC (via `APP_KEY`) | ✅ Yes | PII, lab results, contact data, identifiers |
| `bcrypt` / `Hash::make()` | Bcrypt (cost 12 default) | ❌ No | Passwords, approval tokens |
| `Hash::make()` + base64 | Bcrypt + base64 encoding | ❌ No | Email-approval link tokens |

> **Important:** All encryption relies on the `APP_KEY` value in `.env`. Loss or rotation of this key without re-encrypting existing records will make all encrypted columns unreadable.

---

## Encryption Technology

Laravel's `encrypted` cast applies `Crypt::encryptString()` under the hood before persisting a value to the database, and `Crypt::decryptString()` when reading it back into a model instance. The cipher is **AES-256-CBC** and the key is derived from `APP_KEY`.

Because the encrypted payload includes an HMAC signature, any tampering with stored ciphertext will cause a `DecryptException` at read time.

---

## 1. `User` — `app/User.php`

**Database table:** `users`

The `User` model stores all personally identifiable information (PII) about system users (internal personnel and external customer accounts) in encrypted columns.

| Field | Cast | Description |
|---|---|---|
| `email` | `encrypted` | User's email address. Also used as login identifier. |
| `first_name` | `encrypted` | User's given/first name. |
| `middle_name` | `encrypted` | User's middle name (optional). |
| `last_name` | `encrypted` | User's surname/family name. |
| `phone` | `encrypted` | User's primary phone number. |
| `gender` | `encrypted` | User's gender. |
| `designation` | `encrypted` | Job title or designation within the organisation. |
| `date_of_birth` | `encrypted` | User's date of birth. Sensitive PII. |
| `id_number` | `encrypted` | National ID / passport number. Highly sensitive identity document number. |
| `verify_code` | `encrypted` | One-time email verification code sent to the user. |
| `verify_code_expires` | `encrypted` | Expiry timestamp for the verification code. |
| `two_factor_secret` | `encrypted` | TOTP shared secret (base32) used to generate time-based OTP codes for 2FA. Encrypted via `TotpService::encryptSecret()` before storage. |
| `two_factor_recovery_codes` | `encrypted` | JSON-encoded array of **bcrypt-hashed** recovery codes (see §19). The outer column is itself encrypted, making it doubly protected. |
| `password` | bcrypt hash (hidden) | See [Hashed Data Summary — User Password](#18-user-password--appuserphp). Stored in the `hidden` array and never serialised. |

**Key relationships:** The `User` model is central to the system. It is referenced from virtually every module (lab sections, approvals, personnel records, CRM customer contacts, etc.).

---

## 2. `CRM Customer` — `app/Models/CRM/CRMCustomer.php`

**Database table:** `crm_customers`

Stores master customer/client organisation data for the CRM module. Contact details are encrypted to protect customer PII in compliance with data-protection obligations.

| Field | Cast | Description |
|---|---|---|
| `code` | `encrypted` | Internal customer code / account number assigned to the customer. |
| `postal_address` | `encrypted` | Customer's mailing / postal address. |
| `physical_address` | `encrypted` | Customer's physical / street address. |
| `email` | `encrypted` | Primary email address of the customer organisation. |
| `telephone1` | `encrypted` | Primary telephone number. |
| `telephone2` | `encrypted` | Secondary telephone number. |

**Non-encrypted notable fields:** `name` (plaintext, used for display), `is_internal` (cast to boolean), `report_columns_config` (cast to array/JSON).

**Key relationships:** `CRMCustomer` has many `CustomerContact`, many `CRMCompanyUnit`, many `QuotationHeader`, and many `SampleHeader` records.

---

## 3. `CRM Customer Contact` — `app/Models/CRM/CustomerContact.php`

**Database table:** `crm_customer_contacts`

Stores individual contact persons linked to a customer organisation. All direct contact information is encrypted.

| Field | Cast | Description |
|---|---|---|
| `job_occupation` | `encrypted` | The contact person's job title or occupation. |
| `unit_name` | `encrypted` | The company unit or department name this contact belongs to. |
| `email` | `encrypted` | Contact person's email address. Used for login if the contact has a user account. |
| `telephone` | `encrypted` | Contact's telephone number. |
| `mobile` | `encrypted` | Contact's mobile/cell number. |

**Relationship:** Belongs to `CRMCustomer` via `crm_customer_id`.

---

## 4. `SampleHeader` — `app/SampleHeader.php`

**Database table:** `sample_headers` (inferred)

The `SampleHeader` represents a laboratory submission batch — the top-level record for a group of samples submitted by a customer. It contains a large number of encrypted fields primarily to protect customer-facing text, financial data, and sensitive submission metadata.

| Field | Cast | Description |
|---|---|---|
| `crm_unit_name` | `encrypted` | Name of the customer's company unit that submitted the batch. |
| `reference_number` | `encrypted` | Customer-provided reference number for the batch submission. |
| `method_deviation_reason` | `encrypted` | Free-text reason recorded when an analysis method deviation is approved. |
| `document_number` | `encrypted` | Document number associated with the submission (e.g. customs or legal document). |
| `description` | `encrypted` | General description of the submitted batch. |
| `importer_address` | `encrypted` | Address of the importer (relevant for customs/import submissions). |
| `reason_for_submission` | `encrypted` | Free-text reason stated by the customer for submitting the samples. |
| `how_sample_was_obtained` | `encrypted` | Description of the sampling method/process used by the customer. |
| `sample_appearance_description` | `encrypted` | Physical appearance of the sample as described by the customer at point of submission. |
| `net_quantity_and_unit_of_quantity` | `encrypted` | Net quantity and units of the material submitted (e.g. "500 g", "2 L"). |
| `use_of_goods` | `encrypted` | Intended use of the goods submitted (e.g. food, industrial, pharmaceutical). |
| `declared_amount` | `encrypted` | Monetary value declared by the importer for customs purposes. |
| `where_sample_was_obtained` | `encrypted` | Physical location where the sample was collected. |
| `radio_active_levels` | `encrypted` | Radioactivity level information if the sample is or may be radioactive. Sensitive safety data. |
| `ammendment_number` | `encrypted` | Amendment reference number when a report has been revised/amended. |
| `sampling_officer_name` | `encrypted` | Name of the officer who collected the sample on behalf of the authority/lab. |
| `receiving_officer_name` | `encrypted` | Name of the lab officer who received the sample at the laboratory. |
| `submit_by` | `encrypted` | Name of the person / entity that physically submitted the batch. |
| `batch_report_url` | `encrypted` | Internal URL or file path to the generated batch report (PDF). |
| `batch_report_online_url` | `encrypted` | Public-facing URL for the online batch report (e.g. customer portal link). |
| `batch_instructions` | `encrypted` | Special handling or analysis instructions for the lab regarding this batch. |
| `condition_quality_sample` | `encrypted` | Assessment of the condition/quality of the sample upon receipt. |
| `declaration_customer_signature` | `encrypted` | Customer's signature data or declaration text acknowledging submission. |
| `invoice_amount` | `encrypted` | Total invoiced amount for the batch. Financial data. |
| `cluster_amount` | `encrypted` | Total cluster invoice amount (where samples are grouped under a cluster pricing scheme). |
| `cluster_balance` | `encrypted` | Outstanding balance remaining on the cluster account. |
| `cluster_amount_paid` | `encrypted` | Amount already paid against the cluster invoice. |
| `case_id` | `encrypted` | Reference case ID (e.g. legal case, regulatory case) associated with this submission. |
| `schedule_customer_email` | `encrypted` | Email address used to send automated scheduled reports to the customer. |

**Non-encrypted notable fields:** `receiving_officer` (user UUID, cast string), `sampling_officer` (user UUID), `invoice_id`, `quote_id`, `crm_unit_id`, `crm_contact_id` — all UUID/string foreign keys.

---

## 5. `SampleDetails` — `app/SampleDetails.php`

**Database table:** `sample_details`

Represents an individual sample within a batch (one `SampleHeader` has many `SampleDetails`). Encrypted fields protect identifying barcodes, GPS coordinates, and the generated report content.

| Field | Cast | Description |
|---|---|---|
| `barcode` | `encrypted` | Unique barcode number assigned to the sample at intake. Encrypted to prevent barcode exposure in database dumps. |
| `comments` | `encrypted` | Analyst or customer comments attached to this specific sample. |
| `gps` | `encrypted` | GPS coordinates of the sample collection point. Location data / PII. |
| `section_details` | `encrypted` | Serialised JSON or text block representing section-specific details of the generated report for this sample. |
| `main_body` | `encrypted` | Main body content of the generated sample report. May contain analysis results and conclusions. |
| `header_body` | `encrypted` | Header section content of the generated sample report. |
| `notes_body` | `encrypted` | Notes/footer section content of the generated report. |
| `report_number` | `encrypted` | Unique report number issued for this sample. |

---

## 6. `CapturedResult` — `app/CapturedResult.php`

**Database table:** `captured_results`

The `CapturedResult` records a **single analyte result** as entered by a laboratory analyst for a specific sample. This is the core scientific data model. All measurement values and remarks are encrypted to protect analytical integrity and prevent unauthorised data access.

| Field | Cast | Description |
|---|---|---|
| `analyte_code` | `encrypted` | Code of the analyte (parameter being tested, e.g. "Pb" for lead, "NO3" for nitrate). |
| `result` | `encrypted` | The primary measured value of the analyte for this sample. Core scientific result. |
| `remark` | `encrypted` | Analyst's remark regarding this result (e.g. "< LOD", "Matrix interference noted"). |
| `main_value` | `encrypted` | The main reported numeric value (may differ from `result` after formula application). |
| `secondary_value` | `encrypted` | A secondary measurement value, e.g. a parallel or duplicate reading. |
| `sec_remark` | `encrypted` | Remark associated with the secondary value. |
| `third_remark` | `encrypted` | Third remark field for additional analyst notes. |
| `scienctific_result` | `encrypted` | The result expressed in scientific notation (e.g. `1.23 × 10⁻⁴`). Note: field name has a typo (`scienctific`). |
| `superscript_number` | `encrypted` | The exponent/superscript number for scientific notation display. |
| `superscript_negative` | `encrypted` | Flag/value indicating if the exponent is negative in scientific notation. |
| `supercsript_base` | `encrypted` | The base value used in the scientific notation representation. Note: field name has a typo (`supercsript`). |

---

## 7. `Result` (Published) — `app/Result.php`

**Database table:** `results`

The `Result` model represents the **finalised/published** version of a lab result, derived from one or more `CapturedResult` records after analyst review and approval. All value fields are encrypted, matching the same security posture as the captured results.

| Field | Cast | Description |
|---|---|---|
| `sample_detail_code` | `encrypted` | Code identifying the sample detail this result belongs to. |
| `analyte_code` | `encrypted` | Code of the analyte (parameter) tested. |
| `result` | `encrypted` | The published final result value for the analyte. |
| `guide` | `encrypted` | The regulatory or standard limit/guide value for comparison with the result. |
| `comments` | `encrypted` | Published comments on the result (visible on the report). |
| `recommendations` | `encrypted` | Recommendations issued based on this result (e.g. "Unsafe for human consumption"). |
| `remarks` | `encrypted` | Final remarks on the result as it appears on the certificate/report. |
| `scienctific_result` | `encrypted` | Result expressed in scientific notation for the published report. |

**Relationship:** Belongs to `CapturedResult` via `captured_result_id`.

---

## 8. `SampleCapturedWorksheetFormula` — `app/Models/Worksheets/SampleCapturedWorksheetFormula.php`

**Database table:** `sample_captured_worksheet_formulas` (inferred)

Tracks a formula worksheet run for a sample — a structured computation performed by an analyst using a defined calculation formula. Key fields containing sample identifiers and computed results are encrypted.

| Field | Cast | Description |
|---|---|---|
| `lab_no` | `encrypted` | The laboratory number (sample reference) used during the worksheet computation. |
| `sample_details` | `encrypted` | Serialised sample detail information captured during the worksheet run. |
| `final_result` | `encrypted` | The computed final result produced by the formula worksheet. |

**Non-encrypted fields** include timestamps (`date`, `read_date`, `time_in`, `time_out`, `posted_at`) and foreign key UUIDs (`sample_header_id`, `sample_detail_id`, `captured_result_id`, `formular_id`, `done_by_user_id`, `read_by_user_id`, `posted_by_user_id`).

Uses `SoftDeletes` for safe deletion.

---

## 9. `SampleWorksheetFormularStepData` — `app/Models/Worksheets/SampleWorksheetFormularStepData.php`

**Database table:** `sample_worksheet_formular_step_data` (inferred)

Stores the individual step-by-step intermediate values entered or computed during a formula worksheet run. Each row corresponds to one step in the formula.

| Field | Cast | Description |
|---|---|---|
| `step_value` | `encrypted` | The value entered or computed for this formula step (e.g. weight reading, volume measurement, intermediate calculation result). |

**Relationships:** Belongs to `SampleCapturedWorksheetFormula` and to `FormulaStep`. Optionally linked to an overridden `LookupTable`.

---

## 10. `SampleWorksheetFormularMandatoryData` — `app/Models/Worksheets/SampleWorksheetFormularMandatoryData.php`

**Database table:** `sample_worksheet_formular_mandatory_data` (inferred)

Stores values for mandatory instrument/configuration fields that must be filled in before a formula worksheet can be submitted (e.g. instrument serial number, calibration date, reagent lot number).

| Field | Cast | Description |
|---|---|---|
| `field_value` | `encrypted` | The value entered for the mandatory field on the worksheet (e.g. instrument ID, calibration constant). |

**Relationships:** Belongs to `SampleCapturedWorksheetFormula` and to `FormulaMandatoryField`.

---

## 11. `MethodSequenceStageSampleResult` — `app/Models/Worksheets/MethodSequenceStageSampleResult.php`

**Database table:** `method_sequence_stage_sample_results` (inferred)

Records an intermediate result captured at a specific stage within a multi-stage method sequence run (e.g. a sequential extraction procedure or a tiered analysis method).

| Field | Cast | Description |
|---|---|---|
| `result` | `encrypted` | The measured result value at this stage of the method sequence. |
| `remark` | `encrypted` | Analyst remark for this stage result. |

**Relationships:** Belongs to `MethodSequenceRunStageData` and to `CapturedResult`.

---

## 12. `CapturedProcedureValue` — `app/Models/Procedures/CapturedProcedureValue.php`

**Database table:** `captured_procedure_values` (inferred)

Records the individual step values captured during a procedure worksheet (SOP-driven procedure) for a specific analysis. Associated with equipment, measurands, and analysts.

| Field | Cast | Description |
|---|---|---|
| `value` | `encrypted` | The value entered for this procedure worksheet step (e.g. instrument reading, titration volume, reagent volume, temperature). |

**Non-encrypted fields:** `equipment_ids` (JSON array of equipment UUIDs), `measurand_ids` (JSON array), `analyst_ids` (JSON array of user IDs).

**Relationships:** Belongs to `CapturedResult` and to `ProcedureWorksheetStep`.

---

## 13. `ProcedureTestKitValue` — `app/Models/Procedures/ProcedureTestKitValue.php`

**Database table:** `procedure_test_kit_values` (inferred)

Stores values entered into test-kit matrix cells (rows × columns) during a procedure that uses a test-kit-style grid (e.g. a multi-standard calibration table or multi-replicate titration grid).

| Field | Cast | Description |
|---|---|---|
| `value` | `encrypted` | The value entered in the cell at the intersection of a test-kit row and column (e.g. absorbance reading, concentration value). |

**Relationships:** Belongs to `CapturedResult`, `ProcedureTestKitRow`, and `ProcedureTestKitColumn`.

---

## 14. `CapturedProcedureConfigValue` — `app/Models/Procedures/CapturedProcedureConfigValue.php`

**Database table:** `captured_procedure_config_values` (inferred)

Stores system-level configuration field values captured at the time a procedure worksheet is run (e.g. ambient temperature, humidity, instrument calibration offset used), linking to a specific `CapturedResult`.

| Field | Cast | Description |
|---|---|---|
| `value` | `encrypted` | The configuration/environment value captured at the time of the procedure run (e.g. "22.3°C", "65% RH"). |

**Foreign keys:** `captured_result_id`, `procedure_worksheet_id`, `procedure_config_field_id`.

---

## 15. `SubmissionFormInstanceValue` — `app/Models/SubmissionFormInstanceValue.php`

**Database table:** `submission_form_instance_values` (inferred)

Stores the individual field values submitted within a dynamic form instance (e.g. a Chain of Custody form, a customer profile form, a compliance declaration form). Since forms can contain PII (names, addresses, signatures, document numbers), the value is encrypted.

| Field | Cast | Description |
|---|---|---|
| `value` | `encrypted` | The value entered for a specific form element within a form submission instance. Content varies by element type: text, number, date, select option, etc. |

The model also supports a `file_path` field (unencrypted) for file upload elements.

**Relationships:** Belongs to `SubmissionFormInstance` and to `SubmissionFormElement`. Includes `getFormattedValue()` which intelligently formats the decrypted value based on element type (date, datetime, checkbox, select, radio, file, text).

---

## Hashed Data Summary

The following fields are hashed (one-way, irreversible) rather than encrypted. They **cannot be decrypted** — they are only verified by hash comparison.

---

## 16. `WorkorderApproval` — `app/Models/Workorder/WorkorderApproval.php`

**Database table:** `workorder_approvals` (inferred)

Used in the Work Order module to track individual approvals.

| Field | Hash Algorithm | Description |
|---|---|---|
| `user_key` | `bcrypt` (`Hash::make()`) | A unique token generated per approval action using `Hash::make($userID + time() * random)`. Stored as a bcrypt hash. Used to authenticate approval links sent to approvers. |

**Generation (in `WorkorderApprovalController`):**
```php
$approval->user_key = Hash::make($request->selectedUser . (time() * rand(0, 100000000000)));
```

---

## 17. `EntityApproval` — `app/EntityApproval.php`

**Database table:** `entity_approvals` (inferred)

Used in the Requisition module and other entity-level approval workflows to enable email-based approval actions (approve / reject / recheck).

| Field | Hash Algorithm | Description |
|---|---|---|
| `link_key` | `bcrypt` + `base64_encode` | A one-way hash of `time()`, then base64-encoded, used as the URL token in email approval links. Verified by direct lookup (`WHERE link_key = ?`) rather than `Hash::check()`. |

**Generation (in `RequisitionController`):**
```php
$appr->link_key = base64_encode(\Illuminate\Support\Facades\Hash::make(time()));
```

The `link_key` is embedded in email links for routes: `email-approval`, `email-rejection`, `email-recheck`.

---

## 18. `User` Password — `app/User.php`

**Database table:** `users.password`

| Field | Hash Algorithm | Description |
|---|---|---|
| `password` | `bcrypt` | The user's login password, hashed using Laravel's `bcrypt()` / `Hash::make()`. Listed in `$hidden` so it is never serialised to arrays or JSON responses. |

Password is set/changed in multiple places:
- `Auth/RegisterController.php` — uses `Hash::make($data['password'])`
- `PersonnelController.php` — uses `bcrypt($request->password)` / `bcrypt($gnrt_pass)` (auto-generated)
- `CRM/CRMCustomerController.php` — default `bcrypt('test1234')` for new customer users
- `CRM/CustomerContactController.php` — uses `bcrypt($request->main_password)`
- `Livewire/Personnel/PersonnelTableManager.php` — `bcrypt($firstName + appName + year)` for bulk creation; `bcrypt($this->newPassword)` for password resets
- `Livewire/CRM/ContactsManager.php` — `Hash::make($this->contactForm['main_password'])`
- `Auth/TotpTwoFactorController.php` — verifies password via `Hash::check()` before TOTP operations

---

## 19. Two-Factor Recovery Codes — `app/Services/Auth/TotpService.php`

The TOTP two-factor authentication system implements a doubly-protected storage scheme for recovery codes.

### Storage Scheme

```
Storage flow:
  plaintext codes → bcrypt hash each → JSON encode array → store as encrypted column
```

| Layer | Mechanism | Description |
|---|---|---|
| Per-code hashing | `bcrypt` (`Hash::make()`) | Each individual recovery code (e.g. `ABCDE-FGHIJ`) is bcrypt-hashed before storage. |
| Column encryption | `encrypted` cast | The `two_factor_recovery_codes` column on `users` is an `encrypted` cast, so the entire JSON array of bcrypt hashes is also AES-256-CBC encrypted. |

### Code Generation
```php
// 8 codes are generated, each as two 5-character uppercase segments
$codes[] = strtoupper(Str::random(5)) . '-' . strtoupper(Str::random(5));
```

### Hashing
```php
$hashes = array_map(fn (string $c): string => Hash::make($c), $plainCodes);
return json_encode(array_values($hashes), JSON_THROW_ON_ERROR);
```

### Verification
Each submitted code is verified against all stored hashes using `Hash::check()`. On successful use, the consumed hash is removed from the array, making each recovery code **single-use**.

### TOTP Secret (`two_factor_secret`)
The TOTP base32 secret is explicitly encrypted via `Crypt::encryptString()` in `TotpService::encryptSecret()` before being saved to the `two_factor_secret` column, which also carries the `encrypted` cast — providing an extra encryption layer managed by the service class.

---

## Security Notes & Recommendations

1. **`APP_KEY` is critical.** All `encrypted` cast fields and cookie encryption depend on `APP_KEY`. It must be backed up securely and rotated with a migration that re-encrypts all rows.

2. **Database-level field encryption is not a replacement for access control.** Even though sensitive columns are encrypted, application-level authorisation must still restrict which roles can read decrypted values.

3. **Typos in field names** (`scienctific_result`, `supercsript_base`) are in the live database schema. Any queries against these columns must use the misspelled names exactly.

4. **`link_key` in `EntityApproval`** is base64-encoded rather than used as a true hash token for lookup equality. Because bcrypt is non-deterministic (salted), the same input will produce different hashes on each call, meaning `base64_encode(Hash::make(...))` is not verifiable by `Hash::check()`. The system stores and looks up the full encoded hash string by equality. This is an acceptable pattern as long as the generation is not used for password-like secret verification.

5. **Recovery codes are single-use** and consumed atomically. Ensure the update to `two_factor_recovery_codes` after consumption is wrapped in a database transaction to prevent race conditions.

6. **No plaintext fallback.** None of the encrypted models implement a fallback for NULL decryption. Fields that were populated before encryption was added may need a data migration.
