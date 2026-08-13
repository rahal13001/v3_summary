# Implementation Plan: Branding Organisasi dan Perilaku Keterlibatan

## Overview

Implementasi mengikuti `docs/specs/ORGANIZATION_BRANDING_AND_INVOLVEMENT.md` dengan migration aditif dan fallback yang mempertahankan perilaku Sorong. Nama Keterlibatan tetap dikelola pada master Keterlibatan; Pengaturan Organisasi mengelola branding dan kebijakan pengisian Penyelenggara.

## Architecture Decisions

- Pisahkan nama web (`app_name`) dari nama/singkatan organisasi.
- Pertahankan `logo_path` untuk kompatibilitas, tambahkan `favicon_path`.
- Simpan kebijakan Penyelenggara pada singleton organisasi karena berlaku konsisten untuk seluruh Keterlibatan internal deployment.
- Pertahankan `is_lprl_organizer` sebagai kolom legacy/stable flag untuk Monev; generalisasi hanya pada label UI dan accessor domain.
- Gunakan `OrganizationContext` sebagai satu-satunya pintu akses konfigurasi dan fallback `.env`.

## Dependency Graph

```text
Migration aditif
    -> OrganizationSetting + OrganizationContext
        -> Pengaturan Organisasi
        -> AdminPanelProvider branding
        -> Involvement/Report enforcement
            -> ReportResource interaction
                -> browser verification

Semua irisan -> dokumentasi -> full quality gates -> review -> commit
```

## Task List

### Phase 1: Configuration Foundation

- [x] Tambahkan test schema, fallback, dan override branding/perilaku.
- [x] Tambahkan migration aditif serta field/model/context baru.
- [x] Verifikasi focused OrganizationSettings tests.

### Phase 2: Admin and Report Workflow

- [x] Tambahkan field branding dan kebijakan Penyelenggara pada resource organisasi.
- [x] Generalisasi label resource Keterlibatan tanpa mengubah flag database.
- [x] Terapkan mode locked/editable/manual pada model dan form Report.
- [x] Verifikasi focused resource/model/form tests.

### Phase 3: Documentation and Verification

- [x] Perbarui PRD, ERD, storage/deployment runbook, `.env.example`, dan ADR.
- [x] Jalankan formatter, full tests, strict PSR autoload, frontend build, dan diff check.
- [x] Jalankan browser smoke test branding publik; verifikasi UI admin/Report dilengkapi automated resource tests karena browser tidak memiliki sesi login.
- [x] Review correctness, security, compatibility, performance, maintainability, dan accessibility.
- [x] Buat commit atomik tanpa push.

## Risks and Mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Record lama tidak memiliki field baru | High | Kolom nullable/default locked dan fallback `OrganizationContext`. |
| Logo/favikon aktif tidak ditemukan | Medium | Cek keberadaan public disk sebelum menghasilkan URL; fallback asset lama. |
| Mode editable ditimpa saat save | High | Model hanya memberi default jika nilai kosong; test nilai kustom. |
| Client memanipulasi mode locked | High | Enforcement pada event `Report::saving`. |
| Label Keterlibatan dan konfigurasi organisasi menjadi duplikat | Medium | Label tetap hanya di `involvements.name`. |
| Upload active content | High | MIME allowlist JPEG/PNG/WebP, batas 2 MB, tanpa SVG. |

## Open Questions

Tidak ada.
