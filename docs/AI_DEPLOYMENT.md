# Deployment dan Pemulihan API AI Readonly

## Sebelum deployment

Gunakan backup aplikasi dan database. Pastikan runtime memenuhi composer.lock dan versi PHP proyek. Node.js hanya diperlukan pada lingkungan build dan laptop pengguna, bukan pada shared hosting untuk menjalankan API.

Jalankan pengujian pada database terisolasi. Jangan menjalankan smoke fixture, migrate:fresh, migrate:refresh, atau db:wipe pada database aplikasi. Pengujian standar memaksa SQLite in-memory. Smoke membuat database baru sendiri di storage/framework/testing.

```bash
php artisan test
vendor/bin/pint --test
npm run build
cd tools/summary-assistant
npm ci
npm run typecheck
npm test
npm run build
npm run smoke
```

Smoke memerlukan PHP CLI dengan ekstensi SQLite dan openssl CLI. Smoke menggunakan CA lokal yang diteruskan hanya ke proses MCP fixture; tidak mematikan pemeriksaan TLS. Pada WSL, wrapper PHP Laragon dapat digunakan. Smoke tidak memanggil model AI atau menggunakan login ChatGPT.

Untuk pemeriksaan kompatibilitas tambahan pada MySQL lokal, jalankan dari root proyek:

```bash
php -r 'putenv("SUMMARY_ALLOW_LOCAL_MYSQL_TEST=1"); require "tests/Fixtures/ai-mysql.php";'
```

Helper tersebut menolak host non-localhost dan URL koneksi bebas. Helper membuat database baru dengan nama acak, menjalankan migration dan data sintetis, memeriksa query/readonly, lalu menghapus hanya database yang dibuatnya. Akun MySQL lokal memerlukan hak CREATE/DROP DATABASE. Jangan menjalankannya pada shared hosting atau memberikan akses database produksi untuk pengujian.

Hasil aktual dan masalah formatter lama dicatat di [AI_VERIFICATION.md](AI_VERIFICATION.md). Lulus pemeriksaan lokal tidak berarti hosting sudah aktif.

## Jalur Git/SSH

Pengguna memilih Git/SSH, tetapi membatasi pekerjaan sesi implementasi sampai commit lokal. Tidak ada push atau deployment otomatis pada sesi ini.

Saat deployment diminta, tentukan domain, alias SSH, folder aplikasi, branch, serta revision commit yang akan dikirim. Pastikan backup database/storage selesai dan worktree server bersih. Catat revision server sebelumnya untuk rollback source. Jangan menimpa perubahan lokal server atau `.env`.

Pada server, periksa dahulu kondisi berikut dari folder aplikasi:

```bash
git status --short
git branch --show-current
git rev-parse HEAD
php -v
php artisan migrate:status
```

Ambil revision yang telah ditinjau melalui prosedur Git proyek, dengan pembaruan fast-forward jika memakai branch deployment. Jika branch menyimpang, hentikan dan selesaikan perbedaannya sebelum deployment; jangan memakai reset paksa. Gunakan dependency Composer dari lockfile dan aset frontend hasil build. Node.js tidak perlu dipasang di shared hosting jika aset dibangun sebelum dikirim.

Tinjau seluruh pending migration sebelum menjalankan `migrate --force`. Untuk membatasi aktivasi hanya pada migration fitur AI ketika semua prasyarat sudah terpasang, gunakan:

```bash
php artisan migrate --pretend --path=database/migrations/2026_10_20_000001_create_ai_access_grants_table.php
php artisan migrate --force --path=database/migrations/2026_10_20_000001_create_ai_access_grants_table.php
php artisan optimize:clear
```

Perintah tersebut hanya dijalankan setelah verifikasi target dan backup, dengan feature flag tetap nonaktif sampai permission serta HTTPS siap. Jangan memakai fixture pengujian sebagai perintah setup produksi. Setelah aktivasi, lakukan smoke dengan token minimum dan data yang diizinkan, lalu uji pencabutan. Pembuatan token pribadi tetap melalui UI manusia.

