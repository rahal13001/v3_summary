# Spec: Unit Kerja dan Keterlibatan Laporan

## Objective

Menambahkan dua dimensi organisasi pada laporan kegiatan LPRL Sorong:

1. Unit Kerja menunjukkan satu atau lebih kantor di bawah naungan organisasi deployment yang mengerjakan kegiatan.
2. Keterlibatan menunjukkan satu posisi organisasi deployment dalam kegiatan, misalnya Penyelenggara, Peserta, Sponsor, atau Pemberi Modal.

Pengguna utama adalah admin pengelola referensi dan writer penginput laporan. Fitur berhasil bila laporan baru selalu mencatat minimal satu Unit Kerja dan satu Keterlibatan, perilaku kolom Penyelenggara mengikuti konfigurasi Keterlibatan, dan laporan lama tetap dapat dibaca tanpa migrasi data tebakan.

## Confirmed Business Rules

- Unit Kerja berbeda dari Tim Kerja. Model, menu, tabel, relasi, dan filter Tim Kerja tetap dipertahankan.
- Satu Report mempunyai banyak Unit Kerja dan satu Unit Kerja dapat terhubung ke banyak Report.
- Satu Report mempunyai nol atau satu Keterlibatan pada data historis dan tepat satu Keterlibatan setelah laporan dibuat atau diedit melalui form.
- Laporan baru wajib memilih minimal satu Unit Kerja dan satu Keterlibatan.
- Laporan lama boleh mempunyai `involvement_id = null` dan tidak mempunyai Unit Kerja sampai diedit.
- Unit Kerja mempunyai `name`, `status`, dan satu `unit` dari pilihan tetap Satuan Pelayanan, Wilayah Kerja, atau Gerai Pelayanan.
- Keterlibatan mempunyai `name`, `status`, dan penanda boolean `is_lprl_organizer` agar jenis baru tidak bergantung pada pencocokan nama.
- Jika Keterlibatan bertanda `is_lprl_organizer`, form mengikuti mode Penyelenggara pada Pengaturan Organisasi: `locked`, `editable`, atau `manual`. Nama flag legacy dipertahankan untuk kompatibilitas.
- Jika Keterlibatan tidak bertanda tersebut, form menghapus nilai otomatis lama lalu mengaktifkan dan mewajibkan input Penyelenggara.
- Pergantian Keterlibatan selalu membersihkan nilai Penyelenggara yang tidak lagi sesuai.

## Data Model

### `work_units`

- `id`: primary key
- `name`: string
- `status`: string, nilai `active` atau `inactive`
- `unit`: string, nilai `satuan_pelayanan`, `wilayah_kerja`, atau `gerai_pelayanan`
- timestamps

Nama Unit Kerja harus unik dalam kategori unit yang sama. Kombinasi `name` dan `unit` diberi unique constraint.

### `report_work_unit`

- `report_id`: foreign key ke `reports`, cascade saat Report dihapus
- `work_unit_id`: foreign key ke `work_units`, restrict saat Unit Kerja masih digunakan
- unique composite `report_id`, `work_unit_id`

### `involvements`

- `id`: primary key
- `name`: unique string
- `status`: string, nilai `active` atau `inactive`
- `is_lprl_organizer`: boolean, default `false`
- timestamps

Seeder menyediakan nilai awal:

- Penyelenggara: aktif, `is_lprl_organizer = true`
- Peserta: aktif, `is_lprl_organizer = false`

### `reports`

- Tambah nullable foreign key `involvement_id` ke `involvements`.
- Foreign key menggunakan restrict delete agar klasifikasi laporan historis tidak hilang.
- Kolom `penyelenggara` lama tetap dipakai dan tidak diubah oleh migration.

## Report User Experience

- Tambahkan multi-select Unit Kerja pada bagian Ringkasan Kegiatan atau Pelaksanaan dan Peserta.
- Tambahkan single-select Keterlibatan sebelum kolom Penyelenggara.
- Pilihan baru hanya menampilkan master berstatus aktif.
- Nilai master tidak aktif yang sudah terhubung tetap dapat ditampilkan pada laporan historis.
- Unit Kerja dan Keterlibatan muncul pada detail Report.
- Daftar Report mendapat filter Unit Kerja dan Keterlibatan.
- Excel dan PDF menampilkan Unit Kerja serta Keterlibatan agar keluaran konsisten dengan detail Report.
- Perubahan konfigurasi master tidak mengubah Report lama secara retroaktif.

## Admin User Experience

- Tambahkan resource Unit Kerja dan Keterlibatan pada grup navigasi `Admin Area`.
- Form Unit Kerja memakai input nama, pilihan status, dan pilihan tunggal kategori unit.
- Form Keterlibatan memakai input nama, pilihan status, dan toggle `Organisasi sebagai penyelenggara/internal`.
- Tabel kedua resource dapat dicari dan difilter berdasarkan status; Unit Kerja juga dapat difilter berdasarkan kategori.
- Master yang masih dipakai laporan tidak boleh dihapus. Admin menonaktifkannya untuk menghentikan pemakaian baru.
- Resource mengikuti policy dan permission Filament Shield yang berlaku pada resource bisnis lain.

