-- ====================================================================
-- Migration: Microbiology Submission Form (Local ID = 5)
-- Source:    Development Database
-- Target:    Production Database
-- Generated: 2026-04-16
--
-- SECTIONS INCLUDED:
--   1. submission_forms          (1 row)
--   2. submission_form_sections  (5 rows)
--   3. submission_form_element_holders (6 rows)
--   4. submission_form_elements  (29 rows)
--
-- HOW TO USE:
--   1. Set @prod_form_id below to match the form's ID in production.
--      If the form does not yet exist in production, insert it first
--      (see STEP 0) and then set @prod_form_id = LAST_INSERT_ID().
--   2. Review the section / holder / element explicit IDs.
--      If those IDs already exist in production for OTHER forms,
--      remove the `id` column and add ON DUPLICATE KEY UPDATE, or
--      use the commented-out "safe-insert without explicit IDs" block.
--   3. Run the script inside a transaction so you can ROLLBACK on error.
-- ====================================================================

SET @prod_form_id = 5;   -- ← adjust if form has a different ID in production

START TRANSACTION;

-- ====================================================================
-- STEP 0: submission_forms  (skip if the row already exists)
-- ====================================================================
INSERT INTO `submission_forms`
    (`id`, `name`, `document_code`, `description`,
     `naming_convention_prefix`, `naming_convention_format`,
     `is_published`, `is_active`, `start_submission_number`,
     `version`, `issue_date`, `created_by`, `print_template_name`,
     `created_at`, `updated_at`)
VALUES
    (5, 'Microbiology Submission Form', 'FM/QA/047',
     'Microbiology Submission Form',
     'MB', '{prefix}/{year}/{sequence}',
     1, 1, 1, '5.0  TRIAL', '2023-09-11', 7, NULL,
     '2025-10-13 13:18:02', '2026-03-26 15:06:16')
ON DUPLICATE KEY UPDATE
    `name`                       = VALUES(`name`),
    `document_code`              = VALUES(`document_code`),
    `description`                = VALUES(`description`),
    `naming_convention_prefix`   = VALUES(`naming_convention_prefix`),
    `naming_convention_format`   = VALUES(`naming_convention_format`),
    `is_published`               = VALUES(`is_published`),
    `is_active`                  = VALUES(`is_active`),
    `start_submission_number`    = VALUES(`start_submission_number`),
    `version`                    = VALUES(`version`),
    `issue_date`                 = VALUES(`issue_date`),
    `print_template_name`        = VALUES(`print_template_name`),
    `updated_at`                 = VALUES(`updated_at`);

-- ====================================================================
-- STEP 1: submission_form_sections  (5 rows)
-- ====================================================================
INSERT INTO `submission_form_sections`
    (`id`, `submission_form_id`, `title`, `description`,
     `section_type`, `sort_order`, `created_at`, `updated_at`)
VALUES
    ( 7, @prod_form_id, 'Client Details',
      'Client Details', 'regular', 1,
      '2025-10-13 13:18:49', '2026-04-16 00:11:20'),

    (35, @prod_form_id, 'Case Information',
      NULL, 'regular', 2,
      '2026-04-15 20:15:24', '2026-04-16 00:11:20'),

    (36, @prod_form_id, 'Suspect Information',
      NULL, 'rows_section', 3,
      '2026-04-15 23:55:04', '2026-04-16 00:11:20'),

    (37, @prod_form_id, 'Description of Exhibit Submitted',
      NULL, 'rows_section', 4,
      '2026-04-15 23:59:09', '2026-04-16 00:11:20'),

    (38, @prod_form_id, 'Test Requested',
      NULL, 'rows_section', 5,
      '2026-04-16 00:11:09', '2026-04-16 00:11:20')

ON DUPLICATE KEY UPDATE
    `submission_form_id` = @prod_form_id,
    `title`              = VALUES(`title`),
    `description`        = VALUES(`description`),
    `section_type`       = VALUES(`section_type`),
    `sort_order`         = VALUES(`sort_order`),
    `updated_at`         = VALUES(`updated_at`);

-- ====================================================================
-- STEP 2: submission_form_element_holders  (6 rows)
-- ====================================================================
INSERT INTO `submission_form_element_holders`
    (`id`, `submission_form_section_id`, `holder_type`,
     `max_elements`, `sort_order`, `created_at`, `updated_at`)
VALUES
    -- Section 7: Client Details
    ( 8,  7, 'field', 13, 1, '2025-10-13 13:20:34', '2026-04-15 19:52:00'),

    -- Section 35: Case Information
    (59, 35, 'field', 10, 1, '2026-04-15 20:15:48', '2026-04-15 20:15:48'),
    (60, 35, 'field', 10, 2, '2026-04-15 23:50:50', '2026-04-15 23:50:50'),

    -- Section 36: Suspect Information (rows_section template holder)
    (61, 36, 'field',  5, 1, '2026-04-15 23:55:43', '2026-04-15 23:55:43'),

    -- Section 37: Description of Exhibit Submitted (rows_section template holder)
    (62, 37, 'field',  5, 1, '2026-04-16 00:02:56', '2026-04-16 00:02:56'),

    -- Section 38: Test Requested (rows_section template holder)
    (64, 38, 'field',  5, 1, '2026-04-16 00:11:31', '2026-04-16 00:11:31')

