<?php

return [
    'mixpanel' => [
        'host' => env("MIXPANEL_HOST"),
        'token' => env('MIXPANEL_TOKEN'),
        'enable-default-tracking' => true,
        'consumer' => 'socket',
        'connect-timeout' => 2,
        'timeout' => 2,
        "data_callback_class" => null,
        'passthrough' => env('MIXPANEL_PASSTHROUGH', false),
        'debug' => env('APP_DEBUG', false),
        'group_key' => 'team_id',
    ]
];
