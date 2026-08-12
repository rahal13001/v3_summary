<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportEvaluation extends Model
{
    use HasFactory;

    public const CONTENT_FIELDS = [
        'rencana_pelaksanaan',
        'kendala',
        'saran_rekomendasi',
        'tindak_lanjut',
        'evidence_links',
        'keterangan',
    ];

    protected $fillable = [
        'report_id',
        'work_unit_id',
        'period',
        ...self::CONTENT_FIELDS,
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'evidence_links' => 'array',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ReportEvaluationRevision::class);
    }
}