## Tech Stack

- PHP 8.3
- Laravel 13
- Filament 5
- Eloquent many-to-many dan belongs-to
- PHPUnit 12 dengan SQLite in-memory
- Filament Shield 4

## Commands

```text
Focused tests: php artisan test tests/Feature/ReportOrganizationDimensionsTest.php tests/Feature/ReportResourceFormTest.php tests/Feature/ReportOrganizationOutputsTest.php
Full tests:    php artisan test
PHP format:    vendor/bin/pint --dirty
Autoload:      composer dump-autoload --strict-psr
Frontend:      npm run build
```

Semua test database wajib memakai konfigurasi PHPUnit SQLite `:memory:`. Tidak boleh menjalankan migration atau test terhadap database aplikasi pada `.env`.

## Project Structure

```text
app/Models/                          Model WorkUnit, Involvement, dan relasi Report
app/Filament/Resources/              Resource admin dan integrasi ReportResource
app/Policies/                        Policy Shield untuk master baru
database/migrations/                 Tabel master, pivot, dan FK nullable Report
database/seeders/                    Nilai awal Penyelenggara dan Peserta
tests/Feature/                        Kontrak skema, relasi, form, dan resource
resources/views/pdf/                 Keluaran PDF laporan
app/Exports/                          Keluaran Excel laporan
docs/                                Dokumentasi PRD dan ERD terbaru
```

## Code Style

Ikuti pola Eloquent dan Filament yang sudah ada, dengan nama kode Inggris dan label UI Indonesia:

```php
public function workUnits()
{
    return $this->belongsToMany(
        WorkUnit::class,
        'report_work_unit',
        'report_id',
        'work_unit_id',
    );
}
```

- PSR-12 dan format Pint.
- Nama relationship memakai camelCase: `workUnits`, `involvement`.
- Label UI memakai `Unit Kerja`, `Keterlibatan`, dan `Penyelenggara`.
- Opsi status dan kategori didefinisikan satu kali pada model agar resource dan filter konsisten.

## Testing Strategy

- Migration test: tabel, kolom, foreign key yang dapat diuji lintas SQLite, dan unique composite tersedia.
- Model test: kardinalitas Report–Unit Kerja dan Report–Keterlibatan bekerja.
- Resource test: menu, field, opsi tetap, status aktif, filter, dan tampilan detail tersedia.
- Form behavior test: memilih penyelenggara internal mengisi `LPRL Sorong`; berpindah ke jenis eksternal menghapus nilai tersebut dan mewajibkan input manual.
- Compatibility test: Report lama dengan `involvement_id = null` dan tanpa pivot tetap dapat dibaca.
- Output test: Excel dan PDF memuat label Unit Kerja serta Keterlibatan.
- Regression: full PHPUnit suite, strict PSR-4, Pint pada file berubah, dan frontend build.

## Boundaries

### Always

- Gunakan migration aditif; jangan mengubah migration historis.
- Pertahankan Tim Kerja dan relasi `teams` tanpa perubahan semantik.
- Lindungi pasangan pivot dari duplikasi pada tingkat database.
- Tambahkan policy dan permission untuk resource baru.
- Uji hanya pada SQLite in-memory.
- Perbarui PRD, ERD, dan dokumentasi data Report.

### Ask First

- Mengisi otomatis Keterlibatan atau Unit Kerja pada Report lama.
- Mengubah nama organisasi selain penggunaan nilai baru `LPRL Sorong` pada fitur ini.
- Menambah kategori Unit Kerja ke luar tiga pilihan yang dikonfirmasi.
- Menambah dashboard agregasi baru di luar filter dan tampilan Report.

### Never

- Menjalankan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau operasi destruktif pada database aplikasi.
- Menebak klasifikasi Report lama dari teks Penyelenggara.
- Menghapus atau mengganti Tim Kerja.
- Mengandalkan pencocokan teks `Penyelenggara` untuk menentukan perilaku form.
- Mengedit `vendor/`, aset build pihak ketiga, atau data produksi.

## Success Criteria

1. Admin dapat mengelola Unit Kerja dan jenis Keterlibatan secara fleksibel serta menonaktifkan nilai lama.
2. Writer wajib memilih minimal satu Unit Kerja dan satu Keterlibatan saat membuat atau mengedit Report.
3. Report lama tanpa data baru tetap dapat dilihat tanpa error dan tanpa perubahan data otomatis.
4. Report menyimpan relasi many-to-many Unit Kerja tanpa pasangan duplikat.
5. Keterlibatan internal mengisi Penyelenggara dari singkatan organisasi aktif; keterlibatan lain mengosongkan lalu mewajibkan input manual.
6. Detail, filter daftar, Excel, dan PDF menyajikan dimensi baru secara konsisten.
7. Resource baru mengikuti otorisasi Filament Shield.
8. Focused tests dan full regression suite lulus pada SQLite in-memory.
9. Dokumentasi PRD dan ERD menggambarkan skema baru secara akurat.

## Open Questions

Tidak ada pertanyaan bisnis yang menghalangi implementasi. Spesifikasi telah disetujui sebelum coding.
