<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ReportTeam extends Pivot
{
    use HasFactory;

    protected $table = 'report_teams';

    protected $fillable = [
        'report_id',
        'team_id',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
