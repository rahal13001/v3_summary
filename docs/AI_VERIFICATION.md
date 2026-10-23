# Verifikasi Summary AI Readonly

Tanggal: 20 Oktober 2026. Cakupan akhir sesuai instruksi pengguna: persiapan dan verifikasi lokal sampai commit, tanpa push atau aktivasi shared hosting.

Branch lokal: `codex/summary-readonly-ai`. Implementasi API terdapat pada commit `8e33a9c`; konektor dan dokumen lokal pada commit `89042f6`. Dokumentasi dan checklist disimpan dalam commit terpisah sesudah kedua commit implementasi.

## Hasil pemeriksaan

| Pemeriksaan | Bukti aktual | Hasil |
|---|---|---|
| Seluruh tes Laravel | `php artisan test` | 155 tes, 698 assertion lulus |
| Autoload PHP | Composer `dump-autoload --optimize --strict-psr --no-scripts` melalui PHP Laragon | Lulus, 11.951 class; tanpa menjalankan script atau migration aplikasi |
| Format PHP fitur AI | Pint pada 21 file PHP yang ditambah/diubah | Lulus |
| Format seluruh repository | `php vendor/bin/pint --test --format=json` | Gagal pada 73 file lama; seluruh 73 file identik byte dengan HEAD sebelum commit fitur |
| Kompatibilitas MySQL | `tests/Fixtures/ai-mysql.php` dengan opt-in localhost | Lulus MySQL 8.4.3 pada database acak baru; database uji dihapus pada finally |
| Tes konektor lokal | `npm test` di `tools/summary-assistant` | 16 tes lulus, tanpa skip |
| TypeScript | `npm run typecheck` dan `npm run build` | Lulus |
| Frontend aplikasi | `npm run build` dari root | Lulus, 70 module; tidak mengubah aset build terlacak |
| Dependency konektor, termasuk development | `npm audit --audit-level=low` | 0 vulnerabilities pada audit terakhir |
| OpenAPI 3.1 | Swagger Parser pada kontrak, ditambah tes kesesuaian route Laravel | Lulus; tujuh path GET/HEAD |
| Protokol MCP | SDK client/server melalui transport nyata | Sepuluh tool ditemukan dan alur dataset/analisis/dokumen lulus |
| HTTPS dan kernel Laravel | `npm run smoke` dengan subprocess MCP, CA lokal, dan SQLite baru | Dua laporan Q3 2026, 25 kehadiran, lima format terbentuk |
| Integritas bisnis | Snapshot laporan sebelum/sesudah query pada tes SQLite dan MySQL | Tidak berubah akibat pembacaan API |
| UI token | Browser pada fixture lokal, ditambah tes Livewire | Default scope/kolom benar, buat token, sembunyikan setelah reload, cabut token; tidak membaca token melalui browser automation |
| Layout UI | Pemeriksaan desktop dan viewport sempit; console runtime | Form tetap terbaca, satu kolom pada viewport sempit, tanpa overflow halaman; tidak ada error console pada pemeriksaan |
| Dokumen | Parse XLSX, PDF, XML DOCX/PPTX; render PDF dan konversi Office melalui LibreOffice | Angka/sumber konsisten dan halaman/slide sampel terbaca |

Formatter seluruh repository belum hijau. Masalah lama tersebut tidak diperbaiki karena berada di luar fitur AI. Pemeriksaan tidak dilemahkan, file tidak dikecualikan, dan tidak ada tes yang dinonaktifkan untuk menyembunyikan kegagalan ini.

## Cakupan keamanan yang diuji

- Token wildcard lama, session browser saja, token tanpa grant, mixed ability, dan token kedaluwarsa tidak diterima sebagai token AI.
- Permission eksplisit tidak dilewati oleh Gate yang permisif. Akun nonaktif, pencabutan permission/token, dan scope own/followed diperiksa.
- Token AI ditolak pada namespace API lama serta route web, termasuk ketika session browser tersedia.
- Proyeksi, filter, pencarian, pengurutan, include, referensi, dan evaluasi tunduk pada izin kolom/dataset.
- Evaluasi mengikuti unit dan koordinator aktif terbaru. Parent laporan yang soft-deleted tidak membuka data evaluasi.
- Pagination, batas nested follower, rate limit, period overlap, wildcard pencarian literal, boolean, serta ISO datetime diuji.
- Client menolak HTTP/redirect, memvalidasi envelope JSON, membatasi respons, serta menangani retry/403 dan kegagalan pembacaan body.
- Dokumen memvalidasi source ID, mempertahankan string Excel sebagai literal, menolak folder symlink, dan memakai direktori baru tanpa overwrite.

