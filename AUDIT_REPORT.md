# Summary Production Audit Report

Tanggal audit: 10 Agustus 2026  
Workspace: `C:\laragon\www\summary`  
Stack terdeteksi: Laravel 13.24.0, PHP CLI 8.4.15, Filament 5, Livewire 4  
Status: **NOT READY**

## Ringkasan eksekutif

Aplikasi belum layak dirilis ke production. Perbaikan kode selama audit telah menutup IDOR Report dan Order, self-registration tanpa verifikasi, stored SVG, akses panel oleh user nonaktif, kontrak permission Role yang tidak sinkron, penghapusan file terjadwal yang berbahaya, dan N+1 utama pada daftar disposisi. Full test suite final lulus **62 test / 233 assertion** pada SQLite `:memory:`.

Blocker utama yang tersisa bukan kegagalan test, melainkan ketidakcocokan schema/migration terhadap MySQL aktual dan model “verifikasi tanda tangan” yang belum mengikat penandatangan ke versi dokumen. Migration fresh mendefinisikan `reports.kode` sebagai `VARCHAR(255)`, sedangkan MySQL aktual memakai `TEXT` dan memuat signature sampai 56.106 byte. Repository migration juga berbeda dari schema serta data aktual pada banyak kolom, foreign key, index, dan soft-delete. Menjalankan migration dari repository ini terhadap production belum aman.

Selain itu, sinkronisasi storage wajib diselesaikan. Database memiliki 2.460 record dokumentasi, sedangkan snapshot storage lokal hanya memiliki tiga file. Ini dapat merupakan kondisi deployment lokal, tetapi harus dibuktikan dengan inventory hosting sebelum release.

## Keselamatan audit dan integritas database

- Worktree sudah sangat kotor sebelum audit (lebih dari 190 perubahan/untracked). Perubahan pengguna dipertahankan; tidak ada reset, checkout destruktif, commit, atau push.
- Tidak ada `migrate:fresh`, `migrate:refresh`, `db:wipe`, `truncate`, reset, seed, atau migration terhadap MySQL.
- Agen database menetapkan session read-only, memakai `SELECT`, `SHOW`, dan `EXPLAIN`, lalu rollback.
- Satu focused test awal dari agen fungsional secara tidak sengaja membaca cached config MySQL dan mencoba `CREATE TABLE teams`. Perintah gagal karena tabel sudah ada; tidak ada table/data yang dibuat atau diubah. Kejadian ini memicu perbaikan test isolation fail-closed sebelum test database berikutnya.
- `phpunit.xml` kini memakai `tests/bootstrap.php`, memaksa SQLite `:memory:`, memaksa config cache testing yang tidak ada, dan berhenti sebelum Laravel boot bila konfigurasi tidak aman.
- Semua test database final memakai SQLite in-memory. MySQL tidak di-reset dan tidak menerima successful write dari audit.
- Compiled config cache yang masih menyimpan mapping `public/storage` lama dibersihkan hanya dengan `php artisan config:clear` (tanpa database-backed `cache:clear`). Verifikasi akhir menunjukkan effective `filesystems.links=[]`, `bootstrap/cache/config.php` tidak ada, dan `public/storage` tidak ada.

## Peta arsitektur

- Bootstrap route dan scheduler: `bootstrap/app.php`, `routes/web.php`, `routes/api.php`, `routes/console.php`.
- Panel Filament berada di `/`, dengan authentication middleware, session, CSRF, Shield, Gaze, notification, dan database transactions di `app/Providers/Filament/AdminPanelProvider.php`.
- Domain laporan berpusat pada `ReportResource`, model `Report`, lima halaman resource, relasi Documentation/Indicator/Follower/Team, export Excel, PDF publik, dan storage publik melalui controller aplikasi.
- Domain disposisi berpusat pada `OrderResource`, model `Order`, relasi Executor, email reminder, FCM scheduler, dan file letter/proof.
- Dashboard menggabungkan overview, chart indicator/team, dan ranking bulan/tahun.
- Tidak ada job queue aplikasi. Email dan FCM saat ini masih berjalan sinkron di command.

Graphify digunakan sebagai peta awal. Graph existing dibangun 8 Agustus 2026 dari commit `611184c1` dan memuat 6.642 node, 17.794 edge, serta 1.030 community. Graph tersebut stale terhadap worktree 10 Agustus dan tercemar vendor/minified JavaScript; hubungan runtime Filament/route/model tidak selalu terdeteksi. Semua temuan material karena itu diverifikasi kembali pada source atau runtime terkini.

## Risk matrix

