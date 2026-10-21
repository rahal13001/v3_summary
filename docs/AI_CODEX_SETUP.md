# Menggunakan Summary dari Codex CLI dengan ChatGPT Plus

## Pilih panduan sesuai terminal

Panduan operasional lengkap tersedia dalam dua versi. Ikuti salah satu dari awal; environment dan konfigurasi Windows tidak otomatis menjadi konfigurasi WSL.

- [Windows PowerShell: setup, penggunaan harian, ganti token/domain, dan troubleshooting](AI_MCP_WINDOWS.md).
- [WSL/Bash: setup, penggunaan harian, ganti token/domain, dan troubleshooting](AI_MCP_WSL.md).

Perintah terminal dijalankan sebelum membuka Codex. Token hanya dimasukkan melalui prompt tersembunyi di terminal, bukan melalui percakapan AI. File `.env` Laravel di hosting berbeda dari environment konektor laptop; konektor tidak membaca file `.env` secara otomatis.

## Prasyarat

- Summary sudah dideploy melalui HTTPS dan API AI sudah diaktifkan pengelola.
- Akun Summary aktif serta memiliki permission `use_ai_api` dan permission baca yang diperlukan.
- Node.js 22.13 atau lebih baru dan Codex CLI terpasang di laptop.
- Codex CLI menggunakan login akun ChatGPT Plus, bukan API key.

Tidak perlu membeli kredit API, memasang model lokal, atau memasang Office untuk membuat dokumen. Batas pemakaian AI mengikuti paket ChatGPT. Jika batas tercapai, tunggu pemulihannya; konektor tidak beralih ke layanan berbayar.

