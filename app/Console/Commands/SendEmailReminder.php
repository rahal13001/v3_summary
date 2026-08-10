<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderReminder;

class SendEmailReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-email-reminder';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to executors at 08:00 on the order day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::with('executor.user')->where('order_date', '=', Carbon::now()->toDateString())
            ->get();

            foreach ($orders as $order) {
                $executors = $order->executor;
                foreach ($executors as $executor) {
                    $user = $executor->user;
                    if (!empty($user->email)) {
                        try {
                            Mail::to($user->email)->send(new OrderReminder($user, $order));
                            Log::info('Email reminder sent to user ID: ' . $user->id);
                        } catch (Exception $e) {
                            Log::error('Failed to send email reminder: ' . $e->getMessage());
                        }
                    }
                }
            }
    
            Log::info('SendEmailReminder command executed.');
            $this->info('Email reminders sent successfully.');
        
            
    }
}
