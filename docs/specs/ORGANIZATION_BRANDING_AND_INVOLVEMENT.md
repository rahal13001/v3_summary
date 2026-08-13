# Spesifikasi Branding Organisasi dan Perilaku Keterlibatan

## Objective

Satu source code harus dapat menampilkan identitas aplikasi dan perilaku form laporan yang berbeda pada deployment Sorong dan Kupang tanpa memeriksa nama kota, domain, atau teks Keterlibatan. Setiap deployment tetap memakai database, storage, cache, session, dan konfigurasi teknis yang terpisah.

## Tech Stack

- Laravel 13 dan PHP 8.3+
- Filament 5
- MySQL pada deployment; SQLite `:memory:` pada automated test
- Public disk melalui route aplikasi `/public-storage/{path}`

## Commands

- Focused tests: `php artisan test --do-not-cache-result tests/Feature/OrganizationSettingsTest.php tests/Feature/ReportOrganizationDimensionsTest.php tests/Feature/ReportResourceFormTest.php`
- Full tests: `php artisan test --do-not-cache-result`
- Format: `vendor/bin/pint --dirty`
- Autoload: `composer dump-autoload --strict-psr`
- Frontend build: `npm run build`
- Diff validation: `git diff --check`

## Project Structure

- `database/migrations/`: perubahan skema aditif
- `app/Models/OrganizationSetting.php`: nilai konfigurasi deployment
- `app/Services/OrganizationContext.php`: akses terpusat dengan fallback `.env`
- `app/Filament/Resources/OrganizationSettingResource.php`: UI konfigurasi admin
- `app/Providers/Filament/AdminPanelProvider.php`: nama, logo, dan favicon panel
- `app/Models/Involvement.php` dan `app/Models/Report.php`: klasifikasi serta enforcement Penyelenggara
- `app/Filament/Resources/ReportResource.php`: interaksi form Penyelenggara
- `docs/`: keputusan arsitektur dan runbook deployment

## Code Style

Nilai deployment hanya dibaca melalui `OrganizationContext`; domain logic tidak membaca `.env` langsung dan tidak membandingkan nama organisasi atau nama Keterlibatan.

```php
$organization = app(OrganizationContext::class);

return $organization->organizerInputMode() === OrganizationSetting::ORGANIZER_MODE_LOCKED;
```

## Functional Requirements

### Branding

- `app_name` adalah nama produk/web, misalnya `Summary` atau `Teripang`, dan terpisah dari `name`/`short_name` organisasi.
- `logo_path` adalah logo panel aplikasi per deployment. Nilai lama tetap dipakai agar upload yang sudah ada tidak hilang.
- `favicon_path` adalah favicon per deployment.
- Jika record atau field belum tersedia, fallback berasal dari `APP_NAME`, `ORGANIZATION_*`, lalu asset Summary yang sekarang agar Sorong tidak berubah.
- Logo dan favicon hanya menerima JPEG, PNG, atau WebP, maksimal 2 MB; SVG dan wildcard MIME tidak diperbolehkan.

### Keterlibatan dan Penyelenggara

- Label seperti `Penyelenggara/Peserta` atau `Internal/Eksternal` tetap berasal dari `involvements.name`; admin dapat mengelolanya per database.
- `is_lprl_organizer` dipertahankan untuk kompatibilitas, tetapi UI menyebutnya sebagai penanda “organisasi sebagai penyelenggara/internal”, bukan LPRL.
- Pengaturan Organisasi menyediakan `organizer_name` dan `organizer_input_mode`:
  - `locked`: otomatis memakai `organizer_name` (fallback `short_name`) dan tidak dapat diedit;
  - `editable`: otomatis memberi saran nama, tetapi pengguna boleh mengubahnya;
  - `manual`: tidak mengisi nama otomatis dan pengguna wajib mengisinya.
- Mode hanya berlaku pada Keterlibatan dengan `is_lprl_organizer = true`. Keterlibatan lain selalu meminta input manual.
- Server wajib menegakkan mode `locked` dan memberi fallback pada mode `editable` ketika nilai kosong, sehingga manipulasi client tidak dapat melewati aturan.
- Klasifikasi export Monev tetap memakai flag boolean, bukan nama atau mode input.

## Testing Strategy

- Migration/schema test membuktikan kolom baru aditif dan default mempertahankan perilaku Sorong.
- Context tests membuktikan fallback serta override branding/perilaku.
- Model tests membuktikan tiga mode, external/manual behavior, dan enforcement server-side.
- Resource contract tests membuktikan upload aman dan UI tidak lagi hardcode LPRL.
- Browser smoke test membuktikan Pengaturan Organisasi dan form laporan dapat dirender tanpa error console.

## Boundaries

- Always: migration aditif, cache invalidation, input validation, fallback kompatibel, dan test SQLite in-memory.
- Ask first: dependency baru, perubahan API/SSO, perubahan permission, atau migrasi data historis.
- Never: hardcode Sorong/Kupang pada domain logic, SVG upload, `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau migration/seed pada database pengguna selama automated verification.

## Success Criteria

- Sorong dapat memakai `Summary` dengan logo/favikonnya, sementara Kupang memakai `Teripang` dengan aset berbeda.
- Admin dapat memakai nama Keterlibatan `Penyelenggara/Peserta`, `Internal/Eksternal`, atau nama lain tanpa mengubah kode.
- Pemilihan Keterlibatan internal mengikuti mode locked/editable/manual yang dikonfigurasi.
- Data lama tanpa kolom terisi mempertahankan perilaku locked dengan singkatan organisasi.
- Test, formatter, build, autoload, browser smoke test, dan diff check lulus.

## Open Questions

Tidak ada. Pengguna telah menyetujui konfigurasi per organisasi dan pemisahan deployment.
