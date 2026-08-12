# Amspec Dubai — Sample taxonomy restructure runbook

Production target: Dubai LIMS DB `amspecdubai` only. Do not run against Brazil (`amspec_brazil*` / brazil hosts).

## Full server apply order

### 0. Deploy code (all three apps)

Deploy / pull the taxonomy-restructure branches to:

| App | Path (Dubai) |
|-----|----------------|
| LIMS | `/var/www/html/amspec-dubai-imara-lims` |
| Gateway | `/var/www/html/amspec-dubai-imara-apis` |
| Portal | `/var/www/html/amspec-dubai-imara-customerportal` |

On Gateway/Portal as needed: `composer install --no-dev`, portal `npm ci && npm run build`, restart PHP-FPM/Apache and PM2 portal process.

### 1. LIMS — schema + data wipe + seed (SSH on LIMS host)

```bash
cd /var/www/html/amspec-dubai-imara-lims

# Confirm .env points at Dubai DB (amspecdubai), not Brazil
php artisan migrate --force

# Wipe operational data only (samples / requests / quotations / pricelists)
php artisan ops:purge-pricelists-quotations-samples-requests --force

# Wipe taxonomy catalog only (categories → elements + bind pivots)
php artisan taxonomy:purge-sample-catalog --force

# Seed categories + sample types (matrix sub categories)
php artisan db:seed --class=AmspecDubaiSampleTaxonomySeeder --force

# Seed starter analysis types (Food Micro/Halal, Feed Chemical/Chemistry, Swab Micro, Waste Water Chemical)
php artisan db:seed --class=AmspecDubaiAnalysisTypesSeeder --force

# Relink TRFs to categories + keep only Food / Water / Swab on RFT (+ create Swab TRF)
php artisan db:seed --class=AmspecDubaiTrfCategoryBindSeeder --force

# Optional keyword relink for any other TRFs
php artisan taxonomy:relink-trf-categories --force
# Preview only: php artisan taxonomy:relink-trf-categories --dry-run
```

### 2. Gateway + Portal

```bash
# Gateway
cd /var/www/html/amspec-dubai-imara-apis
php artisan optimize:clear   # if used
# restart php-fpm / apache as you normally do

# Portal
cd /var/www/html/amspec-dubai-imara-customerportal
# npm run build if assets changed
pm2 restart amspec-dubai-imara-customerportal
```

### 3. Smoke checks

1. LIMS: Configurations → Sample Type Category → Sample Types (filter by category).
2. LIMS: open Seafood / Feed / Hand Swab / Waste Water → confirm analysis types above.
3. LIMS: Edit a TRF → Sample Type Categories attached.
4. Gateway: `GET /api/v1/portal/reference/sample-type-categories` and `sample-types?sample_type_category_id=…`
5. Portal: resolve TRF with `sample_type_category_id`; cascade sample type → analysis type → elements.

### 4. Optional follow-up

Bulk-import remaining analysis types / analysis elements via System Bulk Import when ready.

## Analysis type map (this seed)

| Sample type (code) | Analysis types |
|--------------------|----------------|
| Seafood / Nonseafood / Other | Microbiological, Halal |
| Feed | Chemical, Chemistry |
| Hand / Surface / Sponge Swab | Microbiological |
| Waste Water | Chemical |

## Safety

- Purge commands require `--force`. They use allowlisted DELETEs / NULL FKs only — never `truncate … cascade` into CRM, users, form definitions, standards master, inventory, or equipment.
- Invoice **headers** are kept; `customer_invoice.pricelist_id` may be nulled (column made nullable if needed) so deleting pricelists does not CASCADE-delete invoices.
