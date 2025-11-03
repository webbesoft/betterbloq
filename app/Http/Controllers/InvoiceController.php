<?php

namespace App\Http\Controllers;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    //
    /**
     * Generate, email (optionally), and allow downloading an invoice PDF.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Order $order, Request $request)
    {
        if ($request->user()->id !== $order->user_id) {
            abort(403, 'Unauthorized');
        }

        $order->loadMissing(['lineItems', 'lineItems.product']);

        $pdf = Pdf::loadView('invoices.order', ['order' => $order]);

        try {
            $customerEmail = $order->email ?? $request->user()->email;
            if ($customerEmail) {
                Mail::to($customerEmail)->queue(new OrderInvoiceMail($order));
            } else {
                Log::warning("No email address found for order {$order->id}, invoice email not sent.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to queue invoice email for order {$order->id}: ".$e->getMessage());
            // Optional: Flash a message to the user that email failed but download will proceed
            session()->flash('error', 'Failed to send invoice email. You can still download the invoice.');
        }

        // Return PDF as a download response
        return $pdf->download(self::constructInvoiceName($order), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function constructInvoiceName(Order $order): string
    {
        return 'invoice-'.$order->id.'-'.now()->format('Y-m-d').'.pdf';
    }

    public function sendInvoiceEmail(Order $order)
    {
        try {
            $customerEmail = $order->email ?? $order->user?->email;
            if ($customerEmail) {
                Mail::to($customerEmail)->queue(new OrderInvoiceMail($order));
                Log::info("Invoice email queued for order {$order->id}.");
            } else {
                Log::warning("No email address found for order {$order->id}, post-checkout invoice email not sent.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to queue post-checkout invoice email for order {$order->id}: ".$e->getMessage());
        }
    }
}
