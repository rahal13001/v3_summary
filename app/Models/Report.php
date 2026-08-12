<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Report extends Model
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'involvement_id',
        'slug',
        'no_st',
        'what',
        'why',
        'when',
        'tanggal_selesai',
        'where',
        'who',
        'how',
        'penyelenggara',
        'total_peserta',
        'total_wanita',
        'kode',
    ];

    protected static function booted(): void
    {
        static::saving(function (Report $report): void {
            if (! $report->involvement_id) {
                return;
            }

            $involvement = Involvement::query()->find($report->involvement_id);

            if ($involvement?->is_lprl_organizer) {
                $report->penyelenggara = $involvement->organizerName();
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['when', 'what'])
            ->saveSlugsTo('slug');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentation()
    {
        return $this->hasOne(Documentation::class);
    }

    public function indicators()
    {
        return $this->belongsToMany(Indicator::class, 'indicator_reports', 'report_id', 'indicator_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'report_users', 'report_id', 'user_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'report_teams', 'report_id', 'team_id');
    }

    public function workUnits()
    {
        return $this->belongsToMany(WorkUnit::class, 'report_work_unit', 'report_id', 'work_unit_id');
    }

    public function involvement()
    {
        return $this->belongsTo(Involvement::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(ReportEvaluation::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
