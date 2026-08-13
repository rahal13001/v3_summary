# Implementation Plan: Adopsi Summary Kupang dan Evaluasi Monev

Status spesifikasi: disetujui pengguna pada 11 Agustus 2026.

## Outcome

Summary dijalankan sebagai dua deployment dan dua database terpisah untuk Sorong dan Kupang, tetapi tetap memakai satu source code. Instalasi Kupang mengaktifkan Evaluasi Monev dan export matriks resmi; instalasi Sorong mempertahankan perilaku lama sampai fiturnya diaktifkan melalui Pengaturan Organisasi.

## Ruang Lingkup Terkunci

- Identitas organisasi dikelola melalui menu Pengaturan Organisasi; konfigurasi teknis tetap berada di `.env` masing-masing deployment.
- Evaluasi disimpan per kombinasi Report, Unit Kerja, dan periode bulan-tahun.
- Satu Report dapat mempunyai evaluasi berbeda untuk beberapa Unit Kerja dan beberapa periode.
- Field evaluasi: rencana pelaksanaan, kendala/permasalahan, saran/rekomendasi, tindak lanjut, banyak URL bukti tindak lanjut, dan keterangan. Semua nullable.
- Report yang rentang kegiatannya bersinggungan dengan periode export tetap masuk walau belum memiliki evaluasi.
- Report lama masuk kembali bila memiliki evaluasi pada periode terpilih.
- Riwayat perubahan bersifat append-only dan menampilkan editor, waktu, serta nilai yang berubah.
- Tidak ada workflow draft-review-approve.
- Penulis dan pengikut dapat mengelola evaluasi Report terkait. Koordinator hanya dapat mengelola evaluasi untuk Unit Kerja yang dikoordinasikannya. Pimpinan, admin, dan super-admin dapat mengelola seluruh evaluasi.
- Akses pimpinan diwujudkan sebagai permission/ability khusus, bukan hardcode nama role baru; role aktual dapat diberi permission tersebut melalui Filament Shield.
- Evaluasi tampil sebagai bagian tersendiri pada detail Report.
- Export Excel lama dipertahankan. Export Monev menjadi format kedua dan mengikuti workbook referensi secara lengkap.
- INTERNAL berarti organisasi sebagai penyelenggara; EKSTERNAL berarti organisasi sebagai peserta. Sumber klasifikasi adalah Keterlibatan, bukan field kategori baru.
- Export Monev meminta Unit Kerja, bulan, tahun, lokasi penandatanganan, dan tanggal penandatanganan.
- Identitas serta gambar tanda tangan berasal dari koordinator aktif Unit Kerja.

## Keputusan Arsitektur

### Isolasi organisasi

- Satu repository dan jalur rilis; dua deployment dengan `.env`, database, domain, storage, cache, queue, session, dan `APP_KEY` terpisah.
- Gunakan satu sumber identitas organisasi bertipe jelas. Jangan menambah kondisi berdasarkan nama organisasi di banyak file.
- `monev_enabled` menjadi feature flag per deployment.
- Pertahankan kolom lama `involvements.is_lprl_organizer` pada tahap ini untuk kompatibilitas database Sorong, tetapi bungkus semantiknya sebagai “organisasi penyelenggara” dan ambil nama organisasi dari Pengaturan Organisasi. Penggantian nama kolom dapat dilakukan lewat migrasi deprecasi terpisah setelah kedua deployment stabil.

### Model data

