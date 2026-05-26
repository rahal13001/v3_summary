<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'dokumentasi1',
        'dokumentasi2',
        'dokumentasi3',
        'st',
        'lainnya'
    ];

    public function report(){
        return $this->belongsTo(Report::class);
    }

}
