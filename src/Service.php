<?php

namespace youvision\mailer;


use think\Service as ThinkService;

class Service extends ThinkService
{
    public function register()
    {
        $this->app->bind(Mailer::class, function () {
            return Mailer::instance();
        });

        $this->app->bind('mailer', function () {
            return Mailer::instance();
        });
    }

    public function boot()
    {
    }
}