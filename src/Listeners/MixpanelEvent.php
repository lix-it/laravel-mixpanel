<?php namespace GeneaLabs\LaravelMixpanel\Listeners;

use GeneaLabs\LaravelMixpanel\Events\MixpanelEvent as Event;
use Illuminate\Support\Carbon;

class MixpanelEvent
{
    public function handle(Event $event)
    {
        $user = $event->user;

        // if we haven't come from a webhook then use the ip of the request
        $ip = null;
        if (request()->route && request()->route()->name !== 'mp:stripe') {
            $ip = request()->ip();
        }
        
        if ($user && config("services.mixpanel.enable-default-tracking")) {
            $group = app('mixpanel')->getGroup($user);
            $profileData = $this->getProfileData($user);
            $profileData = array_merge($profileData, $event->profileData);

            app('mixpanel')->identify($user->getKey());
            app('mixpanel')->people->set($user->getKey(), $profileData, $ip);

            if (!is_null($group)) {
                $groupData = array_merge($this->getGroupData($group), $event->groupData);
                app('mixpanel')->group->set(config('services.mixpanel.group_key'), $group->getKey(), $groupData, $ip);
            }
            
            if ($event->charge !== 0) {
                app('mixpanel')->people->trackCharge($user->id, $event->charge);
            }

            foreach ($event->trackingData as $eventName => $data) {
                app('mixpanel')->track($eventName, $data);
            }
        }
    }

    private function getProfileData($user) : array
    {
        $firstName = $user->first_name;
        $lastName = $user->last_name;

        if ($user->name) {
            $nameParts = explode(' ', $user->name);
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
        }

        // current team
        // TODO: make this a callback so you can add group identifiers
        $teamId = null;
        if (! is_null($user->currentTeam())) {
            $teamId = $user->currentTeam()->id;
        }
        
        $data = [
            '$first_name' => $firstName,
            '$last_name' => $lastName,
            '$name' => $user->name,
            '$email' => $user->email,
            '$created' => ($user->created_at
                ? (new Carbon())
                    ->parse($user->created_at)
                    ->format('Y-m-d\Th:i:s')
                : null),
            'team_id' => $teamId,
        ];
        array_filter($data);

        return $data;
    }

    private function getGroupData($group): array
    {
        $sparkPlan = $group->sparkPlan();
        $data = [
            '$name' => $group->name,
            '$created' => ($group->created_at
                ? (new Carbon())
                ->parse($group->created_at)
                ->format('Y-m-d\Th:i:s')
                : null),
            '$avatar' => $group->photo_url,
            '$email' => $group->owner->email,
            'plan_key' => $sparkPlan->attributes['planKey'],
            'currency' => $sparkPlan->attributes['currency'],
        ];

        array_filter($data);

        return $data;
    }
}
