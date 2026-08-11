# Implementation Plan: Unit Kerja dan Keterlibatan Laporan

## Overview

Implementasi mengikuti spesifikasi `docs/specs/UNIT_KERJA_KETERLIBATAN.md`. Pekerjaan dibagi menjadi irisan vertikal kecil: fondasi data, master admin, integrasi Report, keluaran, lalu dokumentasi dan verifikasi. Semua perubahan skema bersifat aditif. Semua test database memakai SQLite in-memory.

## Architecture Decisions

- `work_units` menjadi master kantor terpisah dari `teams`.
- `report_work_unit` menyimpan relasi many-to-many dengan unique composite untuk mencegah duplikasi.
- `involvements` menjadi master fleksibel; `is_lprl_organizer` mengendalikan perilaku Penyelenggara tanpa pencocokan nama.
- `reports.involvement_id` nullable menjaga kompatibilitas 2.438+ laporan historis.
- Validasi wajib diterapkan pada form create/edit, bukan constraint `NOT NULL`, karena data lama boleh kosong.
- Master terpakai dilindungi dari penghapusan; status nonaktif menjadi mekanisme penghentian pemakaian.
- Resource baru mengikuti 12 permission standar Filament Shield per resource.
- Opsi status dan kategori dipusatkan pada model untuk mencegah perbedaan antara form, filter, dan tampilan.

## Dependency Graph

```text
Migration aditif
    ├── Model dan relasi
    │   ├── Resource admin Unit Kerja
    │   ├── Resource admin Keterlibatan
    │   └── Integrasi ReportResource
    │       ├── Detail dan filter Report
    │       ├── Excel
    │       └── PDF
    └── Seeder Keterlibatan awal

Resource admin
    └── Policy dan katalog permission Shield

Semua irisan
    └── Dokumentasi dan full verification
```

## Task List

### Phase 1: Data Foundation

- [ ] Task 1: Tambahkan regression test skema lalu migration aditif.
  - Acceptance: tabel `work_units`, `involvements`, `report_work_unit`, dan nullable `reports.involvement_id` tersedia; pivot unik; foreign key delete rules sesuai spesifikasi.
  - Verify: `php artisan test tests/Feature/ReportOrganizationDimensionsTest.php --filter=schema`.
  - Files: satu migration baru, satu test feature.

- [ ] Task 2: Tambahkan model, relasi, konstanta opsi, dan seeder awal.
  - Acceptance: Eloquent Report–Unit Kerja dan Report–Keterlibatan bekerja; seeder idempotent menyediakan Penyelenggara dan Peserta.
  - Verify: `php artisan test tests/Feature/ReportOrganizationDimensionsTest.php --filter='relationship|seed'`.
  - Files: `WorkUnit.php`, `Involvement.php`, `Report.php`, `InvolvementSeeder.php`, test feature.

### Checkpoint: Data Foundation

- [ ] Focused schema/model tests lulus.
- [ ] Migration rollback tervalidasi pada SQLite in-memory.
- [ ] Tidak ada data Report lama yang diisi atau ditebak.

### Phase 2: Admin Master Data

- [ ] Task 3: Bangun resource Unit Kerja.
  - Acceptance: admin dapat list/create/edit Unit Kerja; tiga kategori tetap; status aktif/nonaktif; pencarian dan filter tersedia; record terpakai tidak dapat dihapus.
  - Verify: focused resource contract test dan panel smoke test.
  - Files: `WorkUnitResource.php`, tiga page resource, test feature.

- [ ] Task 4: Bangun resource Keterlibatan.
  - Acceptance: admin dapat list/create/edit Keterlibatan; toggle LPRL organizer tersedia; status dan pencarian/filter tersedia; record terpakai tidak dapat dihapus.
  - Verify: focused resource contract test dan panel smoke test.
  - Files: `InvolvementResource.php`, tiga page resource, test feature.

- [ ] Task 5: Tambahkan authorization Shield.
  - Acceptance: dua policy baru memakai permission standar; katalog Shield bertambah tepat 24 permission tanpa mengubah format permission lama.
  - Verify: `php artisan test tests/Feature/ShieldPermissionContractTest.php`.
  - Files: dua policy, Shield contract test.

