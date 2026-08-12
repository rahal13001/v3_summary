<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class ReportEvaluationRevision extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'report_evaluation_id',
        'changed_by',
        'changed_at',
        'changes',
    ];

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new RuntimeException('Evaluation revisions are append-only.'));
        static::deleting(fn (): never => throw new RuntimeException('Evaluation revisions are append-only.'));
    }

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
            'changes' => 'array',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(ReportEvaluation::class, 'report_evaluation_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
