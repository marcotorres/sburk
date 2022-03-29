<?php

namespace App\Http\Controllers\Plans;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Plan;
use DB;
use App\Driver;
use App\Setting;
use App\SettingType;

use App\Http\Traits\CheckStripe;

class PlansController extends Controller
{
    use CheckStripe;

    public function __construct() {
        \Stripe\Stripe::setApiKey(Setting::where('name','Stripe Secret key')->first()->value);
    }

    /* show all plans*/
    public function showIndex (Request $request)
    {
        $can_create = false;
        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        if($is_stripe_enabled)
        {
            $can_create = $this->is_stripe_ok();
        }
        else
            $can_create = true;
        
        return view('plans.index', ['can_create' => $can_create]);
    }
    
    /* get all plans based on filter */
    public function filter (Request $request)
    {
        //get plans based on search term if any
        $query = Plan::query();
        if($request->search) {
            $query->where('name', 'LIKE', '%'.$request->search.'%');
        }
        $query->orderBy('price');
        // sort the obtained plans
        if($request->input('orderBy.direction')) {
            $plans = $query->orderBy($request->input('orderBy.column'), $request->input('orderBy.direction'))
                    ->paginate($request->input('pagination.per_page'));
        }
        else
        {
            $plans = $query->get();
        }
        return ['plans' => $plans,
        "currency" => Setting::where('name', 'Currency')->first()->value,
        "billing_cycle" => Setting::where('name', 'Billing cycle')->first()->value];
    }
    /* get specific plan */
    public function show ($plan)
    {
        return ['plan' => Plan::findOrFail($plan),
        "currency" => Setting::where('name', 'Currency')->first()->value,
        "billing_cycle" => Setting::where('name', 'Billing cycle')->first()->value];
    }
    /* get all plans */
    public function all ()
    {
        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        return [
            'plans' => Plan::orderBy('price')->get(), 
            'is_stripe_enabled' => $is_stripe_enabled,
            "currency" => Setting::where('name', 'Currency')->first()->value,
            "billing_cycle" => Setting::where('name', 'Billing cycle')->first()->value
        ];
    }
    /* delete a specific plan */
    public function destroy($plan)
    {
        $plan = Plan::with('schools')->find($plan);
        if($plan->price!=0) //not free plan
        {
            $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
            if($is_stripe_enabled)
            {
                DB::beginTransaction();
                try {
                    //delete plan from stripe
                    $stripe_plan = \Stripe\Plan::retrieve(
                        $plan->stripe_plan
                    );
                    $stripe_plan->delete();
                    //delete it from database
                    $plan->delete();
                    //save
                    DB::commit();
                }
                catch (\Stripe\Error\Base $e) {
                    DB::rollback();
                    return response()->json(['errors' => ['Stripe'=> [$e->getMessage()]]], 422);
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['errors' => ['Stripe'=> [$e->getMessage()]]], 422);
                }
            }
            else{
                if(sizeof($plan->schools)!=0)
                {
                    return response()->json(['errors' => ['Stripe'=> ["Can not delete plan because there are schools assigned to this plan!!"]]], 422);
                }
                //delete it from database
                $plan->forceDelete();
            }
        }  
    }
    /* create a new plan */
    public function store (Request $request)
    {
        // make nice names for validation
        $niceNames = [
            'allowed_drivers' => 'maximum number of drivers',
        ]; 
        //validate the request
        $this->validate($request, [
            'name' => 'required|string|unique:plans,name,'.$request->id,
            'price' => 'required|numeric|min:0|not_in:0',
            'allowed_drivers' => 'required|numeric|min:-1',
        ], [], $niceNames);

        $is_stripe_enabled = SettingType::where('name','Stripe')->first()->enabled;
        if($is_stripe_enabled)
        {
            //check if the user has stripe account first
            if(!$this->is_stripe_ok())
                return response()->json(['errors' => ['Stripe'=> ['please update your Stripe settings']]], 404);
            //save plan to stripe account and for local database
            // Start transaction!
            DB::beginTransaction();
            try {
                //create a stripe plan
                $stripe_plan = \Stripe\Plan::create([
                    'product' => Setting::where('name','Stripe product')->first()->value,
                    'nickname' => $request->name,
                    'interval' => Setting::where('name', 'Billing cycle')->first()->value,
                    'currency' => Setting::where('name', 'Currency')->first()->value,
                    'amount' => $request->price*100,
                ]);

                // create local plan on database
                $local_plan = Plan::create([
                    'name' => $request->name,
                    'price' => $request->price,
                    'allowed_drivers' => $request->allowed_drivers,
                    'stripe_plan' => $stripe_plan->id
                ]);
            }
            catch (\Stripe\Error\Base $e) {
                DB::rollback();
                return response()->json(['errors' => ['Stripe'=> [$e->getMessage()]]], 422);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['errors' => ['Stripe'=> [$e->getMessage()]]], 422);
            }
            // If we reach here, then data is valid and working.
            // Commit the queries!
            DB::commit();
            return $local_plan;
        }
        else{
            // create local plan on database
            $local_plan = Plan::create([
                'name' => $request->name,
                'price' => $request->price,
                'allowed_drivers' => $request->allowed_drivers,
            ]);
            return $local_plan;
        } 
    }
    /* update a specific plan's data. Note that the method is allowed only for Free plan*/
    public function update (Request $request)
    {
        $niceNames = [
            'allowed_drivers' => 'maximum number of drivers',
        ]; 
        $this->validate($request, [
            'name' => 'required|string|unique:plans,name,'.$request->id,
            'allowed_drivers' => 'required|numeric|min:-1',
        ], [], $niceNames);

        //update plan
        // get the local plan to be updated
        $plan = Plan::find($request->id);
        if($plan && $plan->price==0)
        {
            //update its attributes and save
            $plan->name = $request->name;
            $plan->allowed_drivers = $request->allowed_drivers;
            $plan->save();
            return $plan; 
        }
        else {
            return response()->json(['errors' => ['Plan'=> ['plan can not be updated']]], 422);
        }
    }

}
