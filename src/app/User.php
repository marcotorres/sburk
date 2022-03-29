<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;


use Illuminate\Support\Facades\DB;

use Storage;

use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'channel', 'address', 'latitude', 'longitude'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'is_super_admin_account'
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['avatar_url'];

    protected $table = 'schools';

    public function getAvatarUrlAttribute()
    {
        return Storage::url('avatars/'.$this->id.'/'.$this->avatar);
    }


    public function plan()
    {
        return $this->belongsTo('App\Plan')->withTrashed();
    }

    public function drivers()
    {
        return $this->hasMany('App\Driver', 'school_id');
    }

    public function parents()
    {
        return $this->hasMany('App\Parent_', 'school_id');
    }
    
    public function buses()
    {
        return $this->hasMany('App\Bus', 'school_id');
    }
    
}
