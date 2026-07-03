# TRF layer deprecation manifest

Legacy parallel capture layer (`TestRequestForm` + `TestRequestFormInstance`) is replaced by
`SubmissionFormInstance` + `submission_form_instance_values` + `SampleSubmissionRequest`.

**Procedure:** replace call sites → verify grep is clean → comment out file body → delete in final PR.

## Replacement map

| Deprecated | Replacement |
|------------|-------------|
| `TestRequestForm` / `TestRequestFormInstance` | `SubmissionForm` template + `SubmissionFormInstance` |
| `TestRequestFormSubmissionService` | `SubmissionFormSubmissionService` |
| `TestRequestFormDataMapper` | `SubmissionFormValueNormalizer` |
| `CommercialEnquiryFromTrfService` | `CommercialEnquirySyncService` |
| `CommercialEnquiryFromFormService` | `CommercialEnquirySyncService` (alias kept temporarily) |
| `TrfFormNumberGenerator` | `FormNumberGenerator` |
| `TrfCheckInMetadataService` | `ReceivingLabMetadataService` |
| `test-request-field-render.blade.php` | `submission-form-capture-sections.blade.php` |

## Models (Phase 6 delete)

- `app/Models/TestRequestForm.php`
- `app/Models/TestRequestFormInstance.php`
- `config/test_request_form_fields.php`

## Services / controllers (comment out after Phase 4)

- `app/Services/TestRequestForm/` (entire directory)
- `app/Services/Commercial/CommercialEnquiryFromTrfService.php`
- `app/Http/Controllers/TestRequestFormController.php` (routes redirect to SFI-based controller)
- `app/Console/Commands/TrfBackfillCanonicalLinksCommand.php`
- `app/Console/Commands/TrfRefreshFormTemplatesCommand.php`
- `app/Services/TestRequestForm/TestRequestFormTemplateProvisioner.php`

## Views (comment out after Phase 2)

- `resources/views/livewire/sampleworkflow/test-request-field-render.blade.php`

## Migrations (Phase 6 drop — do not delete files until tables dropped)

- `2026_06_08_182833_create_test_request_forms_table.php`
- `2026_06_08_182854_create_test_request_form_instances_table.php`
- `2026_06_18_144331_add_canonical_metadata_to_test_request_form_instances_table.php`
- `2026_06_18_144333_add_test_request_form_instance_id_to_sample_submission_requests_table.php`
- `2026_06_09_000001_add_test_request_form_instance_id_to_analysis_acceptance_forms.php`

**Keep:** `test_request_report_*` migrations (lab report delivery, not capture layer).

## Tests (comment out after Phase 5)

- `tests/Feature/Api/Portal/Submissions/TrfPortalSubmitCreatesCanonicalTrfiTest.php`
- `tests/Feature/Api/Portal/Submissions/TestRequestFormPortalPdfTest.php`
- `tests/Feature/Console/TrfBackfillCommandTest.php`
- `tests/Unit/Services/Commercial/CommercialEnquiryFromTrfServiceTest.php`
- `tests/Unit/SubmissionForm/SubmissionRequestSampleLineServiceTrfiTest.php`
- `tests/Unit/Services/SubmissionForm/TrfSampleLineQuantityTest.php`
- `tests/Unit/TestRequestFormDataMapperTest.php`
- `tests/Unit/Sampleworkflow/TestRequestFormPdfServiceTest.php`
- `tests/Unit/Services/Sampleworkflow/TestRequestFormReportDataBuilderTest.php`

## Verification checklist

- [ ] Portal submit → SFI + enquiry, no new TRFI row
- [ ] Walk-in modal → SFI + enquiry + Test Request Form PDF
- [ ] Physical check-in unchanged
- [ ] Process enquiry sample rows match SFI values
- [ ] Acceptance wizard (Laboratory Analysis Acceptance Form labels)
- [ ] `rg "use App\\\\Models\\\\TestRequestForm" app/` — only stubs/manifest
