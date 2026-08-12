# Runbook Deployment Sorong dan Kupang

## Prinsip

Gunakan satu revision source code, tetapi dua deployment yang sepenuhnya terpisah. Jangan berbagi database, APP_KEY, storage, domain, cache prefix, queue, atau session antara Sorong dan Kupang. Sorong mempertahankan Monev nonaktif; Kupang mengaktifkannya setelah data master dan permission siap.

## Konfigurasi minimum per deployment

| Konfigurasi | Sorong | Kupang |
|---|---|---|
| `APP_URL` / domain | Domain Sorong | Domain Kupang |
| `APP_KEY` | Key khusus Sorong | Key khusus Kupang |
| `DB_*` | Database Sorong | Database Kupang |
| `FILESYSTEM_DISK` dan volume `storage` | Volume Sorong | Volume Kupang |
| `CACHE_PREFIX` | Prefix unik Sorong | Prefix unik Kupang |
| `SESSION_COOKIE` | Cookie unik Sorong | Cookie unik Kupang |
| `QUEUE_CONNECTION` / namespace worker | Worker Sorong | Worker Kupang |
| `ORGANIZATION_*` fallback | Identitas Sorong | Identitas Kupang |
| `ORGANIZATION_MONEV_ENABLED` fallback | `false` | `false` saat rollout; aktifkan dari menu setelah verifikasi |

Nilai pada Pengaturan Organisasi mengoverride fallback `ORGANIZATION_*`. Pastikan Redis/database cache, session, dan queue juga memakai database/prefix berbeda bila infrastrukturnya satu host.

## Rollout aman

1. Backup database dan seluruh volume `storage` deployment target.
2. Deploy source pada maintenance window. Jangan menjalankan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau seed yang menebak relasi historis.
3. Jalankan `php artisan migrate --force`. Migration hanya menambah tabel/kolom/indeks.
4. Jalankan `php artisan optimize:clear`, lalu restart worker queue deployment itu saja.
5. Di Pengaturan Organisasi, isi nama, singkatan, logo, dan alamat. Biarkan Monev nonaktif.
6. Pastikan master Unit Kerja dan flag Keterlibatan benar. Kategori export tidak memakai pembandingan nama.
7. Tetapkan Koordinator berperiode pada setiap Unit Kerja yang akan diekspor. Lengkapi nama, NIP, jabatan, dan tanda tangan JPEG/PNG.
8. Berikan permission `manage_all_report_evaluations` kepada pimpinan yang memerlukan akses global dan `export_report_evaluations` kepada pelaksana export. Role admin/super-admin tetap mengikuti policy existing.
9. Jalankan smoke test Report lama, Excel lama, PDF publik, tambah/edit/riwayat evaluasi, dan satu export Monev.
10. Untuk Kupang, aktifkan Monev dari Pengaturan Organisasi. Untuk Sorong, biarkan nonaktif.

## Verifikasi setelah rollout

- Report lama dapat dibuka tanpa evaluasi dan tanpa relasi hasil tebakan.
- UI Evaluasi tidak terlihat saat flag nonaktif.
- Unit evaluasi hanya berasal dari Unit Kerja Report.
- Audit create/update menyimpan editor dan diff; no-op tidak menambah revision.
- Koordinator hanya melihat unit aktifnya; owner/follower dan akses global sesuai policy.
- Export Monev memuat Report overlap atau evaluasi periode, tidak tergantung checkbox, dan Excel lama/PDF tidak berubah.
- File tanda tangan ada pada disk privat deployment dan tidak tersedia melalui URL publik.

## Rollback

Rollback aplikasi terlebih dahulu ke revision sebelumnya dan nonaktifkan Monev dari Pengaturan Organisasi. Migration aditif boleh dibiarkan untuk mempertahankan data. Jangan menjalankan `migrate:rollback` pada produksi tanpa backup dan persetujuan eksplisit karena `down()` akan menghapus tabel/kolom fitur beserta data Monev. Jika rollback schema benar-benar diperlukan, ekspor `organization_settings`, `work_unit_coordinators`, `report_evaluations`, `report_evaluation_revisions`, serta `users.coordinator_signature_path`, lalu lakukan pada maintenance window yang tervalidasi.
