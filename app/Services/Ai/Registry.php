<?php

namespace App\Services\Ai;

use App\Models\Indicator;
use App\Models\Involvement;
use App\Models\Team;
use App\Models\WorkUnit;

class Registry
{
    public const REFERENCES = [
        'indicators' => [Indicator::class, 'indicators'],
        'teams' => [Team::class, 'teams'],
        'work_units' => [WorkUnit::class, 'workUnits'],
        'involvements' => [Involvement::class, 'involvement'],
    ];

    public const LABELS = [
        'reports' => 'Laporan', 'indicators' => 'IKU', 'teams' => 'Tim',
        'work_units' => 'Unit kerja', 'involvements' => 'Keterlibatan',
        'people' => 'Penulis dan pengikut', 'evaluations' => 'Evaluasi Monev',
        'documentation_metadata' => 'Metadata dokumentasi',
    ];

    public function definitions(): array
    {
        return [
            'reports' => [
                'no_st' => ['string', 'Nomor surat tugas'], 'what' => ['text', 'Nama kegiatan'],
                'why' => ['text', 'Latar belakang dan tujuan'], 'when' => ['date', 'Tanggal mulai'],
                'tanggal_selesai' => ['date', 'Tanggal selesai'], 'where' => ['text', 'Lokasi kegiatan'],
                'who' => ['text', 'Pihak yang terlibat'], 'how' => ['text', 'Hasil dan pelaksanaan'],
                'penyelenggara' => ['string', 'Penyelenggara'], 'total_peserta' => ['string', 'Jumlah kehadiran dilaporkan, bukan orang unik'],
                'total_wanita' => ['string', 'Jumlah kehadiran perempuan'], 'kode' => ['string', 'Kode laporan'],
                'created_at' => ['datetime', 'Waktu dibuat'],
            ],
            'indicators' => ['id' => ['integer', 'ID IKU'], 'nomor_iku' => ['string', 'Nomor IKU'], 'nama_iku' => ['string', 'Nama IKU'], 'tahun_iku' => ['string', 'Tahun IKU'], 'slug_iku' => ['string', 'Slug IKU'], 'status_iku' => ['string', 'Status IKU']],
            'teams' => ['id' => ['integer', 'ID tim'], 'nama_tim' => ['string', 'Nama tim'], 'nomor_tim' => ['string', 'Nomor tim'], 'slug_tim' => ['string', 'Slug tim'], 'status_tim' => ['string', 'Status tim']],
            'work_units' => ['id' => ['integer', 'ID unit'], 'name' => ['string', 'Nama unit'], 'unit' => ['string', 'Jenis unit'], 'status' => ['string', 'Status unit']],
            'involvements' => ['id' => ['integer', 'ID keterlibatan'], 'name' => ['string', 'Nama keterlibatan'], 'status' => ['string', 'Status'], 'is_lprl_organizer' => ['boolean', 'Penyelenggara internal']],
            'people' => ['id' => ['integer', 'ID orang terkait'], 'name' => ['string', 'Nama orang terkait']],
            'evaluations' => [
                'id' => ['integer', 'ID evaluasi'], 'report_id' => ['integer', 'ID laporan'], 'work_unit_id' => ['integer', 'ID unit'],
                'period' => ['date', 'Periode evaluasi'], 'rencana_pelaksanaan' => ['text', 'Rencana pelaksanaan'],
                'kendala' => ['text', 'Kendala'], 'saran_rekomendasi' => ['text', 'Saran dan rekomendasi'],
                'tindak_lanjut' => ['text', 'Tindak lanjut'], 'keterangan' => ['text', 'Keterangan'],
                'evidence_links' => ['array', 'Tautan bukti; tidak diunduh otomatis'],
            ],
            'documentation_metadata' => ['kinds' => ['array', 'Jenis lampiran tersedia; tanpa path atau URL']],
        ];
    }

    public function operators(string $type): array
    {
        return match ($type) {
            'text', 'string' => ['eq', 'in', 'contains', 'is_null'],
            'integer', 'date', 'datetime' => ['eq', 'in', 'gte', 'lte', 'is_null'],
            'boolean' => ['eq', 'is_null'],
            default => [],
        };
    }
}
