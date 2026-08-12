# Adopsi Summary Kupang dan Evaluasi Monev

## Phase 1 — Fondasi multi-deployment

- [x] Task 1: Tambahkan Pengaturan Organisasi dan feature flag Monev.
- [x] Task 2: Generalisasi identitas organisasi dan penyelenggara tanpa regresi Sorong.
- [x] Checkpoint A: uji dua profil organisasi, feature gating, dan kontrak API.

## Phase 2 — Koordinator dan otorisasi

- [x] Task 3: Tambahkan tanda tangan pada profil koordinator.
- [x] Task 4: Tambahkan penugasan koordinator berperiode pada Unit Kerja.
- [x] Checkpoint B: uji satu koordinator aktif dan eager loading profil.

## Phase 3 — Evaluasi dan audit

- [x] Task 5: Tambahkan skema/model Evaluasi Monev.
- [x] Task 6: Tambahkan revision log append-only.
- [x] Task 7: Tambahkan policy evaluasi berbasis Report, Unit Kerja, dan permission.
- [x] Checkpoint C: uji transaksi, authorization matrix, dan kompatibilitas data lama.

## Phase 4 — UI Evaluasi

- [ ] Task 8: Tambahkan relation manager Evaluasi pada detail Report.
- [ ] Task 9: Tambahkan linimasa riwayat read-only.
- [ ] Checkpoint D: uji alur UI, audit, XSS, accessibility, dan feature gating.

## Phase 5 — Export Monev

- [ ] Task 10: Bangun query dataset berdasarkan unit, overlap periode, dan evaluasi lanjutan.
- [ ] Task 11: Bangun renderer Excel yang mengikuti template Monev.
- [ ] Task 12: Tambahkan action Export Monev dengan parameter dan validasi tanda tangan.
- [ ] Checkpoint E: uji mapping, format, hyperlink, query count, render visual, dan export lama.

## Phase 6 — Handoff

- [ ] Task 13: Perbarui PRD, ERD, data/storage docs, dan runbook dua deployment.
- [ ] Task 14: Jalankan full test, Pint, strict PSR-4, frontend build, diff check, dan review multi-axis.
- [ ] Checkpoint final: seluruh Definition of Done terpenuhi dan siap direview pengguna.
