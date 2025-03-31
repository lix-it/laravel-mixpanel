<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;
use Illuminate\Auth\Events\Login as LoginEvent;

class Login
{
    public function handle(LoginEvent $login)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            // Check if this is an SSO registration
            $isSSO = request()->route() && strpos(request()->route()->uri(), 'auth/google') !== false;
            if ($isSSO) {
                // This is an SSO login (Google)
                event(new Mixpanel($login->user, [
                    'User Logged In' => [
                        'authentication_method' => 'SSO',
                        'sso_provider' => 'Google'
                    ]
                ], 0, [
                    'Last Logged In' => now()->format('Y-m-d\Th:i:s'),
                ]));
            } else {
                // This is a standard login
                event(new Mixpanel($login->user, [
                    'User Logged In' => [
                        'authentication_method' => 'standard'
                    ]
                ], 0, [
                    'Last Logged In' => now()->format('Y-m-d\Th:i:s'),
                ]));
            }
        }
    }
}