| ID | Severity / prioritas | Dampak | Kemungkinan | Bukti | Rekomendasi | Status |
|---|---|---|---|---|---|---|
| DB-01 | Critical / P0 | Fresh deploy atau migration dapat gagal/truncate signature | Pasti pada fresh schema | Migration `reports.kode` `VARCHAR(255)`; MySQL aktual `TEXT NULL`; 1.045/2.475 report bertanda tangan, maksimum 56.106 byte | Setelah backup dan staging rehearsal, buat baseline/reconciliation migration ke tipe yang disetujui (minimal `TEXT`, pertimbangkan `MEDIUMTEXT`) | **OPEN — blocker** |
| DB-02 | Critical / P0 | Migration dapat mengubah semantics/data aktual | Tinggi | Migration vs actual berbeda pada slug unique, `no_st`, total, `kode`, kolom legacy, JSON/longtext, soft delete, dan migration ledger | Buat schema baseline dari actual, diff ter-review, dan rehearsal pada clone; jangan jalankan migration repo saat ini | **OPEN — blocker** |
| DB-03 | High / P0 | Hapus user dapat menghapus/merusak report dan orphan | Tinggi bila action dibuka | User tanpa SoftDeletes; actual memiliki 3 report orphan dan 25 pivot follower orphan; fresh FK memakai cascade | Tentukan lifecycle user, perbaiki orphan setelah backup, lalu buat FK/soft-delete plan | **MITIGATED in code**: semua delete/restore user fail-closed; data tetap open |
| SEC-01 | High / P0 | Follower/editor dapat memalsukan identitas/tanda tangan atau mengubah dokumen setelah ditandatangani | Tinggi bila PDF dianggap resmi | `user_id` dan `kode` dapat diedit; verifikasi hanya menampilkan relasi user, tanpa signer/hash/version/timestamp/audit | Batasi owner/signature mutation; invalidasi signature saat konten berubah; rancang signer, signed_at, content hash/version, immutable audit trail | **OPEN — blocker** |
| DATA-01 | High / P0 | File dokumentasi hilang setelah deploy | Tinggi tanpa storage sync | 2.460 documentation rows, hanya 3 file pada storage snapshot; 2.459 dokumentasi1 dan ribuan path lain tidak ada lokal | Inventory hosting, checksum manifest, backup, dan sync storage sebelum cutover | **OPEN — deployment blocker** |
| SEC-02 | High / P0 | User anonim langsung masuk panel | Trivial sebelum fix | Registrasi aktif, `MustVerifyEmail` tidak diimplementasi, Shield auto-role `panel_user`, status default aktif | Matikan self-registration atau implement approval + verifikasi nyata | **FIXED**: `/register` 404 |
| SEC-03 | High / P0 | IDOR mutasi Report | Tinggi sebelum fix | Policy hanya mengecek permission generik; runtime proof user unrelated dapat update report orang lain | Permission + owner/follower/admin object scope; destructive bulk fail-closed | **FIXED + regression test** |
| SEC-04 | High / P0 | IDOR view/edit/delete Order | Tinggi sebelum fix | Policy generik sementara UI hanya men-scope tab executor | Executor hanya view; creator/admin mutate; bulk destructive fail-closed | **FIXED + regression test** |
| SEC-05 | High / P0 | Stored XSS melalui SVG upload/public storage | Tinggi sebelum fix | Upload `image/*`, retained extension, public same-origin response | Exact raster MIME, extension–MIME pairing, docs attachment, `nosniff`, no direct symlink | **FIXED + disguised-SVG test**; effective config/cache verified |
| OPS-01 | High / P0 | Scheduler menghapus letter/proof/file lain setiap hari | Pasti pada schema tertentu sebelum fix | Cleanup membaca kolom `settings.pwa` yang tidak ada dan allowlist tidak mencakup Order/Executor | Dry-run default, managed dirs only, preserve all known references, delete explicit only | **FIXED**; manual `--delete` tetap dilarang sebelum guardrail |
| AUTH-01 | High / P0 | User nonaktif masih masuk panel | Tinggi sebelum fix | `canAccessPanel()` hanya mengecek role | Status fail-closed + role | **FIXED + regression test** |
| AUTH-02 | High / P0 | Role management terkunci/tidak konsisten | Tinggi bagi non-bypass admin | Catalog `view_any_role`, policy memakai `*_shield::role` dan placeholder | Samakan policy dengan catalog; deny unsupported operations | **FIXED + contract test** |
| DB-04 | High / P1 | Ambiguous route model binding dan double count | Sedang/aktual | Dua pasangan slug duplicate, duplicate pivot indicator, missing unique composite keys | Deduplicate setelah backup; tambah unique constraints/index dalam reconciliation plan | **OPEN** |
| DB-05 | High / P1 | Relasi diam-diam salah | Aktual | 41 orphan `indicator_reports`, 25 orphan follower pivot, 3 orphan report users | Repair script idempotent pada clone, report before/after, baru jalankan setelah approval | **OPEN** |
| API-01 | Medium / P1 | Token user nonaktif tetap valid tanpa batas | Tinggi setelah token terbit | Route Sanctum hanya auth; expiry null; status tidak diperiksa per request | Active-user middleware, revoke on deactivation/password reset, expiry/abilities | **OPEN** |
| API-02 | Medium / P1 | Password spraying lintas banyak email | Sedang | Limiter key hanya email+IP; enam email berbeda tidak berbagi bucket | Tambah limiter agregat per-IP dan dummy password check | **OPEN** |
| PERF-01 | Medium / P1 | 40–300 query ekstra per halaman Order | Pasti sebelum fix | Empat query accessor per row pada page size 10–75 | `withCount`, eager-load executor, accessor reuse loaded data | **FIXED + query-log test** |
| PERF-02 | Medium / P1 | Reminder/FCM timeout karena N+1 dan I/O serial | Tinggi saat penerima bertambah | Command lazy-load executor user; mail/FCM sinkron | Eager-load user sekarang; queue/batch, retry/backoff/idempotency masih perlu | **PARTIAL** |
| PERF-03 | Medium / P1 | Scheduler overlap/duplicate FCM | Sedang | everyMinute tanpa lock/single server, caller mengabaikan failure | Lock, idempotency marker, timeout, failure exit/metrics | **OPEN** |
| PERF-04 | Medium / P1 | Public PDF dapat menghabiskan CPU/memory | Sedang–tinggi | DomPDF + tiga QR + tiga gambar per anonymous GET, tanpa throttle/cache | Throttle, cache by report/file version, image dimension limits, metrics | **OPEN** |
| PERF-05 | Medium / P1 | Query dashboard tidak memakai index secara efektif | Tinggi saat data tumbuh | `whereDate/year/month`, actual EXPLAIN full scan/filesort, no useful index | Gunakan range `[start,end)`, validasi index dengan EXPLAIN pada clone | **OPEN** |
| PERF-06 | Medium / P1 | Excel selection besar dapat OOM/timeout | Sedang | `FromCollection`, synchronous download, autosize, unbounded selected rows | Queued/query export, chunk, limit, fixed widths, progress | **OPEN** |
| UX-01 | Medium / P1 | Keyboard-only user tidak dapat mengisi signature wajib | Pasti untuk pengguna tersebut | Canvas pointer-only, tanpa keyboard/alternative input | Rancang alternative input/upload/assisted signing yang sah; uji keyboard | **OPEN — needs product decision** |
| UX-02 | Medium / P1 | Link password reset tidak masuk Tab order | Aktual pada login guest | Runtime DOM `tabindex=-1` | Custom auth action dengan natural tab order | **OPEN** |
| UX-03 | Medium / P1 | Link rich text dark mode gagal WCAG AA | Pasti sebelum fix | 3,43:1 terhadap dark container | Warna dark `#60a5fa` + focus-visible | **FIXED** |
| FUNC-01 | Medium / P1 | Team filter menghasilkan key yang salah | Pasti sebelum fix | `Team::pluck('nama_tim')` tanpa ID | `pluck('nama_tim', 'id')` | **FIXED** |
| FUNC-02 | Medium / P1 | PDF tidak mencakup seluruh field report | Aktual | `why`, penyelenggara, dan teams tidak tampil | Tentukan template resmi dan tambah rendering regression test | **OPEN** |
| OPS-02 | Medium / P1 | Manual cleanup irreversible tanpa manifest/quarantine | Tinggi bila operator memakai `--delete` | Command hanya menampilkan jumlah, lalu delete | Candidate manifest, minimum age, quarantine, lock, confirmation | **OPEN; do not use `--delete`** |
| OBS-01 | Medium / P1 | Storage/FCM/PDF failures sulit didiagnosis | Tinggi saat insiden | Exception storage disenyapkan; single log; minim metrics/correlation | Structured logs, scheduler heartbeat, slow query, queue/FCM/PDF metrics dan alert | **OPEN** |
| QA-01 | Low / P2 | Formatting gate repo belum hijau | Pasti | Targeted Pint gagal pada 12 production files; generated JS juga punya whitespace legacy | Tentukan scope perubahan, format hanya audit-owned lines/files, jangan reformat dirty worktree massal | **OPEN, non-runtime** |

