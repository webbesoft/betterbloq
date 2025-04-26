<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UserSettingsController extends Controller
{
    //
    public function markSetupGuideComplete(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->has_completed_guide = true;
            $user->save();

            session()->flash('message', 'Setup guide completed!');
             return Redirect::back()->with('message', 'Setup guide completed!');
        }

        return Redirect::back()->withErrors(['message' => 'Could not mark guide as complete.']);
    }
}