```text
OrganizationSetting (singleton per database)
  - app_name, name, short_name, logo_path, favicon_path, address
  - organizer_name, organizer_input_mode, monev_enabled

User
  - nip, jabatan, coordinator_signature_path
  - M:N WorkUnit melalui work_unit_coordinators

WorkUnitCoordinator
  - work_unit_id, user_id, starts_at, ends_at
  - hanya satu penugasan aktif per Unit Kerja

Report
  - M:N WorkUnit (tetap)
  - 1:N ReportEvaluation

ReportEvaluation
  - report_id, work_unit_id, period (tanggal hari pertama bulan)
  - rencana_pelaksanaan, kendala, saran_rekomendasi
  - tindak_lanjut, evidence_links JSON, keterangan
  - created_by, updated_by, timestamps
  - unique(report_id, work_unit_id, period)

ReportEvaluationRevision
  - report_evaluation_id, changed_by, changed_at
  - changes JSON berisi old/new per field
```

- `period` memakai tipe `date` dengan nilai hari pertama bulan agar query, unique constraint, dan validasi lebih sederhana daripada dua kolom integer.
- `evidence_links` berupa array URL tervalidasi. Export merender satu hyperlink per baris dalam satu sel.
- Riwayat perubahan dibuat melalui service/observer dalam transaksi yang sama dengan penyimpanan evaluasi. Log tidak mempunyai aksi edit atau delete di UI.
- Penugasan koordinator memakai rentang waktu agar histori pergantian koordinator tidak hilang; koordinator aktif dipakai saat export.

### Aturan query Export Monev

Untuk Unit Kerja dan periode terpilih, satu Report masuk bila terkait ke Unit Kerja tersebut dan memenuhi salah satu kondisi:

1. rentang `when`–`tanggal_selesai` bersinggungan dengan awal–akhir periode; atau
2. memiliki `report_evaluation` untuk Unit Kerja dan periode tersebut.

Evaluasi di-left join berdasarkan Report, Unit Kerja, dan periode. Tanpa record evaluasi, enam kolom evaluasi tampil kosong. Data dikelompokkan berdasarkan Keterlibatan: organisasi penyelenggara menjadi INTERNAL; selain itu menjadi EKSTERNAL. Dalam tiap kelompok, urutan adalah `when` lalu `what`, dan nomor dimulai kembali dari 1.

Pemetaan kolom:

| Kolom matriks | Sumber |
|---|---|
| No | nomor urut per kelompok |
| Nama Kegiatan | `reports.what` |
| Rencana Pelaksanaan | `report_evaluations.rencana_pelaksanaan` |
| Realisasi Pelaksanaan | rentang `reports.when`–`tanggal_selesai` |
| Hasil Kegiatan | plain text dari `reports.how` |
| Kendala/Permasalahan | `report_evaluations.kendala` |
| Saran/Rekomendasi | `report_evaluations.saran_rekomendasi` |
| Tindak Lanjut | `report_evaluations.tindak_lanjut` |
| Link Bukti Tindaklanjut | hyperlink dari `evidence_links` |
| Ket. | `report_evaluations.keterangan` |

## Dependency Graph

```text
Pengaturan Organisasi + feature flag
  |-- generalisasi identitas/penyelenggara
  `-- visibilitas fitur Monev

Profil tanda tangan + penugasan koordinator
  `-- authorization evaluasi
      `-- UI Evaluasi pada detail Report

Skema dan audit Evaluasi
  |-- UI Evaluasi
  `-- query dataset Monev
      `-- renderer Excel Monev
          `-- action Export Monev
```

## Task Implementasi

### Phase 1 — Fondasi multi-deployment

#### Task 1: Pengaturan Organisasi bertipe jelas

**Description:** Tambahkan penyimpanan singleton, service pembaca setting, halaman Filament, dan feature flag Monev. Ganti hardcode organisasi pada alur baru melalui service tersebut tanpa mengubah kontrak API/SSO.

**Acceptance criteria:**
- Admin dapat menyimpan nama aplikasi, nama/singkatan organisasi, logo, favicon, kebijakan Penyelenggara, alamat, dan status Monev.
- Instalasi Sorong default Monev nonaktif; Kupang dapat mengaktifkannya lewat setting.
- Setting kosong mempunyai fallback aman dan tidak memutus panel.

