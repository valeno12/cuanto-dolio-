<?php

namespace App\Providers;

use App\Auth\ParticipantGuard;
use App\Models\Participant;
use App\Services\ParticipantSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add a macro to easily get the participant from the request
        Request::macro('participant', function (): ?Participant {
            return $this->attributes->get('participant');
        });

        // Register the custom participant guard driver
        Auth::extend('participant', function ($app, $name, array $config) {
            return new ParticipantGuard(
                $app['request'],
                $app->make(ParticipantSessionService::class)
            );
        });
    }
}

