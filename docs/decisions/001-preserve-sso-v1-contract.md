# ADR-001: Pertahankan Kontrak SSO v1 Selama Hardening

## Status

Accepted

## Date

2026-08-08

## Context

Summary menjadi identity provider berbasis Sanctum untuk beberapa website organisasi. Perubahan field respons, aturan token, atau tabel user dapat memutus consumer yang tidak dideploy bersamaan. Audit pasca-upgrade juga menemukan brute-force login, user nonaktif yang masih dapat login, dan tidak adanya mekanisme revoke current token.

## Decision

Kontrak sukses `POST /api/login` dan `GET /api/user` dipertahankan. Hardening yang tidak memutus consumer ditambahkan secara additive:

- named rate limiter pada login;
- penolakan token baru untuk user nonaktif dengan respons error legacy;
- endpoint `POST /api/logout` untuk mencabut current token;
- characterization tests untuk payload dan kompatibilitas token lama.

Expiry global, token abilities, dan allowlist field user tidak diubah pada versi API lama. Perubahan tersebut harus diperkenalkan melalui versi API baru dan masa transisi consumer.

## Alternatives Considered

### Mengubah respons API lama langsung

Ditolak karena field saat ini sudah dipakai lintas website dan tidak tersedia deployment atomik untuk semua consumer.

### Mengaktifkan expiry dan scope global langsung

Ditolak pada release ini karena dapat membatalkan token lama atau menolak operasi consumer tanpa inventaris kebutuhan scope.

### Tidak melakukan hardening

Ditolak karena endpoint tanpa throttling dan user nonaktif yang tetap dapat login merupakan risiko keamanan nyata.

## Consequences

- Consumer lama tetap bekerja.
- Login lebih tahan brute force dan deaktivasi user berlaku untuk token baru.
- Organisasi perlu menyusun API v2 untuk minimisasi field, expiry, abilities, serta kebijakan revoke token lama ketika user dinonaktifkan.
- Token yang sudah aktif sebelum user dinonaktifkan belum otomatis dicabut; proses administratif perlu melakukan revoke sampai kebijakan API v2 tersedia.