## Temuan dan bukti per agen

### Agen 1 — Architecture dan Graph

- Memetakan panel, Report/PDF/storage, API auth, scheduler, dashboard, dan command.
- Menemukan cleanup harian berisiko, inactive panel access, serta RolePolicy mismatch.
- Membuktikan Graphify stale/noisy dan memverifikasi source terkini serta 33 route saat snapshot awal.
- PHP lint awal lulus untuk 95 file app/routes/config.

### Agen 2 — Functional Flow dan Bug Hunting

- Menemukan test isolation yang masih dapat memakai cached MySQL config, Team filter key bug, nullable/date/schema mismatch, PDF field omissions, absolute hosting path handling, dan Carbon parsing risk.
- Tidak dapat melakukan protected browser CRUD karena tidak ada kredensial/staging user.
- Fokus ReportResource sebelumnya sudah mengintegrasikan signature pad manual, RichEditor tanpa attachment, form sections, dark/light rendering, dan imported-data fallback.

### Agen 3 — Database dan Data Integrity

- Menginspeksi MySQL dalam read-only session/transaction.
- Mengukur 2.475 report, 1.045 signature, max signature 56.106 byte, 1.430 signature null.
- Menemukan 3 report orphan user, 25 follower pivot orphan, 41 indicator pivot orphan, duplicate pivot, duplicate slug, missing FK/index, dan migration ledger/schema drift.
- Data date/rich-text/percentage yang diperiksa umumnya bersih: tidak ditemukan end-before-start, total nonnumeric, percentage di luar pilihan, rich HTML malformed, atau signature filled yang bukan PNG.

