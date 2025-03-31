<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Mixpanel;

class LaravelMixpanelUserObserver
{
    public function created($user)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            // Check if this is an SSO registration
            $isSSO = request()->route() && strpos(request()->route()->uri(), 'auth/google') !== false;

            if ($isSSO) {
                // This is an SSO registration (Google)
                event(new Mixpanel($user, [
                    'User: Registered' => [
                        'authentication_method' => 'SSO',
                        'sso_provider' => 'Google'
                    ]
                ]));
            } else {
                // This is a standard registration
                event(new Mixpanel($user, [
                    'User: Registered' => [
                        'authentication_method' => 'standard'
                    ]
                ]));
            }
        }
    }

    public function saving($user)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Updated' => []]));
        }
    }

    public function deleting($user)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Deactivated' => []]));
        }
    }

    public function restored($user)
    {
        if (config("services.mixpanel.enable-default-tracking")) {
            event(new Mixpanel($user, ['User: Reactivated' => []]));
        }
    }
}
