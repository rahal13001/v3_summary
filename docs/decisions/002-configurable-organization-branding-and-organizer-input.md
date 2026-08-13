# ADR-002: Branding dan Penyelenggara Dikonfigurasi per Deployment

- Status: Accepted
- Tanggal: 13 Agustus 2026

## Context

Sorong dan Kupang memakai satu source code tetapi database serta infrastrukturnya terpisah. Sorong menyebut aplikasi Summary dan biasanya mengunci Penyelenggara ke LPRL Sorong. Kupang dapat menyebut aplikasi Teripang, memakai logo lain, menamai Keterlibatan sebagai Internal/Eksternal, dan membutuhkan Penyelenggara yang dapat disarankan atau diisi manual.

Kondisi berdasarkan kota, domain, atau teks Keterlibatan akan menyebarkan aturan deployment ke banyak file dan mudah rusak saat nama berubah.

## Decision

- Simpan `app_name`, `favicon_path`, `organizer_name`, dan `organizer_input_mode` pada singleton `organization_settings`; pertahankan `logo_path` yang sudah ada.
- Gunakan `OrganizationContext` sebagai satu pintu akses setting, fallback `.env`, pemeriksaan keberadaan aset, dan validasi mode.
- Pertahankan `involvements.name` sebagai sumber label fleksibel dan `is_lprl_organizer` sebagai stable legacy flag untuk perilaku serta klasifikasi Monev.
- Sediakan tiga mode: `locked` memaksa nilai server-side, `editable` memberi default hanya saat kosong, dan `manual` tidak memberi default.
- Batasi upload logo/favicon ke JPEG, PNG, atau WebP maksimal 2 MB; jangan menerima SVG.

## Consequences

- Deployment baru dapat mengganti branding dan istilah tanpa fork source code.
- Data dan export lama tetap kompatibel karena kolom/flag lama tidak diubah dan default `locked` mempertahankan perilaku Sorong.
- Admin deployment bertanggung jawab menjaga kombinasi nama Keterlibatan, flag internal, dan kebijakan Penyelenggara tetap sesuai proses bisnis lokal.
- Perubahan nama database legacy `is_lprl_organizer` ditunda sampai migrasi deprecasi lintas deployment disepakati.
