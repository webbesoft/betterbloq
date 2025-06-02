<?php

namespace App\Listeners;

use App\Mail\NewOrderConfirmation;
use App\Managers\CheckoutSessionCompletedManager;
use App\Models\User;
use App\Services\LogService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Exception\ApiErrorException;

class StripeEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @throws ApiErrorException
     */
    public function handle(WebhookReceived $event): void
    {
        $payloadType = $event->payload['type'] ?? null;

        switch ($payloadType) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->payload);
                break;
                // case 'invoice.payment_succeeded':
                //     $this->handleInvoicePaymentSucceeded($event->payload);
                //     break;
                // Add more cases as needed
            default:
                (new LogService)->createLog(
                    'error',
                    "Unhandled Stripe event type: {$payloadType}",
                    __CLASS__,
                    __METHOD__,
                    ['payload' => $event->payload]
                );
                throw new ApiErrorException("Unhandled Stripe event type: {$payloadType}");
        }
    }

    /**
     * @throws ApiErrorException
     */
    private function handleCheckoutSessionCompleted(array $payload): void
    {
        $validatedData = CheckoutSessionCompletedManager::getValidatedSessionData($payload);
        if (! $validatedData) {
            return;
        }

        $session = $validatedData->session;
        $user = $validatedData->user;
        $metadata = $validatedData->metadata;

        try {
            $lineItemsFromStripe = CheckoutSessionCompletedManager::fetchStripeLineItems(data_get($session, 'id'));
            if (! $lineItemsFromStripe) {
                return;
            }

            $itemDetailsLookup = CheckoutSessionCompletedManager::parseItemDetailsMetadata($metadata, $session['id']);

            $orderService = new OrderService;
            $mainOrder = $orderService->createOrderFromStripeSession(
                $session,
                $user,
                $metadata,
                $lineItemsFromStripe,
                $itemDetailsLookup
            );

            if ($mainOrder) {
                // Mail::to($mainOrder->user->email)->send(new NewOrderConfirmation($mainOrder, $user));
                // Send internal notifications, perhaps via events
                info('Order successfully processed from Stripe webhook.', ['order_id' => $mainOrder->id, 'session_id' => $session['id']]);
            } else {
                FacadesLog::error('Failed to create order from Stripe webhook.', ['session_id' => $session['id']]);
            }
        } catch (ApiErrorException $e) {
            (new LogService)->logException($e, __CLASS__, __METHOD__, [
                'payload' => $payload,
            ]);
        }
    }
}