### Agen 4 — Security

- Membuktikan Report dan Order object authorization IDOR, stored SVG, anonymous self-onboarding, storage symlink bypass, signature forgery model, token lifecycle, password spraying, PDF DoS, dan temporary upload limit.
- Re-audit mengonfirmasi IDOR dan permanent SVG path telah ditutup. Self-registration dan config storage link kemudian juga ditutup dengan focused regression test.
- Tidak menemukan SQL injection berbasis input, effective path traversal, unsafe `$request->all()`, tracked private key/service account, atau unsanitized rich text pada view/PDF.

### Agen 5 — UI/UX dan Browser

- Guest login diuji pada viewport 320, 768, 1024, dan 1440 px: tidak ada horizontal overflow, clipped elements, atau console warning/error; native empty validation berfungsi.
- Menemukan keyboard-inaccessible signature, reset-password tab-order, dark rich-link contrast 3,43:1, branding “Laravel”, rank badge contrast, dan potential stroke loss during resize.
- Protected dashboard/list/create/edit/view/PDF dan device touch/stylus tidak dapat diverifikasi tanpa kredensial.

### Agen 6 — Performance dan Reliability

- Menemukan empat query per Order row, reminder/FCM N+1, synchronous external I/O, scheduler overlap, ignored FCM failures, nonsargable date queries, Livewire polling 10 detik, preloaded select growth, unbounded Excel/PDF, dan telemetry gaps.
- Query per-row dan reminder eager-loading telah diperbaiki; queue/idempotency/timeout/caching/index work tetap open.

### Agen 7 — Testing dan Code Review

- Memvalidasi test isolation, policy fixes, cleanup, upload hardening, N+1, lint, dan dirty-worktree constraints.
- Menemukan direct `storage:link` bypass dan guardrail manual cleanup; link config kemudian dihapus.
- Final independent snapshot sebelum dua patch terakhir: 61/232. Final root suite setelah patch terakhir: **62/233**.
- PHP lint lulus; targeted audit diff check lulus. Pint tetap gagal pada 12 production files yang sebagian besar sudah memiliki style debt/large user edits.
- Targeted security re-audit sesudah `config:clear` membuktikan runtime `filesystems.links=[]` dan tidak ada `public/storage`.

## Bug yang diperbaiki

1. PHPUnit fail-closed ke SQLite `:memory:` dan menolak cached MySQL config.
2. Cleanup scheduler menjadi dry-run, hanya managed directories, dan menjaga Documentation/avatar/Order letter/Executor proof.
3. Inactive/null-status user ditolak panel.
4. RolePolicy memakai permission catalog aktual dan deny unsupported operations.
5. User destructive lifecycle fail-closed sampai preservation plan disetujui.
6. Report mutation authorization object-scoped; bulk destructive fail-closed.
7. Order view/mutation object-scoped; executor hanya view parent order.
8. Exact MIME uploads, SVG rejection, extension–MIME response validation, `nosniff`, forced attachment untuk office docs.
9. Direct public storage symlink config dinonaktifkan; semua file harus melalui controller.
10. Public self-registration dinonaktifkan.
11. Team filter memakai ID sebagai option key.
12. Empat Order queries per row diganti eager loading dan `withCount`.
13. Reminder commands eager-load executor user.
14. Dark link/rank contrast dan panel branding diperbaiki.
15. Public-storage URL test lama yang mengharapkan double slash diperbaiki.

