# Rencana Summary: API Readonly, AI di Laptop, dan Dokumen Lokal

Tanggal: 20 Oktober 2026. Dokumen ini menjadi acuan implementasi dan audit kesiapan. Riwayat pekerjaan branding di bawah tetap dipertahankan.

## 1. Tujuan dan batas pekerjaan

Pengguna memberikan permintaan berbahasa alami melalui Codex CLI atau klien AI yang kompatibel. AI membaca data Summary yang diizinkan, memahami isinya, mengelompokkan kegiatan, dan menyusun jawaban atau dokumen sesuai permintaan.

What, IKU, When, dan How hanyalah contoh. Kolom lain dan relasi dapat digunakan jika tersedia dalam katalog API dan diizinkan untuk pengguna tersebut. Permintaan melalui prompt tidak dapat memperluas izin server.

- Data kegiatan, master, relasi, evaluasi, dan lampiran pada Summary hanya dibaca oleh konektor AI.
- Pengelolaan token dilakukan pengguna melalui antarmuka Summary yang terautentikasi; tidak disediakan sebagai tool AI.
- Pencatatan akses, waktu penggunaan token, dan konfigurasi izin merupakan data operasional. Readonly berarti tidak ada perubahan pada data bisnis, bukan larangan seluruh penulisan operasional server.
- File XLSX, MD, PDF, PPTX, dan DOCX dibuat di laptop. Output awal berupa jawaban terminal; file hanya dibuat ketika diminta.
- Shared hosting menjalankan Laravel dan database yang sudah ada. Tidak membutuhkan proses model AI, Node.js permanen, worker dokumen, atau vector database di hosting.
- SSO, dashboard, input laporan, serta ekspor lama harus tetap berfungsi.

## 2. Kondisi proyek dan asumsi

Model `Report` yang diperiksa mempunyai relasi ke penyusun, pengikut, IKU, tim, unit kerja, keterlibatan, dokumentasi, dan evaluasi. ERD utama tersedia di `docs/ERD.md`.

Workspace sudah memuat draf implementasi API, pengelolaan token, konektor MCP lokal, pengujian, dan dokumentasi. Draf tersebut harus diaudit terhadap rencana ini. Keberadaan file bukan bukti seluruh fitur siap produksi. Hindari membangun ulang komponen yang sudah sesuai dan jangan menghapus perubahan kerja lain.

Asumsi awal:

1. Hosting mendukung versi PHP dan database aplikasi saat ini, HTTPS, serta penerusan header Authorization.
2. Laptop dapat menjalankan Codex CLI dan runtime konektor lokal. Rekomendasi awal Node.js 22.13 atau lebih baru yang kompatibel dengan lockfile.
3. Pengguna mempunyai akun Summary dan ChatGPT Plus sendiri.
4. Data yang dikirim ke model dibatasi pada kolom yang dibutuhkan dan diizinkan. Model tetap dijalankan penyedia AI; memasang CLI di laptop tidak membuat pemrosesan model menjadi offline.
5. Kredensial dan kemampuan hosting sebenarnya harus diverifikasi sebelum aktivasi. Rencana tidak menganggap deployment sudah dilakukan.

## 3. ChatGPT Plus tanpa pembelian token tambahan

Gunakan Codex CLI dengan **Sign in with ChatGPT**. Konektor Summary hanya memakai token akses Summary; konektor tidak memakai OpenAI API key atau memanggil API model secara langsung.

Bedakan dua kredensial:

| Kredensial | Penerbit | Fungsi |
|---|---|---|
| Login ChatGPT | OpenAI | Menggunakan Codex sesuai akses dan batas paket Plus |
| Token Summary | Aplikasi Summary | Membaca data sesuai cakupan token dan izin akun |

Codex tersedia melalui paket Plus menurut dokumentasi resmi yang diperiksa pada tanggal dokumen ini. Batas pemakaian, model yang tersedia, dan aturan paket dapat berubah. Ketika kuota tidak mencukupi, pengguna menunggu reset atau memperkecil pekerjaan; sistem tidak membeli kredit atau beralih ke API berbayar.

Konfigurasi lokal yang direncanakan:

