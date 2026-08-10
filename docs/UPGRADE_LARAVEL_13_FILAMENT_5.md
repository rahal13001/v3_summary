# Upgrade Laravel 13 dan Filament 5

## Status

- Tanggal implementasi: 7 Agustus 2026
- Target: Laravel 13 dan Filament 5
- Ruang lingkup: framework, panel admin, plugin Filament, konfigurasi kompatibilitas, aset, dan pengujian kontrak
- Skema/data produksi: tidak diubah; tidak ada migration atau operasi tulis database yang dijalankan

## Hasil Versi

| Komponen | Versi hasil |
|---|---:|
| PHP | 8.3.16 |
| Laravel Framework | 13.24.0 |
| Filament | 5.7.6 |
| Livewire | 4.3.5 |
| Laravel Sanctum | 4.3.3 |
| Filament Shield | 4.3.1 |
| Filament Gaze | 2.2.1 |
| Filament Autograph | 4.2.0 |
| PHPUnit | 12.5.33 |

## Invariant Kritis SSO dan User

Upgrade ini tidak mengubah tabel `users`, model data pengguna, route API, payload login, atau mekanisme bearer token Sanctum. Kontrak yang dikunci dengan automated test meliputi:

1. kredensial salah tetap menghasilkan HTTP 422 dengan bentuk JSON yang sama;
2. login valid tetap mengembalikan status, user, dan personal access token;
3. password tetap tersembunyi dari serialisasi user;
4. token Sanctum yang sudah ada tetap diterima oleh `GET /api/user`;
5. request tanpa token tetap menghasilkan HTTP 401.

Tidak ada file di `database/migrations` yang diubah oleh upgrade ini.

## Strategi Migrasi

Filament dimigrasikan secara berurutan menggunakan upgrade tool resmi: versi 3 ke 4, kemudian versi 4 ke 5. Langkah ini diperlukan karena transformasi API resource, schema, action, table, page, widget, dan relation manager berbeda pada setiap mayor.

Perubahan utama:

- definisi form memakai `Filament\Schemas\Schema`;
- namespace action dan API table disesuaikan dengan Filament 5;
- resource, page, widget, dan relation manager ditransformasikan oleh Rector resmi Filament;
- middleware CSRF panel menggunakan `PreventRequestForgery` sesuai Laravel 13;
- class `Dashboard` diperbaiki kapitalisasi nama filenya agar PSR-4 aman pada Linux;
- TinyEditor yang diarsipkan dan tidak kompatibel dihapus, lalu field `how` dipindahkan ke `Filament\Forms\Components\RichEditor` dengan penyimpanan HTML tetap dipertahankan;
- aset Filament 5 dan plugin aktif dipublikasikan ulang.

## Kompatibilitas Authorization

Filament Shield diperbarui ke mayor 4 dan RoleResource resmi dipublikasikan untuk Filament 5. Builder permission dipasang secara eksplisit untuk mempertahankan nama permission historis berbentuk underscore, sehingga role dan assignment yang sudah ada tidak perlu dimigrasikan.

Kontrak katalog permission:

- 71 permission sebagaimana katalog lama;
- 12 aksi untuk masing-masing dari 5 resource bisnis;
- 6 aksi RoleResource;
- 5 widget;
- permission `page_Dashboard` tetap dikecualikan;
- tidak ada permission baru dengan pemisah titik dua.

Perintah destruktif Shield juga dilarang saat environment production.

## Verifikasi

Pemeriksaan yang dijalankan:

- Composer dependency resolution dan package discovery;
- `composer validate` dan security audit dependency;
- strict PSR-4 autoload check;
- lint PHP untuk source aplikasi;
- kompilasi route dan Blade view;
- publish/upgrade aset resmi Filament;
- test kontrak API SSO/Sanctum;
- test kontrak katalog permission Shield;
- full PHPUnit suite;
- frontend production build.

Satu test bawaan lama, `Tests\Feature\ExampleTest`, sudah gagal sebelum upgrade karena route `/` melakukan redirect HTTP 302 sementara test mengharapkan 200. Kegagalan baseline ini tidak berhubungan dengan Laravel 13, Filament 5, atau API SSO.

## Deployment Aman

1. Buat backup database dan storage sebelum deployment.
2. Deploy ke staging dengan salinan data yang sudah disanitasi.
3. Jalankan `composer install --no-dev --classmap-authoritative` dengan PHP 8.3+.
4. Jalankan `php artisan filament:upgrade` untuk aset target.
5. Bersihkan dan bangun ulang cache konfigurasi, route, dan view.
6. Jalankan test kontrak SSO dan smoke test panel dengan role representatif.
7. Verifikasi aplikasi organisasi lain masih dapat memakai token lama dan login baru.
8. Lakukan rollout produksi dalam maintenance window dan pantau error autentikasi, HTTP 401/422, serta akses panel.

Karena tidak ada migration skema pada perubahan ini, deployment tidak memerlukan `php artisan migrate` khusus upgrade.

## Rollback

Rollback dilakukan dengan mengembalikan release aplikasi, `composer.lock`, dan aset publik ke release sebelumnya. Database tidak memerlukan down migration karena skemanya tidak berubah. Token Sanctum dan data user tetap berada pada database yang sama. Bila cache pernah dibangun oleh release baru, bersihkan cache setelah mengaktifkan release lama.

## Risiko Tersisa

- Uji end-to-end browser dengan seluruh kombinasi role dan data produksi tetap wajib dilakukan di staging.
- Global Composer pada instalasi Laragon berada di luar repository dan harus diperbarui terpisah bila kebijakan keamanan organisasi mensyaratkannya.
- Baseline code style project belum sepenuhnya memenuhi Pint; upgrade tidak melakukan format massal agar diff bisnis tidak membesar.

## Stabilisasi Pasca-Upgrade

Blocker runtime panel, hardening API kompatibel, dependency frontend, migration safety, CI, dan hasil verifikasi final dicatat di POST_UPGRADE_STABILIZATION.md dan decisions/001-preserve-sso-v1-contract.md.