## Test dan validasi

- Final full suite: **PASS — 62 test, 233 assertion**.
- Database test: SQLite `:memory:`; bootstrap fail-closed membuktikan bukan MySQL.
- PHP lint: **PASS** pada 159 file pada evaluator; root final lint **PASS** pada 122 file app/routes/config/tests.
- `git diff --check` pada audit-targeted paths: **PASS**.
- Global `git diff --check`: gagal hanya pada trailing whitespace dua generated Filament JS yang sudah berada dalam dirty worktree.
- Targeted Pint: **FAIL** pada 12 production files; dicatat sebagai P2/style debt, tidak di-autoformat karena risiko menimpa perubahan pengguna.
- Composer validation: PASS.
- Composer audit: 0 advisory/abandoned pada root run.
- npm audit: 0 vulnerability pada root run.
- Vite 8.2.1 production build: PASS ke temporary isolated output; JS 107,39 KB (gzip 36,08 KB).
- Route list Laravel berhasil dengan SQLite override; public routes yang disengaja dibedakan dari panel-auth routes.
- Browser guest runtime: PASS di 320/768/1024/1440 tanpa overflow/console error.

## File perubahan audit utama

- `phpunit.xml`, `tests/bootstrap.php`, `tests/Feature/TestingDatabaseIsolationTest.php`
- `app/Console/Commands/DeleteUnusedFiles.php`, `tests/Feature/DeleteUnusedFilesCommandTest.php`
- `app/Models/User.php`, `app/Policies/UserPolicy.php`, `app/Policies/RolePolicy.php`
- `app/Policies/ReportPolicy.php`, `tests/Feature/ReportAuthorizationPolicyTest.php`
- `app/Policies/OrderPolicy.php`, `tests/Feature/OrderAuthorizationPolicyTest.php`
- `app/Http/Controllers/PublicStorageController.php`, `config/filesystems.php`, `tests/Feature/PublicStorageRouteTest.php`
- `app/Filament/Resources/ReportResource.php`, `app/Filament/Resources/OrderResource.php`
- `app/Filament/Resources/OrderResource/RelationManagers/ExecutorRelationManager.php`
- `app/Filament/Pages/Auth/EditProfile.php`, `tests/Feature/UploadSecurityConfigurationTest.php`
- `app/Models/Order.php`, `app/Console/Commands/SendEmailReminder.php`, `app/Console/Commands/SendOrderNotifications.php`, `tests/Feature/OrderQueryEfficiencyTest.php`
- `app/Providers/Filament/AdminPanelProvider.php`, `tests/Feature/FilamentPanelSmokeTest.php`
- `resources/views/infolists/components/how.blade.php`, `resources/views/filament/styles/signature-theme.blade.php`, `tests/Feature/UiAccessibilityRegressionTest.php`
- `.env.example`

Daftar ini hanya perubahan yang terkait audit. Worktree memuat banyak perubahan pengguna lain yang tidak diatribusikan ke audit dan tidak boleh di-reset.

## Known limitations

- Tidak ada protected browser CRUD dengan user test; tidak ada kredensial yang ditebak/dibuat pada MySQL.
- Signature pointer belum diuji dengan hardware touch/stylus dan zoom 80/100/125/150 pada authenticated form.
- PDF belum di-load-test atau diverifikasi visual dengan full production data/storage.
- Storage hosting aktual belum tersedia pada workspace, sehingga missing-file result adalah deployment inventory gap, bukan bukti file production benar-benar hilang.
- Graphify stale terhadap worktree dan tidak boleh diperlakukan sebagai source of truth.
- Tidak ada staging clone untuk rehearsal schema reconciliation.
- Tidak ada performance load test, p95, peak memory, queue worker, multi-node scheduler, atau external FCM/mail end-to-end.
- Source-string tests menjaga configuration contracts tetapi tidak menggantikan runtime Filament/Livewire/browser security tests.

## Kesimpulan

Code-level P0 yang aman telah dimitigasi dan regression suite hijau, tetapi **schema/data drift, signature authenticity, dan storage deployment inventory tetap blocker**. Status keseluruhan adalah **NOT READY** sampai seluruh gate di `PRODUCTION_READINESS.md` dipenuhi dan diuji pada staging clone.
