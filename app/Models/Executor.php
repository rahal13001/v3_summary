<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Executor extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'report_id',
        'status',
        'proof',
        'description',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
