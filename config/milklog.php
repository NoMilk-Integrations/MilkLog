<?php

return [
    'slack' => [
        'channel' => env('MILKLOG_SLACK_CHANNEL', '#general'),
        'bot_user_oauth_token' => env('MILKLOG_SLACK_BOT_TOKEN'),
        'tags' => env('MILKLOG_SLACK_TAGS', [
            // Example: ['@here', '@channel', '<@U1234567890>']
        ]),
    ],

    'logging' => [
        'channel' => env('MILKLOG_LOG_CHANNEL', config('logging.default')),
        'include_context' => true,
        'include_trace' => env('MILKLOG_INCLUDE_TRACE', false),
    ],

    'notifications' => [
        'enabled' => env('MILKLOG_NOTIFICATIONS_ENABLED', true),
    ],
];