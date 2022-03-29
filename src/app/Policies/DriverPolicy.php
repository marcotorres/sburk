<?php

namespace App\Policies;

use App\User;
use App\Driver;
use App\Plan;
use Illuminate\Auth\Access\HandlesAuthorization;

class DriverPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the school account can view the driver.
     *
     * @param  \App\User  $school
     * @param  \App\Driver  $driver
     * @return mixed
     */
    public function view(User $school, Driver $driver)
    {
        //
        return $school->id === $driver->school_id;
    }

    /**
     * Determine whether the school account can create drivers.
     *
     * @param  \App\User  $school
     * @return mixed
     */
    public function create(User $school)
    {
        // check if the current school account can add a new driver while still in plan limit
        // get current plan limit
        $allowed_drivers = $school->plan->allowed_drivers;
        //get the current number of drivers in school account
        $current_drivers = $school->drivers->count();
        return $allowed_drivers==-1 || $allowed_drivers >= ($current_drivers + 1);
    }

    /**
     * Determine whether the school account can delete the driver.
     *
     * @param  \App\User  $school
     * @param  \App\Driver  $driver
     * @return mixed
     */
    public function delete(User $school, Driver $driver)
    {
        //
        return $school->id === $driver->school_id;
    }
}
