<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SettingType extends Model
{
    /**
     * Get the settings of a setting type.
    */
    public function settings()
    {
        
        return $this->hasMany('App\Setting');
    }
}
