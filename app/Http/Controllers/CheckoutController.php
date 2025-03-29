<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    //
    public function create(Request $request)
    {
        $priceId = $request->input('priceId');
        $user = $request->user();

        dd($request);
        dd($priceId, $user);

        if ($user && $priceId) {
            try {
                $checkoutUrl = $user->newSubscription('default', $priceId)
                    ->checkout()->url;

                return Redirect::away($checkoutUrl);
            } catch (\Exception $e) {
                Session::flash('message', 'Could not initiate checkout.');

                return back();
            }
        }

        return back()->with('error', 'Invalid request.');
    }
}
