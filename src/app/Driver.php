<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id', 'active', 'created_at', 'updated_at', 'deleted_at'];
    
    /**
     * Get the bus that this driver uses.
     */
    public function bus()
    {
        return $this->belongsTo('App\Bus');
    }

    /**
     * Get the school that this driver belongs to.
     */
    public function school()
    {
        return $this->belongsTo('App\User')
        ->select(array('id', 'name', 'email', 'channel', 'address', 'latitude', 'longitude'));
    }

    /**
     * Get the parents that this driver drives for.
     */
    public function parents()
    {
        return $this->hasMany('App\Parent_');
    }

    /**
     * Get the school visit statuses of the driver.
     */
    public function school_visit_statuses()
    {
        
        return $this->hasMany('App\DriverSchoolVisit');
    }

    /**
     * Get the last school visit status of the driver.
     */
    public function latest_school_visit()
    {
        
        return $this->hasOne('App\DriverSchoolVisit')->latest();
    }


    /**
     * Get the parent visit statuses of the driver.
     */
    public function parents_visit_statuses()
    {
        return $this->hasMany('App\DriverParentVisit');
    }

    /**
     * Get the last parent visit status of the driver.
     */   
    public function latest_parents_visit() {
        return $this->hasMany('App\DriverParentVisit')
            ->select('*')
            ->whereIn('id', function($q){
                $q->select(DB::raw('MAX(id) FROM driver_parent_visits GROUP BY parent_id'));
            });
    }

    public function latest_zones_visit() {
        return $this->hasMany('App\DriverZoneVisit')
            ->select('*')
            ->whereIn('id', function($q){
                $q->select(DB::raw('MAX(id) FROM driver_zone_visits GROUP BY parent_id'));
            });
    }
    
}
