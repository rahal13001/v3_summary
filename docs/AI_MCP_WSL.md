# Setup MCP Summary di WSL / Bash

Panduan ini untuk terminal Linux WSL dengan Bash, misalnya prompt `rahal@komputer:~$`. Semua blok `bash` dijalankan sebelum masuk Codex. Sintaks `$env:...` milik PowerShell tidak berlaku di Bash; pada WSL gunakan `export`.

## Daftar isi

1. [Persiapan dan perbedaan Windows/WSL](#1-persiapan-dan-perbedaan-windowswsl)
2. [Siapkan salinan dan bangun konektor sekali](#2-siapkan-salinan-dan-bangun-konektor-sekali)
3. [Daftarkan MCP pada Codex WSL](#3-daftarkan-mcp-pada-codex-wsl)
4. [Isi environment dan uji koneksi](#4-isi-environment-dan-uji-koneksi)
5. [Simpan pengaturan untuk sesi berikutnya](#5-simpan-pengaturan-untuk-sesi-berikutnya)
6. [Penggunaan harian dan dokumen](#6-penggunaan-harian-dan-dokumen)
7. [Ganti token](#7-ganti-token)
8. [Ganti alamat web atau folder keluaran](#8-ganti-alamat-web-atau-folder-keluaran)
9. [Pembaruan, penghapusan kredensial, dan error](#9-pembaruan-penghapusan-kredensial-dan-error)

## 1. Persiapan dan perbedaan Windows/WSL

Alur: **Codex CLI Linux → MCP stdio Node.js Linux di laptop → HTTPS GET ke API Summary di hosting**. AI membaca data sesuai izin dan membuat dokumen lokal jika diminta. Tidak tersedia operasi menambah, mengubah, atau menghapus data Summary. Isi data dapat diproses oleh layanan penyedia AI.

Siapkan Node.js minimal 22.13, npm, Git, Python 3, serta Codex CLI pada WSL. Python 3 dipakai hanya untuk menyimpan pengaturan secara aman terhadap karakter khusus shell. Gunakan login akun ChatGPT yang memiliki akses Codex; konektor tidak membutuhkan API key model. Periksa:

```bash
command -v node npm codex python3 git
node --version
node -p 'process.platform'
codex --version
codex login status
```

Platform harus `linux`. Executable Node/Codex harus versi Linux, bukan `node.exe`/`codex.exe` dari Windows. Jika perlu, pasang versi Linux melalui prosedur resmi masing-masing alat. Jalankan `codex login` bila belum login, lalu pilih akun ChatGPT. Login Codex berbeda dari token Summary.

| Pengaturan | Windows PowerShell | WSL/Bash |
|---|---|---|
| Mengisi environment | `$env:SUMMARY_BASE_URL = '...'` | `export SUMMARY_BASE_URL='...'` |
| Folder proyek contoh | `C:\laragon\www\summary` | `/mnt/c/laragon/www/summary` |
| Config Codex default | `C:\Users\nama\.codex\config.toml` | `/home/nama/.codex/config.toml` |
| Environment tetap di panduan | Environment User Windows | File privat di home Linux |
| Runtime | Node/Codex Windows | Node/Codex Linux |

Mengisi token di PowerShell tidak otomatis mengisinya di WSL. Konfigurasi dan login masing-masing lingkungan juga perlu disiapkan sendiri. Jika sengaja memakai `CODEX_HOME`, konfigurasi ada di direktori itu, bukan lokasi default tabel.

Di web Summary, pastikan API sudah aktif melalui HTTPS dan akun mempunyai `use_ai_api` serta izin baca. Buka **Akses AI**, buat token laptop, pilih dataset, kolom, relasi, masa berlaku, serta cakupan laporan. Untuk IKU resmi, izinkan `indicators` dan kolom relasi yang diperlukan. Token tidak memperluas izin akun. Jangan mengirim token melalui percakapan AI.

## 2. Siapkan salinan dan bangun konektor sekali

Jika Windows dan WSL sama-sama digunakan, gunakan salinan proyek terpisah agar tidak bergantian menimpa `node_modules`, terutama dependency native. Contoh berikut membuat salinan Linux dari commit lokal proyek Windows:

```bash
git clone --no-hardlinks /mnt/c/laragon/www/summary "$HOME/summary-wsl"
cd "$HOME/summary-wsl/tools/summary-assistant"
```

Jalankan clone hanya jika folder tujuan belum ada. Salinan ini mengambil perubahan yang sudah dikomit, bukan perubahan Windows yang belum dikomit. `origin` salinan mengarah ke proyek Windows tersebut. Jika sudah punya checkout Linux tersendiri, gunakan checkout itu dan sesuaikan semua path pada panduan ini. Tidak perlu mengaktifkan aplikasi Laravel/database di WSL hanya untuk menjalankan konektor.

Bangun paket:

```bash
npm prefix
npm pkg get name scripts.build scripts.test
npm ci
npm run build
npm test
test -f dist/index.js && test -f dist/client.js && echo 'BUILD_OK'
```

Nama paket harus `summary-assistant`, build harus `tsc`, dan hasil akhir `BUILD_OK`. Jika `vite build` atau `Missing script: "test"`, perbaiki folder aktif. Pertahankan folder `assets` bersama paket karena PDF memakai font lokal.

Jika hanya memakai WSL dan tetap menjalankan dari `/mnt/c/laragon/www/summary/tools/summary-assistant`, sesuaikan path konfigurasi dan perintah `cd`. Hindari memakai `node_modules` yang dibangun Windows; instal dependency melalui Linux untuk penggunaan Linux.

## 3. Daftarkan MCP pada Codex WSL

Buka konfigurasi default Linux:

```bash
mkdir -p "$HOME/.codex"
nano "$HOME/.codex/config.toml"
```

Tambahkan atau perbarui satu blok berikut. **Ganti `/home/nama-anda` dengan home Linux yang sebenarnya**, lihat dengan `echo "$HOME"`. TOML tidak mengembangkan `$HOME` atau `~` dalam string path.

```toml
[mcp_servers.summary]
command = "node"
args = ["/home/nama-anda/summary-wsl/tools/summary-assistant/dist/index.js"]
env_vars = ["SUMMARY_BASE_URL", "SUMMARY_API_TOKEN", "SUMMARY_OUTPUT_DIR"]
tool_timeout_sec = 180
```

Pada nano, simpan dengan Ctrl+O, Enter, lalu keluar Ctrl+X. Jangan menimpa pengaturan lain atau menduplikasi `[mcp_servers.summary]`. Jika perlu, gunakan path absolut Node Linux dari `command -v node` sebagai `command`.

`env_vars` meneruskan nilai yang sudah ada pada environment Codex. Jika ada nilai lama untuk variabel yang sama di `[mcp_servers.summary.env]`, hapus entri lama agar satu sumber pengaturan digunakan. Periksa juga config lokal `.codex/config.toml` proyek bila ada.

Ini MCP stdio lokal, sehingga jangan mendaftarkan domain REST Summary sebagai MCP HTTP. Codex memulai proses konektor secara otomatis; tidak perlu `npm start` terpisah.

## 4. Isi environment dan uji koneksi

```bash
export SUMMARY_BASE_URL='https://summary.timurbersinar.com'
read -rsp 'Tempel token Summary lalu Enter: ' SUMMARY_API_TOKEN
printf '\n'
export SUMMARY_API_TOKEN
export SUMMARY_OUTPUT_DIR="$HOME/Summary-outputs"
```

Tempel token lengkap saat diminta, lalu Enter. Input disembunyikan dan nilai token tidak ditulis dalam baris perintah/history. Jangan menambahkan `Bearer`, tanda kutip, atau baris baru pada token.

Gunakan URL dasar HTTPS, tanpa `/api/ai/v1`, query, atau fragment. Sertakan subfolder bila aplikasi memang dipasang pada subfolder. Gunakan alamat akhir yang tidak redirect dan sertifikat sah. File `.env` tidak otomatis dibaca konektor; `.env` hosting hanya mengatur Laravel pada server.

Periksa apa yang diterima Node tanpa menampilkan token:

```bash
node -e 'console.log(JSON.stringify({url:process.env.SUMMARY_BASE_URL ?? null,tokenTerisi:Boolean(process.env.SUMMARY_API_TOKEN)}))'
```

Hasil yang diharapkan:

```json
{"url":"https://summary.timurbersinar.com","tokenTerisi":true}
```

Uji client konektor:

```bash
cd "$HOME/summary-wsl/tools/summary-assistant"
node --input-type=module <<'JS'
import { SummaryClient } from './dist/client.js';
try {
  const c = new SummaryClient(process.env.SUMMARY_BASE_URL, process.env.SUMMARY_API_TOKEN);
  await c.read('capabilities');
  await c.read('schema');
  console.log('CLIENT_OK');
} catch (e) {
  console.log('CLIENT_ERROR:', e.message);
  process.exitCode = 1;
}
JS
```

`CLIENT_OK` membuktikan proses Node saat itu dapat membaca capabilities/schema memakai URL/token tersebut. Dataset/kolom yang boleh diakses tetap mengikuti isi kedua endpoint. Jika belum berhasil, lihat langkah 9; jangan menyimpulkan semua izin tersedia hanya karena autentikasi berhasil.

## 5. Simpan pengaturan untuk sesi berikutnya

Tanpa penyimpanan tetap, ulangi pengisian environment langkah 4 setiap membuka terminal baru. Untuk penggunaan pribadi, simpan nilai setelah `CLIENT_OK` menggunakan file privat di home Linux. File ini berisi token sebagai **teks biasa**, bukan brankas terenkripsi; izin Linux membatasi akses file tetapi bukan perlindungan dari administrator atau proses akunmu sendiri.

Jalankan blok berikut secara utuh. Script menyimpan nilai environment saat ini, menggunakan quoting Bash untuk karakter khusus, dan tidak mencetak token:

```bash
python3 <<'PY'
import os
from pathlib import Path
import shlex

names = ('SUMMARY_BASE_URL', 'SUMMARY_API_TOKEN', 'SUMMARY_OUTPUT_DIR')
if not all(os.environ.get(name) for name in names):
    raise SystemExit('Environment belum lengkap; isi langkah 4 dahulu.')
folder = Path.home() / '.config' / 'summary-assistant'
folder.mkdir(parents=True, exist_ok=True)
folder.chmod(0o700)
target = folder / 'environment'
fd = os.open(target, os.O_WRONLY | os.O_CREAT | os.O_TRUNC, 0o600)
with os.fdopen(fd, 'w') as handle:
    os.fchmod(handle.fileno(), 0o600)
    for name in names:
        handle.write(f'export {name}={shlex.quote(os.environ[name])}\n')
print('Pengaturan Summary tersimpan.')
PY
```

Agar dimuat otomatis oleh Bash interaktif, buka:

```bash
nano "$HOME/.bashrc"
```

Tambahkan baris berikut **sekali** pada akhir file, simpan, lalu keluar:

```bash
if [ -f "$HOME/.config/summary-assistant/environment" ]; then
    source "$HOME/.config/summary-assistant/environment"
fi
```

Buka terminal WSL baru, lalu jalankan pemeriksaan Node pada langkah 4. Jangan memakai `cat` untuk mengirim isi file environment kepada AI. File ini harus tetap di home Linux, bukan dalam repository, folder web publik, atau `/mnt/c`.

Langkah `.bashrc` berlaku untuk Bash interaktif. Jika menjalankan dari aplikasi lain atau shell noninteraktif, muat file secara eksplisit sebelum membuka Codex:

```bash
source "$HOME/.config/summary-assistant/environment"
codex
```

## 6. Penggunaan harian dan dokumen

Jika sudah menyimpan dan mengaktifkan pemuatan otomatis:

```bash
cd "$HOME/summary-wsl/tools/summary-assistant"
codex mcp list
codex
```

Di dalam Codex gunakan `/mcp` untuk memeriksa konektor, lalu misalnya:

> Baca summary_capabilities dan summary_schema terlebih dahulu. Kelompokkan seluruh kegiatan berdasarkan Indikator Kinerja resmi pada triwulan III tahun 2026, 1 Juli–30 September 2026. Jelaskan definisi periode, sumber, dan keterbatasan data. Buat Excel untuk data dan rekap, serta PDF untuk ringkasan. Jangan mengubah data Summary.

Kamu juga dapat meminta MD, DOCX, atau PPTX, dan memakai kolom lain sesuai izin. Tanpa permintaan dokumen, hasil diberikan di terminal. File dibuat pada `SUMMARY_OUTPUT_DIR` (default `$HOME/Summary-outputs`) dan tool mengembalikan path hasilnya.

Untuk membuka hasil WSL dari Windows Explorer setelah folder ada:

```bash
explorer.exe "$(wslpath -w "$SUMMARY_OUTPUT_DIR")"
```

Perintah tersebut memerlukan interoperabilitas Windows/WSL. Bila tidak tersedia, akses melalui Explorer `\\wsl.localhost\NamaDistro\home\nama-anda\Summary-outputs` dengan nama distro/akun yang benar.

Restart konektor menghilangkan dataset sementara di memori. Minta pengambilan ulang jika perlu. Ketentuan rekap, sumber, dan label parsial dijelaskan dalam [panduan analisis](AI_CODEX_SETUP.md#membaca-hasil).

## 7. Ganti token

1. Keluar dari Codex sampai prompt Bash muncul.
2. Buat token baru melalui **Akses AI** di web Summary dengan cakupan yang diperlukan.
3. Isi environment sesi saat ini:

```bash
read -rsp 'Tempel token Summary BARU lalu Enter: ' SUMMARY_API_TOKEN
printf '\n'
export SUMMARY_API_TOKEN
```

4. Ulangi uji Node dan client pada langkah 4.
5. Setelah `CLIENT_OK`, jika memakai penyimpanan tetap, **jalankan kembali blok Python langkah 5** untuk menyimpan seluruh nilai environment terbaru. Jangan menjalankan `source` file lama sebelum menyimpan, karena itu memuat kembali token lama.
6. Cabut token lama melalui web setelah pengganti berhasil. Jika token lama bocor, cabut segera.
7. Buka kembali Codex dari Bash yang sudah diuji. Pada terminal lain yang masih terbuka, keluar dahulu dari Codex, muat file environment terbaru, lalu mulai ulang Codex.

Tidak perlu mengulang npm atau mengubah TOML hanya untuk mengganti token. Penyimpanan WSL tidak memperbarui token Windows; jika keduanya digunakan, perbarui masing-masing. Pencabutan token tidak menarik kembali dokumen yang sudah dibuat.

## 8. Ganti alamat web atau folder keluaran

Keluar dari Codex lalu set URL tujuan sebenarnya:

```bash
export SUMMARY_BASE_URL='https://alamat-summary-baru.example'
```

Ganti placeholder `.example` dengan domain nyata. Pastikan API aktif dan token sah pada instalasi tujuan; jika berbeda instalasi, buat token dari web tujuan dan ikuti langkah 7. Pastikan alamat benar sebelum menguji karena token dikirim ke alamat tersebut.

Jalankan uji langkah 4. Setelah `CLIENT_OK`, ulangi blok Python langkah 5 jika ingin menyimpan secara tetap. Mulai ulang Codex dari terminal yang sudah diuji.

Untuk mengganti folder keluaran:

```bash
export SUMMARY_OUTPUT_DIR="$HOME/Documents/Summary-outputs"
```

Pilih folder lokal yang bisa ditulis dan bukan symlink. Simpan kembali dengan blok Python jika memakai pengaturan tetap, lalu restart Codex. Dokumen lama tetap di folder lama. Ubah path pada TOML hanya bila folder paket/`dist/index.js` atau executable Node dipindah.

## 9. Pembaruan, penghapusan kredensial, dan error

### Kapan mengulang setup?

| Kejadian | Yang dilakukan |
|---|---|
| Pertama setup | Instal dependency, build/test, config MCP, isi environment, uji |
| Menggunakan harian | Muat environment jika belum otomatis, lalu buka Codex |
| Ganti token/domain/folder keluaran | Perbarui environment, uji, simpan bila perlu, restart Codex |
| Source konektor berubah | `npm run build`, `npm test`, restart Codex |
| Dependency/lockfile berubah atau node_modules hilang | `npm ci`, `npm run build`, `npm test`, restart Codex |

Jika memakai clone lokal langkah 2 dan ingin mengambil commit baru dari proyek Windows, pastikan perubahan yang dibutuhkan sudah dikomit pada proyek Windows. Pada clone WSL:

```bash
cd "$HOME/summary-wsl"
git status --short
```

Jika worktree bersih dan branch sesuai, lakukan pembaruan:

```bash
git pull --ff-only
cd tools/summary-assistant
npm ci
npm run build
npm test
```

Jika Git menolak karena branch menyimpang atau ada perubahan lokal, selesaikan perbedaannya terlebih dahulu; jangan melakukan reset paksa. Clone tersebut membaca commit dari proyek Windows lokal, bukan otomatis dari hosting.

### Menghapus kredensial

Keluar dari Codex, lalu:

```bash
rm -f "$HOME/.config/summary-assistant/environment"
unset SUMMARY_API_TOKEN SUMMARY_BASE_URL SUMMARY_OUTPUT_DIR
```

Baris pemuatan bersyarat di `.bashrc` boleh dihapus; tanpa file, baris itu tidak memuat apa pun. Terminal lain yang terbuka masih dapat memegang nilai lama, jadi tutup/restart proses terkait. Cabut juga token melalui web untuk membatalkan akses server.

### Tabel penanganan error

| Hasil/error | Pemeriksaan dan tindakan |
|---|---|
| `:SUMMARY_BASE_URL: command not found` | Kamu memakai sintaks PowerShell di Bash. Gunakan `export SUMMARY_BASE_URL='https://domain-anda'`. |
| `tokenTerisi:false` | Ulangi input token dan `export SUMMARY_API_TOKEN` pada Bash yang akan membuka Codex, atau muat file environment tersimpan. |
| `Invalid URL` / URL `null` | Isi URL HTTPS lengkap di environment Linux; konfigurasi Windows tidak otomatis terwariskan. |
| `CLIENT_ERROR: Summary HTTP 401` | Periksa token lengkap, masa berlaku, pencabutan, dan kecocokan dengan web tujuan. Uji client sebelum memulai Codex. |
| `CLIENT_OK`, tetapi Codex 401 | Keluar dan mulai ulang Codex dari Bash yang berhasil. Periksa config Linux aktif, path Node Linux, dan nilai tetap lama pada `mcp_servers.summary.env`. |
| HTTP 403 / dataset tidak diizinkan | Periksa izin akun dan cakupan dataset/kolom/token; untuk bug checkbox relasi, pastikan perbaikan web terpasang dan buat token baru setelah memuat ulang halaman. |
| HTTP 503 | Pengelola memeriksa `AI_API_ENABLED=true` dan cache Laravel pada hosting. Mengubah environment WSL tidak mengaktifkan API server. Lihat [deployment](AI_DEPLOYMENT.md). |
| HTTP 422 / 429 | Periksa schema, operator serta kedua batas tanggal / tunggu sesuai `Retry-After`. |
| TLS, redirect, atau timeout | Periksa jaringan WSL, domain akhir, dan sertifikat HTTPS. Jangan menonaktifkan verifikasi TLS. |
| Error dependency native/platform | Pastikan Node Linux dan jalankan `npm ci` dalam checkout Linux terpisah, lalu build/test. |
| `node`/`codex` tidak ditemukan | Periksa instalasi dan PATH Linux; jangan mengganti dengan executable Windows untuk mengikuti panduan ini. |
| `vite build` / `Missing script: "test"` | Kembali ke folder `tools/summary-assistant`, periksa `npm prefix`. |
| Dokumen tidak terlihat di Windows | File berada pada home Linux. Gunakan path hasil tool atau buka melalui Explorer WSL, bukan mencari di home Windows. |
| Batas akun AI tercapai | Tunggu pemulihan batas sesuai paket; konektor tidak menggunakan fallback API model berbayar. |

Jika perlu bantuan, kirim hasil pemeriksaan URL/`tokenTerisi`, pesan `CLIENT_OK`/error, serta `command -v node codex`. Jangan kirim token, isi file environment, atau dump seluruh environment.

## Rujukan

- [Konfigurasi MCP resmi Codex](https://learn.chatgpt.com/docs/extend/mcp?surface=cli): konfigurasi stdio, `env_vars`, serta lokasi config default.
- [Autentikasi Codex](https://learn.chatgpt.com/docs/auth).
- [Panduan PowerShell](AI_MCP_WINDOWS.md), [analisis dan batas dataset](AI_CODEX_SETUP.md), [deployment API](AI_DEPLOYMENT.md).
