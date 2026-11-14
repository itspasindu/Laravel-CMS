<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OidcAuthController extends Controller
{
    /**
     * Start the stubbed OIDC authorization redirect.
     *
     * When OIDC is disabled or incomplete, the app falls back to Breeze login
     * so local runs and automated tests never require an external IdP.
     */
    public function redirect(Request $request): RedirectResponse|Response
    {
        if (! config('oidc.enabled')) {
            return redirect()
                ->route('login')
                ->with('status', 'OIDC login is disabled. Use email/password authentication.');
        }

        $authorizationEndpoint = config('oidc.authorization_endpoint');
        $clientId = config('oidc.client_id');
        $redirectUri = config('oidc.redirect_uri');

        if (! $authorizationEndpoint || ! $clientId || ! $redirectUri) {
            return response(
                'OIDC is enabled but authorization settings are incomplete.',
                503
            );
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $request->session()->token(),
        ]);

        return redirect()->away(rtrim($authorizationEndpoint, '?').'?'.$query);
    }

    /**
     * Handle the stubbed OIDC callback without talking to a real IdP.
     */
    public function callback(Request $request): RedirectResponse|Response
    {
        if (! config('oidc.enabled')) {
            return redirect()
                ->route('login')
                ->with('status', 'OIDC login is disabled.');
        }

        // Placeholder only: a real implementation would exchange the code for
        // tokens and create/login a local user. Tests do not require an IdP.
        if (! $request->filled('code')) {
            return response('Missing authorization code.', 400);
        }

        return redirect()
            ->route('login')
            ->with('status', 'OIDC callback received (stub). Configure a real IdP to complete login.');
    }
}