## Temuan review yang telah diperbaiki

1. **Proyeksi dataset:** daftar kolom berasal dari `fields`/`include` aktual. Kolom peserta yang hanya diizinkan tetapi tidak diambil tidak menghasilkan nol palsu.
2. **Pengambilan bersamaan:** hanya satu persiapan aktif, tanpa antrean. Koleksi berjalan mereservasi kapasitas terhadap cache. Tes memeriksa penolakan concurrent prepare, pelepasan slot saat gagal, dan pengeluaran cache sebelum halaman berikutnya.
3. **Unicode:** potongan sel XLSX dan baris/label PPTX menjaga pasangan surrogate UTF-16. Tes membaca kembali emoji pada batas sel/baris.
4. **Perhitungan:** nilai JSON bukan angka peserta; tanggal kalender tidak sah tidak menghasilkan kelompok triwulan; bucket kosong tidak bertabrakan dengan teks nyata.
5. **Relasi dan policy:** identitas orang mengikuti UserPolicy; nested relasi dibatasi dengan metadata parsial; koordinator lama tidak dapat membaca melalui assignment overlap.

Review keamanan terakhir tidak menemukan temuan material tambahan. Review lokal terakhir memverifikasi tiga perbaikan utama dan seluruh 16 tes konektor lulus. Review merupakan pelengkap bukti tes, bukan penggantinya.

## Artefak smoke terakhir

Fixture: `summary-ai-smoke-59a37fa5-d997-479a-8425-5b1a1ee4100a`, di bawah `storage/framework/testing/` yang diabaikan Git.

Data sintetis menghasilkan dua laporan unik dan 25 kehadiran untuk periode 1 Juli–30 September 2026. File XLSX, Markdown, PDF, PPTX, dan DOCX terbentuk melalui proses MCP stdio yang mengakses HTTPS dan kernel Laravel asli.

PDF asli mempunyai dua halaman. DOCX dikonversi menjadi satu halaman PDF. PPTX dikonversi menjadi delapan halaman PDF; slide judul, ringkasan angka, rekap, dan sumber diperiksa sebagai gambar. Konversi dilakukan dengan LibreOffice sementara untuk pemeriksaan; Office/LibreOffice bukan dependency pembuatan dokumen konektor.

Sumber pada file fixture menunjuk URL loopback pengujian, bukan domain produksi. File ini bukan laporan kegiatan organisasi dan tidak dikomit. Server fixture dihentikan setelah verifikasi; token fixture dibersihkan. Preview PDF memakai PDF.js yang diperbarui dan canvas dengan versi kompatibel.

## Batas kesiapan

- API tetap default nonaktif. Database bisnis lokal pengguna tidak dimigrasi dan tidak diterbitkan token untuk akun nyata.
- Tidak ada deployment shared hosting, push Git, atau perubahan konfigurasi SSH/akun ChatGPT.
- Login ChatGPT Plus dan penggunaan model pada akun pengguna belum dijalankan otomatis. Integrasi yang diuji adalah protokol MCP, konektor, HTTPS, Laravel, dan dokumen; ini tidak membuktikan kuota atau akses model akun tertentu.
- Penyedia hosting, sertifikat/domain nyata, penerusan Authorization, versi database hosting, dan kapasitas produksi masih harus diverifikasi ketika deployment diminta.
- Dataset 50 MiB membatasi ukuran data terserialisasi pada cache/koleksi, bukan total heap proses. Dokumen yang sudah tersimpan lokal tetap ada setelah token dicabut.
- Kemampuan narasi dan ketepatan interpretasi model tidak dijamin oleh tes deterministik. Pengguna tetap perlu meninjau narasi serta memastikan data yang diproses model sesuai izinnya.

Panduan berikutnya: [setup laptop dan Codex](AI_CODEX_SETUP.md), [deployment dan rollback](AI_DEPLOYMENT.md), [spesifikasi](specs/AI_READONLY_API.md), dan [rencana lengkap](../tasks/plan.md).

## Perbaikan checkbox relasi — 21 Oktober 2026

Bug pembuatan token direproduksi melalui browser pada fixture lokal: memilih kolom IKU mengisi `fields.indicators` sebagai boolean, sehingga validasi menghasilkan `Dataset atau kolom tidak diizinkan: indicators`. Livewire memperlakukan checkbox sebagai pilihan jamak hanya jika nilai awalnya berupa array. Sebelumnya hanya `fields.reports` yang diinisialisasi.

`AiAccess::mount()` sekarang menyiapkan array kosong untuk setiap dataset yang diizinkan akun. Pilihan awal laporan dan kolom tetap dipertahankan; dataset tambahan tidak otomatis dipilih. Pemeriksaan permission dan allowlist di TokenService tidak diubah.

