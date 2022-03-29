<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverSchoolVisit extends Model
{
    //
    protected $table = 'driver_school_visits';


    public function scopeVisits($query, $start_date, $end_date, $event_type)
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
     * Get the school visit statuses of the driver.
     */
    public function driver()
    {
        return $this->belongsTo('App\Driver');
    }
}
