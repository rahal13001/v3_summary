<?php

namespace App\Console\Commands;


use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Services\FCMservice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOrderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-order-notifications';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for orders starting soon';

    /**
     * Execute the console command.
     */

    //  public function __construct()
    //  {
    //      parent::__construct();
    //  }

    public function handle()
    {
        $orders = Order::with('executor.user')->where('order_date', '=', Carbon::now()->addHour()->toDateString())
        ->whereRaw('TIME_FORMAT(order_time, "%H:%i") = ?', [Carbon::now()->addHour()->format('H:i')])
        ->get();
        

        $fcmService = app()->make(FCMservice::class);

        foreach ($orders as $order) {
            $executors = $order->executor;
            foreach ($executors as $executor) {
                $user = $executor->user;
                if (!empty($user->fcm_token)) {
                    try {
                        $fcmService->sendNotification(
                            $user->fcm_token,
                            $order->instruction,
                            "Tanggal: {$order->order_date} - Waktu: {$order->order_time}",
                            [
                                'instruction' => $order->instruction,
                                'date' => $order->order_date,
                                'time' => $order->order_time,
                                'timestamp' => now()->timestamp,
                            ]
                        );
                        // Log::info('Notification sent to user ID: ' . $user->id);
                    } catch (Exception $e) {
                        Log::error('Failed to send notification: ' . $e->getMessage());
                    }
                }
            }
        }

        Log::info('SendOrderNotifications command started.');
        

        $this->info('Notifications sent successfully.');
    }
}