ON DUPLICATE KEY UPDATE
    `submission_form_section_id` = VALUES(`submission_form_section_id`),
    `holder_type`                = VALUES(`holder_type`),
    `max_elements`               = VALUES(`max_elements`),
    `sort_order`                 = VALUES(`sort_order`),
    `updated_at`                 = VALUES(`updated_at`);

-- ====================================================================
-- STEP 3: submission_form_elements  (29 rows)
-- ====================================================================
INSERT INTO `submission_form_elements`
    (`id`, `submission_form_element_holder_id`,
     `element_type`, `label`, `name`,
     `placeholder`, `help_text`,
     `is_required`, `is_readonly`, `default_value`,
     `validation_rules`,
     `mapping_table`, `mapping_field`, `is_mapped`,
     `depends_on_type`, `depends_on_field`, `source_table`, `source_field`,
     `options`, `calculation_formula`, `conditional_logic`,
     `sort_order`, `created_at`, `updated_at`)
VALUES

-- ── Holder 8 (Section 7 – Client Details) ────────────────────────
( 19, 8, 'client_select',         'Submitting Agency',      'client_detail_id',
  NULL, NULL, 1, 0, NULL, NULL,
  'sample_headers', 'crm_customer_id', 1,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  1, '2025-10-13 13:21:19', '2026-04-16 00:00:09'),

( 20, 8, 'client_unit_select',    'Client Section',         'client_unit_id',
  NULL, NULL, 1, 0, NULL, NULL,
  'sample_headers', 'crm_unit_name', 1,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  2, '2025-10-13 13:21:28', '2026-04-16 00:00:09'),

(137, 8, 'client_contact_select', 'Submitting Officer',     'client_contact_id',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  3, '2026-04-15 19:55:04', '2026-04-16 00:00:09'),

(128, 8, 'depended_field',        'Physical Address',       'physical_address',
  NULL, NULL, 1, 0, NULL, NULL,
  NULL, NULL, 0,
  'client_select', 'client_detail_id', 'crm_customers', 'physical_address',
  NULL, NULL, NULL,
  4, '2026-04-15 19:21:26', '2026-04-16 01:09:47'),

(130, 8, 'text',                  'District',               'district',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  5, '2026-04-15 19:45:55', '2026-04-16 00:00:09'),

(129, 8, 'text',                  'Region',                 'Region',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  6, '2026-04-15 19:35:20', '2026-04-16 00:01:25'),

(131, 8, 'text',                  'Working Station',        'working_station',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  7, '2026-04-15 19:46:29', '2026-04-16 00:00:09'),

(132, 8, 'depended_field',        'Office Telephone No',    'office_telephone_no',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  'client_select', 'client_detail_id', 'crm_customers', 'telephone1',
  NULL, NULL, NULL,
  8, '2026-04-15 19:47:09', '2026-04-16 01:39:44'),

(133, 8, 'depended_field',        'Mobile Telephone No',    'mobile_telephone_no',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  'client_contact_select', 'client_contact_id', 'crm_customer_contacts', 'mobile',
  NULL, NULL, NULL,
  9, '2026-04-15 19:47:39', '2026-04-16 01:11:26'),

(134, 8, 'depended_field',        'Fax',                    'fax',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  'client_select', 'client_detail_id', 'crm_customers', 'fax',
  NULL, NULL, NULL,
  10, '2026-04-15 19:48:01', '2026-04-16 01:12:03'),

(135, 8, 'depended_field',        'E-mail',                 'email',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  'client_contact_select', 'client_contact_id', 'crm_customer_contacts', 'email',
  NULL, NULL, NULL,
  11, '2026-04-15 19:48:18', '2026-04-16 01:12:27'),

-- ── Holder 59 (Section 35 – Case Information, holder 1) ──────────
(138, 59, 'text',                 'Case No',                      'case_number',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  1, '2026-04-15 20:16:27', '2026-04-15 20:16:27'),

(139, 59, 'text',                 'Offence',                      'offence',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  2, '2026-04-15 20:16:53', '2026-04-15 20:16:53'),

(140, 59, 'date',                 'Date of Seizure',              'date_of_seizure',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  3, '2026-04-15 20:17:33', '2026-04-15 20:17:33'),

-- ── Holder 60 (Section 35 – Case Information, holder 2 – Area of Seizure) ──
(146, 60, 'text',                 'Region',                       'area_of_seizure_region',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  1, '2026-04-15 23:50:50', '2026-04-15 23:51:53'),

(147, 60, 'text',                 'District',                     'area_of_seizure_district',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  2, '2026-04-15 23:50:50', '2026-04-15 23:52:17'),

