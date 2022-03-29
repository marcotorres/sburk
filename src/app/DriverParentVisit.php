<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverParentVisit extends Model
{
    //
    protected $table = 'driver_parent_visits';


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
     * Get driver.
     */
    public function driver()
    {
        return $this->belongsTo('App\Driver');
    }

    /**
     * Get parent.
     */
    public function parent()
    {
        return $this->belongsTo('App\Parent_');
    }
}
