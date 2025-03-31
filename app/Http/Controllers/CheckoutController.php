<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    //
    public function create(Request $request)
    {
        $priceId = $request->input('priceId');
        $user = $request->user();

        if ($user && $priceId) {
            try {
                $session = $user->newSubscription('standard', $priceId)
                    ->trialDays(14)
                    ->checkout([
                        'success_url' => route('buy-dashboard'),
                        'cancel_url' => route('buy-plans'),
                    ]);

                return Inertia::render('shop/payment-pending', [
                    'url' => $session->url,
                ]);
            } catch (\Exception $e) {
                info('exception in checkout', [$e]);
                Session::flash('message', 'Could not initiate checkout.');

                return back();
            }
        }

        Session::flash('message', 'Could not initiate checkout. Invalid Request');

        return back()->withErrors('error', 'Invalid request.');
    }
}
