<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Parent_ extends Model
{
    //
    protected $table = 'parents';
    
    protected $guarded = ['id', 'active', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the driver that drives for this parent
     */
    public function driver()
    {
        return $this->BelongsTo(Driver::class);
    }
    /**
     * Get the school of this parent
     */
    public function school()
    {
        return $this->BelongsTo(School::class);
    }

    public function children()
    {
        return $this->hasMany('App\Child' , 'parent_id');
    }
}
