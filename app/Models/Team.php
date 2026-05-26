<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'nama_tim',
        'slug_tim',
        'status_tim',
        'nomor_tim',
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['nomor_tim', 'nama_tim'])
            ->saveSlugsTo('slug_tim');
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'report_teams', 'team_id', 'report_id');
    }

    public function getRouteKeyName()
    {
        return 'slug_tim';
    }
}
