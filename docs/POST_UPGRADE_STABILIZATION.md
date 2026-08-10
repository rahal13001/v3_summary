# Post-Upgrade Stabilization Laravel 13 / Filament 5

## Status

Selesai pada 8 Agustus 2026. Perubahan ini menstabilkan hasil upgrade tanpa mengubah tabel `users`, payload login sukses SSO v1, token lama, atau data produksi.

## Perbaikan yang Diterapkan

### Panel Filament

- Menghapus 88 published override core `filament` dan `filament-panels` dari Filament 3/4 yang mengalahkan view Filament 5.
- Menghapus 18 published view yatim milik Filament Breezy dan Filament Edit Profile yang paketnya sudah tidak terpasang.
- Menghapus konfigurasi Filament PWA yatim.
- Menambahkan smoke test guest untuk login, registrasi, dan permintaan reset password.
- Root cause HTTP 500 adalah referensi `Filament\Support\Enums\ActionSize` pada override lama; enum tersebut tidak tersedia pada Filament 5.

### Keamanan API SSO

- `POST /api/login` dibatasi 5 percobaan per menit untuk kombinasi email lowercase dan alamat IP.
- User dengan `status = false/0` tidak dapat memperoleh token baru.
- Respons penolakan user nonaktif sengaja sama dengan kredensial salah untuk mencegah account enumeration.
- Menambahkan `POST /api/logout` dengan middleware `auth:sanctum`; hanya current access token yang dicabut.
- Payload login sukses, field user, token lama, ability, dan expiry global tidak diubah demi kompatibilitas consumer organisasi.

Token yang pernah ditampilkan di percakapan atau log harus dicabut secara operasional dan diganti. Repository tidak menyimpan nilai token tersebut.

### Database dan Migration

Migration `2025_06_02_154206_create_personal_access_tokens_table.php` menjadi pemilik canonical tabel Sanctum. Migration duplikat `154547` dipertahankan sebagai no-op agar:

- fresh migration tidak mencoba membuat tabel dua kali;
- deployment lama dapat mencatat migration tanpa mengubah token/data;
- rollback migration duplikat tidak menjatuhkan tabel canonical.

Regression test migration selalu memakai SQLite `:memory:` dan tidak menyentuh database konfigurasi aplikasi.

### Rich Text

Output `reports.how` tetap menyimpan dan menampilkan HTML, tetapi kini melewati `sanitizeHtml()` sebelum dirender. Script tag dan event handler berbahaya dibuang; format rich text yang aman tetap dipertahankan.

### Frontend Supply Chain

| Dependency | Versi hasil |
|---|---:|
| Axios | 1.19.0 |
| Firebase JS | 12.17.1 |
| Laravel Vite Plugin | 3.1.3 |
| Vite | 8.2.1 |

Audit npm turun dari 13 vulnerability (3 critical, 7 high, 3 moderate) menjadi 0. Production bundle dibangun ulang dari lock file baru.

### Quality Gate

Workflow `.github/workflows/quality.yml` menjalankan:

- Composer validate/install/audit;
- fresh SQLite migration;
- seluruh PHPUnit suite;
- npm frozen install;
- npm audit level high;
- production frontend build.

## Bukti Verifikasi

- PHPUnit: 19 test lulus, 60 assertion.
- Kontrak API: login lama, token lama, unauthorized, user nonaktif, rate limit, dan current-token logout lulus.
- Panel guest: login, registrasi, dan reset-password request HTTP 200.
- Permission Shield: katalog legacy 71 permission tetap lulus.
- Rich text XSS regression test lulus.
- Fresh migration test lulus dalam SQLite terisolasi.
- Composer audit: tidak ada advisory.
- npm audit: 0 vulnerability.
- Vite production build berhasil.
- Route cache dan Blade view cache berhasil.

## Pengujian Staging yang Masih Wajib

Automated test tidak menggantikan pengujian dengan data realistis. Sebelum production:

1. uji login panel untuk `super_admin`, `admin`, `writer`, `panel_user`, dan `katimja`;
2. uji navigasi dan permission setiap role;
3. uji CRUD laporan, order, user, IKU, tim, dan role;
4. uji Relation Manager Executor, upload file, RichEditor, tanda tangan, PDF, QR, dan Excel;
5. uji filter serta semua widget dashboard;
6. uji consumer SSO organisasi dengan token lama dan token baru;
7. uji logout hanya memutus token aktif;
8. pantau console browser, request Livewire, log 5xx, 401/422, latency, queue, scheduler, email, dan FCM.

## Rollback

Tidak ada schema baru pada stabilisasi ini. Rollback dilakukan dengan mengaktifkan release sebelumnya beserta `composer.lock`, `package-lock.json`, dan aset build sebelumnya, lalu membersihkan cache Laravel. Database tidak memerlukan down migration. Jangan menjalankan rollback migration canonical Sanctum karena dapat menghapus token SSO.