### Checkpoint: Admin Master Data

- [ ] Resource terdeteksi panel dan dilindungi policy.
- [ ] Test resource dan permission lulus.
- [ ] Master aktif/nonaktif bekerja tanpa hard-coded name behavior.

### Phase 3: Report Workflow

- [ ] Task 6: Integrasikan Unit Kerja dan Keterlibatan pada ReportResource.
  - Acceptance: create/edit wajib satu Keterlibatan dan minimal satu Unit Kerja; pilihan hanya master aktif; perubahan Keterlibatan mengisi atau membersihkan Penyelenggara sesuai flag; detail dan filter menampilkan dimensi baru.
  - Verify: focused form behavior dan resource contract tests.
  - Files: `ReportResource.php`, `ReportResourceFormTest.php`, organization-dimensions test.

- [ ] Task 7: Jaga kompatibilitas laporan historis.
  - Acceptance: laporan tanpa Keterlibatan/Unit Kerja tetap dapat dilihat; edit meminta pengisian; nilai master nonaktif yang sudah terhubung tetap tampil.
  - Verify: focused historical compatibility tests.
  - Files: `ReportResource.php`, organization-dimensions test.

### Checkpoint: Report Workflow

- [ ] Focused Report tests lulus.
- [ ] Tim Kerja dan state path `teams` tetap berfungsi.
- [ ] Transisi Penyelenggara internal/eksternal teruji dua arah.

### Phase 4: Output and Documentation

- [ ] Task 8: Tambahkan dimensi baru ke Excel.
  - Acceptance: export eager-load relasi baru dan memuat kolom Unit Kerja serta Keterlibatan tanpa query per baris.
  - Verify: focused export mapping/query test.
  - Files: `ReportsExport.php`, export test.

- [ ] Task 9: Tambahkan dimensi baru ke PDF.
  - Acceptance: PDF menampilkan Unit Kerja, Keterlibatan, dan Penyelenggara; laporan historis null dirender aman.
  - Verify: focused PDF render/layout test.
  - Files: `PdfController.php`, `pdf.blade.php`, PDF test.

- [ ] Task 10: Perbarui dokumentasi sistem.
  - Acceptance: PRD, ERD, dan data/storage docs mencerminkan tabel, relasi, validasi, dan kompatibilitas historis baru.
  - Verify: review diff dokumentasi terhadap migration/model final.
  - Files: tiga dokumen `docs/`.

### Checkpoint: Complete

- [ ] `php artisan test` lulus pada SQLite in-memory.
- [ ] `vendor/bin/pint --dirty` lulus.
- [ ] `composer dump-autoload --strict-psr` lulus.
- [ ] `npm run build` lulus.
- [ ] `git diff --check` bersih.
- [ ] Review correctness, security, maintainability, performance, compatibility, dan accessibility selesai.
- [ ] Tidak ada migration atau seed dijalankan terhadap database aplikasi.

## Risks and Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Test mengenai database aplikasi | Critical | PHPUnit memaksa SQLite `:memory:` dan bootstrap menolak koneksi lain. |
| Field disabled tidak tersimpan oleh Filament | High | Gunakan state yang tetap didehidrasi atau read-only; buktikan dengan component/form test. |
| Master nonaktif hilang dari edit laporan lama | Medium | Query opsi mempertahankan nilai yang sudah dipilih sambil mencegah pilihan baru. |
| Delete master menghilangkan histori | High | FK restrict dan action delete dinonaktifkan saat relation count lebih dari nol. |
| Resource baru mengubah katalog permission | Medium | Update kontrak dari 71 menjadi 95 dan verifikasi tepat 12 permission per resource. |
| Relasi baru menambah N+1 pada export/PDF | Medium | Eager-load `workUnits` dan `involvement`; tambah regression test query/mapping. |
| PDF layout melebar karena daftar Unit Kerja | Low | Tampilkan sebagai teks gabungan dengan wrapping yang sudah ada. |

## Open Questions

Tidak ada. Spesifikasi dan asumsi teknis telah disetujui pengguna.