Bukti verifikasi:

- Regression test gagal sebelum perbaikan karena state relasi bernilai null, kemudian lulus setelah perbaikan.
- Tes mencakup inisialisasi delapan dataset, penyimpanan seluruh pilihan kolom, serta pembacaan relasi IKU memakai token yang diterbitkan halaman.
- Nilai boolean yang dimanipulasi, kolom tidak dikenal, dan permission yang dicabut tetap ditolak tanpa menerbitkan token.
- Browser berhasil mencentang seluruh dataset/kolom yang tersedia dan membuat token. Token tersembunyi setelah reload; baris token tetap tersimpan. Console tidak memiliki error/warning pada pemeriksaan terakhir.
- `php artisan test --compact`: **157 tes, 726 assertion lulus**. Pint untuk kedua file PHP yang diubah juga lulus.
- Smoke MCP–HTTPS–Laravel pada fixture baru menghasilkan dua laporan, 25 kehadiran, dan lima format; perubahan ini tidak menyentuh kode konektor atau renderer.

Setelah kode web diperbarui di hosting, muat ulang halaman Akses AI dan buat token baru dengan dataset serta kolom relasi yang diperlukan. Ganti token pada environment laptop lalu mulai ulang Codex. Token lama tidak memperoleh cakupan tambahan secara otomatis. Perbaikan ini tidak membutuhkan migration atau rebuild konektor npm. Deployment tetap dilakukan pengguna melalui prosedur Git/SSH; pengujian perbaikan memakai data sintetis lokal.

## Tautan sumber laporan untuk browser — 23 Oktober 2026

Penyebab tautan yang keliru ada pada dua lapisan: serializer API mengisi `source.url` dengan endpoint API bernomor, sedangkan pengumpulan dataset konektor membuat ulang URL yang sama dari ID. Endpoint ini membutuhkan bearer token, bukan session browser; tanda kurung penutup yang ikut tersalin juga membuat path tidak cocok dengan route.

Perbaikan menggunakan halaman laporan Filament yang sudah ada (`/laporan-5w1h/{slug}`). Serializer memakai pembuat URL resource; konektor menormalisasi sumber daftar/detail ke halaman web pada base URL yang dikonfigurasi, termasuk subfolder. Dataset mempertahankan sumber yang sudah dinormalisasi. Slug tidak tersedia atau tidak aman mengarah ke daftar laporan. Instruksi MCP meminta model menggunakan `source.url`; Markdown yang dihasilkan memakai tautan eksplisit dengan pembatas URL.

Bukti verifikasi:

- Dua tes regresi Node gagal sebelum perubahan lalu lulus sesudahnya: normalisasi sumber pada detail/daftar/dataset, serta penolakan domain eksternal dan traversal pada tautan sumber.
- **18 tes konektor lulus**, termasuk isi sumber pada XLSX, MD, PDF, PPTX, DOCX dan protokol MCP. `npm run typecheck` serta `npm run build` lulus.
- **162 tes Laravel, 741 assertions lulus** pada SQLite in-memory. Lima tes sumber mencakup respons API, redirect login beserta intended URL, pembukaan laporan oleh pembaca berizin, penolakan akun nonaktif/tanpa permission, dan isolasi bearer AI dari login web.
- Pint untuk controller API dan tes sumber lulus. OpenAPI, tautan dokumentasi lokal, dan pemeriksaan whitespace juga lulus.
- Satu run awal PHP yang bertumpuk dengan run lain mengalami tabrakan rename cache compiled view Windows. Run PHPUnit terpisah dan suite penuh setelahnya lulus; tidak ada pengurangan assertion atau perubahan policy untuk meloloskan tes.

Tidak ada route publik baru, migration, perubahan policy, atau perubahan laporan bisnis. ID tetap tersedia bagi operasi internal API dan pencocokan sumber; menyembunyikan ID dari URL hasil bukan pengganti autentikasi/otorisasi. Koneksi browser memakai login serta izin akun web, bukan token API dalam tautan.

Perubahan belum dideploy atau dipush. Konektor laptop perlu diperbarui/dibangun ulang, Codex dimulai ulang, dan data diambil ulang. Konektor baru tetap dapat memperbaiki tautan dari respons server lama jika slug tersedia; server juga perlu diperbarui agar kontrak sumber benar bagi semua client. Dokumen lama tidak ditulis ulang otomatis. Graphify yang tersedia menghubungkan ViewReport dengan resource/policy tetapi mendahului fitur MCP; kode aktual dan tes dipakai untuk menelusuri lapisan API/konektor yang belum ada pada graph.
