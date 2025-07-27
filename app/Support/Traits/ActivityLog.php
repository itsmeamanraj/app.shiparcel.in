<?php

namespace App\Support\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait ActivityLog
{
    use LogsActivity;

    /**
     * Get the options for the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName($this->getTable())
            ->setDescriptionForEvent(function (string $eventName) {
                return "This model has been {$eventName} {$this->getTable()}";
            });
    }

    /**
     * Customize the activity log entry before it is saved.
     *
     *
     * @return void
     */
    public function tapActivity(Activity $activity)
    {
        Log::info(Request::ip());
        $activity->properties = $activity->properties->merge([
            'IP' => Request::ip(),
        ]);
    }

    /**
     * Log the activity with the provided details.
     *
     * @param  mixed  $user
     * @param  string  $description
     * @param  string  $event
     */
    public function logActivity($user, $description, $event): void
    {
        activity($user->getTable())
            ->causedBy($user)
            ->withProperties([
                'IP' => Request::ip(),
                'User' => $user->toArray(),
            ])
            ->event($event)
            ->log($description);
    }
}