**Verification:** focused schema/service/page tests; panel smoke test; cache setting dapat di-invalidasi.

**Dependencies:** none. **Scope:** M.

**Files likely touched:** migration baru, `app/Models/OrganizationSetting.php`, service organisasi, page Filament, panel provider, tests.

#### Task 2: Generalisasi identitas penyelenggara

**Description:** Hilangkan hardcode nama LPRL dari perilaku Report dan label UI yang harus berbeda per deployment, sambil mempertahankan data Sorong.

**Acceptance criteria:**
- Sorong tetap menghasilkan `LPRL Sorong` dari setting Sorong.
- Kupang menghasilkan nama organisasi Kupang, dapat memakai master Internal/Eksternal, dan dapat memilih mode Penyelenggara editable/manual.
- Tidak ada perbandingan nama keterlibatan untuk menentukan kategori.

**Verification:** regression tests organisasi Sorong/Kupang dan existing Report organization tests.

**Dependencies:** Task 1. **Scope:** S–M.

### Checkpoint A

- Focused tests lulus untuk dua profil organisasi.
- Panel Sorong tidak menampilkan fitur Monev saat flag nonaktif.
- Tidak ada perubahan pada kontrak login/API.

### Phase 2 — Koordinator dan otorisasi

#### Task 3: Profil tanda tangan koordinator

**Description:** Tambahkan tanda tangan pada profil User dengan validasi file/gambar serta preview yang aman.

**Acceptance criteria:**
- User berwenang dapat menyimpan dan mengganti tanda tangan koordinator.
- Nama, NIP, jabatan, dan path tanda tangan tersedia untuk export.
- File lama dibersihkan secara aman hanya setelah penggantian berhasil.

**Verification:** upload/storage tests, authorization test, manual dark/light preview.

**Dependencies:** none. **Scope:** M.

#### Task 4: Penugasan koordinator Unit Kerja

**Description:** Tambahkan relasi berperiode antara User dan Unit Kerja serta UI pengelolaannya.

**Acceptance criteria:**
- Setiap Unit Kerja maksimal mempunyai satu koordinator aktif.
- Seorang User boleh mengoordinasikan lebih dari satu Unit Kerja.
- Pergantian koordinator mempertahankan histori penugasan.

**Verification:** constraint/domain tests, overlap-date tests, resource test.

**Dependencies:** Task 3. **Scope:** M.

### Checkpoint B

- Koordinator aktif dapat ditentukan konsisten dari Unit Kerja.
- Konflik dua koordinator aktif ditolak.
- Data profil tanda tangan dapat dibaca tanpa N+1 query.

### Phase 3 — Evaluasi dan audit

#### Task 5: Skema serta model Evaluasi Monev

**Description:** Buat migration, model, relasi, cast URL JSON, unique period constraint, factory, dan validasi domain.

**Acceptance criteria:**
- Satu evaluasi unik per Report–Unit Kerja–periode.
- Unit evaluasi wajib merupakan Unit Kerja yang terkait ke Report.
- Semua field isi nullable dan URL divalidasi sebagai HTTP/HTTPS.

**Verification:** migration rollback, model relationship, uniqueness, invalid-unit, dan URL tests.

**Dependencies:** none. **Scope:** M.

#### Task 6: Riwayat perubahan append-only

**Description:** Catat create/update evaluasi sebagai diff old/new yang tidak dapat dimutasi dari panel.

**Acceptance criteria:**
- Setiap perubahan menyimpan pengguna, waktu, dan hanya field yang berubah.
- Gagal menyimpan revision menggagalkan perubahan evaluasi dalam transaksi yang sama.
- Tidak tersedia update/delete action untuk revision.

**Verification:** create/update/no-op/transaction tests dan policy tests.

**Dependencies:** Task 5. **Scope:** M.

#### Task 7: Policy evaluasi berbasis konteks

**Description:** Tambahkan ability khusus evaluasi dan satukan keputusan UI dengan policy; jangan mengulang hardcoded role checks.

