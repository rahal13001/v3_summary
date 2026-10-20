<?php

namespace App\Filament\Pages;

use App\Models\AiAccessGrant;
use App\Services\Ai\Access;
use App\Services\Ai\Registry;
use App\Services\Ai\TokenService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;

class AiAccess extends Page
{
    protected static ?string $title = 'Akses AI';

    protected static ?string $navigationLabel = 'Akses AI';

    protected string $view = 'filament.pages.ai-access';

    public string $tokenName = '';

    public string $reportScope = 'own_related';

    public int $days = 30;

    public array $datasets = ['reports'];

    public array $fields = ['reports' => ['what', 'when', 'tanggal_selesai']];

    #[Locked]
    public ?string $plainToken = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ! $user->trashed() && (bool) $user->status && $user->checkPermissionTo('use_ai_api', 'web');
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function issue(): void
    {
        abort_unless(static::canAccess(), 403);
        $selected = array_intersect_key($this->fields, array_flip($this->datasets));
        $this->plainToken = app(TokenService::class)->issue(auth()->user(), $this->tokenName,
            $this->datasets, $selected, $this->reportScope, $this->days);
        $this->tokenName = '';
        Notification::make()->title('Token dibuat. Salin sekarang; token tidak ditampilkan ulang.')->success()->send();
    }

    public function dismissToken(): void
    {
        $this->plainToken = null;
    }

    public function revoke(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        $token = auth()->user()->tokens()->whereKey($id)
            ->whereIn('id', AiAccessGrant::query()->select('personal_access_token_id'))->first();
        abort_unless($token, 404);
        $token->delete();
        $this->plainToken = null;
        Notification::make()->title('Token dicabut.')->success()->send();
    }

    public function availableDatasets(): array
    {
        return array_filter(app(Registry::class)->definitions(),
            fn ($dataset) => app(Access::class)->accountAllows(auth()->user(), $dataset), ARRAY_FILTER_USE_KEY);
    }

    public function tokens(): Collection
    {
        return auth()->user()->tokens()->whereIn('id', AiAccessGrant::query()->select('personal_access_token_id'))
            ->get(['id', 'name', 'expires_at', 'last_used_at']);
    }
}
