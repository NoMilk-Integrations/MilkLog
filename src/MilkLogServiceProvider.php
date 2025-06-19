<?php

namespace RootAccessPlease\MilkLog;

use Illuminate\Support\ServiceProvider;
use RootAccessPlease\MilkLog\Services\MilkLogService;

class MilkLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/milklog.php',
            'milklog'
        );

        $this->app->booted(function () {
            if (! config('services.slack.notifications.bot_user_oauth_token')) {
                config([
                    'services.slack.notifications.bot_user_oauth_token' => config('milklog.slack.bot_user_oauth_token'),
                    'services.slack.notifications.channel' => config('milklog.slack.channel'),
                ]);
            }
        });

        $this->app->singleton('milklog', function ($app) {
            return new MilkLogService();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/milklog.php' => config_path('milklog.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../config/milklog.php' => config_path('milklog.php'),
        ], 'milklog-config');
    }
}
