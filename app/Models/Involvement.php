<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Involvement extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'status',
        'is_lprl_organizer',
    ];

    protected function casts(): array
    {
        return [
            'is_lprl_organizer' => 'boolean',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
