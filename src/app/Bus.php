<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    //
    protected $guarded = ['id', 'active', 'created_at', 'updated_at', 'deleted_at'];

    public function driver()
    {
        return $this->hasOne('App\Driver');
    }
}
