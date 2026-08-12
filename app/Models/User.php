<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens;
    use HasFactory, Notifiable;
    use HasPanelShield;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'nip',
        'jabatan',
        'coordinator_signature_path',
        'fcm_token',
        'status',
    ];

    protected static function booted(): void
    {
        static::updated(function (User $user): void {
            if (! $user->wasChanged('coordinator_signature_path')) {
                return;
            }

            $oldPath = $user->getPrevious()['coordinator_signature_path'] ?? null;
            $newPath = $user->coordinator_signature_path;

            if (blank($oldPath) || $oldPath === $newPath) {
                return;
            }

            DB::afterCommit(fn (): bool => Storage::disk('local')->delete($oldPath));
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // return str_ends_with($this->email, 'http://summary4.test/');
        return ! $this->trashed()
            && ! in_array($this->status, [false, 0, '0', null], true)
            && $this->hasRole(['super_admin', 'admin', 'writer', 'panel_user', 'katimja']);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // return $this->avatar_url ? Storage::url($this->avatar_url) : null ;
        return $this->avatar_url ? asset($this->avatar_url) : null;
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function coordinatorAssignments()
    {
        return $this->hasMany(WorkUnitCoordinator::class);
    }

    public function coordinatedWorkUnits()
    {
        return $this->belongsToMany(WorkUnit::class, 'work_unit_coordinators')
            ->withPivot(['starts_at', 'ends_at'])
            ->withTimestamps();
    }

    public function createdReportEvaluations()
    {
        return $this->hasMany(ReportEvaluation::class, 'created_by');
    }

    public function editedReportEvaluations()
    {
        return $this->hasMany(ReportEvaluation::class, 'updated_by');
    }

    public function reportEvaluationRevisions()
    {
        return $this->hasMany(ReportEvaluationRevision::class, 'changed_by');
    }
}
