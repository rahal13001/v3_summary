# Summary Readonly AI API and Local Documents

- [x] Inspect existing code, graph, authentication, permissions, and completed tasks.
- [x] Write failing API access tests and verify initial failure.
- [x] Add grant migration, registry, scoped token service, query service, and isolated authentication.
- [x] Pass initial AI API access tests.
- [x] Verify expanded authorization, evaluation, relation, and legacy SSO tests.
- [x] Verify human Filament token creation, ownership, expiry, and revocation.
- [x] Build local MCP client and bounded dataset collection.
- [x] Add deterministic grouping and source-backed narrative inputs.
- [x] Generate and inspect XLSX, Markdown, PDF, PPTX, and DOCX.
- [x] Verify live MCP protocol and end-to-end isolated HTTP usage.
- [x] Complete OpenAPI, ERD, setup, Codex Plus, deployment, and rollback documentation.
- [x] Run formatter, application tests, local package tests/typecheck/build, frontend build, and review. PHP fitur AI lulus; full Pint masih melaporkan 73 file lama yang tidak diubah.

## Perincian rencana dan kesiapan — 20 Oktober 2026

Rincian, dependensi, kriteria penerimaan, ERD, dan diagram alur berada di `tasks/plan.md`. Bukti implementasi lokal dicatat di `docs/AI_VERIFICATION.md`. Pengguna membatasi pekerjaan sampai commit lokal, tanpa push atau aktivasi hosting.

- [x] Perinci arsitektur shared hosting, laptop, Codex CLI login ChatGPT, dan lima format keluaran.
- [x] Dokumentasikan izin efektif, katalog kolom/relasi, endpoint, periode, sumber, dan hasil parsial.
- [x] Susun tahapan audit/implementasi beserta kriteria penerimaan dan aktivasi hosting.
- [x] Tahap 0–1: cocokkan kontrak, baseline, dan draf kode dengan rencana terperinci.
- [x] Tahap 5–6: buktikan metadata kolom yang diambil tidak menimbulkan angka peserta nol palsu.
- [x] Tahap 5: batasi concurrent prepare dan antrean, termasuk penggunaan data in-flight.
- [x] Tahap 7: buktikan pemecahan teks panjang XLSX/PPTX mempertahankan Unicode.
- [x] Tahap 8: catat bukti akhir uji lokal dan masalah baseline secara terpisah.
- [x] Catat tahap 9 sebagai runbook pekerjaan berikutnya, sesuai instruksi pengguna “siapkan lokal dulu saja, kamu sampai ke commit saja tugasnya”. Hosting dan akun pribadi belum diaktifkan.
- [x] Buat commit lokal implementasi dan dokumentasi yang sudah diverifikasi, tanpa push. Branch `codex/summary-readonly-ai`; API `8e33a9c`, konektor `89042f6`, dan dokumentasi pada commit penutup.

Hasil lokal: 155 tes Laravel/698 assertion; 16 tes konektor; typecheck dan build; audit dependency 0 vulnerability; MySQL 8.4.3 terisolasi; smoke HTTPS–Laravel–MCP menghasilkan lima format. Tidak ada perubahan data bisnis pengguna. Aktivasi hosting dan login akun ChatGPT pribadi tidak termasuk pekerjaan sesi ini.

---

# Completed: Branding Organisasi dan Perilaku Keterlibatan

- [x] Tulis failing test untuk skema dan fallback konfigurasi baru.
- [x] Tambahkan migration aditif.
- [x] Implementasikan model dan `OrganizationContext`.
- [x] Verifikasi checkpoint fondasi konfigurasi.
- [x] Tulis failing test untuk tiga mode Penyelenggara dan branding panel.
- [x] Perbarui Pengaturan Organisasi dan panel branding.
- [x] Generalisasi master Keterlibatan dan form Report.
- [x] Verifikasi checkpoint workflow laporan.
- [x] Perbarui dokumentasi dan ADR.
- [x] Jalankan browser verification dan seluruh quality gate.
- [x] Review diff dan buat commit tanpa push.
