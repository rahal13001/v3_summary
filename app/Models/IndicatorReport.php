<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IndicatorReport extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'indicator_id',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }


}