(148, 60, 'text',                 'Ward',                         'ward',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  3, '2026-04-15 23:53:31', '2026-04-15 23:53:31'),

(149, 60, 'text',                 'Village/Street',               'village',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  4, '2026-04-15 23:54:00', '2026-04-15 23:54:00'),

-- ── Holder 61 (Section 36 – Suspect Information, rows_section template) ──
(150, 61, 'text',                 'Suspect Name (First, Middle, Last)', 'suspect_name',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  1, '2026-04-15 23:56:47', '2026-04-15 23:56:47'),

(151, 61, 'text',                 'Sex (F/M)',                    'sex',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  2, '2026-04-15 23:57:13', '2026-04-15 23:57:13'),

(152, 61, 'date',                 'Date of Birth',                'suspect_date_of_birth',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  3, '2026-04-15 23:57:52', '2026-04-15 23:57:52'),

(153, 61, 'text',                 'Nationality',                  'nationality',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  4, '2026-04-15 23:58:18', '2026-04-15 23:58:18'),

(154, 61, 'text',                 'ID No./ Passport No.',         'id_no',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  5, '2026-04-15 23:58:42', '2026-04-15 23:58:42'),

-- ── Holder 62 (Section 37 – Description of Exhibit, rows_section template) ─
(155, 62, 'number',
  'No of Items and its Description',
  'no_of_items_and_its_description',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  1, '2026-04-16 00:03:42', '2026-04-16 00:03:42'),

(156, 62, 'text',
  'Suspected Drug, chemical or item',
  'Suspected_Drug_chemical_or_item',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  2, '2026-04-16 00:04:17', '2026-04-16 00:04:17'),

-- ── Holder 64 (Section 38 – Test Requested, rows_section template) ──
(160, 64, 'sample_type_select',   'Specimen Type',               'sample_typ',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  1, '2026-04-16 00:12:30', '2026-04-16 00:12:30'),

(161, 64, 'analysis_type_select', 'Analysis Type',               'analysis_type_ids',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  2, '2026-04-16 00:16:51', '2026-04-16 00:16:51'),

(162, 64, 'analysis_elements_select', 'Test',                    'parameter',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  3, '2026-04-16 00:17:56', '2026-04-16 00:17:56'),

(163, 64, 'text',                 'Quantity',                     'quant',
  NULL, NULL, 0, 0, NULL, NULL,
  NULL, NULL, 0,
  NULL, NULL, NULL, NULL,
  NULL, NULL, NULL,
  4, '2026-04-16 00:18:38', '2026-04-16 00:18:38')

ON DUPLICATE KEY UPDATE
    `submission_form_element_holder_id` = VALUES(`submission_form_element_holder_id`),
    `element_type`        = VALUES(`element_type`),
    `label`               = VALUES(`label`),
    `name`                = VALUES(`name`),
    `placeholder`         = VALUES(`placeholder`),
    `help_text`           = VALUES(`help_text`),
    `is_required`         = VALUES(`is_required`),
    `is_readonly`         = VALUES(`is_readonly`),
    `default_value`       = VALUES(`default_value`),
    `validation_rules`    = VALUES(`validation_rules`),
    `mapping_table`       = VALUES(`mapping_table`),
    `mapping_field`       = VALUES(`mapping_field`),
    `is_mapped`           = VALUES(`is_mapped`),
    `depends_on_type`     = VALUES(`depends_on_type`),
    `depends_on_field`    = VALUES(`depends_on_field`),
    `source_table`        = VALUES(`source_table`),
    `source_field`        = VALUES(`source_field`),
    `options`             = VALUES(`options`),
    `calculation_formula` = VALUES(`calculation_formula`),
    `conditional_logic`   = VALUES(`conditional_logic`),
    `sort_order`          = VALUES(`sort_order`),
    `updated_at`          = VALUES(`updated_at`);

-- ====================================================================
-- Verify counts before committing
-- ====================================================================
SELECT 'submission_form_sections'         AS tbl, COUNT(*) AS cnt
  FROM submission_form_sections WHERE submission_form_id = @prod_form_id
UNION ALL
SELECT 'submission_form_element_holders', COUNT(*)
  FROM submission_form_element_holders
  WHERE submission_form_section_id IN (
      SELECT id FROM submission_form_sections
      WHERE submission_form_id = @prod_form_id)
UNION ALL
SELECT 'submission_form_elements', COUNT(*)
  FROM submission_form_elements
  WHERE submission_form_element_holder_id IN (
      SELECT sfeh.id FROM submission_form_element_holders sfeh
      JOIN submission_form_sections sfs ON sfs.id = sfeh.submission_form_section_id
      WHERE sfs.submission_form_id = @prod_form_id);

-- Expected: sections=5, holders=6, elements=29
-- If the counts look right, run COMMIT; otherwise run ROLLBACK;

-- COMMIT;
-- ROLLBACK;
