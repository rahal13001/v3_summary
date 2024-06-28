<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Arr;
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
    protected $signature = 'app:delete-unused-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $documentations = Documentation::select(['dokumentasi1', 'dokumentasi2', 'dokumentasi3', 'st', 'lainnya'])->get()->toArray();
        $documentations = Arr::flatten($documentations);
        $avatar = User::pluck('avatar_url')->toArray();

        collect(Storage::disk('public')->allFiles())
            ->reject(fn (string $file) => $file === '.gitignore')
            ->reject(fn (string $file) => in_array($file, $documentations))
            ->reject(fn (string $file) => in_array($file, $avatar))
            ->each(fn (string $file) => Storage::disk('public')->delete($file));
    }
}
