# Report Data and Storage Resilience

## Audit result — 10 August 2026

The database currently contains imported report data, while the local public storage does not contain the corresponding documentation files.

- 2,438 active reports were found.
- 15 active reports do not have a `documentations` row.
- 8,851 non-empty documentation/file references do not resolve to a local file on the `public` disk.
- No duplicate documentation row per report was found.
- One imported file path contains characters rejected by Flysystem path normalization.

These results do not justify clearing database paths automatically. A path can still become valid after the original `storage/app/public` files are restored.

## Application rules

1. Database values and physical files are separate concerns. A path in `documentations` is not proof that the file exists locally.
2. Public files must be resolved through `Storage::disk('public')`, not by concatenating `asset()` with a database value.
3. A missing or malformed file path must be treated as unavailable content, not as a fatal rendering error.
4. Report detail pages render only files that pass the public-disk existence check.
5. PDF output skips missing documentation images and optional document QR codes.
6. Existing reports without signatures remain editable; signatures are required for newly created reports.
7. Existing many-to-many report fields use the relationship state paths `followers`, `indicators`, and `teams`.
8. `workUnits` is a separate many-to-many relationship and must not replace or reuse `teams`.
9. Historical reports may have `involvement_id = null` and no `report_work_unit` rows. Viewing them must remain safe; editing requires the new fields.
10. A report with an involvement flagged `is_lprl_organizer` always persists `LPRL Sorong` as `penyelenggara`, including when a client tampers with the read-only field.
11. PDF and Excel eager-load `workUnits` and `involvement` so the new output does not introduce per-row relationship queries.

## Deployment of organization dimensions

Run these commands during deployment after the application code is available:

```text
php artisan migrate --isolated
php artisan db:seed --class=InvolvementSeeder
php artisan shield:generate --all --panel=admin
```

The migration is additive. It leaves historical reports with `involvement_id = null` and does not create guessed Unit Kerja links. Seed only creates the initial `Penyelenggara` and `Peserta` values when missing. Do not use `migrate:fresh`, `migrate:refresh`, or `db:wipe` on an existing environment.

## File restoration

When the hosting files are available, restore them under the same relative paths on the configured public disk. Do not rename database references unless the source file has also been verified.

After restoration, verify a small sample through the application and the public-storage route before attempting a bulk repair.

## Read-only audit queries

Reports without documentation:

```sql
SELECT r.id, r.slug
FROM reports r
LEFT JOIN documentations d ON d.report_id = r.id
WHERE r.deleted_at IS NULL
  AND d.id IS NULL;
```

Duplicate documentation rows:

```sql
SELECT report_id, COUNT(*) AS total
FROM documentations
GROUP BY report_id
HAVING COUNT(*) > 1;
```

Orphan report users:

```sql
SELECT ru.report_id, ru.user_id
FROM report_users ru
LEFT JOIN users u ON u.id = ru.user_id
WHERE u.id IS NULL;
```

## Verification

Run the focused resilience tests with SQLite:

```text
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
php artisan test tests/Feature/ReportResourceFormTest.php tests/Feature/ReportOrganizationDimensionsTest.php tests/Feature/ReportOrganizationOutputsTest.php tests/Feature/ReportImportedDataResilienceTest.php
```

The current code intentionally makes no database content changes. Missing files, missing documentation rows, orphan pivots, and unmapped users should only be edited after their source data has been verified.
