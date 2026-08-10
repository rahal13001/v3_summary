# Summary Production Readiness

Status per 10 Agustus 2026: **NOT READY**

## Release blockers

- [ ] Rekonsiliasi migration dengan schema MySQL aktual pada staging clone; jangan jalankan migration repository sekarang.
- [ ] Tetapkan tipe final `reports.kode` berdasarkan signature aktual hingga 56.106 byte dan buat migration setelah backup/approval.
- [ ] Selesaikan data orphan/duplicate serta foreign-key/unique/index plan dengan repair report yang dapat direview.
- [ ] Definisikan signature resmi: signer identity, siapa boleh sign, invalidasi saat content berubah, timestamp, content hash/version, dan audit trail.
- [ ] Buat manifest/checksum storage hosting dan pastikan seluruh dokumentasi aktif tersedia pada release target.
- [ ] Lakukan authenticated browser regression pada staging untuk report CRUD, signature, rich text table, dark/light, documentation, dashboard, Excel, dan PDF.

Tidak ada deployment production sampai seluruh item blocker mendapat bukti dan approval.

## Kondisi code yang sudah dipenuhi

- [x] PHPUnit dipaksa SQLite `:memory:` dan fail-closed sebelum app bootstrap.
- [x] Full suite final lulus 62 test / 233 assertion.
- [x] PHP lint lulus.
- [x] Report dan Order object authorization memiliki regression test.
- [x] User deletion/restore fail-closed sambil menunggu data lifecycle plan.
- [x] Cleanup scheduler default dry-run dan mempertahankan seluruh referensi file yang dikenal.
- [x] SVG/wildcard upload ditolak; response memvalidasi extension–MIME pair.
- [x] Direct `public/storage` symlink tidak lagi didefinisikan.
- [x] Stale compiled config dibersihkan; runtime `config:show filesystems` menampilkan `links=[]` dan `public/storage` tidak ada.
- [x] Self-registration publik dinonaktifkan.
- [x] Inactive panel user dan Role permission mismatch diperbaiki.
- [x] Order per-row N+1 dihapus dan dibuktikan dengan query-log test.
- [x] Composer/npm advisory audit root tidak menemukan advisory.
- [x] Production asset build lulus.

## Database gate

1. Ambil backup database yang dapat direstore dan rekam checksum/timestamp.
2. Clone backup ke staging terisolasi.
3. Export schema actual (`SHOW CREATE TABLE`, indexes, constraints, migration ledger) tanpa menulis production.
4. Buat baseline/diff yang mencakup `reports.kode`, columns legacy, JSON/longtext, nullable/default, soft deletes, slug/pivot uniqueness, foreign keys, dan indexes.
5. Buat data-repair report untuk 3 orphan report users, 25 orphan follower pivots, 41 orphan indicator pivots, duplicate indicator pivot, dan duplicate slugs.
6. Jalankan repair/migration hanya pada clone; verifikasi row counts, signature lengths, hashes, route binding, report totals, dan rollback.
7. Review plan bersama pemilik data. Perubahan schema/data production memerlukan approval eksplisit terpisah.

### Perintah yang dilarang terhadap database utama

- `php artisan migrate:fresh`
- `php artisan migrate:refresh`
- `php artisan db:wipe`
- `TRUNCATE`, reset, destructive seed, atau ad-hoc repair
- `php artisan migrate` sampai reconciliation plan disetujui dan rehearsal berhasil

## Storage gate

- [ ] Pastikan `public/storage` **tidak ada sebagai symlink/directory yang dilayani webserver**. Verifikasi target secara read-only sebelum menghapus symlink legacy; jangan pernah menghapus real directory dengan asumsi bahwa itu link.
- [ ] Jangan jalankan `php artisan storage:link` untuk arsitektur ini.
- [ ] Semua public file harus melalui `/public-storage/{path}` atau legacy route Laravel `/storage/{path}`.
- [ ] Uji pasca-deploy: valid PNG/JPEG/WebP/PDF bekerja; `.svg`, HTML/XML/JS, dan SVG bytes berkedok `.png` menghasilkan 404.
- [ ] Setelah `config:cache`, jalankan `php artisan config:show filesystems` dan pastikan effective `links` tetap `[]`.
- [ ] Pastikan response valid memiliki `X-Content-Type-Options: nosniff`; office docs memakai attachment.
- [ ] Inventory extension/MIME file legacy. File di luar allowlist harus direview/migrasikan, bukan dibuka kembali secara luas.
- [ ] Buat checksum manifest untuk Documentation 1/2/3, ST, lainnya, avatar, Order letter, dan Executor proof.
- [ ] Jangan gunakan `summary:delete-unused-files --delete` di production. Scheduler dry-run boleh; manual delete menunggu manifest, minimum age, quarantine, lock, confirmation, backup, dan maintenance window.