**Acceptance criteria:**
- Penulis/pengikut hanya pada Report terkait.
- Koordinator hanya pada evaluasi Unit Kerja aktif yang dikoordinasikan.
- Permission pimpinan/admin/super-admin dapat mengelola seluruh evaluasi.

**Verification:** matrix authorization tests untuk view/create/update/history/export.

**Dependencies:** Tasks 4–6. **Scope:** M.

### Checkpoint C

- Evaluasi dan revision konsisten dalam transaksi.
- Semua jalur authorization diuji positif dan negatif.
- Data Report historis tetap valid tanpa evaluasi.

### Phase 4 — UI Evaluasi pada Report

#### Task 8: Relation manager Evaluasi Monev

**Description:** Tambahkan bagian Evaluasi Monev pada detail Report, digate oleh feature flag dan policy.

**Acceptance criteria:**
- Daftar menampilkan Unit Kerja, periode, ringkasan status isi, editor terakhir, dan waktu perubahan.
- Form hanya menawarkan Unit Kerja yang terkait ke Report dan mencegah duplikasi periode.
- Evidence links dapat ditambah/dihapus sebagai daftar URL.

**Verification:** Livewire/Filament component tests dan manual responsive/accessibility check.

**Dependencies:** Tasks 1, 5, 7. **Scope:** M.

#### Task 9: Linimasa riwayat evaluasi

**Description:** Tampilkan revision sebagai linimasa read-only pada view/modal evaluasi.

**Acceptance criteria:**
- Pengguna melihat editor, timestamp lokal, nama field, nilai lama, dan nilai baru.
- Nilai panjang dan link dirender aman; HTML tidak dieksekusi.
- User tanpa permission tidak dapat membuka riwayat.

**Verification:** rendering/XSS tests dan policy tests.

**Dependencies:** Tasks 6–8. **Scope:** S–M.

### Checkpoint D

- Alur tambah/edit evaluasi bekerja end-to-end.
- Audit timeline sesuai perubahan aktual.
- Sorong tetap tidak melihat UI Monev ketika feature flag mati.

### Phase 5 — Dataset dan Excel Monev

#### Task 10: Query dataset matriks

**Description:** Buat query/service teruji untuk aturan overlap periode, evaluasi periode lanjutan, pemisahan unit, kategori, dan urutan.

**Acceptance criteria:**
- Report overlap periode ikut walau evaluasi kosong.
- Report lama dengan evaluasi periode terpilih ikut kembali.
- Report unit lain tidak bocor; nomor reset untuk INTERNAL dan EKSTERNAL.

**Verification:** query matrix tests, edge dates, multi-unit, missing involvement, soft-deleted records, dan bounded query-count test.

**Dependencies:** Tasks 2, 5. **Scope:** M.

#### Task 11: Renderer workbook Monev

**Description:** Buat export class kedua yang meniru template: judul, header dua tingkat, grouping, wrapping, border, ukuran kolom/baris, print setup, hyperlink, dan blok tanda tangan.

**Acceptance criteria:**
- Urutan dan pemetaan 10 kolom sama dengan template referensi.
- Realisasi diformat sebagai satu tanggal atau rentang Indonesia; `how` menjadi plain text aman.
- Formula injection dicegah dan banyak URL menjadi hyperlink per baris.

**Verification:** cell mapping/style/merge/print-area tests, formula-injection test, export-open smoke test, dan render visual dibanding template.

**Dependencies:** Tasks 3, 4, 10. **Scope:** M.

#### Task 12: Action Export Monev

**Description:** Tambahkan action kedua pada ReportResource dengan input Unit Kerja, periode, lokasi, dan tanggal tanda tangan.

