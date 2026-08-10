<?php

namespace Tests\Feature;

use App\Models\Documentation;
use App\Models\Executor;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteUnusedFilesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_cleanup_is_a_safe_dry_run_by_default(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('dokumentasi/orphan.jpg', 'orphan');

        $this->artisan('app:delete-unused-files')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('dokumentasi/orphan.jpg');
    }

    public function test_explicit_cleanup_preserves_every_known_reference_and_unmanaged_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['avatar_url' => 'foto-pegawai/user.jpg']);
        $report = Report::query()->create([
            'user_id' => $user->id,
            'slug' => 'cleanup-test-report',
            'no_st' => 'ST-1',
            'what' => 'Kegiatan',
            'why' => 'Kebutuhan',
            'when' => '2026-08-10',
            'tanggal_selesai' => '2026-08-10',
            'where' => 'Sorong',
            'who' => 'Tim',
            'how' => 'Pelaksanaan',
            'penyelenggara' => 'Summary',
            'total_peserta' => 1,
            'total_wanita' => 0,
            'kode' => 'data:image/png;base64,AA==',
        ]);
        Documentation::query()->create([
            'report_id' => $report->id,
            'dokumentasi1' => 'dokumentasi/report.jpg',
            'st' => 'st/report.pdf',
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_slug' => 'cleanup-test-order',
            'letter' => 'perintah_disposisi/order.pdf',
        ]);
        Executor::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'proof' => 'tindakLanjutDispo/proof.jpg',
        ]);

        foreach ([
            'foto-pegawai/user.jpg',
            'dokumentasi/report.jpg',
            'st/report.pdf',
            'perintah_disposisi/order.pdf',
            'tindakLanjutDispo/proof.jpg',
            'lainnya/orphan.pdf',
            'misc/unmanaged.txt',
        ] as $path) {
            Storage::disk('public')->put($path, $path);
        }

        $this->artisan('app:delete-unused-files', ['--delete' => true])
            ->assertSuccessful();

        foreach ([
            'foto-pegawai/user.jpg',
            'dokumentasi/report.jpg',
            'st/report.pdf',
            'perintah_disposisi/order.pdf',
            'tindakLanjutDispo/proof.jpg',
            'misc/unmanaged.txt',
        ] as $path) {
            Storage::disk('public')->assertExists($path);
        }

        Storage::disk('public')->assertMissing('lainnya/orphan.pdf');
    }
}
