<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use DB;
use App\Driver;
use App\Plan;
use App\Setting;

trait SwitchPlans
{
    /* update plan and adjust #drivers to plan limit */
    public function updatePlanAndAdjustLimit($school, $plan)
    {
        DB::transaction(function() use ($school, $plan) { 
            $school->plan_id = $plan->id;
            if($plan->price!=0)
            {
                $billing_cycle = Setting::where('name', 'Billing cycle')->first()->value;
                //renew plan
                if($billing_cycle === "year")
                    $school->plan_renews_at = date('Y-m-d', strtotime('+1 years'));
                if($billing_cycle === "month")
                    $school->plan_renews_at = date('Y-m-d', strtotime('+1 month'));
            }
            else
                $school->plan_renews_at = null;
            $school->save();
            // get current plan drivers limit
            $allowed_drivers = $school->plan->allowed_drivers;
            //get the current number of drivers in school account
            $current_drivers = $school->drivers->count();
            if($allowed_drivers==-1) //unlimited plan
            {
                //restore all removed drivers
                $driversToAdd = Driver::onlyTrashed()->where('school_id', $school->id)->get();
                Driver::whereIn('id', $driversToAdd->pluck('id'))->restore();
            }
            else 
            {
                if($current_drivers>$allowed_drivers) //downgrade
                {
                    //remove excess drivers
                    $driversToRemoveCount = $current_drivers-$allowed_drivers;
                    $driversToRemove = Driver::where('school_id', $school->id)
                                            ->latest()->take($driversToRemoveCount)->get();
                    Driver::whereIn('id', $driversToRemove->pluck('id'))->delete();
                }
                else if($current_drivers<$allowed_drivers) //upgrade
                {
                    //restore removed drivers if any
                    $driversToAddCount = $allowed_drivers - $current_drivers;
                    $driversToAdd = Driver::onlyTrashed()
                    ->where('school_id', $school->id)->latest()
                    ->take($driversToAddCount)->get();
                    Driver::whereIn('id', $driversToAdd->pluck('id'))->restore();
                }
            }
        });
    }
}