**Acceptance criteria:**
- Action hanya tampil bila Monev aktif dan user mempunyai permission export.
- Action tidak bergantung pada record yang dicentang; dataset selalu dihitung dari Unit Kerja dan periode yang dipilih sesuai aturan Task 10.
- Koordinator aktif serta profil tanda tangan wajib tersedia sebelum export.
- Export 5W1H lama tetap tersedia dan outputnya tidak berubah.

**Verification:** action visibility/validation/download tests dan regression test `ReportsExport` lama.

**Dependencies:** Tasks 1, 4, 11. **Scope:** S–M.

### Checkpoint E

- Workbook hasil export terbuka tanpa repair warning.
- Render visual sesuai template pada data kosong, pendek, dan narasi panjang.
- Tidak ada N+1 pada dataset/export.
- Export lama tetap lulus regression test.

### Phase 6 — Dokumentasi, deployment, dan hardening

#### Task 13: Dokumentasi domain dan operasi dua deployment

**Description:** Perbarui PRD, ERD, data/storage docs, serta runbook konfigurasi Sorong dan Kupang.

**Acceptance criteria:**
- Skema, permission, query periode, audit, dan pemetaan export terdokumentasi.
- Checklist deployment memisahkan DB/storage/cache/queue/session/APP_KEY tiap organisasi.
- Rollout dan rollback migration dijelaskan tanpa operasi destruktif.

**Verification:** review dokumen terhadap source dan migration final.

**Dependencies:** Tasks 1–12. **Scope:** M.

#### Task 14: Full verification dan review

**Description:** Jalankan seluruh quality gate dan review multi-axis sebelum deployment.

**Acceptance criteria:**
- Full PHPUnit, Pint, strict PSR-4, frontend build, dan `git diff --check` lulus.
- Review correctness, security, authorization, performance, compatibility, dan accessibility tidak menyisakan temuan blocking.
- Tidak ada migration/seed dijalankan pada database aplikasi selama test lokal.

**Verification:** hasil command dan checklist review terdokumentasi pada handoff.

**Dependencies:** Tasks 1–13. **Scope:** M.

## Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Data organisasi bocor antar deployment | Kritis | DB, storage, cache, queue, session, domain, dan key terpisah; smoke test konfigurasi sebelum deploy. |
| Koordinator mengubah evaluasi unit lain | Tinggi | Policy berbasis assignment aktif; uji matrix authorization server-side. |
| Report multi-unit memakai evaluasi yang salah | Tinggi | Unique composite dan semua query selalu menyertakan `work_unit_id` serta `period`. |
| Audit dapat dimanipulasi | Tinggi | Revision append-only, tanpa UI mutasi, dibuat dalam transaksi. |
| Export kosong menghilangkan atensi | Sedang | Base query memakai overlap Report dan left join evaluation, bukan hanya evaluation yang sudah ada. |
| Formula injection dari teks/URL | Tinggi | Sanitasi cell value, validasi skema URL, hyperlink API, dan regression test payload berbahaya. |
| Narasi panjang merusak layout | Sedang | Wrap text, lebar tetap terkontrol, tinggi otomatis/terukur, print landscape, dan render visual. |
| Hardcode Sorong menyebar | Tinggi | Satu organization service; audit string organisasi; setting dengan fallback. |
| Dua deployment mengalami version drift | Tinggi | Satu release tag/image; runbook deploy berurutan dan catatan versi per instalasi. |

## Definition of Done

- Seluruh acceptance criteria di atas terpenuhi.
- Export Monev cocok secara struktural dan visual dengan workbook referensi.
- Riwayat perubahan dan authorization terbukti lewat automated tests.
- Export lama, API/SSO, PDF publik, dan data Sorong tidak mengalami regresi.
- Dokumentasi dan runbook cukup untuk memasang deployment Kupang dari source code yang sama.

## Open Questions

Tidak ada pertanyaan bisnis yang menghalangi implementasi. Detail teknis kecil harus mengikuti pola Laravel 13, Filament 5, dan Maatwebsite Excel yang sudah dipakai repository.
