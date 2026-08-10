<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource;
use App\Models\Executor;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderQueryEfficiencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_table_metrics_do_not_issue_queries_per_row(): void
    {
        $creator = User::factory()->create();
        $executor = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $creator->id,
            'order_date' => '2026-08-10',
            'instruction' => 'Audit query disposisi',
        ]);
        Executor::query()->create([
            'order_id' => $order->id,
            'user_id' => $executor->id,
            'status' => true,
            'task' => 'Verifikasi',
        ]);
        $this->actingAs($executor);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $record = OrderResource::getEloquentQuery()->findOrFail($order->id);
        $queriesAfterLoad = count(DB::getQueryLog());

        $this->assertSame(1, $record->pegawaidapatDisposisi());
        $this->assertSame(1, $record->pegawaiSelesai());
        $this->assertSame($executor->id, $record->userStatus()?->user_id);
        $this->assertSame($executor->id, $record->userStatus()?->user_id);
        $this->assertSame($queriesAfterLoad, count(DB::getQueryLog()));
    }
}
