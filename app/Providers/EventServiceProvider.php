<?php

namespace App\Providers;

use App\Events\UserLoggedIn;
use App\Listeners\UserEventListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
   /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserLoggedIn::class => [
            UserEventListener::class.'@onLoggedIn',
        ]
    ];

    /**
     * Class event subscribers.
     *
     * @var array
     */
    protected $subscribe = [
        UserEventListener::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // parent::boot();
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
