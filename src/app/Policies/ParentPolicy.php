<?php

namespace App\Policies;

use App\User;
use App\Parent_;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the school account can view the parent.
     *
     * @param  \App\User  $school
     * @param  \App\Parent_  $parent
     * @return mixed
     */
    public function view(User $school, Parent_ $parent)
    {
        //
        return $school->id === $parent->school_id;
    }

    /**
     * Determine whether the school account can delete the parent.
     *
     * @param  \App\User  $user
     * @param  \App\Parent_  $parent
     * @return mixed
     */
    public function delete(User $school, Parent_ $parent)
    {
        //
        return $school->id === $parent->school_id;
    }
}
