<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


use App\Setting;

trait CheckStripe
{
    /* check if the settings for stripe is ok */
    public function is_stripe_ok()
    {
        // get null settings
        $null_settings = Setting::where('value',null)->get();
        $stripe_ok = true;
        // check if any null setting corresponds to stripe
        foreach($null_settings as $null_setting)
        {
            if($null_setting->name == "Stripe Secret key" || 
                    $null_setting->name == "Stripe Publishable key" || 
                    $null_setting->name == "Stripe product")
                $stripe_ok = false;
        }
        return $stripe_ok;
        
    }
}