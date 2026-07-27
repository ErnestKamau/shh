# TRF layer deprecation — COMPLETE

Legacy parallel capture layer (`TestRequestForm` + `TestRequestFormInstance`) has been
**removed**. Canonical capture is:

`SubmissionForm` template + `SubmissionFormInstance` + `submission_form_instance_values` + `SampleSubmissionRequest`.

## Replacement map (done)

| Deprecated | Replacement |
|------------|-------------|
| `TestRequestForm` / `TestRequestFormInstance` | `SubmissionForm` template + `SubmissionFormInstance` |
| `TestRequestFormSubmissionService` | `SubmissionFormSubmissionService` |
| `TestRequestFormDataMapper` | `SubmissionFormValueNormalizer` |
| `CommercialEnquiryFromTrfService` | `CommercialEnquirySyncService` |
| `TrfFormNumberGenerator` | Form numbering on `SubmissionForm` / SFI |
| `TrfCheckInMetadataService` | `ReceivingLabMetadataService` |
| Report variant helpers on `TestRequestForm` | `TrfDocumentCodeForSampleType` |

## Deleted

- Models: `TestRequestForm`, `TestRequestFormInstance`
- `app/Services/TestRequestForm/`
- `CommercialEnquiryFromTrfService`
- `TrfBackfillCanonicalLinksCommand`, `TrfRefreshFormTemplatesCommand`
- Legacy seeders/factory that only wrapped or seeded JSON TRFs
- Drop migration: `2026_06_26_160000_drop_deprecated_test_request_form_tables.php` (already applied in this environment)

## Kept (not the capture layer)

- `test_request_report_*` (lab report delivery)
- `TestRequestFormPdfService` / `TestRequestFormReportDataBuilder` / `TestRequestFormController` — SFI-based PDF/preview (names retained)
- `config/test_request_form_fields.php` — field aliases still used by SFI normalizer / check-in UI

## RFT admin entry

Request For Testing cards now expose **View** / **Edit** / **Add TRF** against Submission Form routes.