```toml
forced_login_method = "chatgpt"

[mcp_servers.summary]
command = "node"
args = ["/absolute/path/summary-assistant/dist/index.js"]
env_vars = ["SUMMARY_BASE_URL", "SUMMARY_API_TOKEN", "SUMMARY_OUTPUT_DIR"]
tool_timeout_sec = 180
```

Token diteruskan melalui environment atau penyimpanan kredensial lokal; jangan ditulis dalam prompt, repository, screenshot, atau dokumen laporan. Jalankan `codex login` dan `codex login status` secara manual di laptop pengguna.

Kemampuan memahami bahasa, menyusun narasi, dan memilih pendekatan dapat berbeda antarmodel. Jangan mengunci sistem pada nama atau urutan kecerdasan model tertentu. Jumlah laporan, agregasi, izin, dan kelengkapan data harus tetap ditentukan program yang sama pada semua model.

Plus bukan kredensial umum untuk Antigravity, Claude, atau OpenCode. Klien lain dapat memakai API Summary atau konektor MCP jika mendukungnya, tetapi akses model dan biaya masing-masing mengikuti penyedianya. Jalur utama tanpa pembelian tambahan adalah Codex CLI dengan login ChatGPT.

Referensi resmi:

- [Authentication](https://developers.openai.com/codex/auth)
- [Pricing dan batas penggunaan](https://developers.openai.com/codex/pricing)
- [Dukungan MCP](https://developers.openai.com/codex/mcp)

## 4. Arsitektur dan pembagian tanggung jawab

```mermaid
flowchart LR
    U[Pengguna] --> CLI[Codex CLI di laptop]
    CLI <--> MODEL[Model melalui akun ChatGPT Plus]
    CLI <--> MCP[Konektor MCP lokal]
    MCP -->|HTTPS dan token Summary| API[API readonly Laravel]
    subgraph Hosting[Shared hosting]
        API --> AUTH[Token, izin, kolom, dan cakupan laporan]
        AUTH --> READ[Query baca tervalidasi]
        READ --> DB[(Database Summary)]
    end
    MCP --> CACHE[Dataset sementara di laptop]
    CACHE --> AGG[Hitungan dan pengelompokan deterministik]
    AGG --> CLI
    AGG --> FILE[Renderer dokumen lokal]
    CLI -->|Narasi dengan sumber| FILE
    FILE --> OUT[XLSX / MD / PDF / PPTX / DOCX]
```

API menentukan data yang boleh terlihat. Konektor mengambil halaman data, memeriksa kelengkapan, dan menghitung angka. AI memahami permintaan dan menyusun penjelasan berdasarkan sumber. Renderer menghasilkan format dokumen; tidak memerlukan layanan konversi berbayar.

## 5. ERD yang relevan

Diagram berikut adalah bagian ERD yang berkaitan dengan akses AI. Tabel bisnis tetap memakai struktur lama; `ai_access_grants` menjadi metadata pembatas token. Relasi polymorphic Sanctum ke pengguna disederhanakan pada diagram.

```mermaid
erDiagram
    USERS ||--o{ REPORTS : menyusun
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : memiliki_token
    PERSONAL_ACCESS_TOKENS ||--o| AI_ACCESS_GRANTS : dibatasi
    REPORTS ||--o{ INDICATOR_REPORTS : memiliki
    INDICATORS ||--o{ INDICATOR_REPORTS : mengelompokkan
    REPORTS ||--o{ REPORT_TEAMS : memiliki
    TEAMS ||--o{ REPORT_TEAMS : mengelompokkan
    REPORTS ||--o{ REPORT_WORK_UNIT : memiliki
    WORK_UNITS ||--o{ REPORT_WORK_UNIT : mengelompokkan
    REPORTS ||--o{ REPORT_USERS : diikuti
    USERS ||--o{ REPORT_USERS : mengikuti
    INVOLVEMENTS o|--o{ REPORTS : keterlibatan
    REPORTS ||--o{ DOCUMENTATIONS : metadata_lampiran
    REPORTS ||--o{ REPORT_EVALUATIONS : dievaluasi
    WORK_UNITS ||--o{ REPORT_EVALUATIONS : unit_evaluasi

    AI_ACCESS_GRANTS {
        bigint id PK
        bigint personal_access_token_id FK,UK
        string report_scope
        json datasets
        json fields
        timestamp created_at
        timestamp updated_at
    }
    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token_hash UK
        text abilities
        timestamp expires_at
        timestamp last_used_at
    }
    REPORTS {
        bigint id PK
        bigint user_id FK
        bigint involvement_id FK
        string slug UK
        text what
        text why
        date when
        date tanggal_selesai
        text where
        text who
        text how
        string total_peserta
        string total_wanita
    }
```

`token_hash` di diagram menjelaskan isi kolom Sanctum `token`: hanya hash disimpan. Jangan membuat kolom Sanctum baru bernama `token_hash`. Dokumentasi adalah hasOne pada model, tetapi database lama tidak menjamin unik per laporan; diagram menunjukkan kardinalitas penyimpanan. Kunci primer pivot dan indeks mengikuti migration nyata, bukan asumsi diagram ini.

Tidak perlu tabel baru untuk menyimpan percakapan, jawaban AI, atau dokumen keluaran di hosting. Evaluasi dan penetapan koordinator tetap mengikuti kebijakan Monev aplikasi.

## 6. Model izin dan jaminan readonly

Izin efektif merupakan irisan:

**izin akun saat ini ∩ cakupan laporan token ∩ dataset token ∩ kolom token.**

Contoh: akun boleh membaca semua laporan, tetapi token dibatasi pada laporan milik sendiri/diikuti dan kolom What serta tanggal. API tetap hanya mengembalikan bagian itu. Prompt meminta kolom How tidak mengubah pembatasan; pengguna harus mengubah akses melalui UI yang sah atau menerbitkan token baru.

Ketentuan implementasi:

1. Permission eksplisit `use_ai_api`; tidak diberikan otomatis kepada semua pengguna atau lewat bypass Gate admin.
2. Token khusus ability `ai:read`, harus mempunyai grant, masa berlaku, serta pemilik aktif.
3. Scope awal `own_related`; opsi `account` berarti mengikuti hak baca akun, bukan otomatis seluruh organisasi.
4. Token AI tidak dapat digunakan pada API lama atau route web yang dapat mengubah data. Session browser dan token wildcard lama tidak memenuhi autentikasi AI.
5. Respons hanya serialisasi field allowlist. Password, NIP, email, tanda tangan, kredensial, dan path privat tidak ikut terbuka secara implisit. Identitas orang hanya nama/ID yang memang diizinkan.
6. Field yang tidak diizinkan juga tidak dapat dipakai untuk filter, sort, search, atau agregasi yang membocorkan nilainya.
7. Token tidak menyediakan endpoint pembuatan, perubahan, penghapusan, upload, raw SQL, atau eksekusi perintah server.
8. Lampiran awal hanya metadata aman. Mengunduh atau membaca isi berkas adalah kemampuan terpisah yang membutuhkan desain izin sendiri.
9. Izin diperiksa pada setiap permintaan. Pencabutan permission atau token segera memutus akses server berikutnya; file yang sudah diekspor di laptop tidak dapat ditarik kembali oleh server.
10. Evaluasi memerlukan izin Monev dan unit yang sesuai; laporan terhapus tidak muncul melalui relasi evaluasi.

UI manusia: pilih nama token, dataset, kolom, scope, dan masa berlaku 7/30/90 hari. Default 30 hari, scope `own_related`, kolom What/tanggal mulai/tanggal selesai. Token plaintext tampil sekali dan dapat dicabut pemiliknya.

## 7. Kontrak API

Prefix `/api/ai/v1`. Semua endpoint data hanya GET/HEAD, dengan bearer token Summary.

| Endpoint | Kegunaan |
|---|---|
| `/capabilities` | Kemampuan efektif, scope, dan batas untuk token aktif |
| `/schema` | Kolom, tipe, relasi, operator, dan deskripsi yang boleh dipakai |
| `/reports` | Daftar laporan dengan proyeksi, filter, include, sort, dan pagination |
| `/reports/{id}` | Detail satu laporan yang masih berada dalam scope |
| `/reports/{id}/followers` | Pengikut dengan pagination dan izin identitas |
| `/references/{dataset}` | Master IKU, tim, unit kerja, atau keterlibatan yang diizinkan |
| `/evaluations` | Evaluasi dengan policy Monev dan batas unit |

Dokumentasikan tipe input/output, enum, query, pagination, dan format error dalam OpenAPI. ID numerik pada API AI tidak mengubah route slug lama.

Query minimum: `fields`, `include`, periode awal/akhir, mode tanggal, filter field/relasi, pencarian, pengurutan stabil, `page`, `per_page`. Nama parameter dan operator harus cocok dengan OpenAPI serta implementasi; contoh parameter tidak boleh menjadi fitur yang hanya ada di dokumentasi.

- Daftar memakai page size 25, maksimum 100. Relasi nested dibatasi 25 dan menyatakan `has_more` serta cara membaca sisanya.
- Batasi 60 request/menit/token dan 120 request/menit/pengguna; tangani 429 dan `Retry-After` di konektor.
- Periode selalu pasangan awal/akhir. Q3 2026 berarti 1 Juli–30 September 2026, berdasarkan tanggal kegiatan dan zona waktu aplikasi.
- Default mode overlap: `when <= akhir` dan `COALESCE(tanggal_selesai, when) >= awal`. Sediakan mode berdasarkan tanggal mulai jika kontrak mendukungnya.
- Respons memuat `data`, metadata halaman/jumlah, request ID, serta penanda sumber. Jangan mengklaim snapshot transaksi sepanjang pagination; perubahan total atau duplikasi yang terdeteksi harus dilaporkan.
- Kesalahan konsisten: 401 token tidak sah, 403 akses ditolak, 404 data tidak terlihat/tidak ada, 422 query tidak valid, 429 batas akses.
- Audit mencatat pemilik/token ID, endpoint, status, durasi, dan jumlah hasil. Jangan mencatat bearer token atau isi laporan. Gunakan private/no-store untuk respons data.

## 8. Alur permintaan nyata

```mermaid
sequenceDiagram
    actor U as Pengguna
    participant C as Codex CLI
    participant M as MCP lokal
    participant A as API Summary
    participant D as Database
    participant R as Renderer lokal
    U->>C: Kelompokkan kegiatan berdasarkan IK pada triwulan III 2026
    C->>M: Baca capabilities dan schema
    M->>A: GET dengan token Summary
    A-->>M: Kolom, scope, dan relasi yang diizinkan
    alt IK belum jelas
        C->>U: Konfirmasi apakah IK berarti IKU yang tersedia
        U-->>C: Ya, gunakan IKU
    end
    C->>M: Siapkan dataset dengan periode dan field yang diperlukan
    loop Sampai lengkap atau mencapai batas
        M->>A: GET laporan berikutnya
        A->>A: Periksa token, policy, field, dan scope
        A->>D: Query baca yang tervalidasi
        D-->>A: Baris dalam scope
        A-->>M: Data, pagination, dan metadata sumber
    end
    M->>M: Dedup ID, cek kelengkapan, hitung per IKU
    M-->>C: Ringkasan angka dan sumber, lengkap atau parsial
    C-->>U: Jawaban berdasarkan data yang tersedia
    opt Pengguna meminta dokumen
        U->>C: Buatkan Excel dan PDF
        C->>R: Dataset, agregasi, narasi dengan source ID
        R-->>U: File lokal dan lokasi penyimpanan
    end
```

Aturan analisis contoh:

- Jangan menyamakan IK dan IKU tanpa definisi dalam katalog atau konfirmasi bila ambigu.
- Kelompokkan berdasarkan relasi IKU asli. Kegiatan tanpa IKU masuk kelompok “Belum terkait”. Kelompok perkiraan AI harus diberi label terpisah dan tidak disimpan ke Summary.
- Satu laporan dengan dua IKU masuk dua kelompok. Total laporan unik dihitung dengan ID distinct; jumlah kelompok tidak boleh diklaim sebagai jumlah laporan unik.
- Total peserta merupakan jumlah angka yang valid pada laporan, bukan jumlah orang unik. String kosong/teks tidak dipaksa menjadi nol. Kolom yang tidak diambil ditandai “tidak diambil”, bukan “tidak tersedia” atau angka nol.
- Pilih field minimal yang diperlukan. Untuk analisis isi How, ambil How jika diizinkan. Jangan hanya mengambil What lalu mengarang isi How.
- Setiap kesimpulan harus dapat ditelusuri ke ID/tautan sumber dan filter periode yang digunakan. Daftar kegiatan bukan bukti pencapaian target IKU jika target, realisasi, dan rumus tidak tersedia.

## 9. Konektor lokal dan dokumen

Tool MCP meliputi discovery kemampuan/skema, pembacaan laporan/referensi/evaluasi/pengikut, persiapan dataset, analisis deterministik, dan pembuatan dokumen. Tidak ada tool untuk mengubah Summary.

Dataset sementara memakai ID lokal, proyeksi field yang benar-benar diambil, parameter query, waktu pengambilan, jumlah laporan unik, sumber, dan status kelengkapan. Default maksimum 5.000 laporan atau 50 MiB data terserialisasi, maksimal empat dataset, kedaluwarsa setelah 30 menit tidak digunakan.

Batas data tersebut bukan jaminan batas total RAM proses. Perhitungan harus mencakup dataset dalam proses pengambilan dan cache. Serialisasikan persiapan dataset, batasi antrean, dan hindari banyak proses pengambilan paralel yang masing-masing menahan 50 MiB. Batasi request HTTPS aktif dan ukuran setiap respons. Tolak redirect ke host lain.

Jika batas tercapai, query berubah selama pengambilan, atau relasi terpotong, hasil diberi label **parsial** dengan sebab dan cakupan. Pengguna dapat mempersempit periode atau meminta bagian berikutnya. Jangan melabeli hasil parsial sebagai seluruh data organisasi.

| Format | Isi dan pemeriksaan |
|---|---|
| XLSX | Ringkasan, rekap kelompok, data sumber, sumber, dan metadata; angka konsisten; teks tidak dieksekusi sebagai formula |
| MD | Judul, periode, metodologi singkat, rekap, narasi, keterbatasan, dan sumber |
| PDF | Laporan dengan tabel, nomor halaman, font tertanam, teks dan sumber terbaca |
| PPTX | Ikhtisar, rekap, temuan, keterbatasan, dan sumber; teks panjang dipecah agar tidak terpotong |
| DOCX | Laporan yang dapat diedit, heading, tabel, periode, sumber, dan catatan kelengkapan |

Gunakan renderer lokal yang dapat diuji, misalnya ExcelJS, PDFKit, PptxGenJS, dan docx. Pastikan versi dipin lewat lockfile dan diperiksa terhadap kerentanan yang relevan. Pembuatan file tidak menjamin kualitas otomatis: buka, parse, dan periksa hasil render.

Simpan di folder keluaran yang disetujui pengguna, default `~/Summary-outputs`. Sanitasi nama, cegah traversal/symlink keluar folder, jangan menimpa file lama. Pemecahan teks panjang wajib menjaga Unicode, termasuk emoji pada batas sel atau baris. Narasi hanya dapat merujuk source ID dalam dataset.

Perlakukan teks laporan sebagai data, bukan instruksi bagi agent. Isi laporan yang meminta membuka rahasia, mengubah akun, atau mengirim data tidak boleh dijalankan. Penulisan file lokal merupakan satu-satunya kemampuan tulis konektor dan dibatasi folder keluaran.

## 10. Tahapan implementasi dan kriteria penerimaan

Urutan berikut berlaku untuk komponen baru maupun audit draf yang sudah ada. Setiap tahap menghasilkan bukti verifikasi sebelum tahap bergantung padanya dilanjutkan.

| Tahap | Pekerjaan dan lokasi utama | Ketergantungan | Kriteria penerimaan dan verifikasi |
|---|---|---|---|
| 0 | Inventaris model, migration, policy, route SSO, grafik proyek, dan draf AI | Tidak ada | Catat baseline test/build dan diff; jangan menimpa pekerjaan lain; ERD sesuai sumber |
| 1 | Finalisasi kontrak di `docs/specs/AI_READONLY_API.md` dan `docs/api/ai-v1.openapi.yaml` | 0 | Semua route, field, operator, error, scope, serta contoh mempunyai definisi; validator OpenAPI dan tes route cocok |
| 2 | Audit migration grant, `AiAccessGrant`, token service dan middleware | 1 | Token valid bisa membaca satu endpoint; token lama/session/tanpa grant/expired ditolak; rollback flag aman |
| 3 | Audit registry, query, controller dan policy pada `app/Services/Ai/` | 2 | Field dan relasi tidak bocor; filter waktu/izin bekerja; data bisnis sebelum/sesudah sama; tes SQLite dan MySQL terisolasi |
| 4 | Audit halaman `AiAccess` dan view Filament | 2–3 | Pengguna berizin membuat token sekali tampil dan mencabut token sendiri; pengguna lain ditolak; UI desktop dan sempit terbaca |
| 5 | Audit client HTTPS dan dataset pada `tools/summary-assistant/src/` | 3 | Pagination lengkap/parsial benar, scope error jelas, retry terbatas, metadata actual projection benar, concurrency tidak melampaui batas pengambilan |
| 6 | Audit agregasi deterministik pada konektor | 5 | Fixture IKU ganda, tanpa IKU, data kosong, tanggal invalid, peserta invalid, dan field tidak diambil menghasilkan angka/status benar |
| 7 | Audit lima renderer dokumen lokal | 6 | Kelima format terbuka dan angka/sumber sama; Unicode utuh; tidak ada overwrite atau path escape; periksa tampilan PDF, DOCX, PPTX |
| 8 | Uji MCP nyata, HTTPS, Laravel, dan regresi aplikasi | 4–7 | Alur prompt uji sampai lima dokumen lolos memakai data sintetis; SSO lama tetap berfungsi; build, typecheck, test dan audit dicatat |
| 9 | Runbook laptop, deployment hosting, rollback, dan aktivasi bertahap | 8 | Pengguna lain dapat mengikuti dokumentasi; token dicabut memutus akses; hosting nyata diuji sebelum dinyatakan aktif |

Checkpoint setelah tahap 3: akses satu laporan benar-benar readonly dan dibatasi izin. Setelah tahap 6: jawaban angka dan sumber benar. Setelah tahap 8: dokumen dan alur teknis terverifikasi. Aktivasi produksi merupakan checkpoint tersendiri; hasil fixture tidak menggantikannya.

Tes kasus negatif wajib mencakup: pencabutan izin, token milik orang lain, akses endpoint tulis/web, pencarian kolom terlarang, relasi evaluasi lintas unit, laporan soft-deleted, wildcard pencarian literal, tanggal boolean/datetime sesuai tipe, rate limit, nested relation terpotong, pagination duplikat/berubah, response rusak, concurrent prepare, proyeksi tanpa peserta, dan teks Unicode panjang.

Temuan audit draf yang telah diselesaikan dalam implementasi lokal:

- Metadata dataset harus menggambarkan kolom yang diambil, bukan semua kolom yang diizinkan; hindari total peserta nol palsu.
- Batas dataset harus berlaku ketika beberapa panggilan persiapan masuk bersamaan, termasuk antrean dan data in-flight.
- Pemecahan teks panjang Excel/PPTX harus mempertahankan pasangan surrogate Unicode.

Ketiga temuan sudah memiliki regression test yang sebelumnya gagal dan kini lulus. Tes tambahan membuktikan cache dikeluarkan saat koleksi berjalan membutuhkan ruang. Review akhir memverifikasi sumber dan 16 tes konektor lulus. Bukti lengkap tercatat di `docs/AI_VERIFICATION.md`; hasil ini tidak berarti hosting sudah diaktifkan.

## 11. Deployment, aktivasi, dan rollback

1. Simpan backup dan catat baseline aplikasi; verifikasi versi hosting, HTTPS, dan Authorization header.
2. Deploy kode dengan `AI_API_ENABLED=false`. Jalankan hanya migration aditif yang ditinjau, bukan `migrate:fresh` pada database pengguna.
3. Tetapkan permission `use_ai_api` kepada akun yang memang ditunjuk; pastikan policy dataset tetap berlaku. Jangan membuka akses massal melalui seeder role default.
4. Aktifkan API untuk uji terbatas. Pengguna membuat token minimum melalui UI.
5. Di laptop, pasang dependency dari lockfile, build konektor, atur environment dan MCP, lalu login Codex menggunakan ChatGPT.
6. Uji schema, satu query kecil, satu pengelompokan, satu file, lalu seluruh format. Uji akun/field yang ditolak dan pencabutan token.
7. Setelah bukti hosting dan laptop tercatat, nyatakan fitur siap digunakan pada cakupan yang diuji.

Rollback awal: nonaktifkan feature flag dan cabut token terkait. Jangan menghapus tabel bisnis atau memaksa migration down yang menghilangkan konfigurasi akses. Bersihkan cache konfigurasi sesuai runbook Laravel dan verifikasi aplikasi lama tetap berjalan.

Dokumen deployment harus membedakan hasil pengujian lokal, hasil fixture, dan hasil hosting sebenarnya. Kegagalan pemeriksaan lama yang sudah ada dicatat sebagai baseline; jangan memformat atau mengubah modul lain hanya untuk membuat seluruh pemeriksaan tampak hijau.

Keputusan pengguna pada 20 Oktober 2026: pekerjaan sesi ini selesai pada persiapan lokal dan commit. Tidak ada push atau aktivasi hosting. Tahap deployment di atas menjadi runbook untuk pekerjaan berikutnya; bukan syarat aktivasi yang dijalankan pada sesi ini. Login akun ChatGPT dan penerbitan token pribadi dilakukan pengguna mengikuti panduan, tanpa menyimpan kredensial dalam commit.

## 12. Definisi selesai dan instruksi untuk Codex

Sesuai batas tugas terbaru dari pengguna, sesi ini selesai setelah implementasi serta verifikasi lokal dikomit tanpa push. Butir aktivasi dan pemeriksaan hosting berikut tetap menjadi pedoman pekerjaan deployment terpisah.

- API hanya memberikan data dalam irisan izin, menolak seluruh operasi tulis bisnis, dan tidak merusak SSO lama.
- Pengguna dapat mengatur token sendiri sesuai izin, dengan expiry dan pencabutan yang efektif.
- Codex CLI membaca data melalui MCP memakai login ChatGPT, tanpa API model key, pembelian kredit, atau fallback berbayar.
- Permintaan berdasarkan kolom/relasi yang berbeda bekerja sesuai katalog; ambigu dijelaskan dan akses yang ditolak tidak dilewati.
- Angka dihitung deterministik, sumber dapat diperiksa, hasil parsial dinyatakan, dan semua format dokumen terverifikasi.
- Batas pengambilan data dan output aman diuji, termasuk concurrent prepare dan Unicode panjang.
- OpenAPI, ERD, diagram alur, instruksi laptop, bukti pengujian, deployment, serta rollback tersedia dan konsisten.
- Status “kode siap”, “uji lokal lulus”, dan “hosting aktif” dilaporkan terpisah dengan bukti masing-masing.

Prompt pelaksanaan yang dapat dipakai pada sesi berikutnya:

> Baca AGENTS.md, tasks/plan.md, tasks/todo.md, dan spesifikasi AI Summary. Audit kode yang sudah ada sebelum menambahkan kode. Kerjakan tahap berurutan dengan regression test untuk temuan yang belum selesai. Pertahankan data bisnis readonly, izin per akun/token/kolom/relasi, SSO lama, shared hosting tanpa model AI, serta Codex CLI login ChatGPT tanpa API key atau fallback berbayar. Catat hasil aktual setiap tahap. Jangan menjalankan migration destruktif atau menganggap fixture sebagai bukti deployment produksi. Laporkan status implementasi, pengujian lokal, dan aktivasi hosting secara terpisah.

---

# Completed: Branding Organisasi dan Perilaku Keterlibatan

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