## Environment/config checklist

- [ ] `APP_NAME=Summary`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` adalah canonical HTTPS URL tanpa slash ganda
- [ ] `APP_KEY` benar dan tidak berubah dari environment sumber
- [ ] HTTPS enforced; trusted proxy/forwarded headers benar
- [ ] Session cookie secure, HTTP-only, same-site sesuai SSO/API requirement
- [ ] Database host/name/user memiliki least privilege; kredensial tidak berada di repository
- [ ] `FILESYSTEM_DISK`, public disk root/URL, permissions, owner, disk capacity, dan backup terverifikasi
- [ ] Mail transport/from/timeout benar; test delivery ke mailbox non-production
- [ ] Firebase credential/project/timeout benar; jangan log token/service-account
- [ ] Cache/session/queue stores tersedia. Saat ini email/FCM sinkron; jangan mengklaim worker queue menangani keduanya.
- [ ] Scheduler hanya berjalan pada satu logical leader atau memakai lock/idempotency sebelum scale multi-node
- [ ] `LOG_CHANNEL` menggunakan daily/central sink; retention, PII redaction, alert, dan disk rotation diset
- [ ] Sanctum token expiry/active-user middleware diputuskan sebelum memperluas API
- [ ] PHP-FPM/web memory, request timeout, upload/post limits, OPcache, dan max workers disesuaikan untuk PDF/Excel
- [ ] Webserver tidak melayani `storage/app/public` secara direct dan memblok dotfiles/active content

## Safe build dan deployment commands

Jalankan hanya setelah blockers selesai, pada staging/release directory baru, dan dengan backup tervalidasi. Sesuaikan path PHP dengan host production.

```powershell
composer validate --no-check-publish
composer install --no-dev --prefer-dist --no-interaction --classmap-authoritative
npm ci
npm run build
php artisan about --only=environment
php artisan route:list --except-vendor
php artisan schedule:list
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
```

Catatan:

- Jangan jalankan `storage:link`.
- Pada production dengan database-backed cache, evaluasi `optimize:clear` karena ia juga dapat mengosongkan application cache. Untuk perbaikan config saja gunakan `php artisan config:clear`, lalu rebuild `config:cache`.
- Jangan jalankan migration production sebagai bagian deploy ini sampai database gate selesai.
- Jangan menjalankan test suite di production. CI/staging test sudah dipaksa SQLite in-memory.
- `optimize:clear` dan cache commands mengubah cache aplikasi, bukan schema/data; jalankan pada maintenance/release window sesuai strategi deploy.

## Staging smoke test wajib

### Guest/public

- [ ] `/login` render, branding Summary, tidak ada console error.
- [ ] `/register` menghasilkan 404.
- [ ] Password reset request bekerja sesuai mail environment.
- [ ] Public PDF dan signature verification hanya menampilkan report yang memang diperlakukan sebagai public bearer link.
- [ ] Valid public file bekerja; active/disguised content ditolak; tidak ada direct symlink bypass.
- [ ] Burst PDF diberi batas/caching sebelum production.

### Auth/authorization

- [ ] User inactive/null tidak dapat login panel atau memakai existing API token.
- [ ] Unrelated user tidak dapat edit/delete Report atau view/edit Order melalui URL langsung.
- [ ] Owner/follower/admin Report behavior sesuai product rule; follower tidak boleh memalsukan signature.
- [ ] Assigned executor dapat melihat Order dan mengedit hanya Executor record miliknya, bukan parent Order.
- [ ] Role management memakai permission Shield aktual.
- [ ] User delete/restore/force-delete tidak tersedia.

### Report flow

- [ ] Create/edit/view dengan owner, followers, indicator, teams, participant fields.
- [ ] Rich text heading/list/link/table/nested content tersanitasi dan tampil light/dark/PDF.
- [ ] Signature pointer tepat pada zoom 80/100/125/150, sidebar toggle, resize, mouse/touch/stylus.
- [ ] Undo/clear/save/reload signature; legacy PNG tetap tampil.
- [ ] Missing/corrupt/hosting paths tidak memicu fatal error.
- [ ] PDF memuat template resmi lengkap, gambar, signature, QR, table border, dan long content.
- [ ] Excel export diberi batas record dan diuji untuk memory/time.

### Responsive/accessibility

- [ ] 320, 768, 1024, 1440 px pada seluruh protected pages.
- [ ] Keyboard navigation mencakup password reset dan alternative signing workflow.
- [ ] Visible focus, labels, validation/errors, contrast WCAG AA, loading/empty state.
- [ ] Tidak ada horizontal overflow atau clipped action/modal/table.

## Observability gate

- [ ] Structured log untuk PDF, Excel, storage missing/error, cleanup dry-run, email, dan FCM.
- [ ] Request/command correlation ID.
- [ ] Scheduler heartbeat dan alert bila reminder/FCM/cleanup tidak berjalan.
- [ ] FCM matched/sent/failed count; caller mengembalikan failure saat sistemik.
- [ ] Queue age/failure metrics jika pengiriman/export dipindahkan ke queue.
- [ ] Slow query logging/metrics untuk dashboard, Order list, PDF, dan export.
- [ ] Alert pada 5xx, authentication anomalies, storage capacity, log disk, worker saturation, dan PDF latency/memory.

## Rollout yang disarankan

1. Selesaikan schema/signature/storage blockers pada staging clone.
2. Deploy ke staging baru, bukan menimpa release aktif.
3. Jalankan smoke test dan data reconciliation checks.
4. Canary internal dengan user admin, writer, follower, dan executor representatif.
5. Bekukan data write singkat hanya bila cutover storage/schema memang memerlukannya.
6. Ambil backup final, deploy release immutable, warm caches, lalu traffic switch.
7. Pantau error rate, auth failures, DB latency, PDF memory/time, storage 404, email/FCM, dan scheduler selama minimal satu siklus bisnis.

## Rollback plan

- Simpan release directory sebelumnya dan asset manifest-nya.
- Ambil backup database dan storage tepat sebelum cutover; uji restore pada clone terlebih dahulu.
- Untuk code-only rollback: arahkan release symlink/virtual-host kembali ke release sebelumnya, restart/reload PHP worker, lalu clear/warm cache sesuai versi tersebut.
- Jangan mencoba rollback schema dengan ad-hoc reverse SQL. Setiap migration reconciliation wajib memiliki forward/rollback data plan yang telah direhearsal.
- Jika storage sync bermasalah, hentikan write, bandingkan checksum manifest, lalu pulihkan snapshot storage; jangan menjalankan cleanup `--delete`.
- Jika authorization regression terjadi, nonaktifkan akses eksternal/panel yang terdampak terlebih dahulu, rollback code, revoke suspicious sessions/tokens, dan audit logs.
- Rekam waktu, release hash, operator, backup identifiers, validation results, dan alasan rollback.

## Residual risks setelah code fixes

- Signature belum merupakan verifikasi kriptografis/immutable.
- Schema actual tidak dapat direkonstruksi secara aman hanya dari migrations repository.
- Orphan/duplicate data dan missing indexes belum diperbaiki.
- API token user nonaktif dan expiry belum di-hardening.
- PDF/Excel/FCM/email masih memiliki risiko synchronous timeout/DoS.
- Scheduler belum memiliki overlap/idempotency strategy lengkap.
- Manual cleanup delete mode belum production-safe.
- Protected browser, real storage, touch/stylus, load, and multi-node behavior belum diuji.
- Pint/style gate masih merah pada scope legacy/dirty worktree.

## Keputusan release

**NO-GO / NOT READY.** Jangan deploy ke production sampai database, signature, dan storage gates selesai, lalu ulangi full regression, authenticated browser audit, security re-audit, dan release rehearsal pada staging clone.
