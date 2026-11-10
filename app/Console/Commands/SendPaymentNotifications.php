<?php

namespace App\Console\Commands;

use App\Mail\OrderFinalisedNotification;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPaymentNotifications extends Command
{
    protected $signature = 'payments:send-notifications';

    protected $description = 'Deliver committed payment notifications independently of financial processing';

    public function handle(): int
    {
        $failed = false;
        foreach (DB::table('payment_notifications')->whereNull('sent_at')->orderBy('id')->lazyById(100) as $notification) {
            $lock = Cache::lock('payment_notification_'.$notification->id, 300);
            if (! $lock->get()) {
                continue;
            }
            try {
                if (DB::table('payment_notifications')->where('id', $notification->id)->whereNull('sent_at')->exists()) {
                    $order = Order::withTrashed()->with(['user', 'lineItems.product'])->findOrFail($notification->order_id);
                    Mail::to($order->email)->send(new OrderFinalisedNotification($order));
                    DB::table('payment_notifications')->where('id', $notification->id)->update(['sent_at' => now(), 'updated_at' => now()]);
                }
            } catch (Throwable $e) {
                report($e);
                $failed = true;
            } finally {
                $lock->release();
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
