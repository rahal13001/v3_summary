<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
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
        'kode'
    ];

    public function getSlugOptions() : SlugOptions
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


    public function getRouteKeyName()
    {
        return 'slug';
    }


}
