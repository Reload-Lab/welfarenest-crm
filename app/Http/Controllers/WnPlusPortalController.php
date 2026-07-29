<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ConsentType;
use App\Services\ConsentService;

class WnPlusPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');
        $account->load(['organization', 'role', 'level']);

        return view('wn-plus.portal.dashboard', compact('account'));
    }

    public function profile(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');
        $account->load(['organization', 'role', 'level', 'consents.consentType']);

        return view('wn-plus.portal.profile', compact('account'));
    }

    public function updatePassword(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $account->password)) {
            return back()->withErrors(['current_password' => 'Password attuale non corretta.']);
        }

        $account->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password aggiornata correttamente.');
    }

    public function updateConsents(Request $request, ConsentService $consentService)
    {
        $account = $request->attributes->get('wnPlusAccount');

        // TODO: la chiamata esatta a ConsentService dipende dalla sua firma reale — vedi nota sotto.
    }
}