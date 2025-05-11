<?php

namespace App\Models;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasSlug;
    
    protected $fillable = [
        'user_id',
        'order_date',
        'order_time',
        'order_status',
        'instruction',
        'note',
        'letter',
        'fcm_token',
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['order_date', 'instruction'])
            ->saveSlugsTo('order_slug');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function executor()
    {
        return $this->hasMany(Executor::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'executors')
            ->withPivot(['status', 'proof', 'description', 'report_id']);
    }

    protected static function booted()
    {
        
        static::created(function ($order) {
            if (request()->has('users')) {
                $userIds = request()->input('users');
                foreach ($userIds as $userId) {
                    Executor::create([
                        'order_id' => $order->id,
                        'user_id' => $userId,
                        'status' => 'pending', // Set default status
                    ]);
                }
            }
        });

        static::updated(function ($order) {
            if (request()->has('users')) {
                // Remove existing executors
                $order->executor()->delete();
                
                // Add new executors
                $userIds = request()->input('users');
                foreach ($userIds as $userId) {
                    Executor::create([
                        'order_id' => $order->id,
                        'user_id' => $userId,
                        'status' => 'pending', // Set default status
                    ]);
                }
            }
        });
    }

    public function userStatus()
    {
        $person = Auth::user()->id;
        $taskId = $this->id;
        $statusData = Executor::where('user_id', $person)->where('order_id', $taskId)->first();
        return $statusData;       
    }

    public function pegawaidapatDisposisi()
    {
        $taskId = $this->id;
        $data = Executor::where('order_id', $taskId)->count();
        return $data;
    }

    public function pegawaiSelesai(){
        $taskId = $this->id;
        $data = Executor::where('order_id', $taskId)->where('status', 1)->count();
        return $data;
    }

    public function getRouteKeyName()
    {
        return 'order_slug';
    }


}
