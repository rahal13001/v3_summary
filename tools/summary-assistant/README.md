# Summary Assistant

Konektor MCP lokal untuk API AI readonly Summary. Codex CLI menggunakan akun ChatGPT Plus; konektor tidak memanggil API model atau layanan konversi berbayar.

## Instalasi

Node.js 22.13 atau lebih baru diperlukan.

```bash
npm ci
npm run typecheck
npm test
npm run build
```

Atur SUMMARY_BASE_URL dan SUMMARY_API_TOKEN pada environment, lalu daftarkan `node /path/absolut/dist/index.js` sebagai MCP stdio. Pertahankan direktori assets bersama dist. Panduan lengkap ada di [setup Codex](../../docs/AI_CODEX_SETUP.md).

Tools menyediakan pembacaan laporan, referensi, pengikut, dan evaluasi; pengambilan dataset sementara; penghitungan lokal; dan ekspor XLSX/MD/PDF/PPTX/DOCX hanya bila diminta. Penulisan file lokal berbeda dari perubahan data Summary, yang tidak disediakan.

## Pengujian

`npm test` memeriksa HTTP client, kapasitas dan expiry dataset, penghitungan, lima format, sumber, path, OpenAPI, dan protokol MCP.

`npm run smoke` menguji proses MCP stdio melalui HTTPS lokal terhadap kernel Laravel dan database fixture baru. PHP CLI dengan SQLite serta openssl CLI diperlukan. Tidak menggunakan akun ChatGPT atau database aplikasi.

`SUMMARY_SMOKE_KEEP_ALIVE=1 npm run smoke` mempertahankan server fixture untuk pemeriksaan browser. Hentikan proses setelah selesai. Pada Windows atur environment melalui PowerShell sebelum menjalankan perintah. Jangan membuka fixture pengujian ke jaringan publik.

Renderer PDF pengembangan tersedia melalui `node test/render-pdf.mjs <path-pdf-fixture> [nomor-halaman]`. Script hanya menerima PDF di folder smoke fixture. Word/PowerPoint dapat diperiksa menggunakan LibreOffice atau Office pada lingkungan pengembangan, tetapi kedua aplikasi tidak diperlukan untuk menghasilkan dokumen.

## Lisensi font

PDF memakai DejaVu Sans yang dibundel. Lihat assets/FONT-LICENSE.txt untuk pemberitahuan lisensi font. Dependensi dikunci pada package-lock.json. Override uuid dan image-size mempertahankan perbaikan kerentanan pada dependensi turunan pembuat dokumen.

Lihat juga [spesifikasi dan diagram](../../docs/specs/AI_READONLY_API.md), [OpenAPI](../../docs/api/ai-v1.openapi.yaml), dan [deployment](../../docs/AI_DEPLOYMENT.md).
