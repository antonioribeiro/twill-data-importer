<?php

return [
    'enabled' => env('TWILL_DATA_IMPORTER_ENABLED', true),

    'middleware' => [
        'inject' => env('TWILL_SECURITY_MIDDLEWARE_INJECTION_ENABLED', true),

        'enabled_inside_twill' => false,

        'groups' => [
            [
                'group' => 'web',
                'type' => 'prepend',
                'classes' => [\A17\TwillDataImporter\Http\Middleware\All::class],
            ],

            [
                'group' => 'api',
                'type' => 'prepend',
                'classes' => [\A17\TwillDataImporter\Http\Middleware\All::class],
            ],
        ],
    ],

    'headers' => [
    ],

    'unwanted-headers' => ['X-Powered-By', 'server', 'Server'],
];
