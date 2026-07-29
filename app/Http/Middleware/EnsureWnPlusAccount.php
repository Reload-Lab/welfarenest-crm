<?php

namespace App\Http\Middleware;

use App\Models\WnPlusAccount;
use Closure;
use Illuminate\Http\Request;

class EnsureWnPlusAccount
{
    public function handle(Request $request, Closure $next)
    {
        $accountId = session('wn_plus_account_id');

        $account = $accountId
            ? WnPlusAccount::where('id', $accountId)->where('status', 'active')->first()
            : null;

        if (! $account) {
            session()->forget('wn_plus_account_id');

            return redirect()->route('wn-plus.login');
        }

        $request->attributes->set('wnPlusAccount', $account);

        return $next($request);
    }
}