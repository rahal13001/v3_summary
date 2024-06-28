<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Indicator extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'nama_iku',
        'nomor_iku',
        'tahun_iku',
        'slug_iku',
        'status_iku',
    ];

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'indicator_reports', 'indicator_id', 'report_id');
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['tahun_iku', 'nama_iku'])
            ->saveSlugsTo('slug_iku');
    }

    public function getRouteKeyName()
    {
        return 'slug_iku';
    }
}
