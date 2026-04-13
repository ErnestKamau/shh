# CRM Migration Files (Synced from `nas`)

Run from project root on Ubuntu. Use `--force` on production.

## Run all CRM migrations (one loop)

```bash
cd /var/www/html/fivet/polucon

awk -F'`' '/`database\/migrations\/.*\.php`/ {print $2}' CRM_MIGRATIONS.md \
| while read -r path; do
  [ -z "$path" ] && continue
  echo "Running: $path"
  php artisan migrate --path="$path" --force --no-interaction || exit 1
done
```

The paths below are listed in **execution order**. You can also run them one-by-one using **Explicit paths** (next section).

---

## Explicit paths (copy/paste)

```bash
php artisan migrate --path=database/migrations/2025_10_22_074303_create_supplier_contacts_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_05_164713_create_customer_submission_form_custom_fields_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_06_125500_add_is_public_to_complaint_notes_and_attachments.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_06_131628_add_receive_feedback_to_customer_contacts_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_06_173000_add_receive_feedback_to_crm_customer_contacts.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_07_135911_add_structured_fields_to_complaintsresolutions_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_09_180001_add_customer_type_to_crm_customers_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_10_072352_add_detailed_fields_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_10_092400_add_status_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_10_163206_add_contact_id_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_11_082229_add_password_to_crm_customer_contacts_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_12_100325_create_feedback_requests_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_12_173000_add_expires_at_to_feedback_requests_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_13_093140_add_delivery_status_to_customer_feedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_16_085618_add_last_reminded_at_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_18_162932_add_report_columns_config_to_crm_customers_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_19_202907_create_crm_report_info_columns_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_23_104554_create_customer_submission_form_columns_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_23_160854_add_indexes_to_crm_customers_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_25_020824_add_crm_unit_id_to_sample_details.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_25_133151_create_crm_company_sections_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_25_133220_add_crm_company_section_id_to_crm_company_units_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_25_154445_create_custom_field_category_customers_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_25_160254_drop_customer_submission_form_custom_fields_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_26_173001_add_closure_metadata_to_complaints_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_27_144404_add_crm_company_section_id_to_sample_details_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_28_153442_create_crm_evaluation_metrics_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_28_153444_create_crm_feedback_ratings_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_28_153555_backfill_crm_feedback_ratings_data.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_02_28_161928_add_specific_feedback_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_04_200526_add_max_rating_to_crm_evaluation_metrics.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_04_203236_add_rating_labels_to_crm_evaluation_metrics.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_04_210255_add_submitted_at_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_09_135304_convert_chain_of_custody_complaints_workflow_stage_to_names.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_09_160049_add_is_internal_to_crm_customers_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_09_165039_add_other_customers_to_crm_customer_contacts_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_09_193736_add_missing_feedback_columns_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_09_194108_seed_default_crm_evaluation_metrics.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_09_235233_add_specific_feedback_column_to_customerfeedbacks_if_missing.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_10_000439_add_crm_contact_columns_to_users_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_20_144312_add_workflow_fields_to_complaints_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_20_144315_add_capa_fields_to_complaintsresolutions_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_20_164235_add_intake_fields_to_complaints_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_20_191500_add_corrective_action_signature_fields_to_complaintsresolutions_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_20_194500_update_car_no_nullable_in_complaintsresolutions_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_22_120757_add_prompt_text_to_crm_evaluation_metrics_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_22_120850_add_comment_to_crm_feedback_ratings_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_22_123503_add_demographic_fields_to_customerfeedbacks_table.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_22_190139_add_workflow_fields_to_complaint_tables.php --force --no-interaction
php artisan migrate --path=database/migrations/2026_03_23_123106_repurpose_crm_company_section_id_to_section_details_in_sample_details_table.php --force --no-interaction
```

## Verify

```bash
php artisan migrate:status --no-interaction
```

---

## Migration file paths (reference list)

- `database/migrations/2025_10_22_074303_create_supplier_contacts_table.php`
- `database/migrations/2026_02_05_164713_create_customer_submission_form_custom_fields_table.php`
- `database/migrations/2026_02_06_125500_add_is_public_to_complaint_notes_and_attachments.php`
- `database/migrations/2026_02_06_131628_add_receive_feedback_to_customer_contacts_table.php`
- `database/migrations/2026_02_06_173000_add_receive_feedback_to_crm_customer_contacts.php`
- `database/migrations/2026_02_07_135911_add_structured_fields_to_complaintsresolutions_table.php`
- `database/migrations/2026_02_09_180001_add_customer_type_to_crm_customers_table.php`
- `database/migrations/2026_02_10_072352_add_detailed_fields_to_customerfeedbacks_table.php`
- `database/migrations/2026_02_10_092400_add_status_to_customerfeedbacks_table.php`
- `database/migrations/2026_02_10_163206_add_contact_id_to_customerfeedbacks_table.php`
- `database/migrations/2026_02_11_082229_add_password_to_crm_customer_contacts_table.php`
- `database/migrations/2026_02_12_100325_create_feedback_requests_table.php`
- `database/migrations/2026_02_12_173000_add_expires_at_to_feedback_requests_table.php`
- `database/migrations/2026_02_13_093140_add_delivery_status_to_customer_feedbacks_table.php`
- `database/migrations/2026_02_16_085618_add_last_reminded_at_to_customerfeedbacks_table.php`
- `database/migrations/2026_02_18_162932_add_report_columns_config_to_crm_customers_table.php`
- `database/migrations/2026_02_19_202907_create_crm_report_info_columns_table.php`
- `database/migrations/2026_02_23_104554_create_customer_submission_form_columns_table.php`
- `database/migrations/2026_02_23_160854_add_indexes_to_crm_customers_table.php`
- `database/migrations/2026_02_25_020824_add_crm_unit_id_to_sample_details.php`
- `database/migrations/2026_02_25_133151_create_crm_company_sections_table.php`
- `database/migrations/2026_02_25_133220_add_crm_company_section_id_to_crm_company_units_table.php`
- `database/migrations/2026_02_25_154445_create_custom_field_category_customers_table.php`
- `database/migrations/2026_02_25_160254_drop_customer_submission_form_custom_fields_table.php`
- `database/migrations/2026_02_26_173001_add_closure_metadata_to_complaints_table.php`
- `database/migrations/2026_02_27_144404_add_crm_company_section_id_to_sample_details_table.php`
- `database/migrations/2026_02_28_153442_create_crm_evaluation_metrics_table.php`
- `database/migrations/2026_02_28_153444_create_crm_feedback_ratings_table.php`
- `database/migrations/2026_02_28_153555_backfill_crm_feedback_ratings_data.php`
- `database/migrations/2026_02_28_161928_add_specific_feedback_to_customerfeedbacks_table.php`
- `database/migrations/2026_03_04_200526_add_max_rating_to_crm_evaluation_metrics.php`
- `database/migrations/2026_03_04_203236_add_rating_labels_to_crm_evaluation_metrics.php`
- `database/migrations/2026_03_04_210255_add_submitted_at_to_customerfeedbacks_table.php`
- `database/migrations/2026_03_09_135304_convert_chain_of_custody_complaints_workflow_stage_to_names.php`
- `database/migrations/2026_03_09_160049_add_is_internal_to_crm_customers_table.php`
- `database/migrations/2026_03_09_165039_add_other_customers_to_crm_customer_contacts_table.php`
- `database/migrations/2026_03_09_193736_add_missing_feedback_columns_to_customerfeedbacks_table.php`
- `database/migrations/2026_03_09_194108_seed_default_crm_evaluation_metrics.php`
- `database/migrations/2026_03_09_235233_add_specific_feedback_column_to_customerfeedbacks_if_missing.php`
- `database/migrations/2026_03_10_000439_add_crm_contact_columns_to_users_table.php`
- `database/migrations/2026_03_20_144312_add_workflow_fields_to_complaints_table.php`
- `database/migrations/2026_03_20_144315_add_capa_fields_to_complaintsresolutions_table.php`
- `database/migrations/2026_03_20_164235_add_intake_fields_to_complaints_table.php`
- `database/migrations/2026_03_20_191500_add_corrective_action_signature_fields_to_complaintsresolutions_table.php`
- `database/migrations/2026_03_20_194500_update_car_no_nullable_in_complaintsresolutions_table.php`
- `database/migrations/2026_03_22_120757_add_prompt_text_to_crm_evaluation_metrics_table.php`
- `database/migrations/2026_03_22_120850_add_comment_to_crm_feedback_ratings_table.php`
- `database/migrations/2026_03_22_123503_add_demographic_fields_to_customerfeedbacks_table.php`
- `database/migrations/2026_03_22_190139_add_workflow_fields_to_complaint_tables.php`
- `database/migrations/2026_03_23_123106_repurpose_crm_company_section_id_to_section_details_in_sample_details_table.php`
