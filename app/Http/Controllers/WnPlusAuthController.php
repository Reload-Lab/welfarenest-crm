<?php

namespace App\Http\Controllers;

use App\Models\WnPlusAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WnPlusAuthController extends Controller
{
    public function showLogin()
    {
        return view('wn-plus.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $account = WnPlusAccount::where('email', $validated['email'])
            ->where('status', 'active')
            ->first();

        if (! $account || ! Hash::check($validated['password'], $account->password)) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Credenziali non valide o account non attivo.',
                ]);
        }

        session([
            'wn_plus_account_id' => $account->id,
        ]);

        $account->update([
            'last_login_at' => now(),
        ]);

        $intendedOidcUrl = session()->pull('wn_plus_oidc_authorize_request');

        if ($intendedOidcUrl) {
            return redirect()->away($intendedOidcUrl);
        }

        return redirect()->intended(route('wn-plus.portal.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('wn_plus_account_id');

        return redirect()->route('wn-plus.login');
    }
}