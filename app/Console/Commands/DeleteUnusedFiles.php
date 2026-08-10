<?php

namespace App\Console\Commands;

use App\Models\Executor;
use App\Models\Order;
use App\Models\User;
use App\Models\Documentation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteUnusedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-unused-files
                            {--delete : Permanently delete unreferenced files from managed upload directories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report unreferenced managed uploads, or delete them with --delete';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $normalizePath = static function (mixed $path): ?string {
            if (! is_string($path) || blank($path)) {
                return null;
            }

            $path = trim(str_replace('\\', '/', $path));

            if (filter_var($path, FILTER_VALIDATE_URL)) {
                $path = (string) parse_url($path, PHP_URL_PATH);
            }

            $path = ltrim(rawurldecode($path), '/');

            foreach (['storage/', 'public-storage/'] as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $path = substr($path, strlen($prefix));
                }
            }

            return blank($path) ? null : $path;
        };

        $referencedFiles = Documentation::query()
            ->select(['dokumentasi1', 'dokumentasi2', 'dokumentasi3', 'st', 'lainnya'])
            ->get()
            ->flatMap(fn (Documentation $documentation): array => [
                $documentation->dokumentasi1,
                $documentation->dokumentasi2,
                $documentation->dokumentasi3,
                $documentation->st,
                $documentation->lainnya,
            ])
            ->merge(User::query()->pluck('avatar_url'))
            ->merge(Order::query()->pluck('letter'))
            ->merge(Executor::query()->pluck('proof'))
            ->map($normalizePath)
            ->filter()
            ->unique()
            ->flip();

        $managedDirectories = [
            'dokumentasi',
            'st',
            'lainnya',
            'foto-pegawai',
            'perintah_disposisi',
            'tindakLanjutDispo',
        ];

        $publicDisk = Storage::disk('public');
        $unreferencedFiles = collect($managedDirectories)
            ->flatMap(fn (string $directory): array => $publicDisk->allFiles($directory))
            ->map($normalizePath)
            ->filter()
            ->reject(fn (string $file): bool => $referencedFiles->has($file))
            ->values();

        $this->components->info("Found {$unreferencedFiles->count()} unreferenced managed upload(s).");

        if (! $this->option('delete')) {
            $this->components->warn('Dry run only. Re-run with --delete after reviewing backups and references.');

            return self::SUCCESS;
        }

        $deletedFiles = $unreferencedFiles
            ->filter(fn (string $file): bool => $publicDisk->delete($file));

        $this->components->info("Deleted {$deletedFiles->count()} unreferenced managed upload(s).");

        return self::SUCCESS;
    }
}
