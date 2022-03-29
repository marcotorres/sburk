<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverChildCheckInOut extends Model
{
    //
    protected $table = 'driver_child_check_in_out';

    public function scopeChecks($query, $start_date, $end_date, $event_type)
    {
        if($event_type == 0)
            return $query->where('updated_at', '>=', $start_date)
                     ->where('updated_at', '<=', $end_date);
        else {
            return $query->where('updated_at', '>=', $start_date)
                ->where('updated_at', '<=', $end_date)
                ->where('case_id', $event_type);
        }
    }

    /**
     * Get driver.
     */
    public function driver()
    {
        return $this->belongsTo('App\Driver');
    }

    /**
     * Get child.
     */
    public function child()
    {
        return $this->belongsTo('App\Child');
    }
}