Rujukan resmi: [autentikasi Codex](https://learn.chatgpt.com/docs/auth), [MCP lokal](https://learn.chatgpt.com/docs/extend/mcp?surface=cli), [pemakaian Plus](https://learn.chatgpt.com/docs/pricing).

## 1. Siapkan token Summary

Masuk melalui browser, buka **Akses AI**, lalu buat token bernama Laptop Pribadi. Pilih kelompok data dan kolom seperlunya. Untuk membaca triwulan berdasarkan irisan kegiatan, pilih tanggal mulai dan tanggal selesai. Untuk memilih atau mengelompokkan IKU, pilih dataset IKU serta ID dan nama IKU.

Default hanya laporan sendiri/yang diikuti. Pilih seluruh laporan sesuai izin akun bila kebutuhan analisis mencakup data bersama. Cakupan token tidak pernah memperluas izin akun. Dataset people memerlukan izin baca User; token tetap hanya memberikan ID dan nama orang yang terkait laporan.

Salin token ketika ditampilkan. Jika hilang, buat token pengganti dan cabut token lama. Jangan menaruh token dalam prompt, berkas yang dikomit, screenshot, atau pesan kepada AI.

## 2. Bangun konektor

Jalankan dari **folder `tools/summary-assistant`**, bukan `tools` atau root proyek. Folder `tools` tidak mempunyai `package.json`; npm akan memilih paket induk Summary. Akibatnya build menjalankan Vite dan `npm test` menghasilkan `Missing script: "test"`.

Untuk Terminal Laragon/CMD pada Windows dengan lokasi proyek `C:\laragon\www\summary`:

```bat
cd /d C:\laragon\www\summary\tools\summary-assistant
node --version
npm prefix
npm pkg get name scripts.build scripts.test
npm ci
npm run build
npm test
```

`npm prefix` harus menunjuk folder `summary-assistant`. Nama paket harus `summary-assistant`, script build harus `tsc`, dan script test harus `node --import tsx test/run.ts`. Jika build masih menampilkan `vite build`, periksa kembali folder aktif.

Untuk PowerShell, pindah folder dengan `Set-Location 'C:\laragon\www\summary\tools\summary-assistant'`, lalu jalankan perintah npm yang sama.

Untuk Bash/WSL, mulai dari root proyek:

```bash
cd tools/summary-assistant
npm ci
npm run build
npm test
```

Audit dependency frontend dan konektor terpisah. Jalankan `npm audit` dari folder paket yang ingin diperiksa. Peringatan vulnerability atau install script bukan penyebab error `Missing script`; jangan mengubah versi dependency dengan `npm audit fix --force` untuk memperbaiki lokasi eksekusi.

Paket ini tidak perlu dijalankan di hosting. Pertahankan folder assets bersama dist karena PDF memakai font lokal dari assets.

## 3. Tetapkan environment pada terminal yang akan menjalankan Codex

Bash/WSL:

```bash
export SUMMARY_BASE_URL='https://domain-summary-anda'
read -rsp 'Token akses Summary: ' SUMMARY_API_TOKEN
export SUMMARY_API_TOKEN
export SUMMARY_OUTPUT_DIR="$HOME/Summary-outputs"
```

PowerShell:

```powershell
$env:SUMMARY_BASE_URL = 'https://domain-summary-anda'
$env:SUMMARY_API_TOKEN = [System.Net.NetworkCredential]::new('', (Read-Host 'Token akses Summary' -AsSecureString)).Password.Trim()
$env:SUMMARY_OUTPUT_DIR = Join-Path $HOME 'Summary-outputs'
```

Gunakan domain dasar aplikasi, termasuk subfolder jika ada, tanpa `/api/ai/v1`. Jangan memasukkan username/password ke URL. Sertifikat HTTPS harus sah. Jangan menonaktifkan pemeriksaan TLS.

Environment hanya berlaku pada proses terminal dan turunannya. Jika memakai pengelola kredensial lokal, pastikan nilai diteruskan ke proses konektor tanpa dicetak.

## 4. Daftarkan MCP pada Codex

Tambahkan atau gabungkan blok berikut ke konfigurasi Codex milik Anda. Gunakan path absolut yang benar untuk laptop; jangan menimpa pengaturan lain.

```toml
forced_login_method = "chatgpt"

[mcp_servers.summary]
command = "node"
args = ["/path/absolut/summary/tools/summary-assistant/dist/index.js"]
env_vars = ["SUMMARY_BASE_URL", "SUMMARY_API_TOKEN", "SUMMARY_OUTPUT_DIR"]
tool_timeout_sec = 180
```

Pada Windows gunakan path seperti `C:/folder/summary/tools/summary-assistant/dist/index.js`. Nilai token tidak dimasukkan dalam TOML. Buka Codex dari terminal tempat environment sudah diatur.

```bash
codex login
codex login status
codex mcp list
codex
```

Pastikan status autentikasi menggunakan ChatGPT. Pendaftaran MCP tidak mengubah langganan atau membuat tagihan API model.

## 5. Contoh permintaan

Tanpa file:

> Periksa kolom yang boleh saya baca. Kelompokkan kegiatan berdasarkan IKU pada triwulan III 2026. Gunakan periode 1 Juli sampai 30 September dengan aturan irisan kegiatan. Sebutkan jumlah laporan unik dan sumbernya.

Excel dan Word:

> Kelompokkan kegiatan Q3 2026 berdasarkan IKU resmi. Buat Excel berisi data dan rekap serta Word berisi ringkasan hasil, kendala, dan tindak lanjut jika kolom tersebut tersedia. Jelaskan bagian yang tidak dapat dibaca karena izin.

Presentasi dan PDF:

> Buat presentasi PowerPoint ringkas dan laporan PDF dari hasil tadi. Gunakan angka rekap yang sudah dihitung. Sertakan sumber dan keterbatasan data.

Markdown:

> Simpan analisis sebagai Markdown dengan tabel pengelompokan dan ID laporan pendukung.

Codex membaca capabilities/schema, menyiapkan dataset, menghitung rekap lokal, membaca rincian relevan, kemudian membuat format yang diminta. Sampel tiga laporan bukan dasar untuk menyimpulkan seluruh dataset.

## Membaca hasil

- Default keluaran adalah jawaban terminal. File hanya dibuat jika diminta.
- Lokasi file dikembalikan oleh tool pembuatan dokumen.
- XLSX berisi data lengkap yang diambil; sheet relasi mencegah penggandaan baris laporan.
- Sumber lengkap PPTX tersedia pada catatan pembicara.
- Jumlah kehadiran bukan jumlah orang unik. Total kelompok dapat melebihi total laporan unik.
- Label PARSIAL berarti hasil belum mencakup seluruh data/relasi yang dibutuhkan. Persempit query atau lanjutkan pagination terkait sebelum kesimpulan menyeluruh.

## Batas lokal dan penanganan masalah

Dataset disimpan dalam memori, maksimal empat handle dan total 50 MiB ukuran data terserialisasi, dengan maksimum 5.000 laporan per pengambilan serta masa idle 30 menit. Heap proses dapat lebih besar. Proses konektor yang ditutup kehilangan dataset. Ambil ulang jika kedaluwarsa.

Satu persiapan dataset berjalan pada satu waktu, tanpa antrean. Jika persiapan lain masih berjalan, tunggu selesai lalu coba kembali. Cache lama dapat dikeluarkan untuk memberi ruang bagi data yang sedang diambil. Kolom yang tidak dipilih tidak dihitung sebagai angka nol.

`SUMMARY_MAX_RECORDS` dan `SUMMARY_MAX_BYTES` dapat diatur sebelum memulai konektor jika kapasitas laptop memungkinkan. Tidak ada jaminan pemrosesan model terhadap seluruh data panjang dalam satu konteks. Gunakan periode dan kolom yang terarah.

| Kondisi | Tindakan |
|---|---|
| 401 | Periksa token pada proses Node dan jalankan uji `CLIENT_OK` sesuai panduan OS; restart Codex dari terminal yang berhasil sebelum menyimpulkan token kedaluwarsa |
| 403 | Periksa permission akun, dataset, kolom, dan cakupan token |
| 422 | Periksa schema; berikan kedua batas periode dan operator yang tersedia |
| 429 | Tunggu sesuai Retry-After |
| 503 | Minta pengelola memeriksa aktivasi API |
| Error TLS/redirect | Periksa domain dan sertifikat; jangan menonaktifkan verifikasi |
| Folder symlink ditolak | Gunakan folder lokal langsung yang dapat ditulis |
| Batas Plus tercapai | Tunggu pemulihan batas; tidak perlu mengisi API key |

Model dapat berbeda dalam kedalaman analisis dan penyajian. Aturan izin, periode, rekap, dan validasi sumber tetap sama. Data yang dijadikan konteks model dapat diproses melalui layanan penyedia AI.
