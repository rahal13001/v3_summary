<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'user_id',
    ];

    // public function report()
    // {
    //     return $this->belongsTo(Report::class);
    // }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