## Deployment shared hosting

1. Pertahankan `AI_API_ENABLED=false` selama upload dan migration.
2. Deploy source aplikasi beserta dependensi dan aset yang dibangun sesuai prosedur proyek. Jangan mengunggah node_modules paket MCP, fixture pengujian, atau dokumen lokal pengguna sebagai aset publik.
3. Jalankan `php artisan migrate --force` melalui CLI atau fasilitas deployment hosting. Migration hanya menambahkan tabel grant dan permission, tanpa memberikan permission otomatis kepada semua pengguna.
4. Jalankan `php artisan optimize:clear`, lalu bangun ulang cache konfigurasi sesuai prosedur hosting. Tidak memerlukan restart worker baru untuk fitur ini.
5. Pastikan document root mengarah ke public, HTTPS sah, dan Authorization diteruskan ke PHP. Jangan membuat endpoint web migration.
6. Berikan `use_ai_api` kepada akun uji melalui role yang sudah tersedia. Pastikan permission baca laporan dan relasi juga tersedia. Permission people mengikuti policy User; evaluasi mengikuti Monev dan policy unit.
7. Set `AI_API_ENABLED=true`, bersihkan/bangun ulang cache konfigurasi, lalu uji melalui token terbatas dari halaman Akses AI.
8. Hubungkan MCP laptop sesuai AI_CODEX_SETUP.md. Uji capabilities, daftar/detail laporan, satu periode, pencabutan token, dan dokumen.
9. Periksa log, durasi, jumlah query, ukuran respons, dan kapasitas hosting dengan data representatif sebelum penggunaan luas.

Jika hosting memakai Apache dan menghapus Authorization, gunakan pengaturan resmi penyedia hosting untuk meneruskan header tersebut. Jangan mengubah kredensial atau kontrak SSO lama untuk mengatasi masalah token AI.

## Operasional

- API default nonaktif dan semua token AI tetap terisolasi dari route lama ketika fitur nonaktif.
- Rate limit 60 permintaan/menit/token dan 120/menit/pengguna memakai cache yang tersedia.
- Respons private/no-store. Jangan menyimpan respons token di CDN bersama.
- Audit `storage/logs/ai-access-*.log` disimpan 14 hari melalui channel daily.
- Audit menyebut request ID, token ID, pengguna, route, status, durasi, dan jumlah hasil; tanpa token atau isi laporan.
- Pastikan storage/logs dan penyimpanan cache dapat ditulis.
- Token 7/30/90 hari diperiksa pada setiap permintaan. Token yang kedaluwarsa tidak membutuhkan cron agar ditolak.
- Tidak mengubah expiry global Sanctum, sehingga token SSO lama tidak terpengaruh.
- Dokumen dan token di laptop menjadi tanggung jawab pemilik. Pencabutan API tidak menarik kembali file yang sudah disimpan.

## Pemulihan

Nonaktifkan `AI_API_ENABLED`, perbarui cache konfigurasi, dan cabut token melalui panel bila diperlukan. Tabel tambahan dapat dibiarkan. Jangan menjalankan rollback migration pada produksi sebagai pemulihan biasa karena dapat menghapus grant.

Kontrak SSO lama dipertahankan. Jika ada regresi aplikasi, gunakan prosedur rollback source proyek sambil mempertahankan data. Tidak ada perubahan atau penghapusan laporan yang diperlukan untuk menonaktifkan fitur AI.

## Verifikasi yang masih menjadi tanggung jawab deployment

Pengujian pengembangan membuktikan alur pada fixture SQLite, kernel Laravel asli, HTTPS lokal, dan MCP stdio. Sebelum aktivasi hosting, pengelola perlu memverifikasi mesin database hosting, header Authorization, kapasitas shared hosting, domain/sertifikat nyata, serta login ChatGPT Plus pada laptop pengguna. Kredensial produksi dan akun ChatGPT tidak disimpan dalam repository.
