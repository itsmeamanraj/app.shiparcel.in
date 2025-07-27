<?php

namespace App\Listeners;

use App\Support\Traits\ActivityLog;
use Illuminate\Contracts\Events\Dispatcher;

class UserEventListener
{
    use ActivityLog;

    /**
     * Handle the user's login event.
     *
     * @param  mixed  $event
     */
    public function onLoggedIn($event): void
    {
        $user = $event->user;

        $this->logActivity($user, 'Logged In', 'Login');
    }

    /**
     * Handle the user's logout event.
     *
     * @param  mixed  $event
     */
    public function onLoggedOut($event): void
    {
        $user = $event->user;

        $this->logActivity($user, 'Logged Out', 'Logout');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            //UserLoggedIn::class => 'onLoggedIn',
        ];
    }
}
