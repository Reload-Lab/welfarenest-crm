<?php

namespace App\Http\Controllers;

use App\Models\WnPlusInvitation;

class WnPlusInvitationController extends Controller
{
    public function accept(string $token)
    {
        $invitation = WnPlusInvitation::query()
            ->with('account.organization')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->expires_at->isPast()) {
            abort(410, 'Invito scaduto.');
        }

        return view('wn-plus.invitations.accept', compact('invitation'));
    }
}