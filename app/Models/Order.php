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
        'order_slug',
        'order_time',
        'order_status',
        'instruction',
        'note',
        'letter',
        'fcm_token',
        'order_finishdate'
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

        if ($this->relationLoaded('executor')) {
            return $this->executor->firstWhere('user_id', $person);
        }

        return $this->executor()->where('user_id', $person)->first();
    }

    public function pegawaidapatDisposisi()
    {
        if ($this->getAttribute('executors_count') !== null) {
            return (int) $this->getAttribute('executors_count');
        }

        return $this->executor()->count();
    }

    public function pegawaiSelesai(){
        if ($this->getAttribute('completed_executors_count') !== null) {
            return (int) $this->getAttribute('completed_executors_count');
        }

        return $this->executor()->where('status', 1)->count();
    }

    public function getRouteKeyName()
    {
        return 'order_slug';
    }


}
